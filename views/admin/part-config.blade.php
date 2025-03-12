<?php
/** @var \Rhymix\Modules\Da_reaction\Src\Models\ReactionPartConfig $daReactionPartConfig */
?>

<section class="section">
    <h1>리액션 설정</h1>

    <form action="./" method="post" class="x_form-horizontal">
        <input type="hidden" name="module" value="da_reaction" />
        <input type="hidden" name="act" value="procDa_reactionAdminSaveModuleConfig" />
        <input type="hidden" name="success_return_url" value="{getRequestUriByServerEnviroment()}" />
        <input type="hidden" name="target_module_srl" value="{$module_info->module_srl ?: $module_srls}" />

        <div class="x_control-group">
            <label class="x_control-label">기본 설정 사용</label>
            <div class="x_controls">
                <label class="x_inline">
                    <input type="checkbox" name="ignore_part_config" value="Y" @checked($daReactionPartConfig->ignore_part_config)> 리액션 모듈의 기본 설정을 사용
                </label>
            </div>
        </div>

        <div class="x_control-group">
            <label class="x_control-label">리액션 기능 사용</label>
            <div class="x_controls">
                <label class="x_inline">
                    <input type="radio" name="enable" value="Y" @checked($daReactionPartConfig->enable)> 사용
                </label>
                <label class="x_inline">
                    <input type="radio" name="enable" value="N" @checked(!$daReactionPartConfig->enable)> 사용 안 함
                </label>
            </div>
        </div>

        <div class="x_control-group">
            <label class="x_control-label">리액션 버튼 자동 추가 - 글</label>
            <div class="x_controls">
                <label class="x_inline">
                    <input type="radio" name="document_insert_position" value="before" @checked($daReactionPartConfig->document_insert_position === 'before') /> 위
                </label>
                <label class="x_inline">
                    <input type="radio" name="document_insert_position" value="after" @checked($daReactionPartConfig->document_insert_position === 'after') /> 아래
                </label>
                <label class="x_inline">
                    <input type="radio" name="document_insert_position" value="disable" @checked($daReactionPartConfig->document_insert_position === 'disable') /> 안함
                </label>
            </div>
        </div>

        <div class="x_control-group">
            <label class="x_control-label">리액션 버튼 자동 추가 - 댓글</label>
            <div class="x_controls">
                <label class="x_inline">
                    <input type="radio" name="comment_insert_position" value="before" @checked($daReactionPartConfig->comment_insert_position === 'before') /> 위
                </label>
                <label class="x_inline">
                    <input type="radio" name="comment_insert_position" value="after" @checked($daReactionPartConfig->comment_insert_position === 'after') /> 아래
                </label>
                <label class="x_inline">
                    <input type="radio" name="comment_insert_position" value="disable" @checked($daReactionPartConfig->comment_insert_position === 'disable') /> 안함
                </label>
            </div>
        </div>

        <div class="x_control-group">
            <label class="x_control-label">리액션 제한 횟수</label>
            <div class="x_controls">
                <input type="number" min="1" name="reaction_limit" placeholter="20" value="{{ $daReactionPartConfig->reaction_limit }}" />
            </div>
        </div>

        <div class="x_control-group">
            <label class="x_control-label">자신의 글, 댓글에 리액션 허용</label>
            <div class="x_controls">
                <label><input type="radio" name="reaction_self" value="Y" @checked($daReactionPartConfig->reaction_self) /> 허용 - 자신의 글, 댓글에 리액션 가능</label>
                <label><input type="radio" name="reaction_self" value="N" @checked(!$daReactionPartConfig->reaction_self) /> 허용하지 않음</label>
            </div>
        </div>

        <div class="x_control-group">
            <label class="x_control-label">리액션 허용 그룹</label>
            <div class="x_controls">
                @foreach ($group_list as $group)
                    <label class="x_inline">
                        <input type="checkbox" name="reaction_allows[]" value="{{ $group->group_srl }}" @checked(in_array($group->group_srl, $daReactionPartConfig->getAllowGroups()))> {{ $group->title }}
                    </label>
                @endforeach
                <p class="x_help-block">그룹을 선택하지않으면 로그인 사용자에게 허용됩니다.</p>
            </div>
        </div>

        <div class="btnArea">
            <button class="x_btn x_btn-primary" type="submit">{$lang->cmd_save}</button>
        </div>
    </form>

    <script>
        jQuery(function ($) {
            $('input[name="ignore_part_config"]').on('change', function () {
                const $this = $(this);

                $this.closest('.x_control-group').nextAll('.x_control-group').toggle(!$this.prop('checked'));
            }).trigger('change');
        });
    </script>
</section>