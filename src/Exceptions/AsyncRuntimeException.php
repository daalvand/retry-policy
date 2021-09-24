<?php


namespace Daalvand\RetryPolicy\Exceptions;

use Exception;
use Throwable;

class AsyncRuntimeException extends Exception
{
    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        parent::__construct('Async Runtime Exception :: ' . $message, $code, $previous);
    }
}
