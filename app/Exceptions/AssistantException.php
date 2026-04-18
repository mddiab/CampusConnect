<?php

namespace App\Exceptions;

use RuntimeException;

class AssistantException extends RuntimeException
{
    public function __construct(
        string $message,
        public readonly int $status = 502,
    ) {
        parent::__construct($message);
    }
}
