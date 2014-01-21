<?php

namespace AMQPy;

use AMQPEnvelope;
use AMQPQueue;
use AMQPy\Client\Delivery;
use AMQPy\Serializers\Exceptions\SerializerException;
use AMQPy\Serializers\SerializersPool;
use AMQPy\Support\EnvelopeWrapper;
use Exception;

class Listenter
{
    private $queue;
    private $serializers;

    private $read_timeout;

    public function __construct(AMQPQueue $queue, SerializersPool $serializers)
    {
        $this->serializers = $serializers;
        $this->queue       = $queue;
    }

    public function getQueue()
    {
        return $this->queue;
    }

    public function getSerializers()
    {
        return $this->serializers;
    }

    public function isEndless()
    {
        return !$this->queue->getChannel()->getConnection()->getReadTimeout();
    }

    public function setEndlessOn()
    {
        if (null === $this->read_timeout) {
            if ($this->isEndless()) {
                $this->read_timeout = 0;
            } else {
                $this->read_timeout = $this->queue->getChannel()->getConnection()->getReadTimeout();
                $this->queue->getChannel()->getConnection()->setReadTimeout(0);
            }
        }
    }

    public function setEndlessOff()
    {
        if ($this->read_timeout) {
            $this->queue->getChannel()->getConnection()->setReadTimeout($this->read_timeout);
        }

        $this->read_timeout = null;
    }

    public function get($auto_ack = false)
    {
        $envelope = $this->queue->get($auto_ack ? AMQP_AUTOACK : AMQP_NOPARAM);

        if ($envelope) {
            $wrapper  = new EnvelopeWrapper($envelope);
            $delivery = new Delivery($wrapper->getBody(), $wrapper->getEnvelope(), $wrapper->getProperties());

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

                            $wrapper  = new EnvelopeWrapper($envelope);
                            $delivery = new Delivery($wrapper->getBody(), $wrapper->getEnvelope(), $wrapper->getProperties());

                            $consumer->before($delivery, $this);

                            $consumer_error = null;

                            // TODO:
                            // +begin +before() -> +consume() -> {ok ? after() : failure()} -> always() -> end()
                            try {
                                $payload = $serializers->get($delivery->getProperties()->getContentType())
                                                       ->parse($envelope->getBody());

                                $consumer->consume($payload, $delivery, $this);
                            } catch (Exception $e) {
                                $consumer_error = $e;
                                $consumer->failure($e, $delivery, $this);
                                throw $e;
                            } finally {
                                $consumer->after($delivery, $this, $consumer_error);

                                return $consumer->active(); // amqp consumer will return processing thread back only when FALSE returned
                            }

                        },
                            $auto_ack ? AMQP_AUTOACK : AMQP_NOPARAM
            );
        } catch (Exception $e) {
            $outside_error = $e;
            throw $e;
        } finally {
            $consumer->end($this, $outside_error);
        }
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

    public function stop()
    {
        $this->queue->cancel();
    }
}
