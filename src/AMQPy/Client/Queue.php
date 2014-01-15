<?php


namespace AMQPy\Client;


class Queue extends \AMQPQueue
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
