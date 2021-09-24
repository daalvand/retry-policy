<?php


namespace Daalvand\RetryPolicy\ObjectValues;

class AsyncMessage
{
    private array $headers;
    private string $body;
    private string $key;

    /**
     * @return array
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * @param array $headers
     * @return $this
     */
    public function setHeaders(array $headers): self
    {
        $this->headers = $headers;
        return $this;
    }

    /**
     * @return string
     */
    public function getBody(): string
    {
        return $this->body;
    }

    /**
     * @param string $body
     * @return $this
     */
    public function setBody(string $body): self
    {
        $this->body = $body;
        return $this;
    }

    /**
     * @return string
     */
    public function getKey(): string
    {
        return $this->key;
    }

    /**
     * @param string $key
     * @return $this
     */
    public function setKey(string $key): self
    {
        $this->key = $key;
        return $this;
    }
}
