<?php


namespace Daalvand\RetryPolicy\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * Class RetryContext
 * @method static bool perform(array $retryables)
 * @method static void consume(?array $queue = null)
 *
 * @package App\Services\RetryPolicy\Facades
 */
class RetryContext extends Facade
{
    public static function getFacadeAccessor()
    {
        return 'retry-context';
    }
}
