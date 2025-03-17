<?php
declare(strict_types=1);

namespace Rhymix\Modules\Da_reaction\Src;

use BaseObject;
use Context;
use MemberModel;
use ModuleHandler;
use Rhymix\Framework\Exception;
use Rhymix\Framework\Session;
use Rhymix\Modules\Da_reaction\Src\Models\ReactionModel;
use TemplateHandler;

class EventHandler extends ModuleBase
{
    /**
     * 트리거를 이용해 관리자 대시보드에 출력하는 예제
     *
     * @see \ModuleHandler::triggerCall()
     */
    public function adminDashboard(object $object): void
    {
        $html = <<<HTML
        <section style="background-color: #eff6ff;">
            <h2>Example1 모듈</h2>
            <p style="padding: 10px;">Example1 모듈의 `EventHandler::adminDashboard()`에서 출력</p>
            <p style="padding: 10px;">Example1 모듈이 활성화되면 출력됨</p>
        </section>
        HTML;

        array_unshift($object->right, $html);
    }

    /**
     * @param object $object
     */
    public function ListenerModuleHandlerProcAfter($object): void
    {
        if (Context::getResponseMethod() !== 'HTML') {
            return;
        }

        // FIXME
        if ($object->module !== 'board' || $object->act !== 'dispBoardContent') {
            return;
        }

        $moduleSrl = intval($object->module_srl);
        $config = ModuleBase::getPartConfig($moduleSrl);

        if ($config->enable === false) {
            return;
        }

        $requestDocumentSrl = intval(Context::get('document_srl'));

        if (!$requestDocumentSrl) {
            return;
        }

        $member = Session::getMemberInfo();
        $memberSrl = $member->member_srl;
        $isAdmin = $member->isAdmin();
        $memberInfo = json_encode([
            'memberId' => $memberSrl,
            'isAdmin' => $isAdmin,
        ]);


        $reactions = ReactionModel::getReactionsByParentId(
            ReactionHelper::generateIdByDocument($moduleSrl, intval($requestDocumentSrl)),
            $memberSrl
        );

        $modulePath = ModuleHandler::getModulePath('da_reaction');
        Context::loadFile(["{$modulePath}public/assets/alpinejs.3.14.8.min.js", 'body']);
        Context::loadFile(["{$modulePath}public/assets/da_reaction.js", 'head']);
        Context::loadFile(["{$modulePath}public/assets/da_reaction.css"]);

        $data = [
            'categories' => [
                ['category' => 'emoji', 'title' => '이모지', 'renderType' => 'emoji'],
                ['category' => 'image', 'title' => '이미지', 'renderType' => 'image'],
                ['category' => 'import-image', 'title' => '이미지', 'renderType' => 'image'],
            ],
            'emoticons' => [],
            'alias' => [],
        ];
        $custom = ModuleBase::loadCustomConfig();
        $data = array_merge_recursive($data, $custom);
        if (!$data['emoticons']) {
            $data['emoticons'] = [
                ['reaction' => 'emoji:1f44d'], // 👍
                ['reaction' => 'emoji:1f44e'], // 👎
                ['reaction' => 'emoji:1f606'], // 😆
                ['reaction' => 'emoji:1f62e'], // 😮
                ['reaction' => 'emoji:1f622'], // 😢
                ['reaction' => 'emoji:1f621'], // 😡
            ];
        }
        $data['emoticons'] = array_merge($data['emoticons'], ReactionHelper::getImportImages());


        $reactionModel = new ReactionModel();

        $reactionData = $reactionModel->getReactionsByParentId("document:{$moduleSrl}:{$requestDocumentSrl}", $memberSrl);

        $categories = json_encode($data['categories']);
        $emoticons = json_encode($data['emoticons']);
        $alias = json_encode($data['alias']);
        $endpoints = json_encode([
            'react' => getNotEncodedUrl('', 'module', 'da_reaction', 'act', 'procDa_reactionReact'),
        ]);
        $reactionData = json_encode($reactionData);

        $reactable = true;

        try {
            $reactable = $config->reactable($member);
        } catch (Exception $e) {
            $reactable = false;
        }

        $config = json_encode([
            'reactable' => $reactable,
            'reactionLimit' => $config->reaction_limit,
            'reactionSelf' => $config->reaction_self,
        ]);

        $headContent = <<<HTML
        <script>
        document.addEventListener('daReaction:init', function (e) {
            const daReaction = e.detail;
            daReaction.setOptions({$config});
            daReaction.setMemberInfo({$memberInfo});
            daReaction.setEndpoints({$endpoints});
            daReaction.addCategories({$categories});
            daReaction.addEmoticons({$emoticons});
            daReaction.addAlias({$alias});
            daReaction.setReactions({$reactionData});
        });
        </script>
        HTML;

        $headContent .= <<<'HTML'
        <div x-data="daReactionPopover" class="da-reaction-popover" x-show="open" tabindex="-1" x-ref="daReactionPopover" x-cloak x-transition @click.outside="hide()" aria-hidden="true">
            <div class="da-reaction">
                <template x-for="item in emoticons" :key="item.reaction">
                    <span role="button" tabindex="0" class="da-reaction__badge" @click="react(item.reaction, 'add')" @keyup.enter="react(item.reaction, 'add')">
                        <template x-if="item.renderType === 'emoji'">
                            <span class="da-reaction__emoji" x-text="item.emoji"></span>
                        </template>
                        <template x-if="item.renderType === 'image'">
                            <img x-bind:src="item.url" loading="lazy" :alt="`이모티콘: ${item.reaction}`" />
                        </template>
                    </span>
                </template>
            </div>
        </div>
        HTML;

        Context::addHtmlHeader($headContent);

        /** @var \ModuleController */
        $moduleControler = getController('module');
        $moduleControler->addTriggerFunction('display', 'after', [$this, 'ListenerDisplay']);
    }

    public function ListenerDisplay(string &$content): void
    {
        if (Context::getResponseMethod() !== 'HTML') {
            return;
        }

        $moduleInfo = Context::get('current_module_info');
        $moduleSrl = intval($moduleInfo->module_srl);
        $documentSrl = null;

        if ($moduleInfo->module !== 'board') {
            return;
        }

        $config = ModuleBase::getPartConfig($moduleInfo->module_srl);

        $requestDocumentSrl = intval(Context::get('document_srl'));

        if ($requestDocumentSrl) {
            preg_match_all('/<!--AfterDocument\((?<srl>[0-9]+),([0-9]+)\)-->/', $content, $matches);
            $documentSrl = $matches['srl'][0] ?? null;
        }

        if (!$documentSrl) {
            return;
        }

        // 문서 리액션
        if ($config->document_insert_position !== 'disable') {
            if (stripos($content, "daReaction('" . ReactionHelper::generateIdByDocument($moduleSrl, intval($documentSrl)) . "'") === false) {
                $targetId = ReactionHelper::generateIdByDocument($moduleSrl, intval($documentSrl));
                $tag = '<div class="da-reaction" x-da-reaction-print x-data="daReaction(\'' . $targetId . '\')"></div>';
                $position = $config->document_insert_position;
                $content = preg_replace(
                    '/<!--' . ucfirst($position) . 'Document\([0-9]+,[0-9]+\)-->/',
                    $position === 'before' ? "\$0{$tag}" : "{$tag}\$0",
                    $content
                ) ?? $content;
            }
        }

        // 댓글 리액션
        if ($config->comment_insert_position !== 'disable') {
            preg_match_all('/<!--AfterComment\((?<srl>[0-9]+),([0-9]+)\)-->/', $content, $matches);
            $commentSrlArray = $matches['srl'];
            foreach ($commentSrlArray as $commentSrl) {
                $targetId = ReactionHelper::generateIdByComment($moduleSrl, intval($commentSrl), intval($documentSrl));
                if (strpos($content, "daReaction('{$targetId}'") === false) {
                    $tag = '<div class="da-reaction" x-da-reaction-print x-data="daReaction(\'' . $targetId . '\')"></div>';
                    $position = $config->comment_insert_position;
                    $content = preg_replace(
                        '/<!--' . ucfirst($position) . 'Comment\(' . $commentSrl . ',[0-9]+\)-->/',
                        $position === 'before' ? "\$0{$tag}" : "{$tag}\$0",
                        $content
                    ) ?? $content;
                }
            }
        }
    }

    public function ListenerModuleDispAdditionSetup(string &$content): void
    {
        $moduleConfig = ModuleBase::getConfig();

        if (!$moduleConfig->enable) {
            return;
        }

        $current_module_srl = Context::get('module_srl');
        if (!$current_module_srl) {
            $current_module_srl = Context::get('current_module_info')->module_srl ?? 0;
            if (!$current_module_srl) {
                return;
            }
        }

        $current_module_srl = intval($current_module_srl);

        $partConfig = ModuleBase::getPartConfig($current_module_srl);

        $group_list = MemberModel::getGroups();
        foreach ($group_list ?: [] as $group) {
            $group->title = Context::replaceUserLang($group->title, true);
        }
        Context::set('group_list', $group_list);

        Context::set('daReactionPartConfig', $partConfig);

        $oTemplate = TemplateHandler::getInstance();
        $tpl = $oTemplate->compile("{$this->module_path}views/admin/", 'part-config');
        $content .= $tpl;
    }

    /**
     * @see \DocumentAdminController::moveDocumentModule()
     */
    public function listenerAfterDocumentMoveDocumentModule(\stdClass $args): BaseObject
    {
        $output = new BaseObject();

        foreach ($args->document_list as $document) {
            $originModuleSrl = $document->module_srl;
            $newModuleSrl = $args->module_srl;
            $documentSrl = $document->document_srl;

            $originTargetId = ReactionHelper::generateIdByDocument($originModuleSrl, $documentSrl);
            $newTargetId = ReactionHelper::generateIdByDocument($newModuleSrl, $documentSrl);

            ReactionModel::moveDocumentReaction(
                $originTargetId,
                $newTargetId,
                $newTargetId,
            );


            $db = \DB::getInstance();

            // 댓글 이동
            $table = ModuleBase::$tableReaction;
            $result = $db->query("SELECT * FROM `{$table}` WHERE `parent_id` = ?", [$originTargetId]);
            $result = $result->fetchAll();
            foreach ($result as $row) {
                $targetInfo = ReactionHelper::parseTargetId($row->target_id);
                $originTargetId = ReactionHelper::generateIdByComment($originModuleSrl, $targetInfo['comment_srl'], null);
                $newTargetId = ReactionHelper::generateIdByComment($newModuleSrl, $targetInfo['comment_srl'], null);
                $newParentId = ReactionHelper::generateIdByDocument($newModuleSrl, $documentSrl);

                ReactionModel::moveCommentReaction(
                    $originTargetId,
                    $newTargetId,
                    $newParentId,
                );
            }
        }

        return $output;
    }
}
