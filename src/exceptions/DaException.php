<?php

namespace Rhymix\Modules\Da_reaction\Src\Exceptions;

class DaException extends \Rhymix\Framework\Exception
{
    protected ?string $langCode;

    public function __construct(string $message = '', ?\Throwable $previous = null)
    {
        if (!$message && $this->langCode) {
            $message = lang($this->langCode);
            if (!is_string($message)) {
                $message = $this->message;
            }
        }

        parent::__construct($message, -2, $previous);
    }
}
