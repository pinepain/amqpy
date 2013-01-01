<?php
/**
 * @author Ben Pinepain <pinepain@gmail.com>
 * @created 12/26/12 5:14 PM
 */

namespace AMQPy;


use \Exceptions\AMQPy\SerializerException;


interface ISerializer {
    /**
     * Returns string representation of a value
     *
     * @param mixed $value Value to serialize
     *
     * @throws SerializerException When serialization fails
     *
     * @return string Serialized value
     */
    public function serialize($value);

    /**
     * Decodes serialized string
     *
     * @param string $string String to parse
     *
     * @throws SerializerException When parsing fails
     *
     * @return mixed The value parsed from the given string
     */
    public function parse($string);

    /**
     * Get associated MIME type with serializer
     *
     * @return string MIME type according to IANA, RFC 2046, RFC 6648 and RFC 4288
     */
    public function getContentType();
}
