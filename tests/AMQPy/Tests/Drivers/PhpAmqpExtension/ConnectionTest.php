<?php


namespace AMQPy\Tests\Drivers\PhpAmqpExtension;

use AMQPy\Drivers\PhpAmqpExtension\Connection;
use Mockery as m;

class ConnectionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Connection | \Mockery\Mock
     */
    private $connection;

    protected function setUp()
    {
        $this->connection = m::mock('AMQPy\Drivers\PhpAmqpExtension\Connection')
                             ->makePartial();
    }

    protected function tearDown()
    {
        m::close();
    }

    /**
     * @covers \AMQPy\Drivers\PhpAmqpExtension\Connection::getCredentials
     * @covers \AMQPy\Drivers\PhpAmqpExtension\Connection::__construct
     *
     * @group  interface
     */
    public function testGetCredentials()
    {
        $credentials = array();
        $connection  = new Connection();

        $this->assertEquals($credentials, $connection->getCredentials());

        $credentials = array('user' => 'dummy');
        $connection  = new Connection($credentials);

        $this->assertEquals($credentials, $connection->getCredentials());
    }

    /**
     * @covers \AMQPy\Drivers\PhpAmqpExtension\Connection::isAsync
     * @covers \AMQPy\Drivers\PhpAmqpExtension\Connection::__construct
     *
     * @group  interface
     */
    public function testIsAsync()
    {
        $is_async   = false;
        $connection = new Connection();

        $this->assertEquals($is_async, $connection->isAsync());

        $is_async   = true;
        $connection = new Connection(array(), $is_async);

        $this->assertEquals($is_async, $connection->isAsync());
    }

    /**
     * @covers \AMQPy\Drivers\PhpAmqpExtension\Connection::isPersistent
     * @covers \AMQPy\Drivers\PhpAmqpExtension\Connection::__construct
     *
     * @group  interface
     */
    public function testIsPersistent()
    {
        $is_persistent   = false;
        $connection = new Connection();

        $this->assertEquals($is_persistent, null, $connection->isPersistent());

        $is_persistent   = true;
        $connection = new Connection(array(), null, $is_persistent);

        $this->assertEquals($is_persistent, $connection->isPersistent());
    }

    /**
     * @covers \AMQPy\Drivers\PhpAmqpExtension\Connection::makeClass
     *
     * @group  internals
     */
    public function testMakeClassWithDefaultArguments()
    {
        $connection = $this->connection;

        $this->assertInstanceOf('StdClass', $connection->makeClass('StdClass'));
    }

    /**
     * @covers \AMQPy\Drivers\PhpAmqpExtension\Connection::makeClass
     *
     * @group  internals
     */
    public function testMakeClassWithPresetArguments()
    {
        $connection = $this->connection;

        $datetime = '1999-01-01 01:01:01';
        $instance = $connection->makeClass('DateTime', $datetime);

        $this->assertInstanceOf('DateTime', $instance);
        $this->assertEquals($datetime, $instance->format('Y-m-d H:i:s'));
    }

    /**
     * @covers \AMQPy\Drivers\PhpAmqpExtension\Connection::createChannel
     *
     * @group  internals
     */
    public function testCreateChannel()
    {
        $connection = $this->connection;

        $channel = 'channel object';

        $connection->shouldReceive('connect')->withNoArgs()->once();
        $connection->shouldReceive('makeClass')->with('AMQPChannel', null)->once()->andReturn($channel);

        $this->assertSame($channel, $connection->createChannel());
    }

    /**
     * @covers \AMQPy\Drivers\PhpAmqpExtension\Connection::connect
     *
     * @group  interface
     */
    public function testConnect()
    {
        $connection = $this->connection;

        $amqp_connection = m::mock('stdClass');
        $amqp_connection->shouldReceive('connect')->withNoArgs()->once()->andReturn(true);
        $amqp_connection->shouldReceive('isConnected')->withNoArgs()->once()->andReturn(true);

        $connection->shouldReceive('makeClass')->with('AMQPConnection', array())->once()->andReturn($amqp_connection);

        $this->assertTrue($connection->connect());
        // Connection::connect() method is idempotent
        $this->assertTrue($connection->connect());
    }

    /**
     * @covers \AMQPy\Drivers\PhpAmqpExtension\Connection::connect
     *
     * @group  interface
     */
    public function testConnectPersistent()
    {
        $connection = $this->connection;

        $connection->shouldReceive('isPersistent')->andReturn(true);

        $amqp_connection = m::mock('stdClass');
        $amqp_connection->shouldReceive('pconnect')->withNoArgs()->once()->andReturn(true);
        $amqp_connection->shouldReceive('isConnected')->withNoArgs()->once()->andReturn(true);

        $connection->shouldReceive('makeClass')->with('AMQPConnection', array())->once()->andReturn($amqp_connection);

        $this->assertTrue($connection->connect());
        // Connection::connect() method is idempotent
        $this->assertTrue($connection->connect());
    }


    /**
     * @covers \AMQPy\Drivers\PhpAmqpExtension\Connection::isConnected
     *
     * @group  interface
     */
    public function testIsConnected()
    {
        $connection = $this->connection;

        $amqp_connection = m::mock('stdClass');
        $amqp_connection->shouldReceive('connect')->withNoArgs()->once()->andReturn(true);
        $amqp_connection->shouldReceive('isConnected')->withNoArgs()->twice()->andReturnValues(array(true, false));

        $connection->shouldReceive('makeClass')->with('AMQPConnection', array())->once()->andReturn($amqp_connection);

        $this->assertFalse($connection->isConnected()); // no connection at all

        $connection->connect();

        $this->assertTrue($connection->isConnected()); // established connection
        $this->assertFalse($connection->isConnected()); // connection exists but is disconnected
    }

    /**
     * @covers \AMQPy\Drivers\PhpAmqpExtension\Connection::disconnect
     *
     * @group  interface
     */
    public function testDisconnect()
    {
        $connection = $this->connection;

        $amqp_connection = m::mock('stdClass');
        $amqp_connection->shouldReceive('connect')->withNoArgs()->once()->andReturn(true);
        $amqp_connection->shouldReceive('isConnected')->withNoArgs()->once()->andReturn(true);
        $amqp_connection->shouldReceive('disconnect')->withNoArgs()->once()->andReturn(true);

        $connection->shouldReceive('makeClass')->with('AMQPConnection', array())->once()->andReturn($amqp_connection);

        // no established connection
        $this->assertFalse($connection->isConnected());
        $this->assertTrue($connection->disconnect());

        $connection->connect();

        // we have established connection
        $this->assertTrue($connection->isConnected());
        $this->assertTrue($connection->disconnect());
    }

    /**
     * @covers \AMQPy\Drivers\PhpAmqpExtension\Connection::disconnect
     *
     * @group  interface
     */
    public function testDisconnectPersistent()
    {
        $connection = $this->connection;
        $connection->shouldReceive('isPersistent')->andReturn(true);

        $amqp_connection = m::mock('stdClass');
        $amqp_connection->shouldReceive('pconnect')->withNoArgs()->once()->andReturn(true);
        $amqp_connection->shouldReceive('isConnected')->withNoArgs()->once()->andReturn(true);
        $amqp_connection->shouldReceive('disconnect')->withNoArgs()->once()->andReturn(true);

        $connection->shouldReceive('makeClass')->with('AMQPConnection', array())->once()->andReturn($amqp_connection);

        // no established connection
        $this->assertFalse($connection->isConnected());
        $this->assertTrue($connection->disconnect());

        $connection->connect();

        // we have established connection
        $this->assertTrue($connection->isConnected());
        $this->assertTrue($connection->disconnect());
    }

    /**
     * @covers \AMQPy\Drivers\PhpAmqpExtension\Connection::disconnect
     *
     * @group  interface
     */
    public function testDisconnectPersistentForever()
    {
        $connection = $this->connection;
        $connection->shouldReceive('isPersistent')->andReturn(true);

        $amqp_connection = m::mock('stdClass');
        $amqp_connection->shouldReceive('pconnect')->withNoArgs()->once()->andReturn(true);
        $amqp_connection->shouldReceive('isConnected')->withNoArgs()->once()->andReturn(true);
        $amqp_connection->shouldReceive('pdisconnect')->withNoArgs()->once()->andReturn(true);

        $connection->shouldReceive('makeClass')->with('AMQPConnection', array())->once()->andReturn($amqp_connection);

        // no established connection
        $this->assertFalse($connection->isConnected());
        $this->assertTrue($connection->disconnect(true));

        $connection->connect();

        // we have established connection
        $this->assertTrue($connection->isConnected());
        $this->assertTrue($connection->disconnect(true));
    }

    /**
     * @covers \AMQPy\Drivers\PhpAmqpExtension\Connection::reconnect
     *
     * @group  interface
     */
    public function testReconnect()
    {
        $connection = $this->connection;

        $amqp_connection = m::mock('stdClass');
        $amqp_connection->shouldReceive('connect')->withNoArgs()->once()->andReturn(true);
        $amqp_connection->shouldReceive('reconnect')->withNoArgs()->once()->andReturn(true);
        $amqp_connection->shouldReceive('isConnected')->withNoArgs()->twice()->andReturn(true);

        $connection->shouldReceive('makeClass')->with('AMQPConnection', array())->once()->andReturn($amqp_connection);

        // no established connection
        $this->assertFalse($connection->isConnected());
        $this->assertTrue($connection->reconnect());

        // we have established connection
        $this->assertTrue($connection->isConnected());
        $this->assertTrue($connection->reconnect());

        $this->assertTrue($connection->isConnected());
    }

    /**
     * @covers \AMQPy\Drivers\PhpAmqpExtension\Connection::reconnect
     *
     * @group  interface
     */
    public function testReconnectPersistent()
    {
        $connection = $this->connection;
        $connection->shouldReceive('isPersistent')->andReturn(true);

        $amqp_connection = m::mock('stdClass');
        $amqp_connection->shouldReceive('pconnect')->withNoArgs()->once()->andReturn(true);
        $amqp_connection->shouldReceive('preconnect')->withNoArgs()->once()->andReturn(true);
        $amqp_connection->shouldReceive('isConnected')->withNoArgs()->twice()->andReturn(true);

        $connection->shouldReceive('makeClass')->with('AMQPConnection', array())->once()->andReturn($amqp_connection);

        // no established connection
        $this->assertFalse($connection->isConnected());
        $this->assertTrue($connection->reconnect());

        // we have established connection
        $this->assertTrue($connection->isConnected());
        $this->assertTrue($connection->reconnect());

        $this->assertTrue($connection->isConnected());
    }

    /**
     * @covers \AMQPy\Drivers\PhpAmqpExtension\Connection::wait
     *
     * @group  interface
     */
    public function testWait()
    {
        $connection = $this->connection;

        $this->assertNull($connection->wait());
    }
}