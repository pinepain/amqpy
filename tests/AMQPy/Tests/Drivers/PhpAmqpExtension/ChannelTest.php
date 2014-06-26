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

    protected function setUp()
    {
        $this->channel = m::mock('AMQPy\Drivers\PhpAmqpExtension\Channel')
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
        $channel = new Channel($connection);
        
        $this->assertSame($connection, $channel->getConnection());
    }

    /**
     * @covers \AMQPy\Drivers\PhpAmqpExtensionDriver::connect
     *
     * @group  interface
     */
    public function testConnectWhenConnected()
    {
        $driver = $this->driver;

        $driver->shouldReceive('isConnected')->withNoArgs()->once()->andReturn(true);
        $driver->shouldReceive('disconnect')->withNoArgs()->once();

        $this->assertNull($driver->connect());
    }

    /**
     * @covers                   \AMQPy\Drivers\PhpAmqpExtensionDriver::getActiveConnection
     *
     * @expectedException \AMQPy\Drivers\DriverException
     * @expectedExceptionMessage No connection credentials set
     *
     * @group                    internals
     */
    public function testGetActiveConnectionWhenNoCredentialsSet()
    {
        $driver = $this->driver;

        $driver->getActiveConnection();
    }

    /**
     * @covers \AMQPy\Drivers\PhpAmqpExtensionDriver::getActiveConnection
     * @covers \AMQPy\Drivers\PhpAmqpExtensionDriver::refreshInternals
     *
     * @group  internals
     */
    public function testGetActiveConnection()
    {
        $driver = $this->driver;

        $connection = m::mock('stdClass');
        $connection->shouldReceive('connect')->once();
        $connection->shouldReceive('isConnected')->once()->andReturn(false);
        $connection->shouldReceive('reconnect')->once();

        $credentials = array('user' => 'dummy');

        $driver->connect($credentials);

        $driver->shouldReceive('makeClass')
               ->with('AMQPConnection', array($credentials))
               ->once()
               ->andReturn($connection);

        $this->assertSame($connection, $driver->getActiveConnection());
        $this->assertSame($connection, $driver->getActiveConnection());
    }

    /**
     * @covers \AMQPy\Drivers\PhpAmqpExtensionDriver::getActiveChannel
     * @covers \AMQPy\Drivers\PhpAmqpExtensionDriver::refreshInternals
     *
     * @group  internals
     */
    public function testGetActiveChannel()
    {
        $driver = $this->driver;

        $dead_channel = m::mock('stdClass');
        $dead_channel->shouldReceive('isConnected')->once()->andReturn(false);

        $channel = m::mock('stdClass');
        $channel->shouldReceive('isConnected')->once()->andReturn(true);

        $connection = m::mock('stdClass');

        $driver->shouldReceive('getActiveConnection')->twice()->andReturn($connection);

        $driver->shouldReceive('makeClass')
               ->with('AMQPChannel', array($connection))
               ->twice()
               ->andReturnValues(array($dead_channel, $channel));

        $this->assertSame($dead_channel, $driver->getActiveChannel());
        $this->assertSame($channel, $driver->getActiveChannel());
        $this->assertSame($channel, $driver->getActiveChannel());
    }

    /**
     * @covers \AMQPy\Drivers\PhpAmqpExtensionDriver::isConnected
     *
     * @group  interface
     */
    public function testIsConnected()
    {
        $driver = $this->driver;

        $this->assertFalse($driver->isConnected());

//        $channel = m::mock('stdClass');
//        $channel->shouldReceive('isConnected')->once()->andReturn(true);

//        $connection = m::mock('stdClass');
//        $connection->shouldReceive('isConnected')->once()->andReturn(true);

//        $driver->shouldReceive('makeClass')->with('AMQPChannel')->andReturn($channel);
//        $driver->shouldReceive('makeClass')->with('AMQPConnection')->andReturn($connection);

    }

    /**
     * @covers \AMQPy\Drivers\PhpAmqpExtensionDriver::isConnected
     *
     * @group  interface
     */
    public function testIsConnectedOnChannel()
    {
        $driver = $this->driver;

        $connection = m::mock('stdClass');

        $channel = m::mock('stdClass');
        $channel->shouldReceive('isConnected')->twice()->andReturnValues(array(true, false));

        $driver->shouldReceive('makeClass')->with('AMQPConnection', array(array()))->andReturn($connection);
        $driver->shouldReceive('makeClass')->with('AMQPChannel', array($connection))->andReturn($channel);

        $this->assertFalse($driver->isConnected());

        $driver->getActiveChannel();

        $this->assertTrue($driver->isConnected());

//        $channel = m::mock('stdClass');
//        $channel->shouldReceive('isConnected')->once()->andReturn(true);

//        $connection = m::mock('stdClass');
//        $connection->shouldReceive('isConnected')->once()->andReturn(true);

//        $driver->shouldReceive('makeClass')->with('AMQPChannel')->andReturn($channel);
//        $driver->shouldReceive('makeClass')->with('AMQPConnection')->andReturn($connection);

    }

    /**
     * @covers \AMQPy\Drivers\PhpAmqpExtensionDriver::disconnect
     * @covers \AMQPy\Drivers\PhpAmqpExtensionDriver::refreshInternals
     *
     * @group  interface
     */
    public function testDisconnectWhenNotConnected()
    {
        $driver = $this->driver;

        $this->assertNull($driver->disconnect());
    }

    /**
     * @covers \AMQPy\Drivers\PhpAmqpExtensionDriver::disconnect
     *
     * @group  interface
     */
    public function testDisconnectWhenConnected()
    {
        $driver = $this->driver;

        $connection = m::mock('stdClass');
        $connection->shouldReceive('isConnected')->twice()->andReturn(true);
        $connection->shouldReceive('connect')->once();
        $connection->shouldReceive('disconnect')->once();

        $credentials = array('user' => 'dummy');

        $driver->shouldReceive('makeClass')
               ->with('AMQPConnection', array($credentials))
               ->once()
               ->andReturn($connection);

        $driver->connect($credentials);
        $driver->getActiveConnection(); // do real connection

        $this->assertTrue($driver->isConnected());

        $this->assertTrue($driver->disconnect());
        $this->assertFalse($driver->isConnected());
    }

}
 