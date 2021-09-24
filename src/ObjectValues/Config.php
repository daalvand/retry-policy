<?php


namespace Daalvand\RetryPolicy\ObjectValues;


class Config
{
    private ?string $queue = null;
    private ?int $requeueDelay = null;
    private ?int $requeueCount = null;
    private ?int $retryDelay = null;
    private ?int $retryCount = null;
    private ?int $timeout = null;
    private ?int $memory = null;

    /**
     * @return string|null
     */
    public function getQueue(): ?string
    {
        return $this->queue;
    }

    /**
     * @param string|null $queue
     * @return Config
     */
    public function setQueue(?string $queue): self
    {
        $this->queue = $queue;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getRequeueDelay(): ?int
    {
        return $this->requeueDelay;
    }

    /**
     * @param int|null $requeueDelay
     * @return Config
     */
    public function setRequeueDelay(?int $requeueDelay): self
    {
        $this->requeueDelay = $requeueDelay;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getRequeueCount(): ?int
    {
        return $this->requeueCount;
    }

    /**
     * @param int|null $requeueCount
     * @return Config
     */
    public function setRequeueCount(?int $requeueCount): self
    {
        $this->requeueCount = $requeueCount;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getRetryDelay(): ?int
    {
        return $this->retryDelay;
    }

    /**
     * @param int|null $retryDelay
     * @return Config
     */
    public function setRetryDelay(?int $retryDelay): self
    {
        $this->retryDelay = $retryDelay;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getRetryCount(): ?int
    {
        return $this->retryCount;
    }

    /**
     * @param int|null $retryCount
     * @return Config
     */
    public function setRetryCount(?int $retryCount): self
    {
        $this->retryCount = $retryCount;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getTimeout(): ?int
    {
        return $this->timeout;
    }

    /**
     * @param int|null $timeout
     * @return Config
     */
    public function setTimeout(?int $timeout): self
    {
        $this->timeout = $timeout;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getMemory(): ?int
    {
        return $this->memory;
    }

    /**
     * @param int|null $memory
     * @return Config
     */
    public function setMemory(?int $memory): self
    {
        $this->memory = $memory;
        return $this;
    }

}