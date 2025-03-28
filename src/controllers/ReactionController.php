<?php
declare(strict_types=1);

namespace Rhymix\Modules\Da_reaction\Src\Controllers;

use CommentModel;
use DocumentModel;
use Rhymix\Framework\Exceptions\MustLogin;
use Rhymix\Framework\Helpers\SessionHelper;
use Rhymix\Modules\Da_reaction\Src\Exceptions\CannotReactToOwnTargetException;
use Rhymix\Modules\Da_reaction\Src\Exceptions\DaException;
use Rhymix\Modules\Da_reaction\Src\Exceptions\ReactionLimitExceededException;
use Rhymix\Modules\Da_reaction\Src\Models\ReactionConfig;
use Rhymix\Modules\Da_reaction\Src\Models\ReactionModel;
use Rhymix\Modules\Da_reaction\Src\ModuleBase;
use Rhymix\Modules\Da_reaction\Src\ReactionHelper;

class ReactionController extends ModuleBase
{
    /**
     * 리액션 추가 및 취소
     *
     * @throws MustLogin
     * @throws ReactionLimitExceededException
     */
    public static function react(ReactionConfig $config, SessionHelper $member, string $reactionMode, string $reaction, string $targetId, string $parentId): bool
    {
        if (!$member->isMember()) {
            throw new MustLogin();
        }

        ReactionHelper::validateReactionId($reaction);
        ReactionHelper::validateTargetId($targetId);
        if ($parentId) {
            ReactionHelper::validateTargetId($parentId);
        }

        $targetInfo = ReactionHelper::parseTargetId($targetId);

        // 리액션 제한 확인
        $reactable = ReactionModel::reactable($config, $member, $reactionMode, $targetId, $reaction);

        if ($reactable === ModuleBase::NOT_REACTABLE) {
            throw new DaException('리액션을 추가할 수 없습니다.');
        }

        if (
            $reactionMode === 'toggle'
            && $reactable ^ ModuleBase::REACTABLE_ADD
            && $reactable & ModuleBase::REACTABLE_REVOKE
        ) {
            return ReactionModel::revokeReaction($member->member_srl, $reaction, $targetId);

        } else if ($reactable & ModuleBase::REACTABLE_ADD) {
            try {
                if (!$member->isAdmin()) {
                    $config->reactable($member);
                }
            } catch (DaException $e) {
                throw new DaException("리액션 할 수 없습니다. ({$e->getmessage()})", $e);
            }

            if (!$member->isAdmin() && !$config->reaction_self) {
                if ($targetInfo['type'] === 'document') {
                    $documentItem = DocumentModel::getDocument($targetInfo['document_srl']);
                    if ($documentItem->get('member_srl') === $member->member_srl) {
                        throw new CannotReactToOwnTargetException('자신의 글에는 리액션할 수 없습니다.');
                    }
                } else if ($targetInfo['type'] === 'comment') {
                    $commentItem = CommentModel::getComment($targetInfo['comment_srl']);
                    if ($commentItem->get('member_srl') === $member->member_srl) {
                        throw new CannotReactToOwnTargetException('자신의 댓글에는 리액션할 수 없습니다.');
                    }
                }
            }

            return ReactionModel::addReaction($member->member_srl, $reaction, $targetId, $parentId);
        }

        return false;
    }
}
