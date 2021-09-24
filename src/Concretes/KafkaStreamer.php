<?php


namespace Daalvand\RetryPolicy\Concretes;

use Daalvand\Kafka\Consumer\ConsumerBuilderInterface;
use Daalvand\Kafka\Consumer\ConsumerInterface;
use Daalvand\Kafka\Exceptions\ConsumerCommitException;
use Daalvand\Kafka\Exceptions\ConsumerEndOfPartitionException;
use Daalvand\Kafka\Exceptions\ConsumerTimeoutException;
use Daalvand\Kafka\Message\ConsumerMessageInterface;
use Daalvand\Kafka\Message\ProducerMessage;
use Daalvand\Kafka\Producer\ProducerBuilderInterface;
use Daalvand\Kafka\Producer\ProducerInterface;
use Daalvand\RetryPolicy\Contracts\AsyncStreamer;
use Daalvand\RetryPolicy\Exceptions\AsyncConsumerException;
use Daalvand\RetryPolicy\Exceptions\AsyncRuntimeException;
use Daalvand\RetryPolicy\Exceptions\AsyncCommitException;
use Daalvand\RetryPolicy\Exceptions\AsyncSubscribeException;
use Daalvand\RetryPolicy\ObjectValues\AsyncMessage;
use Daalvand\Kafka\Exceptions\ConsumerConsumeException;
use Daalvand\Kafka\Exceptions\ConsumerSubscriptionException;
use Daalvand\Kafka\Exceptions\RuntimeException;

class KafkaStreamer implements AsyncStreamer
{
    private string $topic;
    private array $brokers;
    private ConsumerBuilderInterface $consumerBuilder;
    private ?ConsumerMessageInterface $consumerMessage = null;
    private ProducerInterface $producer;
    private ?ConsumerInterface $consumer = null;
    private const CONNECTION = 'kafka';

    /**
     * KafkaStreamer constructor.
     * @param ProducerBuilderInterface $producerBuilder
     * @param ConsumerBuilderInterface $consumerBuilder
     * @param string $defaultTopic
     * @param array $brokers
     */
    public function __construct(
        ProducerBuilderInterface $producerBuilder,
        ConsumerBuilderInterface $consumerBuilder,
        string $defaultTopic,
        array $brokers
    ) {
        $this->brokers  = $brokers;
        $this->topic    = $defaultTopic;
        $this->producer = $producerBuilder->setBrokers($brokers)->build();
        $this->consumerBuilder = $consumerBuilder;
    }

    /**
     * @param string $body
     * @param array $headers
     * @param string|null $topic
     * @return bool
     * @throws AsyncRuntimeException
     */
    public function produce(string $body, ?string $topic = null, array $headers = []): bool
    {
        $producerMessage = (new ProducerMessage($topic ?? $this->topic, 0))
            ->withHeaders($headers)
            ->withBody($body);
        try {
            $this->producer->produce($producerMessage);
        } catch (RuntimeException $e) {
            throw new AsyncRuntimeException($e->getMessage(), $e->getCode(), $e);
        }
        return true;
    }

    /**
     * @param array|null $topics
     * @return AsyncMessage
     * @throws AsyncConsumerException
     * @throws AsyncSubscribeException
     */
    public function consume(?array $topics = null): ?AsyncMessage
    {
        if(!$this->consumer){
            $this->startConsumer($topics);
        }
        try {
            $this->consumerMessage = $this->consumer->consume();
        } catch (ConsumerEndOfPartitionException|ConsumerTimeoutException $e) {
            return $this->consume();
        } catch (ConsumerConsumeException|ConsumerSubscriptionException $exception) {
            throw new AsyncConsumerException($exception->getMessage(), $exception->getCode(), $exception);
        }
        return (new AsyncMessage)->setBody($this->consumerMessage->getBody())->setHeaders($this->consumerMessage->getHeaders());
    }


    /**
     * @throws AsyncCommitException
     */
    public function commit(): void
    {
        try {
            if ($this->consumerMessage) {
                $this->consumer->commit($this->consumerMessage);
                $this->consumerMessage = null;
            }
        } catch (ConsumerCommitException $e) {
            throw new AsyncCommitException($e->getMessage(), $e->getCode(), $e);
        }
    }


    /**
     * @throws AsyncSubscribeException
     */
    private function subscribe(): void
    {
        try {
            $this->consumer->subscribe();
        } catch (ConsumerSubscriptionException $e) {
            throw new AsyncSubscribeException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * @return string
     */
    public function getQueue(): string
    {
        return $this->topic;
    }

    /**
     * @param string|null $queue
     * @return $this
     */
    public function setQueue(?string $queue): self
    {
        if(!is_null($queue)){
            $this->topic = $queue;
        }
        return $this;
    }

    /**
     * @param array|null $topics
     * @return $this
     * @throws AsyncSubscribeException
     */
    private function startConsumer(?array $topics = null): self
    {
        $topics = ((bool)$topics ? $topics : [$this->topic]);
        $this->consumerBuilder->withSubscription(reset($topics));
        array_shift($topics);
        foreach ($topics as $key => $topic) {
            $this->consumerBuilder->withAdditionalSubscription($topic);
        }
        $this->consumer = $this
            ->consumerBuilder
            ->setBrokers($this->brokers)->build();
        $this->subscribe();
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getConnection(): string
    {
        return self::CONNECTION;
    }
}
