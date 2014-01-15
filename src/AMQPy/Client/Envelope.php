<?php

namespace AMQPy\Client;

class Envelope
{
    private $routing_key;
    private $exchange;
    private $delivery_tag;
    private $redeliver;

    public function __construct($exchange, $routing_key, $delivery_tag = null, $redeliver = null)
    {
        $this->delivery_tag = $delivery_tag;
        $this->exchange     = $exchange;
        $this->redeliver    = $redeliver;
        $this->routing_key  = $routing_key;
    }

    /**
     * @return null
     */
    public function getDeliveryTag()
    {
        return $this->delivery_tag;
    }

    /**
     * @return mixed
     */
    public function getExchange()
    {
        return $this->exchange;
    }

    /**
     * @return mixed
     */
    public function getRoutingKey()
    {
        return $this->routing_key;
    }

    /**
     * @return null
     */
    public function isRedeliver()
    {
        return $this->redeliver;
    }
}
