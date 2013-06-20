<?php

namespace AMQPy;

use AMQPEnvelope;
use AMQPQueue;
use AMQPy\Hooks\PostConsumeInterface;
use AMQPy\Hooks\PreConsumeInterface;
use AMQPy\Serializers\Exceptions\SerializerException;
use Exception;

class Queue extends AMQPQueue
{
    /**
     * @var SerializerInterface
     */
    private $serializer = null;

    /**
     * @var Channel
     */
    private $channel = null;

    public function getChannel()
    {
        return $this->channel;
    }

    public function getSerializer()
    {
        return $this->serializer;
    }

    public function __construct(Channel $amqp_channel, SerializerInterface $serializer)
    {
        parent::__construct($amqp_channel);

        $this->serializer = $serializer;
        $this->channel    = $amqp_channel;
    }

    /**
     * Attach consumer to process payload from queue
     *
     * @param ConsumerInterface $consumer Consumer to process payload and handle possible errors
     * @param int               $flags    Consumer flags, AMQP_NOPARAM or AMQP_AUTOACK
     * @param bool              $forever  Should we ignore read timeout and still listen to data forever (useful for daemon scripts)
     *
     * @return mixed
     *
     * @throws SerializerException
     * @throws Exception           Any exception from pre/post-consume handlers and from exception handler
     */
    public function listen(ConsumerInterface $consumer, $flags = AMQP_NOPARAM, $forever = true)
    {
        $serializer = $this->getSerializer();

        $_orig_read_timeout = null;

        if ($forever) {
            $_orig_read_timeout = $this->channel->getConnection()->getReadTimeout();
            $this->getChannel()->getConnection()->setReadTimeout(0);
        }

        $e = null; // implement finally statement
        try {
            $this->consume(
                function (AMQPEnvelope $envelope, Queue $queue) use ($consumer, $serializer) {
                    if ($consumer instanceof PreConsumeInterface) {
                        $consumer->preConsume($envelope, $queue);
                    }

                    $ret = null;
                    $err = null;

                    try {
                        if ($envelope->getContentType() !== $serializer->getContentType()) {
                            throw new SerializerException('Content type mismatch');
                        }

                        $payload = $serializer->parse($envelope->getBody());

                        $ret = $consumer->consume($payload, $envelope, $queue);
                    } catch (Exception $e) {
                        try {
                            $ret = $consumer->except($e, $envelope, $queue);
                        } catch (Exception $e) {
                            $err = $e;
                        }
                    }

                    if ($consumer instanceof PostConsumeInterface) {
                        $consumer->postConsume($envelope, $queue, $err);
                    }

                    if ($err) { // implement finally statement
                        throw $err;
                    }

                    return $ret;

                },
                $flags
            );
        } catch (\Exception $e) {
        }

        if ($forever) {
            $this->getChannel()->getConnection()->setReadTimeout($_orig_read_timeout);
        }

        if ($e) { // implement finally statement
            throw $e;
        }


    }

    public function received(AMQPEnvelope $envelope)
    {
        return $this->ack($envelope->getDeliveryTag());
    }

    public function resend(AMQPEnvelope $envelope)
    {
        $this->nack($envelope->getDeliveryTag(), AMQP_REQUEUE);
    }

    public function drop(AMQPEnvelope $envelope)
    {
        $this->nack($envelope->getDeliveryTag());
    }
}
