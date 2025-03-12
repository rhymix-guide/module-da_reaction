<?php
declare(strict_types=1);

namespace Rhymix\Modules\Da_reaction\Src\Models;

use CommentModel;
use DocumentModel;
use Rhymix\Framework\DB;
use Rhymix\Framework\Exception;
use Rhymix\Framework\Exceptions\MustLogin;
use Rhymix\Framework\Helpers\SessionHelper;
use Rhymix\Modules\Da_reaction\Src\Exceptions\CannotReactToOwnTargetException;
use Rhymix\Modules\Da_reaction\Src\ModuleBase;
use Rhymix\Modules\Da_reaction\Src\ReactionHelper;

/**
 * @template TReactionItem of array{
 *     reaction: string,
 *     type: string,
 *     id: string,
 *     count: int,
 *     choose: bool,
 * }
 */
class ReactionModel extends ModuleBase
{
    public function __construct()
    {
    }

    /**
     * @return string[]
     */
    public static function getLogs(string $targetId, int $memberSrl): array
    {
        $oDB = DB::getInstance();

        try {
            $stmt = $oDB->query(
                'SELECT `reaction`
                    FROM `da_reaction_choose`
                    WHERE
                        `member_srl` = ?
                        AND `target_id` = ?
                ',
                $memberSrl,
                $targetId,
            );
            if (!$stmt) {
                throw new Exception('테이블이 생성되어있지 않습니다.');
            }
            $result = $stmt->fetchAll();
        } catch (\Exception $e) {
            return [];
        }

        $reactions = [];

        array_map(function ($row) use (&$reactions) {
            $reactions[] = $row->reaction;
        }, $result);

        return $reactions;
    }

    /**
     * @return array<string,string[]>
     */
    public static function getLogsByParentId(string $parentId, int $memberSrl): array
    {
        $oDB = DB::getInstance();

        try {
            $stmt = $oDB->query(
                'SELECT `reaction`, `target_id`
                    FROM `da_reaction_choose`
                    WHERE
                        `member_srl` = ?
                        AND `parent_id` = ?
                ',
                $memberSrl,
                $parentId,
            );
            if (!$stmt) {
                throw new Exception('테이블이 생성되어있지 않습니다.');
            }
            $result = $stmt->fetchAll();
        } catch (\Exception $e) {
            return [];
        }

        $reactions = [];
        array_map(function ($row) use (&$reactions) {
            if (!is_array($reactions[$row->target_id] ?? null)) {
                $reactions[$row->target_id] = [];
            }
            $reactions[$row->target_id][] = $row->reaction;
        }, $result);

        return $reactions;
    }

    /**
     * 대상의 리액션 목록 반환
     * @return array<string,TReactionItem[]>
     */
    public static function getReactions(string $targetId, ?int $memberSrl): array
    {
        $reactions = [];

        try {
            $stmt = DB::getInstance()->query(
                'SELECT `parent_id`, `target_id`, `reaction`, `reaction_count`
                    FROM `da_reaction`
                    WHERE `target_id` = ?
                    ORDER BY `id` ASC
                ',
                $targetId
            );
            if (!$stmt) {
                throw new Exception('테이블이 생성되어있지 않습니다.');
            }
            $result = $stmt->fetchAll();
        } catch (\Exception $e) {
            return [];
        }

        $memberActions = [];
        if ($memberSrl) {
            $memberActions = self::getLogs($targetId, $memberSrl);
        }

        foreach ($result as $row) {
            $reactionData = ReactionHelper::parseReaction($row->reaction, intval($row->reaction_count ?? 0));
            if ($memberSrl && $memberActions) {
                $reactionData['choose'] = in_array($reactionData['reaction'], $memberActions);
            }
            $reactions[$row->target_id][] = $reactionData;
        }

        return $reactions;
    }

    /**
     * 대상의 리액션 목록 반환
     * @return array<string,TReactionItem[]>
     */
    public static function getReactionsByParentId(string $parentId, ?int $memberSrl): array
    {
        $reactions = [];

        try {
            $stmt = DB::getInstance()->query(
                'SELECT `parent_id`, `target_id`, `reaction`, `reaction_count`
                    FROM `da_reaction`
                    WHERE `parent_id` = ?
                ',
                $parentId
            );
            if (!$stmt) {
                throw new Exception('테이블이 생성되어있지 않습니다.');
            }
            $result = $stmt->fetchAll();
        } catch (\Exception $e) {
            return [];
        }

        $memberActions = [];
        if ($memberSrl) {
            $memberActions = self::getLogsByParentId($parentId, $memberSrl);
        }

        foreach ($result as $row) {
            $reactionData = ReactionHelper::parseReaction($row->reaction, intval($row->reaction_count ?? 0));
            if ($memberSrl && $memberActions) {
                $reactionData['choose'] = in_array($reactionData['reaction'], $memberActions[$row->target_id] ?? []);
            }
            $reactions[$row->target_id][] = $reactionData;
        }

        return $reactions;
    }

    /**
     * 리액션을 추가, 취소할 수 있는지 반환
     *
     * - 추가 및 취소 불가능: 0 (ModuleBase::NOT_REACTABLE)
     * - 추가 가능: 1 (ModuleBase::REACTABLE_ADD)
     * - 취소 가능: 2 (ModuleBase::REACTABLE_REVOKE)
     * - 추가 및 취소 가능: 3 (ModuleBase::REACTABLE)
     *
     * @throws CannotReactToOwnTargetException
     */
    public static function reactable(ReactionConfig $config, SessionHelper $member, string $targetId, string $reaction): int
    {
        $reactable = ModuleBase::NOT_REACTABLE;

        $reactionLimit = $config->reaction_limit;

        if (!$member->isMember()) {
            throw new MustLogin();
        }

        $db = DB::getInstance();
        try {
            // 리액션 횟수 제한 확인
            $table = ModuleBase::$tableReaction;
            $result = $db->query(
                "SELECT COUNT(*) AS `reactionRows` FROM `{$table}` WHERE `target_id` = ?",
                $targetId
            );
            $reactionRows = $result->fetch()->reactionRows ?? 0;
            $result = null;

            // 토글 모드일 때 취소할 수 있는지 확인
            $table = ModuleBase::$tableReactionChoose;
            $result = $db->query(
                "SELECT COUNT(*) AS `count` FROM `{$table}` WHERE `member_srl` = ? AND `target_id` = ? AND `reaction` = ?",
                $member->member_srl,
                $targetId,
                $reaction,
            );
            $choose = boolval($result->fetch()->count ?? 0);
            $result = null;
        } catch (\Exception $e) {
            $message = $member->isAdmin() ? "관리페이지에서 모듈이 설치되었는지 확인하세요. {$e->getMessage()}" : '리액션을 추가할 수 없습니다.';
            throw new Exception($message, 0, $e);
        }

        if ($choose) {
            $reactable |= ModuleBase::REACTABLE_REVOKE;
        } else if ($reactionRows < $reactionLimit) {
            $reactable |= ModuleBase::REACTABLE_ADD;
        } else if ($member->isAdmin()) {
            $reactable |= ModuleBase::REACTABLE_ADD;
        }

        return $reactable;
    }

    /**
     * 리액션 추가
     */
    public static function addReaction(int $memberSrl, string $reaction, string $targetId, ?string $parentId): bool
    {
        ReactionHelper::validateReactionId($reaction);
        ReactionHelper::validateTargetId($targetId);
        if ($parentId !== null) {
            ReactionHelper::validateTargetId($parentId);
        }

        $datetime = date('YmdHis');
        $db = DB::getInstance();

        try {
            // 로그
            $table = ModuleBase::$tableReactionChoose;
            $db->query(
                "INSERT INTO `{$table}`
                    SET
                        `member_srl` = ?,
                        `reaction` = ?,
                        `target_id` = ?,
                        `parent_id` = ?,
                        `regdate` = ?
                ",
                $memberSrl,
                $reaction,
                $targetId,
                $parentId,
                $datetime,
            );
        } catch (Exception $e) {
            throw new Exception('리액션 기록에 실패했습니다.', 0, $e);
        }

        // 리액션 카운트 업데이트 및 추가
        $table = ModuleBase::$tableReaction;
        // 카운트 증가
        $result = $db->query(
            "UPDATE `{$table}`
                SET
                    `reaction_count` = `reaction_count` + 1
                WHERE
                    `reaction` = ?
                    AND `target_id` = ?
                    AND `parent_id` = ?
            ",
            $reaction,
            $targetId,
            $parentId,
        );
        // 없으면 추가
        if (!$result->rowCount()) {
            $result = $db->query(
                "INSERT INTO `{$table}`
                    SET
                        `reaction` = ?,
                        `target_id` = ?,
                        `parent_id` = ?,
                        `reaction_count` = 1
                ",
                $reaction,
                $targetId,
                $parentId,
            );
        }

        return true;
    }

    public static function revokeReaction(int $memberSrl, string $reaction, string $targetId): bool
    {
        ReactionHelper::validateReactionId($reaction);
        ReactionHelper::validateTargetId($targetId);

        $db = DB::getInstance();

        // choose 테이블에서 리액션 삭제
        $table = ModuleBase::$tableReactionChoose;
        $result = $db->query(
            "DELETE FROM `{$table}` WHERE `member_srl` = ? AND `target_id` = ? AND `reaction` = ?",
            $memberSrl,
            $targetId,
            $reaction,
        );

        if ($result->rowCount()) {
            // reaction 테이블에서 카운트 감소
            $table = ModuleBase::$tableReaction;
            $result = $db->query(
                "UPDATE `{$table}`
                    SET
                        `reaction_count` = `reaction_count` - 1
                    WHERE
                        `reaction` = ?
                        AND `target_id` = ?
                ",
                $reaction,
                $targetId,
            );

            // 리액션이 없는 경우 삭제
            $db->query(
                "DELETE FROM `{$table}`
                    WHERE
                        `target_id` = ?
                        AND `reaction` = ?
                        AND `reaction_count` <= 0
                ",
                $targetId,
                $reaction,
            );
        }

        return true;
    }
}
