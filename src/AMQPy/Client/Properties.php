<?php

namespace AMQPy\Client;

use Traversable;
use AMQPy\Client\Exceptions\PropertiesException;

// http://www.rabbitmq.com/resources/specs/amqp-xml-doc0-9-1.pdf Section 1.8.2 Properties
class Properties implements \ArrayAccess
{
    const DELIVERY_MODE_NON_PERSISTENT = 1;
    const DELIVERY_MODE_PERSISTENT     = 2;

    protected $properties_map = [
        'content_type'     => false, // 'setContentType',
        'content_encoding' => false, // 'setContentEncoding',
        'headers'          => 'setHeaders',
        'delivery_mode'    => false, // 'setDeliveryMode',
        'priority'         => false, // 'setPriority',
        'correlation_id'   => false, // 'setCorrelationId',
        'reply_to'         => false, // 'setReplyTo',
        'expiration'       => false, // 'setExpiration',
        'message_id'       => false, // 'setMessageId',
        'timestamp'        => false, // 'setTimestamp',
        'type'             => false, // 'setType',
        'user_id'          => false, // 'setUserId',
        'app_id'           => false, // 'setAppId',
    ];

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

    /**
     * @param mixed $app_id
     */
    public function setAppId($app_id)
    {
        $this->properties['app_id'] = $app_id;
    }

    /**
     * @return mixed
     */
    public function getAppId()
    {
        return $this->properties['app_id'];
    }

    /**
     * @param mixed $content_encoding
     */
    public function setContentEncoding($content_encoding)
    {
        $this->properties['content_encoding'] = $content_encoding;
    }

    /**
     * @return mixed
     */
    public function getContentEncoding()
    {
        return $this->properties['content_encoding'];
    }

    /**
     * @param mixed $content_type
     */
    public function setContentType($content_type)
    {
        $this->properties['content_type'] = $content_type;
    }

    /**
     * @return mixed
     */
    public function getContentType()
    {
        return $this->properties['content_type'];
    }

    /**
     * @param mixed $correlation_id
     */
    public function setCorrelationId($correlation_id)
    {
        $this->properties['correlation_id'] = $correlation_id;
    }

    /**
     * @return mixed
     */
    public function getCorrelationId()
    {
        return $this->properties['correlation_id'];
    }

    /**
     * @param mixed $delivery_mode
     */
    public function setDeliveryMode($delivery_mode)
    {
        $this->properties['delivery_mode'] = $delivery_mode;
    }

    /**
     * @return mixed
     */
    public function getDeliveryMode()
    {
        return $this->properties['delivery_mode'];
    }

    /**
     * @param mixed $expiration
     */
    public function setExpiration($expiration)
    {
        $this->properties['expiration'] = $expiration;
    }

    /**
     * @return mixed
     */
    public function getExpiration()
    {
        return $this->properties['expiration'];
    }

    /**
     * @param mixed $headers
     *
     * @throws PropertiesException When try to set different from array or other iterable value as headers
     */
    public function setHeaders($headers)
    {
        if (is_array($headers)) {
            $_headers = $headers;
        } elseif ($headers instanceof Traversable) {
            $_headers = iterator_to_array($headers);
        } else {
            $type = is_object($headers)
                ? get_class($headers) . " object"
                : "scalar " . gettype($headers);

            throw new PropertiesException ("Headers should be array or iterable, {$type} given instead");
        }

        $this->properties['headers'] = $_headers;
    }

    /**
     * @return mixed
     */
    public function getHeaders()
    {
        return $this->properties['headers'];
    }

    /**
     * @param mixed $message_id
     */
    public function setMessageId($message_id)
    {
        $this->properties['message_id'] = $message_id;
    }

    /**
     * @return mixed
     */
    public function getMessageId()
    {
        return $this->properties['message_id'];
    }

    /**
     * @param mixed $priority
     */
    public function setPriority($priority)
    {
        $this->properties['priority'] = $priority;
    }

    /**
     * @return mixed
     */
    public function getPriority()
    {
        return $this->properties['priority'];
    }

    /**
     * @param mixed $reply_to
     */
    public function setReplyTo($reply_to)
    {
        $this->properties['reply_to'] = $reply_to;
    }

    /**
     * @return mixed
     */
    public function getReplyTo()
    {
        return $this->properties['reply_to'];
    }

    /**
     * @param mixed $timestamp
     */
    public function setTimestamp($timestamp)
    {
        $this->properties['timestamp'] = $timestamp;
    }

    /**
     * @return mixed
     */
    public function getTimestamp()
    {
        return $this->properties['timestamp'];
    }

    /**
     * @param mixed $type
     */
    public function setType($type)
    {
        $this->properties['type'] = $type;
    }

    /**
     * @return mixed
     */
    public function getType()
    {
        return $this->properties['type'];
    }

    /**
     * @param mixed $user_id
     */
    public function setUserId($user_id)
    {
        $this->properties['user_id'] = $user_id;
    }

    /**
     * @return mixed
     */
    public function getUserId()
    {
        return $this->properties['user_id'];
    }

    public function toArray()
    {
        return $this->properties;
    }

    public function fromArray(array $properties)
    {
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
}
