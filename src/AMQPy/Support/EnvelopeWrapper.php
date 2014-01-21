<?php


namespace AMQPy\Support;

use AMQPEnvelope;

class EnvelopeWrapper
{
    /**
     * @var \AMQPEnvelope
     */
    private $original;

    /**
     * @var \AMQPy\Client\BasicProperties
     */
    private $properties;

    /**
     * @var \AMQPy\Client\Envelope
     */
    private $envelope;

    private $properties_skeleton = '\AMQPy\Client\BasicProperties';
    private $envelope_skeleton = '\AMQPy\Client\Envelope';

    /**
     * @param AMQPEnvelope $envelope
     * @param string       $properties_skeleton
     * @param string       $envelope_skeleton
     *
     * @throws EnvelopeWrapperException When properties or envelope skeleton classes aren't inherit basic classes
     */
    public function __construct(AMQPEnvelope $envelope, $properties_skeleton = null, $envelope_skeleton = null)
    {
        if (null === $properties_skeleton) {
            $properties_skeleton = $this->properties_skeleton;
        } elseif (!is_a($properties_skeleton, $this->properties_skeleton, true)) {
            throw new EnvelopeWrapperException("Properties skeleton class should be '{$this->properties_skeleton}' or inherited from it");
        }

        if (null === $envelope_skeleton) {
            $envelope_skeleton = $this->envelope_skeleton;
        } elseif (!is_a($envelope_skeleton, $this->envelope_skeleton, true)) {
            throw new EnvelopeWrapperException("Envelope skeleton class should be '{$this->envelope_skeleton}' or inherited from it");
        }

        $this->original = $envelope;

        $this->properties_skeleton = $properties_skeleton;
        $this->envelope_skeleton   = $envelope_skeleton;
    }

    public function getOriginal()
    {
        return $this->original;
    }

    public function getBody()
    {
        return $this->original->getBody();
    }

    public function getProperties()
    {
        if (!$this->properties) {
            $class = $this->properties_skeleton;

            $properties_map = [
                'content_type'     => 'contentType',
                'content_encoding' => 'contentEncoding',
                'headers'          => 'headers',
                'delivery_mode'    => 'deliveryMode',
                'priority'         => 'priority',
                'correlation_id'   => 'correlationId',
                'reply_to'         => 'replyTo',
                'expiration'       => 'expiration',
                'message_id'       => 'messageId',
                'timestamp'        => 'timestamp',
                'type'             => 'type',
                'user_id'          => 'userId',
                'app_id'           => 'appId',
            ];

            $properties = [];

            foreach ($properties_map as $key => $parameter) {
                $parameter_getter = 'get' . ucfirst($parameter);

                $properties[$key] = $this->original->$parameter_getter();
            }

            $this->properties = new $class($properties);
        }

        return $this->properties;
    }

    public function getEnvelope()
    {
        if (!$this->envelope) {
            $class = $this->envelope_skeleton;

            $this->envelope = new $class(
                $this->original->getExchangeName(),
                $this->original->getRoutingKey(),
                $this->original->getDeliveryTag(),
                $this->original->isRedelivery()
            );

        }

        return $this->envelope;
    }
}
