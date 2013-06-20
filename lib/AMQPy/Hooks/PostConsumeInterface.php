<?php

namespace AMQPy\Hooks;

use AMQPEnvelope;
use AMQPy\Queue;

interface PostConsumeInterface
{
    /**
     * Post-consume hook. Invoked on each envelope receive regardless to any error in ConsumerInterface methods
     *
     * @param AMQPEnvelope $envelope  An instance representing the message pulled from the queue
     * @param Queue        $queue     Queue from which the message was consumed
     * @param \Exception   $exception Exception thrown during consumption, if any
     */
    public function postConsume(AMQPEnvelope $envelope, Queue $queue, \Exception $exception = null);
}
