<?php
/**
 * @author Bogdan Padalko <pinepain@gmail.com>
 * @created 1/20/13 @ 6:32 PM
 */

namespace Tests\AMQPy;


use AMQPy\Channel;
use AMQPy\Connection;

/**
 * @group core
 */
class ChannelTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Connection
     */
    protected $connection;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->connection = new Connection();
        $this->connection->connect();
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
        $this->connection->disconnect();
    }

    /**
     * @covers AMQPy\Connection::__construct
     */
    public function testConstructor()
    {
        $chn = new Channel($this->connection);

        $this->assertSame($this->connection->isConnected(), $chn->isConnected());

        $this->connection->disconnect();

        $this->assertSame($this->connection->isConnected(), $chn->isConnected());
    }

    /**
     * @covers AMQPy\Connection::getDefaultChannel
     */
    public function testGetDefaultChannel()
    {
        $chn = new Channel($this->connection);
        $this->assertSame($this->connection, $chn->getConnection());
    }


}

