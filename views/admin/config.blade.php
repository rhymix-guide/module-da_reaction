<?php
/** @var \Rhymix\Modules\Da_reaction\Src\Models\ReactionConfig $daReactionConfig */
?>

@json($daReactionConfig->gets());

@if($XE_VALIDATOR_MESSAGE && $XE_VALIDATOR_ID === 'da_reactioin-admin-config')
    <div class="message {$XE_VALIDATOR_MESSAGE_TYPE}">
        <p>{$XE_VALIDATOR_MESSAGE}</p>
    </div>
@endif

<form action="./" method="post" class="x_form-horizontal">
    <input type="hidden" name="module" value="admin" />
    <input type="hidden" name="act" value="procDa_reactionAdminSaveConfig" />
    <input type="hidden" name="xe_validator_id" value="da_reactioin-admin-config" />

    <section class="section">
        <h2>리액션 모듈 설정</h2>

        <div class="x_control-group">
            <label class="x_control-label">리액션 기능 사용</label>
            <div class="x_controls">
                <label class="x_inline">
                    <input type="radio" name="enable" value="Y" @checked($daReactionConfig->enable)> 사용
                </label>
                <label class="x_inline">
                    <input type="radio" name="enable" value="N" @checked(!$daReactionConfig->enable)> 모듈 비활성화
                </label>
            </div>
        </div>

        <div class="x_control-group">
            <label class="x_control-label">리액션 버튼 자동 추가 - 글</label>
            <div class="x_controls">
                <label class="x_inline">
                    <input type="radio" name="document_insert_position" value="before" @checked($daReactionConfig->document_insert_position === 'before') /> 위
                </label>
                <label class="x_inline">
                    <input type="radio" name="document_insert_position" value="after" @checked($daReactionConfig->document_insert_position === 'after') /> 아래
                </label>
                <label class="x_inline">
                    <input type="radio" name="document_insert_position" value="disable" @checked($daReactionConfig->document_insert_position === 'disable') /> 안함
                </label>
            </div>
        </div>

        <div class="x_control-group">
            <label class="x_control-label">리액션 버튼 자동 추가 - 댓글</label>
            <div class="x_controls">
                <label class="x_inline">
                    <input type="radio" name="comment_insert_position" value="before" @checked($daReactionConfig->comment_insert_position === 'before') /> 위
                </label>
                <label class="x_inline">
                    <input type="radio" name="comment_insert_position" value="after" @checked($daReactionConfig->comment_insert_position === 'after') /> 아래
                </label>
                <label class="x_inline">
                    <input type="radio" name="comment_insert_position" value="disable" @checked($daReactionConfig->comment_insert_position === 'disable') /> 안함
                </label>
            </div>
        </div>

        <div class="x_control-group">
            <label class="x_control-label">리액션 제한 횟수</label>
            <div class="x_controls">
                <input type="number" min="1" name="reaction_limit" placeholter="20" value="{{ $daReactionConfig->reaction_limit }}" />
            </div>
        </div>

        <div class="x_control-group">
            <label class="x_control-label">자신의 글, 댓글에 리액션 허용</label>
            <div class="x_controls">
                <label><input type="radio" name="reaction_self" value="Y" @checked($daReactionConfig->reaction_self) /> 허용 - 자신의 글, 댓글에 리액션 가능</label>
                <label><input type="radio" name="reaction_self" value="N" @checked(!$daReactionConfig->reaction_self) /> 허용하지 않음</label>
            </div>
        </div>

        <div class="x_control-group">
            <label class="x_control-label">리액션 허용 그룹</label>
            <div class="x_controls">
                @foreach ($group_list as $group)
                    <label class="x_inline">
                        <input type="checkbox" name="reaction_allows[]" value="{{ $group->group_srl }}" @checked(in_array($group->group_srl, $daReactionConfig->getAllowGroups()))> {{ $group->title }}
                    </label>
                @endforeach
                <p class="x_help-block">그룹을 선택하지않으면 로그인 사용자에게 허용됩니다.</p>
            </div>
        </div>
    </section>

    <div class="x_clearfix btnArea">
        <div class="x_pull-left">
        </div>
        <div class="x_pull-right">
            <button type="submit" class="x_btn x_btn-primary">{$lang->cmd_save}</button>
        </div>
    </div>
</form>