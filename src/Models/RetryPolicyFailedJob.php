<?php

namespace Daalvand\RetryPolicy\Models;

use DB;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Class FailedJob
 * @property int $id
 * @property int $requeue_count
 * @property int $max_requeue
 * @property int $requeue_delay
 * @property string $connection
 * @property string $queue
 * @property string $exception
 * @property string $payload
 * @property string $failed_at
 * @method static Builder canRequeue()
 * @package Daalvand\Models
 */
class RetryPolicyFailedJob extends Model
{

    public $timestamps = false;
    protected $fillable = [
        'requeue_count',
        'max_requeue',
        'requeue_delay',
        'connection',
        'queue',
        'exception',
        'payload',
    ];

    /**
     * @param Builder $query
     * @return Builder
     */
    public function scopeCanRequeue(Builder $query): Builder
    {
        $now = now()->toDateTimeString();
        return $query
            ->whereColumn('requeue_count', '<', 'max_requeue')
            ->whereRaw(DB::raw("'$now' > `failed_at` + INTERVAL `requeue_delay` SECOND"));
    }

}
