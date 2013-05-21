<?php

namespace AMQPy;

use AMQPEnvelope;
use AMQPQueue;
use AMQPy\Hooks\IPostConsumer;
use AMQPy\Hooks\IPreConsumer;
use AMQPy\Serializers\Exceptions\SerializerException;
use Exception;

class Queue extends AMQPQueue
{
    /**
     * @var ISerializer
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

    public function __construct(Channel $amqp_channel, ISerializer $serializer)
    {
        parent::__construct($amqp_channel);

        $this->serializer = $serializer;
        $this->channel    = $amqp_channel;
    }

    /**
     * Attach consumer to process payload from queue
     *
     * @param IConsumer $consumer Consumer to process payload and handle possible errors
     * @param int $flags    Consumer flags, AMQP_NOPARAM or AMQP_AUTOACK
     *
     * @return mixed
     *
     * @throws SerializerException
     * @throws Exception           Any exception from pre/post-consume handlers and from exception handler
     */
    public function listen(IConsumer $consumer, $flags = AMQP_NOPARAM)
    {
        // Do not catch exceptions from Queue::listen to prevent your app from
        // failing. If something bad happens here in most cases it's quite
        // critical problem or crappy code.

        $serializer = $this->getSerializer();

        $this->consume(
            function (AMQPEnvelope $envelope, Queue $queue) use ($consumer, $serializer) {
                if ($consumer instanceof IPreConsumer) {
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

                if ($consumer instanceof IPostConsumer) {
                    $consumer->postConsume($envelope, $queue);
                }

                if ($err) {
                    throw $err;
                }

                return $ret;

            },
            $flags
        );
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
