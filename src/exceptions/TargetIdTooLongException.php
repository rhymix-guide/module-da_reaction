<?php

namespace Rhymix\Modules\Da_reaction\Src\Exceptions;

class TargetIdTooLongException extends DaException
{
    public function __construct(string $message = '', ?\Throwable $previous = null)
    {
        $this->langCode = 'da_reaction_exception_target_id_too_long';

        parent::__construct($message, $previous);
    }
}
