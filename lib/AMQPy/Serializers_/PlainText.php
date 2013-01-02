<?php
/**
 * @author Ben Pinepain <pinepain@gmail.com>
 * @created 12/26/12 5:57 PM
 */

namespace AMQPy\Serializers;


use AMQPy\ISerializer;

use \Exceptions\AMQPy\SerializerException;


class PlainText implements ISerializer {
    private static $mime = 'plain/text';

    public function serialize($value) {
        if (!is_string($value) && !is_numeric($value)) {
            throw new SerializerException("Failed to serialize value: Incompatible type");
        }

        return (string)$value;
    }

    public function parse($string) {
        if (!is_string($string)|| !is_numeric($string)) {
            throw new SerializerException("Failed to parse value: Incompatible type");
        }

        return $string;
    }

    public function getContentType() {
        return self::$mime;
    }
}
