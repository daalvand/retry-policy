<?php


namespace Daalvand\RetryPolicy\Concretes;

use Daalvand\RetryPolicy\Contracts\Retryable;
use Daalvand\RetryPolicy\Contracts\RetryContextAbstract;

class RetryContextMock extends RetryContextAbstract
{

    public function perform(array $retryables): bool
    {
        /** @var Retryable $retryable */
        $this->ValidateRetryables($retryables);
        foreach ($retryables as $retryable) {
            $retryable->execute();
        }
        return true;
    }

    public function consume(?array $queues = null): void
    {
        // TODO: Implement consume() method.
    }
}