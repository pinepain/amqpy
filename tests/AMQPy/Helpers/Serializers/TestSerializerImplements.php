<?php


namespace AMQPy\Helpers\Serializers;


use AMQPy\Serializers\SerializerInterface;

class TestSerializerImplements implements SerializerInterface
{
    const MIME = 'test/implements';

    public function serialize($value)
    {
    }

    public function parse($string)
    {
    }

    public function getContentType()
    {
        return self::MIME;
    }
}