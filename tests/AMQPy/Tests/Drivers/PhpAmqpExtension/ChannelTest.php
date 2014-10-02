<?php


namespace AMQPy\Tests\Drivers\PhpAmqpExtension;

use AMQPy\Drivers\PhpAmqpExtension\Channel;
use Mockery as m;

class ChannelTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Channel | \Mockery\Mock
     */
    private $channel;

    /**
     * @var \AMQPy\Drivers\PhpAmqpExtension\Connection | \Mockery\Mock
     */
    private $connection;

    protected function setUp()
    {
        $this->connection = m::mock('stdClass');

        $this->channel = m::mock('AMQPy\Drivers\PhpAmqpExtension\Channel', array($this->connection))
                          ->makePartial();
    }

    protected function tearDown()
    {
        m::close();
    }

    /**
     * @covers \AMQPy\Drivers\PhpAmqpExtension\Channel::getConnection
     * @covers \AMQPy\Drivers\PhpAmqpExtension\Channel::__construct
     *
     * @group  interface
     */
    public function testGetConnection()
    {
        $connection = m::mock('stdClass');
        $channel    = new Channel($connection);

        $this->assertSame($connection, $channel->getConnection());
    }

    /**
     * @covers \AMQPy\Drivers\PhpAmqpExtension\Channel::isAsync
     * @covers \AMQPy\Drivers\PhpAmqpExtension\Channel::__construct
     *
     * @group  interface
     */
    public function testIsAsync()
    {
        /** @var  $connection */
        $this->connection->shouldReceive('isAsync')->withNoArgs()->once()->andReturn(true);
        $this->assertTrue($this->channel->isAsync());
    }

    /**
     * @covers \AMQPy\Drivers\PhpAmqpExtension\Channel::wait
     *
     * @group  interface
     */
    public function testWait()
    {
        // dummy test case to show that there are no waiting and to make coverage fans happy

        $time = microtime(true);
        $this->assertNull($this->channel->wait());
        $this->assertLessThan(1, microtime(true) - $time);
    }

    /**
     * @covers \AMQPy\Drivers\PhpAmqpExtension\Channel::connect
     *
     * @group  interface
     */
    public function testConnect()
    {
        $amqp_channel = m::mock('stdClass');
        $amqp_channel->shouldReceive('isConnected')->withNoArgs()->twice()->andReturn(true);

        $this->connection->shouldReceive('createChannel')->withNoArgs()->once()->andReturn($amqp_channel);

        $this->assertTrue($this->channel->connect());
        $this->assertTrue($this->channel->connect());
    }

    /**
     * @covers \AMQPy\Drivers\PhpAmqpExtension\Channel::isConnected
     *
     * @group  interface
     */
    public function testIsConnectedWhenDisconnected()
    {
        $this->assertFalse($this->channel->isConnected());
    }

    /**
     * @covers \AMQPy\Drivers\PhpAmqpExtension\Channel::isConnected
     *
     * @group  interface
     */
    public function testIsConnectedWhenConnected()
    {
        $amqp_channel = m::mock('stdClass');
        $amqp_channel->shouldReceive('isConnected')->withNoArgs()->times(3)->andReturnValues(array(true, true, false));

        $this->connection->shouldReceive('createChannel')->withNoArgs()->once()->andReturn($amqp_channel);

        $this->assertTrue($this->channel->connect());

        $this->assertTrue($this->channel->isConnected());
        $this->assertFalse($this->channel->isConnected());
    }

    /**
     * @covers \AMQPy\Drivers\PhpAmqpExtension\Channel::disconnect
     *
     * @group  interface
     */
    public function testDisconnectWhenNotConnected()
    {

        $this->assertFalse($this->channel->isConnected());
        $this->assertTrue($this->channel->disconnect());
        $this->assertFalse($this->channel->isConnected());
    }

    /**
     * @covers \AMQPy\Drivers\PhpAmqpExtension\Channel::disconnect
     *
     * @group  interface
     */
    public function testDisconnectWhenConnected()
    {
        $amqp_channel = m::mock('stdClass');
        $amqp_channel->shouldReceive('isConnected')->withNoArgs()->twice()->andReturn(true);

        $this->connection->shouldReceive('createChannel')->withNoArgs()->once()->andReturn($amqp_channel);

        $this->channel->connect();
        $this->assertTrue($this->channel->isConnected());

        $this->assertTrue($this->channel->disconnect());
        $this->assertFalse($this->channel->isConnected());
    }

    /**
     * @covers \AMQPy\Drivers\PhpAmqpExtension\Channel::reconnect
     *
     * @group  interface
     */
    public function testReconnect()
    {
        $this->channel->shouldReceive('disconnect')->withNoArgs()->andReturn(true)->once()->ordered();
        $this->channel->shouldReceive('connect')->withNoArgs()->andReturn(true)->once()->ordered();

        $this->assertTrue($this->channel->reconnect());
    }

    /**
     * @covers \AMQPy\Drivers\PhpAmqpExtension\Channel::getActiveChannel
     *
     * @group  driver-specific
     */
    public function testGetActiveChannel()
    {
        $this->assertFalse($this->channel->isConnected());

        $amqp_channel = m::mock('stdClass');
        $amqp_channel->shouldReceive('isConnected')->withNoArgs()->once()->andReturn(true);

        $this->connection->shouldReceive('createChannel')->withNoArgs()->once()->andReturn($amqp_channel);

        $this->assertSame($amqp_channel, $this->channel->getActiveChannel());
        $this->assertSame($amqp_channel, $this->channel->getActiveChannel());
    }

    /**
     * @covers \AMQPy\Drivers\PhpAmqpExtension\Channel::getActiveExchange
     *
     * @group  driver-specific
     */
    public function testGetActiveExchange()
    {
        $this->assertFalse($this->channel->isConnected());

        $this->channel->shouldReceive('connect')->withNoArgs()->once();

        $amqp_exchange = m::mock('stdClass');
        $this->connection->shouldReceive('makeClass')->once()->with('AMQPExchange', null)->andReturn($amqp_exchange);

        $this->assertSame($amqp_exchange, $this->channel->getActiveExchange());
        $this->assertSame($amqp_exchange, $this->channel->getActiveExchange());
    }

    /**
     * @covers \AMQPy\Drivers\PhpAmqpExtension\Channel::getActiveQueue
     *
     * @group  driver-specific
     */
    public function testGetActiveQueue()
    {
        $this->assertFalse($this->channel->isConnected());

        $this->channel->shouldReceive('connect')->withNoArgs()->once();

        $amqp_queue = m::mock('stdClass');
        $this->connection->shouldReceive('makeClass')->once()->with('AMQPQueue', null)->andReturn($amqp_queue);

        $this->assertSame($amqp_queue, $this->channel->getActiveQueue());
        $this->assertSame($amqp_queue, $this->channel->getActiveQueue());
    }


    // !!! INVALID TEST CASES BELOW !!! TODO: refactor them


//    /**
//     * @covers                   \AMQPy\Drivers\PhpAmqpExtensionDriver::getActiveConnection
//     *
//     * @expectedException \AMQPy\Drivers\DriverException
//     * @expectedExceptionMessage No connection credentials set
//     *
//     * @group                    internals
//     */
//    public function testGetActiveConnectionWhenNoCredentialsSet()
//    {
//        $driver = $this->driver;
//
//        $driver->getActiveConnection();
//    }
//
//    /**
//     * @covers \AMQPy\Drivers\PhpAmqpExtensionDriver::getActiveConnection
//     * @covers \AMQPy\Drivers\PhpAmqpExtensionDriver::refreshInternals
//     *
//     * @group  internals
//     */
//    public function testGetActiveConnection()
//    {
//        $driver = $this->driver;
//
//        $connection = m::mock('stdClass');
//        $connection->shouldReceive('connect')->once();
//        $connection->shouldReceive('isConnected')->once()->andReturn(false);
//        $connection->shouldReceive('reconnect')->once();
//
//        $credentials = array('user' => 'dummy');
//
//        $driver->connect($credentials);
//
//        $driver->shouldReceive('makeClass')
//               ->with('AMQPConnection', array($credentials))
//               ->once()
//               ->andReturn($connection);
//
//        $this->assertSame($connection, $driver->getActiveConnection());
//        $this->assertSame($connection, $driver->getActiveConnection());
//    }
//
//    /**
//     * @covers \AMQPy\Drivers\PhpAmqpExtensionDriver::getActiveChannel
//     * @covers \AMQPy\Drivers\PhpAmqpExtensionDriver::refreshInternals
//     *
//     * @group  internals
//     */
//    public function testGetActiveChannel()
//    {
//        $driver = $this->driver;
//
//        $dead_channel = m::mock('stdClass');
//        $dead_channel->shouldReceive('isConnected')->once()->andReturn(false);
//
//        $channel = m::mock('stdClass');
//        $channel->shouldReceive('isConnected')->once()->andReturn(true);
//
//        $connection = m::mock('stdClass');
//
//        $driver->shouldReceive('getActiveConnection')->twice()->andReturn($connection);
//
//        $driver->shouldReceive('makeClass')
//               ->with('AMQPChannel', array($connection))
//               ->twice()
//               ->andReturnValues(array($dead_channel, $channel));
//
//        $this->assertSame($dead_channel, $driver->getActiveChannel());
//        $this->assertSame($channel, $driver->getActiveChannel());
//        $this->assertSame($channel, $driver->getActiveChannel());
//    }
//
//    /**
//     * @covers \AMQPy\Drivers\PhpAmqpExtensionDriver::isConnected
//     *
//     * @group  interface
//     */
//    public function testIsConnected()
//    {
//        $driver = $this->driver;
//
//        $this->assertFalse($driver->isConnected());
//
////        $channel = m::mock('stdClass');
////        $channel->shouldReceive('isConnected')->once()->andReturn(true);
//
////        $connection = m::mock('stdClass');
////        $connection->shouldReceive('isConnected')->once()->andReturn(true);
//
////        $driver->shouldReceive('makeClass')->with('AMQPChannel')->andReturn($channel);
////        $driver->shouldReceive('makeClass')->with('AMQPConnection')->andReturn($connection);
//
//    }
//
//    /**
//     * @covers \AMQPy\Drivers\PhpAmqpExtensionDriver::isConnected
//     *
//     * @group  interface
//     */
//    public function testIsConnectedOnChannel()
//    {
//        $driver = $this->driver;
//
//        $connection = m::mock('stdClass');
//
//        $channel = m::mock('stdClass');
//        $channel->shouldReceive('isConnected')->twice()->andReturnValues(array(true, false));
//
//        $driver->shouldReceive('makeClass')->with('AMQPConnection', array(array()))->andReturn($connection);
//        $driver->shouldReceive('makeClass')->with('AMQPChannel', array($connection))->andReturn($channel);
//
//        $this->assertFalse($driver->isConnected());
//
//        $driver->getActiveChannel();
//
//        $this->assertTrue($driver->isConnected());
//
////        $channel = m::mock('stdClass');
////        $channel->shouldReceive('isConnected')->once()->andReturn(true);
//
////        $connection = m::mock('stdClass');
////        $connection->shouldReceive('isConnected')->once()->andReturn(true);
//
////        $driver->shouldReceive('makeClass')->with('AMQPChannel')->andReturn($channel);
////        $driver->shouldReceive('makeClass')->with('AMQPConnection')->andReturn($connection);
//
//    }
//

}
 