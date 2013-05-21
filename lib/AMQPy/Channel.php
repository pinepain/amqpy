<?php

namespace AMQPy;

use AMQPChannel;

class Channel extends AMQPChannel
{
    /**
     * @var Connection
     */
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
