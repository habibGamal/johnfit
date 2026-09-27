<?php

namespace App\Exceptions;

use Exception;

class PlanGenerationNotAllowedException extends Exception
{
    /**
     * @param string $message
     * @param int $code
     * @param \Throwable|null $previous
     */
    public function __construct(
        string $message = 'You have already generated your AI Plan. Each user is allowed to generate an AI plan only once.',
        int $code = 403,
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }
}
