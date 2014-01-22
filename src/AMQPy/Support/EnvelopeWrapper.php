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
     * @var \AMQPy\Client\Properties
     */
    private $properties;

    /**
     * @var \AMQPy\Client\Envelope
     */
    private $envelope;

    private $properties_skeleton;
    private $envelope_skeleton;

    /**
     * @param AMQPEnvelope $envelope
     * @param string       $properties_skeleton
     * @param string       $envelope_skeleton
     */
    public function __construct(AMQPEnvelope $envelope, $properties_skeleton = 'AMQPy\Client\Properties', $envelope_skeleton = 'AMQPy\Client\Envelope')
    {
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
                'content_type'     => 'getContentType',
                'content_encoding' => 'getContentEncoding',
                'headers'          => 'getHeaders',
                'delivery_mode'    => 'getDeliveryMode',
                'priority'         => 'getPriority',
                'correlation_id'   => 'getCorrelationId',
                'reply_to'         => 'getReplyTo',
                'expiration'       => 'getExpiration',
                'message_id'       => 'getMessageId',
                'timestamp'        => 'getTimestamp',
                'type'             => 'getType',
                'user_id'          => 'getUserId',
                'app_id'           => 'getAppId',
            ];

            $properties = [];

            foreach ($properties_map as $key => $getter) {
                $properties[$key] = $this->original->$getter();
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
