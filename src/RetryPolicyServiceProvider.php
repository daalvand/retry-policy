<?php
/** @noinspection StaticInvocationViaThisInspection */


namespace Daalvand\RetryPolicy;

use Daalvand\Kafka\Consumer\ConsumerBuilder;
use Daalvand\Kafka\Producer\ProducerBuilder;
use Daalvand\RetryPolicy\Commands\RetryPolicyConsumer;
use Daalvand\RetryPolicy\Commands\RetryPolicyRequeue;
use Daalvand\RetryPolicy\Concretes\KafkaStreamer;
use Daalvand\RetryPolicy\Concretes\RetryContext;
use Daalvand\RetryPolicy\Concretes\RetryContextMock;
use Daalvand\RetryPolicy\Concretes\Serializer;
use Daalvand\RetryPolicy\Contracts\SerializerInterface;
use Daalvand\RetryPolicy\Contracts\AsyncStreamer;
use Daalvand\RetryPolicy\Exceptions\RetryPolicyInvalidDriverException;
use Illuminate\Contracts\Container\Container as Application;
use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use Spatie\Async\Pool;

class RetryPolicyServiceProvider extends BaseServiceProvider
{
    private string $driver;

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->loadMigrationsFrom(__DIR__ . "/migrations");
            $this->commands([RetryPolicyConsumer::class, RetryPolicyRequeue::class]);
            $this->publishes([__DIR__ . '/config/retry_policy.php' => config_path('retry_policy.php')], 'config');
        }
    }

    public function register(): void
    {
        $this->driver = strtolower($this->app['config']->get('retry_policy.current_driver'));
        if ($this->driver === 'sync') {
            $this->app->bind('retry-context', function () {
                return new RetryContextMock();
            });
        } else {
            $this->app->bind(SerializerInterface::class, function () {
                return new Serializer();
            });

            $this->app->bind(AsyncStreamer::class, function (Application $app) {
                if ($this->driver === 'kafka') {
                    $brokers = $app['config']->get('retry_policy.connections.kafka.brokers');
                    $topic   = $app['config']->get('retry_policy.connections.kafka.topic');
                    return new KafkaStreamer(new ProducerBuilder(), new ConsumerBuilder(), $topic, $brokers);
                }
                throw new RetryPolicyInvalidDriverException($this->driver);
            });

            $this->app->bind('retry-context', function (Application $app) {
                $autoload = $this->app['config']->get('retry_policy.autoload', base_path('bootstrap/app.php'));
                return new RetryContext($app->make(AsyncStreamer::class), (new Pool)->autoload($autoload));
            });
        }

    }
}
