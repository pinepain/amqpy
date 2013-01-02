<?php
/**
 * @author Ben Pinepain <pinepain@gmail.com>
 * @created 12/27/12 4:23 PM
 */

namespace AMQPy;

use \AMQPQueue;
use \AMQPChannel;
use \AMQPEnvelope;
use \Exception;

use \Exceptions\AMQPy\SerializerException;


class Queue extends AMQPQueue {
    /**
     * @var ISerializer
     */
    private $serializer = null;

    public function getSerializer() {
        return $this->serializer;
    }

    public function __construct(AMQPChannel $amqp_channel, ISerializer $serializer) {
        parent::__construct($amqp_channel);
        $this->serializer = $serializer;
    }

    /**
     * Attach consumer to process payload from queue
     *
     * @param IConsumer $consumer Consumer to process payload and handle possible errors
     * @param int       $flags    Consumer flags, AMQP_NOPARAM or AMQP_AUTOACK
     *
     * @throws SerializerException
     */
    public function listen(IConsumer $consumer, $flags = AMQP_NOPARAM) {
        $serializer = $this->getSerializer();

        $this->consume(function (AMQPEnvelope $envelope, Queue $queue) use ($consumer, $serializer) {
            try {
                if ($envelope->getContentType() !== $serializer->getContentType()) {
                    throw new SerializerException('Content type mismatch');
                }

                $payload = $serializer->parse($envelope->getBody());

                return $consumer->consume($payload, $envelope, $queue);
            } catch (Exception $e) {
                return $consumer->except($e, $envelope, $queue);
            }
        }, $flags);
    }

    public function received(AMQPEnvelope $envelope) {
        return $this->ack($envelope->getDeliveryTag());
    }

    public function resend(AMQPEnvelope $envelope) {
        $this->nack($envelope->getDeliveryTag(), AMQP_REQUEUE);
    }

    public function drop(AMQPEnvelope $envelope) {
        $this->nack($envelope->getDeliveryTag());
    }
}
