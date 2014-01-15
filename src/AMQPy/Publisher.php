<?php


namespace AMQPy;

use AMQPExchange;
use AMQPy\Client\BasicProperties;
use AMQPy\Serializers\SerializersPool;

class Publisher
{
    private $exchange;
    private $serializers;

    public function __construct(AMQPExchange $exchange, SerializersPool $serializers)
    {
        $this->exchange    = $exchange;
        $this->serializers = $serializers;
    }

    public function getExchange()
    {
        return $this->exchange;
    }

    public function getSerializers()
    {
        return $this->serializers;
    }

    public function publish($message, $routing_key, BasicProperties $properties, $flags = AMQP_NOPARAM)
    {
        $content_type = $properties->getContentType() ? : 'text/plain'; // default content type;

        $message    = $this->serializers->get($content_type)->serialize($message);
        $attributes = $properties->toArray();

        $attributes['content_type'] = $content_type;

        var_dump("publish @{$routing_key} ". $message);
        var_dump($attributes);
        echo PHP_EOL, PHP_EOL;

        $this->exchange->publish($message, $routing_key, $flags, $attributes);
    }
}
