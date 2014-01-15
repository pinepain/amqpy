<?php


namespace AMQPy\Tests\Client;

use AMQPy\Client\Channel;

class ChannelTest extends \PHPUnit_Framework_TestCase {

    /**
     * @covers AMQPy\Client\Channel::__construct
     */
    public function testConstruct() {
        $this->testGetConnection();
    }

    /**
     * @covers AMQPy\Client\Channel::getConnection
     */
    public function testGetConnection() {
        $stub = $this->getMock('AMQPy\Client\Connection');

        $channel = new Channel($stub);

        $this->assertSame($stub, $channel->getConnection());
    }

}
 