<?php


namespace Daalvand\RetryPolicy\Exceptions;

use Exception;
use Throwable;

class AsyncCommitException extends Exception
{
    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        parent::__construct('Async Commit Exception :: ' . $message, $code, $previous);
    }
}
