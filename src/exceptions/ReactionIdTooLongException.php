<?php

namespace Rhymix\Modules\Da_reaction\Src\Exceptions;

class ReactionIdTooLongException extends DaException
{
    public function __construct(string $message = '', \Throwable $previous = null)
    {
        $this->langCode = 'da_reaction_exception_reaction_id_too_long';

        parent::__construct($message, $previous);
    }
}
