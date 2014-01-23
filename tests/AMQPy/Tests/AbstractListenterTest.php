<?php


namespace AMQPy\Tests;

use AMQPy\AbstractListenter;


class AbstractListenterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var AbstractListenter | \PHPUnit_Framework_MockObject_MockObject
     */
    private $object;

    private $prop_delivery_tag = 1337;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $queue_stub;
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $envelope_stub;
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $client_envelope_stub;
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $connection;
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $channel;
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $builder_stub;
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $delivery_stub;
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $properties;
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $serializer_stub;
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $serializers_pool_stub;

    public function setUp()
    {
        $this->queue_stub    = $this->getMockBuilder('\AMQPQueue')
                                    ->setMethods(['getConnection', 'getChannel', 'get', 'consume', 'cancel', 'ack', 'nack'])
                                    ->disableOriginalConstructor()->getMock();
        $this->envelope_stub = $this->getMockBuilder('\AMQPEnvelope')
                                    ->disableOriginalConstructor()->getMock();
        $this->connection    = $this->getMockBuilder('\AMQPConnection')
                                    ->disableOriginalConstructor()->getMock();
        $this->channel       = $this->getMockBuilder('\AMQPChannel')
                                    ->disableOriginalConstructor()->getMock();
        $this->builder_stub  = $this->getMockBuilder('\AMQPy\Support\DeliveryBuilder')
                                    ->disableOriginalConstructor()->getMock();
        $this->properties    = $this->getMockBuilder('\AMQPy\Client\Properties')
                                    ->disableOriginalConstructor()->getMock();

        $this->serializer_stub       = $this->getMock('\AMQPy\Serializers\SerializerInterface');
        $this->serializers_pool_stub = $this->getMock('\AMQPy\Serializers\SerializersPool');

        $this->client_envelope_stub = $this->getMockBuilder('\AMQPy\Client\Envelope')
                                           ->disableOriginalConstructor()
                                           ->getMock();

        $this->client_envelope_stub->expects($this->any())
                                   ->method('getDeliveryTag')
                                   ->will($this->returnValue($this->prop_delivery_tag));


        $this->delivery_stub = $this->getMockBuilder('\AMQPy\Client\Delivery')->disableOriginalConstructor()->getMock();

        $this->delivery_stub->expects($this->any())
                            ->method('getProperties')
                            ->will($this->returnValue($this->properties));

        $this->delivery_stub->expects($this->any())
                            ->method('getBody')
                            ->will($this->returnValue('some body as text'));

        $this->delivery_stub->expects($this->any())
                            ->method('getEnvelope')
                            ->will($this->returnValue($this->client_envelope_stub));

        $connection_read_timeout = 0;

        $this->channel->expects($this->any())
                      ->method('isConnected')
                      ->will($this->returnValue(true));

        $this->connection->expects($this->any())
                         ->method('getReadTimeout')
                         ->will(
                         $this->returnCallback(
                              function () use (&$connection_read_timeout) {
                                  return $connection_read_timeout;
                              }
                         )
            );

        $this->connection->expects($this->any())
                         ->method('setReadTimeout')
                         ->will(
                         $this->returnCallback(
                              function ($read_timeout) use (&$connection_read_timeout) {
                                  $connection_read_timeout = $read_timeout;

                                  return true;
                              }
                         )
            );


        $this->queue_stub->expects($this->any())
                         ->method('getConnection')
                         ->will($this->returnValue($this->connection));

        $this->queue_stub->expects($this->any())
                         ->method('getChannel')
                         ->will($this->returnValue($this->channel));

        $this->queue_stub->expects($this->any())
                         ->method('cancel')
                         ->will($this->returnValue(null));

        $this->queue_stub->expects($this->any())
                         ->method('consume')
                         ->will(
                         $this->returnCallback(
                              function ($callback, $flags) {
                                  while (false !== $res = call_user_func($callback, $this->envelope_stub, $this->queue_stub)) {
                                  }

                              }
                         )
            );

        $this->serializer_stub->expects($this->any())
                              ->method('parse')
                              ->will(
                              $this->returnCallback(
                                   function ($message) {
                                       return 'parsed: ' . $message;
                                   }
                              )
            );


        $this->serializers_pool_stub->expects($this->any())
                                    ->method('get')
                                    ->will($this->returnValue($this->serializer_stub));


        $this->builder_stub->expects($this->any())
                           ->method('build')
                           ->will($this->returnValue($this->delivery_stub));

        $this->object = $this->getMockForAbstractClass(
                             'AMQPy\AbstractListenter',
                             [$this->queue_stub, $this->serializers_pool_stub, $this->builder_stub]
        );
    }

    /**
     * @covers \AMQPy\AbstractListenter::__construct
     * @covers \AMQPy\AbstractListenter::getQueue
     * @covers \AMQPy\AbstractListenter::getSerializers
     * @covers \AMQPy\AbstractListenter::getBuilder
     */
    public function testConstruct()
    {
        $this->assertSame($this->queue_stub, $this->object->getQueue());
        $this->assertSame($this->serializers_pool_stub, $this->object->getSerializers());
        $this->assertSame($this->builder_stub, $this->object->getBuilder());
    }

    /**
     * @covers \AMQPy\AbstractListenter::isEndless
     * @covers \AMQPy\AbstractListenter::setEndless
     */
    public function testEndless()
    {
        $this->assertTrue($this->object->isEndless());

        $this->object->getQueue()->getConnection()->setReadTimeout(111);

        $this->assertFalse($this->object->isEndless());

        $this->assertTrue($this->object->setEndless(true));
        $this->assertTrue($this->object->isEndless());
        $this->assertTrue($this->object->setEndless(true));
        $this->assertTrue($this->object->isEndless());

        $this->assertFalse($this->object->setEndless(false));
        $this->assertFalse($this->object->isEndless());
        $this->assertFalse($this->object->setEndless(false));
        $this->assertFalse($this->object->isEndless());
    }

    /**
     * @covers \AMQPy\AbstractListenter::accept
     */
    public function testAccept()
    {
        $this->queue_stub->expects($this->once())
                         ->method('ack')
                         ->with($this->prop_delivery_tag, AMQP_NOPARAM);

        $this->object->accept($this->delivery_stub);
    }

    /**
     * @covers \AMQPy\AbstractListenter::resend
     */
    public function testResend()
    {
        $this->queue_stub->expects($this->once())
                         ->method('nack')
                         ->with($this->prop_delivery_tag, AMQP_REQUEUE);

        $this->object->resend($this->delivery_stub);
    }

    /**
     * @covers \AMQPy\AbstractListenter::drop
     */
    public function testDrop()
    {
        $this->queue_stub->expects($this->once())
                         ->method('nack')
                         ->with($this->prop_delivery_tag, AMQP_NOPARAM);

        $this->object->drop($this->delivery_stub);
    }

    /**
     * @covers \AMQPy\AbstractListenter::get
     */
    public function testGetNoAutoAck()
    {
        $this->queue_stub->expects($this->exactly(2))
                         ->method('get')
                         ->with(AMQP_NOPARAM)
                         ->will($this->onConsecutiveCalls(null, $this->envelope_stub));

        $this->assertNull($this->object->get());

        $this->assertSame($this->delivery_stub, $this->object->get());
    }

    /**
     * @covers \AMQPy\AbstractListenter::get
     */
    public function testGetAutoAck()
    {
        $this->queue_stub->expects($this->exactly(2))
                         ->method('get')
                         ->with(AMQP_AUTOACK)
                         ->will($this->onConsecutiveCalls(null, $this->envelope_stub));

        $this->assertNull($this->object->get(true));

        $this->assertSame($this->delivery_stub, $this->object->get(true));
    }


    /**
     * @covers \AMQPy\AbstractListenter::consume
     */
    public function testConsumeCancel()
    {
        $consumer = $this->getMock('\AMQPy\AbstractConsumer');

        $consumer->expects($this->exactly(2))
                 ->method('active')
                 ->will($this->onConsecutiveCalls(true, false));

        $this->queue_stub->expects($this->once())
                         ->method('cancel');

        $this->object->consume($consumer);

        $this->queue_stub->expects($this->never())
                         ->method('cancel');
    }

    /**
     * @covers \AMQPy\AbstractListenter::consume
     */
    public function testConsumeInactive()
    {
        $consumer = $this->getMock('\AMQPy\AbstractConsumer');

        $consumer->expects($this->any())
                 ->method('active')
                 ->will($this->returnValue(false));

        $consumer->expects($this->never())
                 ->method('begin');

        $this->object->consume($consumer);
    }

    /**
     * @covers \AMQPy\AbstractListenter::consume
     */
    public function testConsumeActiveOnce()
    {
        $consumer = $this->getMock('\AMQPy\AbstractConsumer');

        $consumer->expects($this->exactly(2))
                 ->method('active')
                 ->will($this->onConsecutiveCalls(true, false));

        $consumer->expects($this->at(1))
                 ->method('begin')
                 ->with($this->object);

        $this->object->expects($this->once())
                     ->method('feed')
                     ->with($this->delivery_stub, $consumer);

        $consumer->expects($this->at(2))
                 ->method('active');

        $consumer->expects($this->at(3))
                 ->method('end')
                 ->with($this->object, null);

        $this->object->consume($consumer);
    }

    /**
     * @covers                   \AMQPy\AbstractListenter::consume
     *
     * @expectedException \Exception
     * @expectedExceptionMessage Test feed exception
     */
    public function testConsumeActiveOnceWithException()
    {
        $consumer = $this->getMock('\AMQPy\AbstractConsumer');
        $e        = new \Exception('Test feed exception');

        $consumer->expects($this->at(0))
                 ->method('active')
                 ->will($this->returnValue(true));

        $consumer->expects($this->at(1))
                 ->method('begin')
                 ->with($this->object);

        $this->object->expects($this->once())
                     ->method('feed')
                     ->with($this->delivery_stub, $consumer)
                     ->will($this->throwException($e));

        $consumer->expects($this->at(2))
                 ->method('end')
                 ->with($this->object, $e);

        $this->object->consume($consumer);
    }

    /**
     * @covers \AMQPy\AbstractListenter::consume
     */
    public function testConsumeActiveTwice()
    {
        $consumer = $this->getMock('\AMQPy\AbstractConsumer');

        $consumer->expects($this->exactly(3))
                 ->method('active')
                 ->will($this->onConsecutiveCalls(true, true, false));

        $consumer->expects($this->once())
                 ->method('begin');

        $this->object->expects($this->exactly(2))
                     ->method('feed');

        $consumer->expects($this->once())
                 ->method('end');

        $this->object->consume($consumer);
    }

    /**
     * @covers \AMQPy\AbstractListenter::consume
     */
    public function testConsumeNoAutoAck()
    {
        $consumer = $this->getMock('\AMQPy\AbstractConsumer');

        $consumer->expects($this->exactly(2))
                 ->method('active')
                 ->will($this->onConsecutiveCalls(true, false));

        $this->queue_stub->expects($this->any())
                         ->method('consume')
                         ->with($this->isType('callable'), AMQP_NOPARAM);

        $this->object->consume($consumer);
    }

    /**
     * @covers \AMQPy\AbstractListenter::consume
     */
    public function testConsumeAutoAck()
    {
        $consumer = $this->getMock('\AMQPy\AbstractConsumer');

        $consumer->expects($this->exactly(2))
                 ->method('active')
                 ->will($this->onConsecutiveCalls(true, false));

        $this->queue_stub->expects($this->any())
                         ->method('consume')
                         ->with($this->isType('callable'), AMQP_AUTOACK);

        $this->object->consume($consumer, true);
    }
}
