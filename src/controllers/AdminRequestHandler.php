<?php
declare(strict_types=1);

namespace Rhymix\Modules\Da_reaction\Src\Controllers;

use Context;
use MemberModel;
use Rhymix\Modules\Da_reaction\Src\ModuleBase;

class AdminRequestHandler extends ModuleBase
{
    public function dispDa_reactionAdminConfig(): void
    {

        // 현재 설정 상태 불러오기
        $config = self::getConfig();

        Context::set('daReactionConfig', $config);

        $group_list = MemberModel::getGroups();
        foreach ($group_list ?: [] as $group) {
            $group->title = Context::replaceUserLang($group->title, true);
        }
        Context::set('group_list', $group_list);

        $this->setTemplatePath("{$this->module_path}views/admin/");
        $this->setTemplateFile('config');
    }

    public function procDa_reactionAdminSaveConfig(): void
    {
        // 현재 설정 상태 불러오기
        $config = self::getConfig();

        $vars = (array) Context::getRequestVars();

        $config->setVars($vars);

        // 변경된 설정을 저장
        $result = $config->save();

        $this->setMessage('success_saved');
        $this->setRedirectUrl(getNotEncodedUrl('', 'module', 'admin', 'act', 'dispDa_reactionAdminConfig'));
    }

    public function procDa_reactionAdminSaveModuleConfig(): void
    {
        $moduleSrl = intval(Context::get('target_module_srl') ?? 0);

        // 현재 설정 상태 불러오기
        $config = ModuleBase::getPartConfig($moduleSrl);


        $vars = Context::getRequestVars();
        $vars->ignore_part_config ??= false;

        dp($vars);
        $config->setVars((array) $vars);

        // 변경된 설정을 저장
        $config->save();

        $this->setMessage('success_saved');
        $this->setRedirectUrl(Context::get('success_return_url'));
    }
}
