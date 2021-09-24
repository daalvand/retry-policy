<?php

/**
 * @noinspection JsonEncodingApiUsageInspection
 * @noinspection UnserializeExploitsInspection
 */


namespace Daalvand\RetryPolicy\Concretes;

use __PHP_Incomplete_Class;
use Daalvand\RetryPolicy\Contracts\AsyncStreamer;
use Daalvand\RetryPolicy\Contracts\Retryable;
use Daalvand\RetryPolicy\Contracts\RetryContextAbstract;
use Daalvand\RetryPolicy\Exceptions\AsyncCommitException;
use Daalvand\RetryPolicy\Exceptions\AsyncRuntimeException;
use Daalvand\RetryPolicy\Exceptions\InvalidRetryableException;
use Daalvand\RetryPolicy\Facades\Serializer as SerializerFacade;
use Daalvand\RetryPolicy\Models\RetryPolicyFailedJob;
use Daalvand\RetryPolicy\ObjectValues\AsyncMessage;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Spatie\Async\Pool;
use Throwable;

class RetryContext extends RetryContextAbstract
{
    private AsyncStreamer $asyncStreamer;
    private const SUCCESS_MESSAGE = 'RETRY POLICY CONSUMER SUCCESS';
    private const ERROR_MESSAGE = 'RETRY POLICY CONSUMER ERROR';
    private Pool $pool;

    /**
     * SocialActions constructor.
     * @param AsyncStreamer $asyncStreamer
     * @param Pool $pool
     */
    public function __construct(AsyncStreamer $asyncStreamer, Pool $pool) {
        $this->asyncStreamer = $asyncStreamer;
        $this->pool       = $pool;
    }


    /**
     * @param Retryable[] $retryables
     * @return bool
     * @throws InvalidRetryableException
     */
    public function perform(array $retryables): bool
    {
        if(!empty($retryables)){
            $this->ValidateRetryables($retryables);
            $this->parallelExecute($retryables);
        }
        return true;
    }

    /**
     * @param Retryable $retryable
     * @return bool
     * @throws AsyncRuntimeException
     */
    private function produce(Retryable $retryable): bool
    {
        $body = SerializerFacade::serialize($retryable);
        $this->asyncStreamer->produce($body, $retryable->getQueue());
        return true;
    }

    /**
     * consume form streamer
     * @param array|null $queues
     */
    public function consume(?array $queues = null): void
    {
        do {
            $message   = $this->asyncStreamer->consume($queues);
            $retryable = $this->unserialize($message);
            if($retryable){
                $this->tryExecute($retryable);
            }
        } while (true);
    }

    /**
     * @param Retryable $retryable
     * @param int $counter
     * @throws AsyncCommitException
     * @throws AsyncRuntimeException
     */
    private function tryExecute(Retryable $retryable, int $counter = 0): void
    {
        try {
            $retryable->execute();
            Log::info(self::SUCCESS_MESSAGE);
            $this->asyncStreamer->commit();
        } catch (Throwable $e) {
            Log::error(self::ERROR_MESSAGE);
            if($counter > $retryable->getMaxRetry()){
                $this->asyncStreamer->commit();
                $retryable->incRequeueCount();
                $this->trySendToFailedJobs($retryable, $e);
            }else{
                sleep($retryable->getRetryDelay());
                $this->tryExecute($retryable, ++$counter);
            }
        }
    }

    /**
     * @param AsyncMessage|null $message
     * @return Retryable|null
     * @throws AsyncCommitException
     */
    private function unserialize(?AsyncMessage $message): ?Retryable
    {
        $retryable = null;
        if($message){
            try {
                $retryable = SerializerFacade::unserialize($message->getBody());
            } catch (Throwable $e) {
                $retryable = null;
                $this->asyncStreamer->commit();
                Log::error('UNSERIALIZABLE EXCEPTION');
            }
            $inCompleteClass = __PHP_Incomplete_Class::class;
            if($retryable instanceof $inCompleteClass || !($retryable instanceof Retryable)){
                $retryable = null;
                $this->asyncStreamer->commit();
            }
        }
        return $retryable;
    }

    /**
     * send failed jobs to failedJobs Table
     * @param Retryable $retryable
     * @param Throwable|null $exception
     * @return bool
     */
    private function sendToFailedJobs(Retryable $retryable, ?Throwable $exception = null): bool
    {
        $model = new RetryPolicyFailedJob();
        $model->requeue_count = $retryable->getRequeueCount();
        $model->max_requeue   = $retryable->getMaxRequeue();
        $model->requeue_delay = $retryable->getRequeueDelay();
        $model->connection    = $retryable->getConnection() ?? $this->asyncStreamer->getConnection();
        $model->queue         = $retryable->getQueue() ?? $this->asyncStreamer->getQueue();
        $model->payload       = SerializerFacade::serialize($retryable);
        $model->failed_at     = now();
        $model->exception     = $exception ? json_encode([
                'message' => $exception->getMessage(),
                'code'    => $exception->getCode(),
                'trace'   => $exception->getTrace()
        ]) : null;
        try {
            $model->save();
        }catch (RuntimeException $exception){
            return false;
        }
        return true;
    }

    /**
     * try send job to failed jobs if throws has bug reproduced to kafka
     * @param Retryable $retryable
     * @param Throwable|null $exception
     * @throws AsyncRuntimeException
     */
    private function trySendToFailedJobs(Retryable $retryable, ?Throwable $exception = null): void
    {
        if(!$this->sendToFailedJobs($retryable, $exception)){
            $this->produce($retryable);
        }
    }

    /**
     * @param array $retryables
     * @return void
     */
    private function parallelExecute(array $retryables): void
    {
        /** @var Retryable $retryable */
        foreach ($retryables as $retryable) {
            $this->pool->add(function () use ($retryable) {
                $retryable->execute();
            })->then(static function ($output) {

            })->catch(function (Throwable $e) use ($retryable){
                $this->handleExecutionError($retryable, $e);
            });
            $this->pool->wait();
        }
    }

    /**
     * @param Retryable $retryable
     * @param Throwable $e
     * @throws AsyncRuntimeException
     * @throws Throwable
     */
    private function handleExecutionError(Retryable $retryable, Throwable $e): void
    {
        $mustProduce = false;
        $exceptions = $retryable->getExceptions();
        if (is_null($exceptions)) {
            $mustProduce = true;
        } else {
            foreach ($exceptions as $exception) {
                if ($e instanceof $exception && is_string($exception) && class_exists($exception)) {
                    $mustProduce = true;
                    break;
                }
            }
        }
        if ($mustProduce) {
            $this->produce($retryable);
        } else {
            throw $e;
        }
    }
}
