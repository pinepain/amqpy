<?php


namespace AMQPy;

use AMQPExchange;
use AMQPy\Client\Properties;
use AMQPy\Serializers\SerializersPool;

class Publisher
{
    private $exchange;
    private $serializers;

    public function __construct(AMQPExchange $exchange, SerializersPool $serializers_pool)
    {
        $this->exchange    = $exchange;
        $this->serializers = $serializers_pool;
    }

    public function getExchange()
    {
        return $this->exchange;
    }

    public function getSerializers()
    {
        return $this->serializers;
    }

    public function publish($message, $routing_key, Properties $properties = null, $flags = AMQP_NOPARAM)
    {
        $content_type = 'text/plain';
        $attributes   = [];

        if ($properties) {
            if ($properties->getContentType()) {
                $content_type = $properties->getContentType();
            }

            $attributes = $properties->toArray();
        }

        $message = $this->serializers->get($content_type)->serialize($message);

        $attributes['content_type'] = $content_type;

        $this->exchange->publish($message, $routing_key, $flags, $attributes);
    }
}
