<?php


namespace AMQPy\Client;


class Channel extends \AMQPChannel
{
    private $connection;

    public function __construct(Connection $connection)
    {
        parent::__construct($connection);
        $this->connection = $connection;
    }

    public function getConnection()
    {
        return $this->connection;
    }
} 