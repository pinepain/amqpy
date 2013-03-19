<?php
/**
 * @author Ben Pinepain <pinepain@gmail.com>
 * @url https://github.com/pinepain/amqpy
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace AMQPy\Serializers;


use AMQPy\ISerializer;

use \AMQPy\Exceptions\SerializerException;


class PlainText implements ISerializer {
    const MIME = 'plain/text';

    public function serialize($value) {
        if (!is_string($value) && !is_numeric($value)) {
            throw new SerializerException("Failed to serialize value: Incompatible type");
        }

        return (string)$value;
    }

    public function parse($value) {
        if (!is_string($value) && !is_numeric($value)) {
            throw new SerializerException("Failed to parse value: Incompatible type");
        }

        return $value;
    }

    public function getContentType() {
        return self::MIME;
    }
}
