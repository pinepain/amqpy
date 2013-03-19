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


class PhpNative implements ISerializer {
    const MIME = 'application/vnd.php.serialized';

    private static $false = 'b:0;';

    public function serialize($value) {
        return serialize($value);
    }

    public function parse($string) {
        if (!is_string($string)) {
            throw new SerializerException("Failed to parse value: Incompatible type");
        }

        $parsed = @unserialize($string); // shut up but then throw an exception

        if (false === $parsed && self::$false !== $string) {
            throw new SerializerException("Failed to parse value: String is not unserializeable");
        }

        return $parsed;
    }

    public function getContentType() {
        return self::MIME;
    }
}
