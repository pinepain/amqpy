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
use \AMQPEnvelope;
use \Exception;

use \Exceptions\AMQPy\SerializerException;


class Queue extends AMQPQueue {
    /**
     * @var ISerializer
     */
    private $serializer = null;

    /**
     * @var AMQPChannel
     */
    private $channel = null;

    public function getChannel() {
        return $this->channel;
    }

    public function getSerializer() {
        return $this->serializer;
    }

    public function __construct(Channel $amqp_channel, ISerializer $serializer) {
        parent::__construct($amqp_channel);

        $this->serializer = $serializer;
        $this->channel    = $amqp_channel;
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
        // Do not catch exceptions from Queue::listen to prevent your app from
        // failing. If something bad happens here in most cases it's quite
        // critical problem or crappy code.

        $serializer = $this->getSerializer();

        if (false === $consumer->preConsume()) {
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

        // yupp, if IConsumer::except throw some exception Queue::postConsumer
        // will not run, but generally if even exception handler fails something
        // really goes wrong and it's good idea to do not try to keep app running
        // and bring it down. I'm not talking about that architectures where
        // thrown exceptions is another way to return result. Please don't do
        // that, at least not in PHP.
        return $consumer->postConsume();
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
