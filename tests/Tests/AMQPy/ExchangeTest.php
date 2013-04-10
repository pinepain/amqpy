<?php
/**
 * @author Bogdan Padalko <pinepain@gmail.com>
 * @created 1/20/13 @ 6:32 PM
 */

namespace Tests\AMQPy;


use AMQPQueue;
use AMQPy\Connection;
use AMQPy\Exchange;
use AMQPy\Serializers\PhpNative;

/**
 * @group core
 */
class ExchangeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Connection
     */
    protected $connection;

    /**
     * @var PhpNative
     */
    protected $serializer;

    protected function setUp()
    {
        $this->connection = new Connection();
        $this->connection->connect();
        $this->serializer = new PhpNative();
    }

    protected function tearDown()
    {
        $this->connection->disconnect();
    }

    /**
     * @covers AMQPy\Exchange::__construct
     * @covers AMQPy\Exchange::getConnection
     * @covers AMQPy\Exchange::getSerializer
     */
    public function testConstructor()
    {
        $ex = new Exchange($this->connection->getDefaultChannel(), $this->serializer);
        $this->assertSame($this->connection->getDefaultChannel(), $ex->getChannel());
        $this->assertSame($this->serializer, $ex->getSerializer());

    }

    /**
     * @covers AMQPy\Connection::setDefaultChannel
     */
    public function testSend()
    {
        $time         = microtime(true);
        $exhange_name = 'test.exchange.' . $time;
        $queue_name   = 'test.exchange.' . $time;
        $route_key    = 'route.key1.' . $time;

        $message = array('datetime' => new \DateTime(), 'test' => 'message');
        $headers = array('one' => 'two');

        $ex = new Exchange($this->connection->getDefaultChannel(), $this->serializer);
        $ex->setType(AMQP_EX_TYPE_FANOUT);
        $ex->setName($exhange_name);
        $ex->declare();

        $q = new AMQPQueue($this->connection->getChannel());
        $q->setName($queue_name);
        $q->declare();

        $q->bind($exhange_name, $route_key);

        $ex->send($message, AMQP_NOPARAM, array('headers' => $headers));
        $m = $q->get(AMQP_AUTOACK);

        $this->assertEquals($message, $this->serializer->parse($m->getBody()));
        $this->assertEquals($this->serializer->getContentType(), $m->getHeader('content_type'));

        $_h = $m->getHeaders();
        unset($_h['content_type']);

        $this->assertEquals($headers, $_h);

        $headers['content_type'] = 'will be overridden silently';

        $ex->send($message, AMQP_NOPARAM, array('headers' => $headers));
        $m = $q->get(AMQP_AUTOACK);

        $this->assertEquals($this->serializer->getContentType(), $m->getHeader('content_type'));
    }

    /**
     * @covers AMQPy\Connection::getDefaultChannel
     */
    public function testGetDefaultChannel()
    {
        $ch = $this->object->getDefaultChannel();
        $this->assertInstanceOf('\AMQPy\Channel', $ch);
        $this->assertSame($ch, $this->object->getDefaultChannel());
    }

    /**
     * @covers AMQPy\Connection::setDefaultChannel
     */
    public function testSetDefaultChannelImplicit()
    {
        $ch     = $this->object->getChannel();
        $ch_set = $this->object->setDefaultChannel($ch);

        $this->assertSame($ch, $ch_set);
        $this->assertSame($ch, $this->object->getDefaultChannel());
    }

    /**
     * @covers AMQPy\Connection::setDefaultChannel
     */
    public function testSetDefaultChannelExplicit()
    {
        $ch = $this->object->setDefaultChannel();

        $this->assertInstanceOf('\AMQPy\Channel', $ch);
        $this->assertSame($ch, $this->object->getDefaultChannel());
    }

    /**
     * @covers AMQPy\Connection::setDefaultChannel
     *
     * @expectedException \AMQPConnectionException
     * @expectedExceptionMessage Channel does not belong to this connection
     */
    public function testSetDefaultChannelFromOtherConnection()
    {
        $cnn = new Connection();
        $this->assertNotSame($cnn, $this->object);

        $this->object->setDefaultChannel($cnn->getChannel());
    }
}

