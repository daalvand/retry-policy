<?php


namespace Daalvand\RetryPolicy\Exceptions;

use Exception;

class RetryPolicyInvalidDriverException extends Exception
{
    private const MESSAGE = 'SELECTED DRIVER ":driver" IS INVALID.';
    public function __construct(?string $driver)
    {
        parent::__construct(str_replace(':driver', $driver, self::MESSAGE));
    }
}
