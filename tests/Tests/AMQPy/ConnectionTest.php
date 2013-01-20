<?php
/**
 * @author Ben Pinepain <pinepain@gmail.com>
 * @created 1/20/13 @ 6:32 PM
 */

namespace Tests\AMQPy;

use \AMQPy\Connection;


/**
 * @group core
 */
class ConnectionTest extends \PHPUnit_Framework_TestCase {
    /**
     * @var Connection
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp() {
        $this->object = new Connection();
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown() {
        $this->object->disconnect();
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
        $ch = $this->object->getChannel();
        $this->assertInstanceOf('\AMQPy\Channel', $ch);
        $this->assertSame($ch->getConnection(), $this->object);
        $this->assertNotSame($ch, $this->object->getChannel());
    }

    /**
     * @covers AMQPy\Connection::getDefaultChannel
     */
    public function testGetDefaultChannel() {
        $ch = $this->object->getDefaultChannel();
        $this->assertInstanceOf('\AMQPy\Channel', $ch);
        $this->assertSame($ch, $this->object->getDefaultChannel());
    }

    /**
     * @covers AMQPy\Connection::setDefaultChannel
     */
    public function testSetDefaultChannelImplicit() {
        $ch = $this->object->getChannel();
        $ch_set = $this->object->setDefaultChannel($ch);

        $this->assertSame($ch, $ch_set);
        $this->assertSame($ch, $this->object->getDefaultChannel());
    }

    /**
     * @covers AMQPy\Connection::setDefaultChannel
     */
    public function testSetDefaultChannelExplicit() {
        $ch = $this->object->setDefaultChannel();

        $this->assertInstanceOf('\AMQPy\Channel', $ch);
        $this->assertSame($ch, $this->object->getDefaultChannel());
    }

    /**
     * @covers AMQPy\Connection::setDefaultChannel
     *
     *  @expectedException \AMQPConnectionException
     *  @expectedExceptionMessage Channel does not belong to this connection
     */
    public function testSetDefaultChannelFromOtherConnection() {
        $cnn = new Connection();
        $this->assertNotSame($cnn, $this->object);

        $this->object->setDefaultChannel($cnn->getChannel());
    }
}

