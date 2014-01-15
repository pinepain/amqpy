<?php

namespace AMQPy\Client;

// http://www.rabbitmq.com/resources/specs/amqp-xml-doc0-9-1.pdf Section 1.8.2 Properties
class BasicProperties
{
    const DELIVERY_MODE_NON_PERSISTENT = 1;
    const DELIVERY_MODE_PERSISTENT     = 2;

    private $content_type;
    private $content_encoding;
    private $headers;
    private $delivery_mode;
    private $priority;
    private $correlation_id;
    private $reply_to;
    private $expiration;
    private $message_id;
    private $timestamp;
    private $type;
    private $user_id;
    private $app_id;

    protected $properties = array(
        'content_type'     => 'content_type',
        'content_encoding' => 'content_encoding',
        'headers'          => 'headers',
        'delivery_mode'    => 'delivery_mode',
        'priority'         => 'priority',
        'correlation_id'   => 'correlation_id',
        'reply_to'         => 'reply_to',
        'expiration'       => 'expiration',
        'message_id'       => 'message_id',
        'timestamp'        => 'timestamp',
        'type'             => 'type',
        'user_id'          => 'user_id',
        'app_id'           => 'app_id',
    );

    public function __construct(array $properties = array())
    {
        $this->fromArray($properties);
    }

    /**
     * @param mixed $appId
     */
    public function setAppId($appId)
    {
        $this->app_id = $appId;
    }

    /**
     * @return mixed
     */
    public function getAppId()
    {
        return $this->app_id;
    }

    /**
     * @param mixed $contentEncoding
     */
    public function setContentEncoding($contentEncoding)
    {
        $this->content_encoding = $contentEncoding;
    }

    /**
     * @return mixed
     */
    public function getContentEncoding()
    {
        return $this->content_encoding;
    }

    /**
     * @param mixed $contentType
     */
    public function setContentType($contentType)
    {
        $this->content_type = $contentType;
    }

    /**
     * @return mixed
     */
    public function getContentType()
    {
        return $this->content_type;
    }

    /**
     * @param mixed $correlationId
     */
    public function setCorrelationId($correlationId)
    {
        $this->correlation_id = $correlationId;
    }

    /**
     * @return mixed
     */
    public function getCorrelationId()
    {
        return $this->correlation_id;
    }

    /**
     * @param mixed $deliveryMode
     */
    public function setDeliveryMode($deliveryMode)
    {
        $this->delivery_mode = $deliveryMode;
    }

    /**
     * @return mixed
     */
    public function getDeliveryMode()
    {
        return $this->delivery_mode;
    }

    /**
     * @param mixed $expiration
     */
    public function setExpiration($expiration)
    {
        $this->expiration = $expiration;
    }

    /**
     * @return mixed
     */
    public function getExpiration()
    {
        return $this->expiration;
    }

    /**
     * @param mixed $headers
     */
    public function setHeaders($headers)
    {
        $this->headers = $headers;
    }

    /**
     * @return mixed
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * @param mixed $messageId
     */
    public function setMessageId($messageId)
    {
        $this->message_id = $messageId;
    }

    /**
     * @return mixed
     */
    public function getMessageId()
    {
        return $this->message_id;
    }

    /**
     * @param mixed $priority
     */
    public function setPriority($priority)
    {
        $this->priority = $priority;
    }

    /**
     * @return mixed
     */
    public function getPriority()
    {
        return $this->priority;
    }

    /**
     * @param mixed $replyTo
     */
    public function setReplyTo($replyTo)
    {
        $this->reply_to = $replyTo;
    }

    /**
     * @return mixed
     */
    public function getReplyTo()
    {
        return $this->reply_to;
    }

    /**
     * @param mixed $timestamp
     */
    public function setTimestamp($timestamp)
    {
        $this->timestamp = $timestamp;
    }

    /**
     * @return mixed
     */
    public function getTimestamp()
    {
        return $this->timestamp;
    }

    /**
     * @param mixed $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * @return mixed
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param mixed $userId
     */
    public function setUserId($userId)
    {
        $this->user_id = $userId;
    }

    /**
     * @return mixed
     */
    public function getUserId()
    {
        return $this->user_id;
    }

    public function toArray()
    {
        $properties = array();

        foreach ($this->properties as $property) {
            if (!empty($this->$property)) {
                $properties[$property] = $this->$property;
            }
        }

        if (!empty($properties['headers'])) {
            $properties['headers'] = (array)$properties['headers'];
        }

        return $properties;
    }

    public function fromArray(array $properties)
    {
        foreach ($properties as $prop => $value) {
            if (isset($this->properties[$prop])) {
                $this->$prop = $value;
            }
        }
    }
}
