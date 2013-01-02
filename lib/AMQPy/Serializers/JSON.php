<?php
/**
 * @author Ben Pinepain <pinepain@gmail.com>
 * @created 12/26/12 5:57 PM
 */

namespace AMQPy\Serializers;


use AMQPy\ISerializer;

use \Exceptions\AMQPy\SerializerException;


class JSON implements ISerializer {
    private static $mime = 'application/json';

    const JSON_UNKNOWN_ERROR = 'Unknown error';
    const JSON_ENCODED_NULL  = 'null';

    private static $errors = array(
        JSON_ERROR_DEPTH          => 'Maximum stack depth exceeded',
        JSON_ERROR_STATE_MISMATCH => 'Underflow or the modes mismatch',
        JSON_ERROR_CTRL_CHAR      => 'Unexpected control character found',
        JSON_ERROR_SYNTAX         => 'Syntax error, malformed JSON',
        JSON_ERROR_UTF8           => 'Malformed UTF-8 characters, possibly incorrectly encoded',
    );

    public function serialize($value, $deep = null) {
        $value = json_encode($value);

        if ($this->isErrorOccurred()) {
            throw new SerializerException("Failed to serialize value: " . $this->getLastError());
        }

        return $value;
    }

    public function parse($string, $assoc = true) {
        $value = json_decode($string, $assoc);

        if ($this->isErrorOccurred()) {
            throw new SerializerException("Failed to parse value: " . $this->getLastError());
        }
        return $value;
    }

    public function getContentType() {
        return self::$mime;
    }


    private function isErrorOccurred() {
        return JSON_ERROR_NONE == json_last_error();
    }

    private function getLastError() {
        $error_code = json_last_error();

        if (isset(self::$errors[$error_code])) {
            $error = self::$errors[$error_code];
        } else {
            $error = self::JSON_UNKNOWN_ERROR;
        }

        return $error;
    }
}
