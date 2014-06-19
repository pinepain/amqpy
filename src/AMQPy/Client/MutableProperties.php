<?php

namespace AMQPy\Client;

use AMQPy\Client\Exceptions\PropertiesException;
use Traversable;

// http://www.rabbitmq.com/resources/specs/amqp-xml-doc0-9-1.pdf Section 1.8.2 Properties
/**
 * Class Properties
 *
 * @property mixed            $content_encoding
 * @method mixed getContentEncoding()
 * @method mixed setContentEncoding(mixed $content_encoding)
 *
 * @property mixed            $content_type
 * @method mixed getContentType()
 * @method mixed setContentType(mixed $content_type)
 *
 * @property mixed            $correlation_id
 * @method mixed getCorrelationId()
 * @method mixed setCorrelationId(mixed $correlation_id)
 *
 * @property mixed            $delivery_mode
 * @method mixed getDeliveryMode()
 * @method mixed setDeliveryMode(mixed $delivery_mode)
 *
 * @property mixed            $expiration
 * @method mixed getExpiration()
 * @method mixed setExpiration(mixed $expiration)
 *
 * @property array            $headers
 *
 * @method array getHeaders()
 *
 * @param array | Traversable $headers
 *
 * @throws PropertiesException When try to set different from array or other iterable value as headers
 * @method array setHeaders($headers)
 *
 * @property mixed            $message_id
 * @method mixed getMessageId()
 * @method mixed setMessageId(mixed $message_id)
 *
 * @property mixed            $priority
 * @method mixed getPriority()
 * @method mixed setPriority(mixed $priority)
 *
 * @property mixed            $reply_to
 * @method mixed getReplyTo()
 * @method mixed setReplyTo(mixed $reply_to)
 *
 * @property mixed            $timestamp
 * @method mixed getTimestamp()
 * @method mixed setTimestamp(mixed $timestamp)
 *
 * @property mixed            $type
 * @method mixed getType()
 * @method mixed setType(mixed $type)
 *
 * @property mixed            $user_id
 * @method mixed getUserId()
 * @method mixed setUserId(mixed $user_id)
 *
 * @property mixed            $app_id
 * @method mixed getAppId()
 * @method mixed setAppId(mixed $app_id)
 */
// TODO: ArrayAccess interface and Traversable support
class MutableProperties
{
    const DELIVERY_MODE_NON_PERSISTENT = 1;
    const DELIVERY_MODE_PERSISTENT = 2;

    protected $properties = [
        'content_type'     => null, // shortstr MIME content type
        'content_encoding' => null, // shortstr MIME content encoding
        'headers'          => [], // table
        'delivery_mode'    => null, // octet
        'priority'         => null, // octet
        'correlation_id'   => null, // shortstr
        'reply_to'         => null, // shortstr
        'expiration'       => null, // shortstr
        'message_id'       => null, // shortstr
        'timestamp'        => null, // timestamp
        'type'             => null, // shortstr
        'user_id'          => null, // shortstr
        'app_id'           => null, // shortstr
    ];

    public function __construct(array $properties = [])
    {
        $this->fromArray($properties);
    }

    public function toArray()
    {
        return $this->properties;
    }

    function __isset($name)
    {
        $this->checkProperty($name);

        return isset($this->properties[$name]);
    }

    public function __get($name)
    {
        $this->checkProperty($name);

        $getter = 'get' . str_replace(' ', '', ucwords(str_replace(array('-', '_'), ' ', $name)));

        return method_exists($this, $getter)
            ? $this->$getter()
            : $this->properties[$name];
    }

    function __set($name, $value)
    {
        $this->checkProperty($name);

        //$setter = $this->properties_map[$name];

        $setter = 'set' . str_replace(' ', '', ucwords(str_replace(array('-', '_'), ' ', $name)));

        return method_exists($this, $setter)
            ? $this->$setter($value)
            : $this->properties[$name] = $value;
    }

    function __call($name, $arguments)
    {
        $direction = substr($name, 0, 3);

        if ($direction != 'set' && $direction != 'get') {
            throw new \RuntimeException('Call to undefined method ' . get_class($this) . '::' . $name);
        }

        $replace = '$1_$2';

        $name = substr($name, 3);

        $name = ctype_lower($name)
            ? $name
            : strtolower(preg_replace('/(.)([A-Z])/', $replace, $name));

        $this->checkProperty($name);

        if ($direction == 'get') {
            return $this->properties[$name];
        } else {
            return $this->properties[$name] = count($arguments) ? $arguments[0] : null;
        }

    }

    public function fromArray(array $properties)
    {
        // TODO: get keys intersection and iterate only over them

        foreach ($properties as $prop => $value) {
            if (isset($this->properties_map[$prop])) {
                $setter = $this->properties_map[$prop];

                if ($setter) {
                    $this->$setter($value);
                } else {
                    $this->properties[$prop] = $value;
                }
            }
        }
    }

    protected function checkProperty($name)
    {
        if (!array_key_exists($name, $this->properties)) {
            throw new PropertiesException("Property '{$name}' doesn't exists");
        }
    }
}
