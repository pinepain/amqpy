<?php

namespace AMQPy\Hooks;

use AMQPEnvelope;
use AMQPy\Queue;

interface PreConsumeInterface
{
    /**
     * Pre-consume hook. Invoked on each envelope receive.
     *
     * @param AMQPEnvelope $envelope An instance representing the message pulled from the queue
     * @param Queue $queue    Queue from which the message was consumed
     */
    public function preConsume(AMQPEnvelope $envelope, Queue $queue);
}
