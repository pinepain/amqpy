<?php


namespace AMQPy\Client;


class Exchange extends \AMQPExchange
{
    private $channel;

    public function __construct(Channel $channel)
    {
        parent::__construct($channel);
        $this->channel = $channel;
    }

    public function getChannel()
    {
        return $this->channel;
    }
}
