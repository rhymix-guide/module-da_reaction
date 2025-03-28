<?php

namespace Rhymix\Modules\Da_reaction\Src\Exceptions;

class ReactionLimitExceededException extends DaException
{
    protected int $reactionLimit;

    public function __construct(int $reactionLimit, string $message = '', \Throwable $previous = null)
    {
        $this->reactionLimit = $reactionLimit;
        $message = $message ?: "리액션은 최대 {$reactionLimit}개까지 가능합니다.";

        parent::__construct($message, $previous);
    }

    public function getLimit(): int
    {
        return $this->reactionLimit;
    }
}
