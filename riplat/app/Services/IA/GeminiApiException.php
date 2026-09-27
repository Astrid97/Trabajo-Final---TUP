<?php

namespace App\Services\IA;

use RuntimeException;

class GeminiApiException extends RuntimeException
{
    public function __construct(
        string $message,
        public readonly int $httpStatus
    ) {
        parent::__construct($message);
    }
}
