<?php


namespace Daalvand\RetryPolicy\Exceptions;

use Exception;
use Throwable;

class AsyncSubscribeException extends Exception
{
    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        parent::__construct('Async Subscribe Exception :: ' . $message, $code, $previous);
    }
}
