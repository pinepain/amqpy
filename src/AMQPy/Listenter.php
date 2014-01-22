<?php

namespace AMQPy;

use AMQPEnvelope;
use AMQPQueue;
use AMQPy\Client\Delivery;
use AMQPy\Serializers\Exceptions\SerializerException;
use AMQPy\Serializers\SerializersPool;
use AMQPy\Support\DeliveryBuilder;
use Exception;

class Listenter
{
    private $queue;
    private $serializers;
    private $builder;

    private $read_timeout;

    public function __construct(AMQPQueue $queue, SerializersPool $serializers, DeliveryBuilder $builder)
    {
        $this->serializers = $serializers;
        $this->queue       = $queue;
        $this->builder     = $builder;
    }

    public function getQueue()
    {
        return $this->queue;
    }

    public function getSerializers()
    {
        return $this->serializers;
    }

    public function getBuilder()
    {
        return $this->builder;
    }

    public function isEndless()
    {
        return !$this->queue->getConnection()->getReadTimeout();
    }

    public function setEndless($is_endless)
    {
        $internal_is_endless = $this->isEndless();

        $is_endless = (bool)$is_endless;

        if ($internal_is_endless === $is_endless) {
            return $is_endless;
        }

        if ($is_endless) {
            $this->read_timeout = $this->queue->getConnection()->getReadTimeout();
            $this->queue->getConnection()->setReadTimeout(0);
        } else {
            if ($this->read_timeout) {
                $this->queue->getConnection()->setReadTimeout($this->read_timeout);
                $this->read_timeout = null;
            }
        }

        return $is_endless;
    }

    public function get($auto_ack = false)
    {
        $envelope = $this->queue->get($auto_ack ? AMQP_AUTOACK : AMQP_NOPARAM);

        if ($envelope) {
            $delivery = $this->builder->build($envelope);
        } else {
            $delivery = null;
        }

        return $delivery;
    }

    /**
     * Attach consumer to process payload from queue
     *
     * @param AbstractConsumer $consumer Consumer to process payload and handle possible errors
     * @param bool             $auto_ack Should message been acknowledged upon receive
     *
     * @return mixed
     *
     * @throws SerializerException
     * @throws Exception           Any exception from pre/post-consume handlers and from exception handler
     */
    public function consume(AbstractConsumer $consumer, $auto_ack = false)
    {
        if (!$consumer->active()) {
            // prevent dirty consumer been listening on queue
            return;
        }

        $serializers = $this->serializers;

        $outside_error = null;

        try {
            $consumer->begin($this);

            $this->queue->consume(
                        function (AMQPEnvelope $envelope /*, AMQPQueue $queue*/) use ($consumer, $serializers) {
                            $delivery = $this->builder->build($envelope);
                            $this->feed($delivery, $consumer);

                            return $consumer->active();
                        }, $auto_ack ? AMQP_AUTOACK : AMQP_NOPARAM
            );
        } catch (Exception $e) {
            $outside_error = $e;
        }

        try {
            $this->queue->cancel();
        } catch (Exception $e) {
        }

        try {
            $consumer->end($this, $outside_error);
        } finally {

            if ($outside_error) {
                throw $outside_error;
            }
        }
    }

    public function feed(Delivery $delivery, AbstractConsumer $consumer)
    {
        $consumer->before($delivery, $this);

        $consumer_exception = null;
        $consume_result     = null;
        $consume_payload    = null;

        // TODO:
        // +begin +before() -> +consume() -> {ok ? after() : failure()} -> always() -> end()
        try {
            $consume_payload = $this->serializers->get($delivery->getProperties()->getContentType())
                                                 ->parse($delivery->getBody());

            $consume_result = $consumer->consume($consume_payload, $delivery, $this);
        } catch (Exception $e) {
            $consumer_exception = $e;
        }

        if ($consumer_exception) {
            $consumer->failure($consumer_exception, $delivery, $this);
        } else {
            $consumer->after($consume_result, $delivery, $this);
        }

        $consumer->always($consume_result, $consume_payload, $delivery, $this, $consumer_exception);
    }

    public function accept(Delivery $delivery)
    {
        return $this->queue->ack($delivery->getEnvelope()->getDeliveryTag());
    }

    public function resend(Delivery $delivery)
    {
        $this->queue->nack($delivery->getEnvelope()->getDeliveryTag(), AMQP_REQUEUE);
    }

    public function drop(Delivery $delivery)
    {
        $this->queue->nack($delivery->getEnvelope()->getDeliveryTag());
    }
}
