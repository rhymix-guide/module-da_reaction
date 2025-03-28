<?php
declare(strict_types=1);

namespace Rhymix\Modules\Da_reaction\Src\Controllers;

use Context;
use MemberModel;
use Rhymix\Modules\Da_reaction\Src\ModuleBase;

class AdminRequestHandler extends ModuleBase
{
    /**
     * 관리자 설정 페이지
     */
    public function dispDa_reactionAdminConfig(): void
    {
        // 현재 설정 상태 불러오기
        $config = self::getConfig();

        // 그룹제한 설정을 위한 회원그룹 목록
        $group_list = MemberModel::getGroups();
        foreach ($group_list ?: [] as $group) {
            $group->title = Context::replaceUserLang($group->title, true);
        }

        Context::set('daReactionConfig', $config);
        Context::set('group_list', $group_list);

        $this->setTemplatePath("{$this->module_path}views/admin/");
        $this->setTemplateFile('config');
    }

    /**
     * 모듈 설정 저장
     */
    public function procDa_reactionAdminSaveConfig(): void
    {
        $vars = (array) Context::getRequestVars();

        $config = self::getConfig();
        $config->setVars($vars);
        $config->save();

        $this->setMessage('success_saved');
        $this->setRedirectUrl(getNotEncodedUrl('', 'module', 'admin', 'act', 'dispDa_reactionAdminConfig'));
    }

    /**
     * 게시판 설정에서 모듈 설정 저장
     */
    public function procDa_reactionAdminSaveModuleConfig(): void
    {
        $moduleSrl = intval(Context::get('target_module_srl') ?? 0);

        /** @var \stdClass $vars */
        $vars = Context::getRequestVars();
        $vars->ignore_part_config ??= false;

        $config = ModuleBase::getPartConfig($moduleSrl);
        $config->setVars((array) $vars);
        $config->save();

        $this->setMessage('success_saved');
        $this->setRedirectUrl(Context::get('success_return_url'));
    }
}
