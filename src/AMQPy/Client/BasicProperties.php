<?php

namespace AMQPy\Client;

// http://www.rabbitmq.com/resources/specs/amqp-xml-doc0-9-1.pdf Section 1.8.2 Properties
class BasicProperties
{
    const DELIVERY_MODE_NON_PERSISTENT = 1;
    const DELIVERY_MODE_PERSISTENT     = 2;

    private $contentType;
    private $contentEncoding;
    private $headers;
    private $deliveryMode;
    private $priority;
    private $correlationId;
    private $replyTo;
    private $expiration;
    private $messageId;
    private $timestamp;
    private $type;
    private $userId;
    private $appId;

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
        $this->appId = $appId;
    }

    /**
     * @return mixed
     */
    public function getAppId()
    {
        return $this->appId;
    }

    /**
     * @param mixed $contentEncoding
     */
    public function setContentEncoding($contentEncoding)
    {
        $this->contentEncoding = $contentEncoding;
    }

    /**
     * @return mixed
     */
    public function getContentEncoding()
    {
        return $this->contentEncoding;
    }

    /**
     * @param mixed $contentType
     */
    public function setContentType($contentType)
    {
        $this->contentType = $contentType;
    }

    /**
     * @return mixed
     */
    public function getContentType()
    {
        return $this->contentType;
    }

    /**
     * @param mixed $correlationId
     */
    public function setCorrelationId($correlationId)
    {
        $this->correlationId = $correlationId;
    }

    /**
     * @return mixed
     */
    public function getCorrelationId()
    {
        return $this->correlationId;
    }

    /**
     * @param mixed $deliveryMode
     */
    public function setDeliveryMode($deliveryMode)
    {
        $this->deliveryMode = $deliveryMode;
    }

    /**
     * @return mixed
     */
    public function getDeliveryMode()
    {
        return $this->deliveryMode;
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
        $this->messageId = $messageId;
    }

    /**
     * @return mixed
     */
    public function getMessageId()
    {
        return $this->messageId;
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
        $this->replyTo = $replyTo;
    }

    /**
     * @return mixed
     */
    public function getReplyTo()
    {
        return $this->replyTo;
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
        $this->userId = $userId;
    }

    /**
     * @return mixed
     */
    public function getUserId()
    {
        return $this->userId;
    }

    public function toArray()
    {
        $properties = array();

        foreach ($this->properties as $property) {
            $properties[$property] = $this->$property;
        }

        $properties['headers'] = (array)$properties['headers'];

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
