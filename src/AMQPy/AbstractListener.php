<?php

namespace AMQPy;

use AMQPEnvelope;
use AMQPQueue;
use AMQPy\Client\Delivery;
use AMQPy\Serializers\Exceptions\SerializerException;
use AMQPy\Serializers\SerializersPool;
use AMQPy\Support\DeliveryBuilder;
use Exception;

abstract class AbstractListener
{
    private $queue;
    private $serializers;
    private $builder;

    private $read_timeout;

    public function __construct(AMQPQueue $queue, SerializersPool $serializers_pool, DeliveryBuilder $delivery_builder)
    {
        $this->serializers = $serializers_pool;
        $this->queue       = $queue;
        $this->builder     = $delivery_builder;
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
        $is_endless = (bool)$is_endless;

        if ($this->isEndless() === $is_endless) {
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

        $outside_error = null;

        try {
            $consumer->begin($this);

            $this->queue->consume(
                        function (AMQPEnvelope $envelope /*, AMQPQueue $queue*/) use ($consumer) {
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

        $consumer->end($this, $outside_error);

        if ($outside_error) {
            throw $outside_error;
        }
    }

    abstract public function feed(Delivery $delivery, AbstractConsumer $consumer);

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
