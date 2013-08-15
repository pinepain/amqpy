<?php

namespace AMQPy\Serializers;

use AMQPy\SerializerInterface;
use AMQPy\Serializers\Exceptions\SerializerException;

class PhpNative implements SerializerInterface
{
    const MIME = 'application/vnd.php.serialized';

    private static $false = 'b:0;';

    public function serialize($value)
    {
        return serialize($value);
    }

    public function parse($string)
    {
        if (!is_string($string)) {
            throw new SerializerException("Failed to parse value: Incompatible type");
        }

        $parsed = @unserialize($string); // shut up but then throw an exception

        if (false === $parsed && self::$false !== $string) {
            throw new SerializerException("Failed to parse value: String is not unserializable");
        }

        return $parsed;
    }

    public function getContentType()
    {
        return self::MIME;
    }
}
