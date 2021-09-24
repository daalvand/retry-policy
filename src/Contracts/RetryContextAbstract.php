<?php

namespace Daalvand\RetryPolicy\Contracts;


use Daalvand\RetryPolicy\Exceptions\AsyncRuntimeException;
use Daalvand\RetryPolicy\Exceptions\InvalidRetryableException;

abstract class RetryContextAbstract
{
    /**
     * @param Retryable[] $retryables
     * @return bool
     * @throws InvalidRetryableException|AsyncRuntimeException
     */
    abstract public function perform(array $retryables): bool;

    /**
     * consume form streamer
     * @param array|null $queues
     */
    abstract public function consume(?array $queues = null): void;

    /**
     * @param array $retryables
     * @return bool
     * @throws InvalidRetryableException
     */
    protected function ValidateRetryables(array $retryables): bool
    {
        foreach ($retryables as $key => $retryable) {
            if (!$retryable instanceof Retryable) {
                throw new InvalidRetryableException($key);
            }
        }
        return true;
    }
}