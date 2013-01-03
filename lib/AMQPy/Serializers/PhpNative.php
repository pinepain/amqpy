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

use \Exceptions\AMQPy\SerializerException;


class PhpNative implements ISerializer {
    private static $false = 'b:0;';

    private static $mime = 'application/vnd.php.serialized';

    public function serialize($value) {
        return serialize($value);
    }

    public function parse($string) {
        if (!is_string($string)) {
            throw new SerializerException("Failed to parse value: Incompatible type");
        }

        $parsed = unserialize($string);

        if (false === $parsed && self::$false !== $string) {
            throw new SerializerException("Failed to parse value: String is not unserializeable");
        }

        return $parsed;
    }

    public function getContentType() {
        return self::$mime;
    }
}
