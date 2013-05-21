<?php

namespace AMQPy;

use AMQPEnvelope;
use Exception;

interface IConsumer
{
    /**
     * Process received data from queued message.
     *
     * @param mixed $payload  Payload data from the message
     * @param AMQPEnvelope $envelope An instance representing the message pulled from the queue
     * @param Queue $queue    Queue from which the message was consumed
     *
     * @return mixed | boolean Return FALSE to break the consumption event loop
     */
    public function consume($payload, AMQPEnvelope $envelope, Queue $queue);

    /**
     * Handle any exception during queued message data processing.
     *
     * @param Exception $e        Exception thrown during consumption
     * @param AMQPEnvelope $envelope An instance representing the message pulled from the queue
     * @param Queue $queue    Queue from which the message was consumed
     *
     * @return mixed | boolean Return FALSE to break the consumption event loop
     */
    public function except(Exception $e, AMQPEnvelope $envelope, Queue $queue);
}
