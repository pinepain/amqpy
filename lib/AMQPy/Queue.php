<?php
/**
 * @author Ben Pinepain <pinepain@gmail.com>
 * @url https://github.com/pinepain/amqpy
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
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
     * @return mixed
     *
     * @throws SerializerException
     */
    public function listen(IConsumer $consumer, $flags = AMQP_NOPARAM) {
        $serializer = $this->getSerializer();

        if (false === $consumer->postConsume($this)) {
            return null;
        }

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

        return $consumer->postConsume($this);
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
