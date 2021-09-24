<?php


namespace Daalvand\RetryPolicy\Contracts;

use Daalvand\RetryPolicy\ObjectValues\AsyncMessage;
use Daalvand\RetryPolicy\Exceptions\AsyncConsumerException;
use Daalvand\RetryPolicy\Exceptions\AsyncCommitException;
use Daalvand\RetryPolicy\Exceptions\AsyncRuntimeException;

interface AsyncStreamer
{
    /**
     * @param array|null $queues
     * @return AsyncMessage
     * @throws AsyncConsumerException
     */
    public function consume(?array $queues = null): ?AsyncMessage;

    /**
     * @throws AsyncCommitException
     */
    public function commit(): void;

    /**
     * @param string $body
     * @param string|null $topic
     * @param array $headers
     * @return bool
     * @throws AsyncRuntimeException
     */
    public function produce(string $body, ?string $topic = null, array $headers = []): bool;

    /**
     * @return string
     */
    public function getQueue(): string;

    /**
     * @param string|null $queue
     * @return $this
     */
    public function setQueue(?string $queue): self;

    /**
     * get type of connection
     * @return string
     */
    public function getConnection(): string;
}
