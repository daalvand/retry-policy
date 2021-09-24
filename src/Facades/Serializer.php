<?php


namespace Daalvand\RetryPolicy\Facades;

use Daalvand\RetryPolicy\Contracts\Retryable;
use Daalvand\RetryPolicy\Contracts\SerializerInterface;
use Illuminate\Support\Facades\Facade;

/**
 * Class RetryContext
 * @method static string serialize(Retryable $retryable);
 * @method static Retryable unserialize(string $payload);
 *
 * @package App\Services\RetryPolicy\Facades
 */
class Serializer extends Facade
{
    public static function getFacadeAccessor()
    {
        return SerializerInterface::class;
    }
}
