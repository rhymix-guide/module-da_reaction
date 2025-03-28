<?php

namespace Rhymix\Modules\Da_reaction\Src\Exceptions;

class CannotReactToOwnTargetException extends DaException
{
    public function __construct(string $message = '', ?\Throwable $previous = null)
    {
        $message = $message ?: '리액션 할 수 없습니다.';

        parent::__construct($message, $previous);
    }
}
