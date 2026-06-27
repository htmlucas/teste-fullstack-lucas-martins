<?php

namespace App\Exceptions;

use Exception;
use Throwable;

class FakeStoreApiException extends Exception
{
    public function __construct(
        string $message = 'FakeStore API error',
        int $code = 0,
        public readonly ?array $response = null,
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, $code, $previous);
    }
}