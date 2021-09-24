<?php


namespace Daalvand\RetryPolicy\Exceptions;

use Exception;

class InvalidRetryableException extends Exception
{
    private const MESSAGE = 'RETRYABLE BY INDEX ":index" IS INVALID.';
    public function __construct(int $index)
    {
        parent::__construct(str_replace(':index', $index, self::MESSAGE));
    }
}
