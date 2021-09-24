<?php


namespace Daalvand\RetryPolicy\Contracts;

interface SerializerInterface
{
    public function serialize(Retryable $retryable):string;
    public function unserialize(string $payload):Retryable;
}