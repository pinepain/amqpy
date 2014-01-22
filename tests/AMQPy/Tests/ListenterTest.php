<?php


namespace AMQPy\Tests;

use AMQPy\Listenter;

class ListenerGoodFeedHelper extends Listenter
{
    public function feed()
    {
    }
}

class ListenerBadFeedHelper extends Listenter
{
    public function feed()
    {
        throw new \Exception('Test feed exception');
    }
}

class ListenterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Listenter
     */
    private $object;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $queue_stub;
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $envelope;
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
    private $delivery;
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
    private $pool_stub;

    public function setUp()
    {
        $this->queue_stub      = $this->getMock('\AMQPQueue', ['getConnection', 'getChannel', 'get', 'consume', 'cancel'], [], '', false);
        $this->envelope        = $this->getMock('\AMQPEnvelope', [], [], '', false);
        $this->connection      = $this->getMock('\AMQPConnection', [], [], '', false);
        $this->channel         = $this->getMock('\AMQPChannel', [], [], '', false);
        $this->builder_stub         = $this->getMock('\AMQPy\Support\DeliveryBuilder', [], [], '', false);
        $this->delivery        = $this->getMock('\AMQPy\Client\Delivery', [], [], '', false);
        $this->properties      = $this->getMock('\AMQPy\Client\Properties', [], [], '', false);
        $this->serializer_stub = $this->getMock('\AMQPy\Serializers\SerializerInterface');
        $this->pool_stub       = $this->getMock('\AMQPy\Serializers\SerializersPool');

        $this->delivery->expects($this->any())
                       ->method('getProperties')
                       ->will($this->returnValue($this->properties));

        $this->delivery->expects($this->any())
                       ->method('getBody')
                       ->will($this->returnValue('some body as text'));

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
                         ->method('get')
                         ->will($this->returnValue($this->envelope));

        $this->queue_stub->expects($this->any())
                         ->method('cancel')
                         ->will($this->returnValue(null));

        $this->queue_stub->expects($this->any())
                         ->method('consume')
                         ->will(
                         $this->returnCallback(
                              function ($callback, $flags) {
//                                  call_user_func($callback, $this->envelope, $this->queue_stub);
                                  while (false !== $res = call_user_func($callback, $this->envelope, $this->queue_stub)) {
                                      var_dump($res);
                                      die;
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


        $this->pool_stub->expects($this->any())
                        ->method('get')
            //->with('test/serializer')
                        ->will($this->returnValue($this->serializer_stub));


        $this->builder_stub->expects($this->any())
                      ->method('build')
                      ->will($this->returnValue($this->delivery));

        $this->object = new Listenter($this->queue_stub, $this->pool_stub, $this->builder_stub);
    }

    /**
     * @covers \AMQPy\Listenter::__construct
     * @covers \AMQPy\Listenter::getQueue
     * @covers \AMQPy\Listenter::getSerializers
     * @covers \AMQPy\Listenter::getBuilder
     */
    public function testConstruct()
    {
        $queue       = $this->getMock('\AMQPQueue', [], [], '', false);
        $serializers = $this->getMock('\AMQPy\Serializers\SerializersPool', [], [], '', false);
        $builder     = $this->getMock('\AMQPy\Support\DeliveryBuilder', [], [], '', false);

        $listener = new Listenter($queue, $serializers, $builder);

        $this->assertSame($queue, $listener->getQueue());
        $this->assertSame($serializers, $listener->getSerializers());
        $this->assertSame($builder, $listener->getBuilder());
    }

    /**
     * @covers \AMQPy\Listenter::isEndless
     * @covers \AMQPy\Listenter::setEndless
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
     * @covers \AMQPy\Listenter::accept
     */
    public function testAccept()
    {
        $queue       = $this->getMock('\AMQPQueue', [], [], '', false);
        $serializers = $this->getMock('\AMQPy\Serializers\SerializersPool', [], [], '', false);
        $builder     = $this->getMock('\AMQPy\Support\DeliveryBuilder', [], [], '', false);

        $envelope = $this->getMock('\AMQPEnvelope', [], [], '', false);

        $delivery = $this->getMock('\AMQPy\Client\Delivery', [], [], '', false);
        $delivery->expects($this->any())
                 ->method('getEnvelope')
                 ->will($this->returnValue($envelope));

        $queue->expects($this->once())
              ->method('ack');

        $listener = new Listenter($queue, $serializers, $builder);

        $listener->accept($delivery);
    }

    /**
     * @covers \AMQPy\Listenter::resend
     */
    public function testResend()
    {
        $queue       = $this->getMock('\AMQPQueue', [], [], '', false);
        $serializers = $this->getMock('\AMQPy\Serializers\SerializersPool', [], [], '', false);
        $builder     = $this->getMock('\AMQPy\Support\DeliveryBuilder', [], [], '', false);

        $envelope = $this->getMock('\AMQPEnvelope', [], [], '', false);

        $delivery = $this->getMock('\AMQPy\Client\Delivery', [], [], '', false);
        $delivery->expects($this->any())
                 ->method('getEnvelope')
                 ->will($this->returnValue($envelope));

        $queue->expects($this->once())
              ->method('nack');

        $listener = new Listenter($queue, $serializers, $builder);

        $listener->resend($delivery);
    }

    /**
     * @covers \AMQPy\Listenter::drop
     */
    public function testDrop()
    {
        $queue       = $this->getMock('\AMQPQueue', [], [], '', false);
        $serializers = $this->getMock('\AMQPy\Serializers\SerializersPool', [], [], '', false);
        $builder     = $this->getMock('\AMQPy\Support\DeliveryBuilder', [], [], '', false);

        $envelope = $this->getMock('\AMQPEnvelope', [], [], '', false);

        $delivery = $this->getMock('\AMQPy\Client\Delivery', [], [], '', false);
        $delivery->expects($this->any())
                 ->method('getEnvelope')
                 ->will($this->returnValue($envelope));

        $queue->expects($this->once())
              ->method('nack');

        $listener = new Listenter($queue, $serializers, $builder);

        $listener->drop($delivery);
    }

    /**
     * @covers \AMQPy\Listenter::get
     */
    public function testGet()
    {
        $queue       = $this->getMock('\AMQPQueue', ['get'], [], '', false);
        $serializers = $this->getMock('\AMQPy\Serializers\SerializersPool', [], [], '', false);
        $builder     = $this->getMock('\AMQPy\Support\DeliveryBuilder', [], [], '', false);
        $envelope    = $this->getMock('\AMQPEnvelope', [], [], '', false);
        $delivery    = $this->getMock('\AMQPy\Client\Delivery', [], [], '', false);

        $builder->expects($this->any())
                ->method('build')
                ->will($this->returnValue($delivery));

        $queue->expects($this->at(0))
              ->method('get')
              ->will($this->returnValue(null));

        $queue->expects($this->at(1))
              ->method('get')
              ->will($this->returnValue($envelope));

        $listener = new Listenter($queue, $serializers, $builder);

        $this->assertNull($listener->get());

        $this->assertInstanceOf('\AMQPy\Client\Delivery', $listener->get());
    }

    /**
     * @covers \AMQPy\Listenter::consume
     * @covers \AMQPy\Listenter::feed
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
     * @covers \AMQPy\Listenter::consume
     * @covers \AMQPy\Listenter::feed
     */
    public function testConsumeInactive()
    {
        $queue       = $this->getMock('\AMQPQueue', ['get'], [], '', false);
        $serializers = $this->getMock('\AMQPy\Serializers\SerializersPool', [], [], '', false);
        $builder     = $this->getMock('\AMQPy\Support\DeliveryBuilder', [], [], '', false);

        $consumer = $this->getMock('\AMQPy\AbstractConsumer');

        $consumer->expects($this->any())
                 ->method('active')
                 ->will($this->returnValue(false));

        $consumer->expects($this->never())
                 ->method('begin');

        $listener = new Listenter($queue, $serializers, $builder);

        $listener->consume($consumer);
    }

    /**
     * @covers \AMQPy\Listenter::consume
     * @covers \AMQPy\Listenter::feed
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

        $consumer->expects($this->at(3))
                 ->method('consume');

        $consumer->expects($this->at(6))
                 ->method('active');

        $consumer->expects($this->at(7))
                 ->method('end')
                 ->with($this->object, null);

        $listener = new ListenerGoodFeedHelper($this->queue_stub, $this->serializer_stub, $this->builder_stub);
        $this->object->consume($consumer);
    }

    /**
     * @covers                   \AMQPy\Listenter::feed
     */
    public function testConsumeActiveOnceWithConsumerException()
    {
        $consumer   = $this->getMock('\AMQPy\AbstractConsumer');
        $e_consumer = new \Exception('Test consumer exception');

        $consumer->expects($this->exactly(2))
                 ->method('active')
                 ->will($this->onConsecutiveCalls(true, false));

        $consumer->expects($this->at(1))
                 ->method('begin')
                 ->with($this->object);

        $consumer->expects($this->at(3))
                 ->method('consume')
                 ->will($this->throwException($e_consumer));

        $consumer->expects($this->at(4))
                 ->method('failure')
                 ->with($e_consumer, $this->delivery, $this->object);

        $consumer->expects($this->at(7))
                 ->method('end')
                 ->with($this->object);

        $this->object->consume($consumer);
    }

    /**
     * @covers                   \AMQPy\Listenter::consume
     * @covers                   \AMQPy\Listenter::feed
     *
     * @expectedException \Exception
     * @expectedExceptionMessage Test failure exception
     */
    public function testConsumeActiveOnceWithFailureException()
    {
        $consumer  = $this->getMock('\AMQPy\AbstractConsumer');
        $e_failure = new \Exception('Test failure exception');

        $consumer->expects($this->exactly(1))
                 ->method('active')
                 ->will($this->returnValue(true));

        $consumer->expects($this->at(1))
                 ->method('begin')
                 ->with($this->object);

        $consumer->expects($this->at(3))
                 ->method('consume');

        $consumer->expects($this->once())
                 ->method('failure')
                 ->will($this->throwException($e_failure));

        $consumer->expects($this->at(5))
                 ->method('end')
                 ->with($this->object, $e_failure);

        $this->object->consume($consumer);
    }


    /**
     * @covers \AMQPy\Listenter::consume
     * @covers \AMQPy\Listenter::feed
     */
    public function testConsumeActiveTwice()
    {
        $consumer = $this->getMock('\AMQPy\AbstractConsumer');

        $consumer->expects($this->exactly(3))
                 ->method('active')
                 ->will($this->onConsecutiveCalls(true, true, false));

        $consumer->expects($this->once())
                 ->method('begin');


        $consumer->expects($this->exactly(2))
                 ->method('consume');

        $consumer->expects($this->exactly(2))
                 ->method('after');

        $consumer->expects($this->once())
                 ->method('end');

        $consumer->expects($this->never())
                 ->method('failure');

        $this->object->consume($consumer);
    }

    /**
     * @covers                        \AMQPy\Listenter::consume
     * @covers                        \AMQPy\Listenter::feed
     *
     * @SKIP_expectedException \Exception
     * @SKIP_expectedExceptionMessage Test consumer exception
     */
    public function SKIP_testConsumeActiveWithConsumerException()
    {
        $consumer = $this->getMock('\AMQPy\AbstractConsumer');
        $e        = new \Exception('Test consumer exception');

//        $this->isType('callable'),

        $consumer->expects($this->at(0))
                 ->method('active')
                 ->will($this->returnValue(true));

        $consumer->expects($this->at(1))
                 ->method('begin');

        $consumer->expects($this->at(2))
                 ->method('before');

        $consumer->expects($this->at(3))
                 ->method('consume')
                 ->will($this->throwException($e));

        $consumer->expects($this->at(4))
                 ->method('failure')
                 ->with($e, $this->delivery, $this->object);

        $consumer->expects($this->at(5))
                 ->method('after')
                 ->with($this->delivery, $this->object, $e);

        $consumer->expects($this->at(6))
                 ->method('active')
                 ->will($this->returnValue(false));

        $consumer->expects($this->at(7))
                 ->method('end')
                 ->with($this->object, null);


        $this->object->consume($consumer);
    }

    /**
     * @covers \AMQPy\Listenter::consume
     * @covers \AMQPy\Listenter::feed
     */
    public function SKIP_testConsumeActiveWithBeginException()
    {
        $consumer = $this->getMock('\AMQPy\AbstractConsumer');
        $e        = new \Exception('test consumer exception');

        $consumer->expects($this->at(0))
                 ->method('active')
                 ->will($this->returnValue(true));

        $consumer->expects($this->at(1))
                 ->method('begin');

        $consumer->expects($this->at(2))
                 ->method('before');

        $consumer->expects($this->at(3))
                 ->method('consume')
                 ->will($this->throwException($e));

        $consumer->expects($this->at(4))
                 ->method('failure')
                 ->with($e, $this->delivery, $this->object);

        $consumer->expects($this->at(5))
                 ->method('after')
                 ->with($this->delivery, $this->object, $e);

        $consumer->expects($this->at(6))
                 ->method('active')
                 ->will($this->returnValue(false));

        $consumer->expects($this->at(7))
                 ->method('end')
                 ->with($this->object, null);


        $this->object->consume($consumer);
    }

    /**
     * @covers \AMQPy\Listenter::consume
     */
    public function testConsumeActiveWithBeginException()
    {
        $consumer = $this->getMock('\AMQPy\AbstractConsumer');

        $this->object->consume($consumer);
    }
}
