<?php


namespace Daalvand\RetryPolicy\Concretes;


use Daalvand\RetryPolicy\Contracts\Retryable;
use Daalvand\RetryPolicy\Contracts\SerializerInterface;
use function Opis\Closure\serialize as OpisSerializer;
use function Opis\Closure\unserialize as OpisUnserializer;

class Serializer implements SerializerInterface
{
    public function serialize(Retryable $retryable):string
    {
        $payload = [
            'data'  => OpisSerializer($retryable),
            'class' => get_class($retryable),
            'json'  => $retryable->toJson()
        ];
        return json_encode($payload);
    }

    public function unserialize(string $payload):Retryable
    {
        $array = json_decode($payload, true);
        return OpisUnserializer($array['data']);
    }
}