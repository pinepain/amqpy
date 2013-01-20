<?php
/**
 * @author Ben Pinepain <pinepain@gmail.com>
 * @created 1/20/13 @ 6:32 PM
 */

namespace Tests\AMQPy;


use \AMQPy\Connection;
use \AMQPy\Serializers\PhpNative;

use \AMQPExchangeException;


/**
 * @group core
 */
class ConnectionTest extends \PHPUnit_Framework_TestCase {
    /**
     * @var Connection
     */
    protected $connection;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp() {
        $this->connection = new Connection();
        $this->connection->connect();
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown() {
        $this->connection->disconnect();
    }

    /**
     * @covers AMQPy\Connection::__construct
     */
    public function testConstructor() {
        $cnn = new Connection();
        $this->assertTrue($cnn->isConnected());
        $cnn->disconnect();

        $cnn = new Connection(array(), false);
        $this->assertFalse($cnn->isConnected());
        $cnn->connect();
        $this->assertTrue($cnn->isConnected());
        $cnn->disconnect();
    }

    /**
     * @covers AMQPy\Connection::setDefaultChannel
     */
    public function testGetChannel() {
        $ch = $this->connection->getChannel();
        $this->assertInstanceOf('\AMQPy\Channel', $ch);
        $this->assertSame($ch->getConnection(), $this->connection);
        $this->assertNotSame($ch, $this->connection->getChannel());
    }

    /**
     * @covers AMQPy\Connection::getDefaultChannel
     */
    public function testGetDefaultChannel() {
        $ch = $this->connection->getDefaultChannel();
        $this->assertInstanceOf('\AMQPy\Channel', $ch);
        $this->assertSame($ch, $this->connection->getDefaultChannel());
    }

    /**
     * @covers AMQPy\Connection::setDefaultChannel
     */
    public function testSetDefaultChannelImplicit() {
        $ch     = $this->connection->getChannel();
        $ch_set = $this->connection->setDefaultChannel($ch);

        $this->assertSame($ch, $ch_set);
        $this->assertSame($ch, $this->connection->getDefaultChannel());
    }

    /**
     * @covers AMQPy\Connection::setDefaultChannel
     */
    public function testSetDefaultChannelExplicit() {
        $ch = $this->connection->setDefaultChannel();

        $this->assertInstanceOf('\AMQPy\Channel', $ch);
        $this->assertSame($ch, $this->connection->getDefaultChannel());
    }

    /**
     * @covers AMQPy\Connection::setDefaultChannel
     *
     * @expectedException \AMQPConnectionException
     * @expectedExceptionMessage Channel does not belong to this connection
     */
    public function testSetDefaultChannelFromOtherConnection() {
        $cnn = new Connection();
        $this->assertNotSame($cnn, $this->connection);

        $this->connection->setDefaultChannel($cnn->getChannel());
    }

    /**
     * @covers AMQPy\Connection::getExchange
     * @covers AMQPy\Exchange::getSerializer
     * @covers AMQPy\Exchange::getConnection
     */
    public function testGetExchange() {
        $name = 'test.exchange.' . microtime(true);
        $type = AMQP_EX_TYPE_FANOUT;
        $serializer = new PhpNative();
        $flags = AMQP_DURABLE | AMQP_AUTODELETE;
        $args = array('alternate-exchange' => 'amq.fanout');

        $ex = $this->connection->getExchange($name, $type, $serializer, $flags, $args);

        try {
            $this->connection->getExchange($name, $type, AMQP_EX_TYPE_TOPIC, $flags, $args);
        } catch (AMQPExchangeException $e) {
            $this->assertStringMatchesFormat("%sPRECONDITION_FAILED - cannot redeclare exchange%s", $e->getMessage());
        }

        $this->assertSame($name, $ex->getName());
        $this->assertSame($type, $ex->getType());
        $this->assertSame($flags, $ex->getFlags());
        $this->assertSame($args, $ex->getArguments());

        $this->assertSame($serializer, $ex->getSerializer());
        $this->assertSame($this->connection->getDefaultChannel(), $ex->getChannel());

    }


}

