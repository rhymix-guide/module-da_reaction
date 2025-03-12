<?php
declare(strict_types=1);

namespace Rhymix\Modules\Da_reaction\Src\Controllers;

use Context;
use Rhymix\Framework\Exception;
use Rhymix\Framework\Session;
use Rhymix\Modules\Da_reaction\Src\Models\ReactionModel;
use Rhymix\Modules\Da_reaction\Src\ModuleBase;
use Rhymix\Modules\Da_reaction\Src\ReactionHelper;

/**
 * 라이믹스 요청 핸들러
 */
class RequestHandler extends ModuleBase
{
    /**
     * 리액션 추가 및 취소 요청 처리
     */
    public function procDa_reactionReact(): void
    {
        $reaction = Context::get('reaction');
        $reactionMode = Context::get('reactionMode') ?? 'toggle';
        $targetId = Context::get('targetId');
        $parentId = Context::get('parentId');

        $targetInfo = ReactionHelper::parseTargetId($targetId);
        $moduleSrl = $targetInfo['module_srl'];

        $member = Session::getMemberInfo();
        $config = $moduleSrl ? static::getPartConfig($moduleSrl) : static::getConfig();

        try {
            ReactionController::react($config, $member, $reactionMode, $reaction, $targetId, $parentId);
        } catch (Exception $e) {
            $this->setError(-1);
            $this->setMessage($e->getMessage(), 'error');
            return;
        }

        // 요청 처리 후 대상의 리액션 데이터 반환
        $reactions = ReactionModel::getReactions($targetId, $member->member_srl);
        $this->set('reactions', $reactions[$targetId]);
    }
}
