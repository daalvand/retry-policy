<?php


namespace Daalvand\RetryPolicy\Contracts;

use DateTimeInterface;
use Exception;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Support\Str;
use JsonSerializable;

abstract class Retryable implements Arrayable, Jsonable, JsonSerializable
{
    protected ?string $connection = null;
    protected ?string $queue = null;
    protected int $timeout = 100;
    protected int $memory = 128;
    protected int $maxRetry = 3;
    protected int $retryDelay = 5;
    protected int $maxRequeue = 10;
    protected int $requeueDelay = 3600;
    protected int $requeueCount = 0;
    protected const EXCEPTIONS = null;

    abstract public function execute(): bool;

    /**
     * get retryable queue name
     * @return string|null
     */
    public function getQueue(): ?string
    {
        return $this->queue;
    }

    /**
     * set retryable queue name
     * @param string $queue
     * @return $this
     */
    public function setQueue(string $queue): self
    {
        $this->queue = $queue;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getConnection(): ?string
    {
        return $this->connection;
    }

    /**
     * @param string $connection
     * @return Retryable
     */
    public function setConnection(string $connection): self
    {
        $this->connection = $connection;
        return $this;
    }

    /**
     * get retryable execute timeout by seconds
     * @return int
     */
    public function getTimeout(): int
    {
        return $this->timeout;
    }

    /**
     * set retryable execute timeout in seconds
     * @param int $timeout
     * @return Retryable
     */
    public function setTimeout(int $timeout): self
    {
        $this->timeout = $timeout;
        return $this;
    }

    /**
     * set retryable execute timeout in seconds
     * @return int
     */
    public function getMemory(): int
    {
        return $this->memory;
    }

    /**
     * set memory limit in megabytes
     * @param int $memory
     * @return Retryable
     */
    public function setMemory(int $memory): self
    {
        $this->memory = $memory;
        return $this;
    }

    /**
     * The memory limit in megabytes
     * @return int
     */
    public function getMaxRetry(): int
    {
        return $this->maxRetry;
    }

    /**
     * Set max count of retry after throws exception
     * @param int $maxRetry
     * @return Retryable
     * @throws Exception
     */
    public function setMaxRetry(int $maxRetry): self
    {
        $this->maxRetry = $maxRetry;
        return $this;
    }

    /**
     * Set delay seconds for next retry
     * @return int
     */
    public function getRetryDelay(): int
    {
        return $this->retryDelay;
    }

    /**
     * delay seconds of next retry
     * @param int $retryDelay
     * @return Retryable
     */
    public function setRetryDelay(int $retryDelay): self
    {
        $this->retryDelay = $retryDelay;
        return $this;
    }

    /**
     * get max count of requeue after failed
     * @return int
     */
    public function getMaxRequeue(): int
    {
        return $this->maxRequeue;
    }

    /**
     * set max count of requeue after failed
     * @param int $maxRequeue
     * @return Retryable
     * @throws Exception
     */
    public function setMaxRequeue(int $maxRequeue): self
    {
        $this->maxRequeue = $maxRequeue;
        return $this;
    }

    /**
     * Set delay seconds of execution after failed
     * @return int
     */
    public function getRequeueDelay(): int
    {
        return $this->requeueDelay;
    }

    /**
     * delay seconds of execution after failed
     * @param int $requeueDelay
     * @return Retryable
     */
    public function setRequeueDelay(int $requeueDelay): self
    {
        $this->requeueDelay = $requeueDelay;
        return $this;
    }

    /**
     * The number of requeue until now
     * @return int
     */
    public function getRequeueCount(): int
    {
        return $this->requeueCount;
    }

    /**
     * increment number of requeue
     * @return Retryable
     */
    public function incRequeueCount(): self
    {
        ++$this->requeueCount;
        return $this;
    }

    /**
     * @return array|null
     */
    public function getExceptions(): ?array
    {
        return static::EXCEPTIONS;
    }


    public function toJson($options = 0):string
    {
        return json_encode($this->jsonSerialize(), $options);
    }

    public function toArray(): array
    {
        return $this->jsonSerialize();
    }

    public function jsonSerialize(): array
    {
        return $this->serialize();
    }

    protected function serialize(array $props = null): array
    {
        $props ??= get_object_vars($this);
        $properties = [];
        foreach ($props as $index => $objectVar) {
            $key = Str::snake($index);
            if(is_scalar($objectVar)) {
                $properties[$key] = $objectVar;
            } elseif (is_object($objectVar)) {
                if ($objectVar instanceof JsonSerializable) {
                    $properties[$key] = $objectVar->jsonSerialize();
                }else{
                    $properties[$key] = json_encode($objectVar);
                }
            }elseif (is_array($objectVar)){
                $properties[$key] = $this->serialize($objectVar);
            }
        }
        return $properties;
    }

}
