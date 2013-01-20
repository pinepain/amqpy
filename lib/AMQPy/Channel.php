<?php
/**
 * @author Ben Pinepain <pinepain@gmail.com>
 * @created 1/20/13 @ 7:25 PM
 */

namespace AMQPY;


use \AMQPChannel;


class Channel extends AMQPChannel {
    /**
     * @var Connection
     */
    private $connection;

    public function __construct(Connection $connection) {
        parent::__construct($connection);

        $this->connection = $connection;
    }

    public function getConnection() {
        return $this->connection;
    }
}
