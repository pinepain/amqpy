<?php

namespace AMQPy\Serializers;

use AMQPy\ISerializer;
use AMQPy\Serializers\Exceptions\SerializerException;

class JSON implements ISerializer
{
    const MIME = 'application/json';

    const JSON_UNKNOWN_ERROR = 'Unknown error';
    const JSON_ENCODED_NULL  = 'null';

    private static $errors = array(
        JSON_ERROR_DEPTH          => 'Maximum stack depth exceeded',
        JSON_ERROR_STATE_MISMATCH => 'Underflow or the modes mismatch',
        JSON_ERROR_CTRL_CHAR      => 'Unexpected control character found',
        JSON_ERROR_SYNTAX         => 'Syntax error, malformed JSON',
        JSON_ERROR_UTF8           => 'Malformed UTF-8 characters, possibly incorrectly encoded',
    );

    public function serialize($value)
    {
        $value = json_encode($value); // shut up but then throw an exception

        if ($this->isErrorOccurred()) {
            throw new SerializerException("Failed to serialize value: " . $this->getLastError());
        }

        return $value;
    }

    public function parse($string, $assoc = true)
    {
        $value = json_decode($string, $assoc);

        if ($this->isErrorOccurred()) {
            throw new SerializerException("Failed to parse value: " . $this->getLastError());
        }
        return $value;
    }

    public function getContentType()
    {
        return self::MIME;
    }


    private function isErrorOccurred()
    {
        return JSON_ERROR_NONE !== json_last_error();
    }

    private function getLastError()
    {
        $error_code = json_last_error();

        if (isset(self::$errors[$error_code])) {
            $error = self::$errors[$error_code];
        } else {
            $error = self::JSON_UNKNOWN_ERROR;
        }

        return $error;
    }
}
