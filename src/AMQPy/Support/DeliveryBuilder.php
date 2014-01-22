<?php


namespace AMQPy\Support;

use AMQPEnvelope;
use AMQPy\Client\Delivery;

class DeliveryBuilder
{
    private $wrapper_skeleton;
    private $delivery_skeleton;

    public function __construct($wrapper_skeleton = '\AMQPy\Support\EnvelopeWrapper', $delivery_skeleton = '\AMQPy\Client\Delivery')
    {
        $this->wrapper_skeleton  = $wrapper_skeleton;
        $this->delivery_skeleton = $delivery_skeleton;
    }

    /**
     * @param AMQPEnvelope $envelope
     * @param string       $properties_skeleton
     * @param string       $envelope_skeleton
     *
     * @return EnvelopeWrapper
     */
    public function wrap(AMQPEnvelope $envelope, $properties_skeleton = 'AMQPy\Client\Properties', $envelope_skeleton = 'AMQPy\Client\Envelope')
    {
        return new $this->wrapper_skeleton($envelope, $properties_skeleton, $envelope_skeleton);
    }

    /**
     * @param AMQPEnvelope $envelope
     *
     * @return Delivery
     */
    public function build(AMQPEnvelope $envelope)
    {
        $wrapper = $this->wrap($envelope);

        return new $this->delivery_skeleton($wrapper->getBody(), $wrapper->getEnvelope(), $wrapper->getProperties());
    }
} 