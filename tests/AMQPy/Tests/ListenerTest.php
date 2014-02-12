<?php


namespace AMQPy\Tests;

use AMQPy\Listener;

class ListenerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Listener | \PHPUnit_Framework_MockObject_MockObject
     */
    private $object;

    private $prop_content_type = 'test/type';
    private $prop_message_body = 'message body sample';

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $serializer_stub;
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $serializers_pool_stub;
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $properties_stub;
    /**
     * @var \AMQPy\Client\Delivery | \PHPUnit_Framework_MockObject_MockObject
     */
    private $delivery_stub;
    /**
     * @var \AMQPy\AbstractConsumer | \PHPUnit_Framework_MockObject_MockObject
     */
    private $consumer_stub;

    public function setUp()
    {
        $this->serializer_stub = $this->getMock('\AMQPy\Serializers\SerializerInterface');

        $this->serializer_stub->expects($this->any())
                              ->method('parse')
                              ->will(
                              $this->returnCallback(
                                   function ($message) {
                                       return 'parsed (' . $this->prop_content_type . '): ' . $message;
                                   }
                              )
            );

        $this->serializers_pool_stub = $this->getMock('\AMQPy\Serializers\SerializersPool');
        $this->serializers_pool_stub->expects($this->any())
                                    ->method('get')
                                    ->with($this->prop_content_type)
                                    ->will($this->returnValue($this->serializer_stub));

        $this->object = $this->getMockBuilder('\AMQPy\Listener')
                             ->setMethods(['getSerializers'])
                             ->disableOriginalConstructor()->getMock();

        $this->object->expects($this->any())
                     ->method('getSerializers')
                     ->will($this->returnValue($this->serializers_pool_stub));


        $this->properties_stub = $this->getMockBuilder('\AMQPy\Client\Properties')
                                      ->disableOriginalConstructor()->getMock();

        $this->properties_stub->expects($this->any())
                              ->method('getContentType')
                              ->will($this->returnValue($this->prop_content_type));


        $this->delivery_stub = $this->getMockBuilder('\AMQPy\Client\Delivery')->disableOriginalConstructor()->getMock();


        $this->delivery_stub->expects($this->any())
                            ->method('getProperties')
                            ->will($this->returnValue($this->properties_stub));

        $this->delivery_stub->expects($this->any())
                            ->method('getBody')
                            ->will($this->returnValue($this->prop_message_body));

        $this->consumer_stub = $this->getMock('\AMQPy\AbstractConsumer');

    }

    /**
     * @covers \AMQPy\Listener::feed
     */
    public function testFeedRegularUseCase()
    {
        $consumer_result  = 'consumer_result';
        $consumer_payload = 'parsed (' . $this->prop_content_type . '): ' . $this->prop_message_body;

        $this->consumer_stub->expects($this->at(0))
                            ->method('before')
                            ->with($this->delivery_stub, $this->object);

        $this->consumer_stub->expects($this->at(1))
                            ->method('consume')
                            ->with($consumer_payload, $this->delivery_stub, $this->object)
                            ->will($this->returnValue($consumer_result));

        $this->consumer_stub->expects($this->at(2))
                            ->method('after')
                            ->with($consumer_result, $this->delivery_stub, $this->object);

        $this->consumer_stub->expects($this->never())
                            ->method('failure');

        $this->consumer_stub->expects($this->at(3))
                            ->method('always')
                            ->with($consumer_result, $consumer_payload, $this->delivery_stub, $this->object, null);


        $this->object->feed($this->delivery_stub, $this->consumer_stub);
    }

    /**
     * @covers                   \AMQPy\Listener::feed
     *
     * @expectedException \Exception
     * @expectedExceptionMessage Test before exception
     */
    public function testFeedBeforeException()
    {
        $e = new \Exception('Test before exception');

        $this->consumer_stub->expects($this->at(0))
                            ->method('before')
                            ->with($this->delivery_stub, $this->object)
                            ->will($this->throwException($e));

        $this->consumer_stub->expects($this->never())
                            ->method('consume');
        $this->consumer_stub->expects($this->never())
                            ->method('after');
        $this->consumer_stub->expects($this->never())
                            ->method('failure');
        $this->consumer_stub->expects($this->never())
                            ->method('always');

        $this->object->feed($this->delivery_stub, $this->consumer_stub);
    }

    /**
     * @covers \AMQPy\Listener::feed
     */
    public function testFeedSerializerException()
    {
        $e = new \Exception('Test serializer exception');

        $this->consumer_stub->expects($this->at(0))
                            ->method('before')
                            ->with($this->delivery_stub, $this->object);

        $this->serializers_pool_stub->expects($this->once())
                                    ->method('get')
                                    ->with($this->prop_content_type)
                                    ->will($this->throwException($e));

        $this->consumer_stub->expects($this->never())
                            ->method('consume');

        $this->consumer_stub->expects($this->never())
                            ->method('after');

        $this->consumer_stub->expects($this->at(1))
                            ->method('failure')
                            ->with($e, $this->delivery_stub, $this->object);

        $this->consumer_stub->expects($this->at(2))
                            ->method('always')
                            ->with(null, null, $this->delivery_stub, $this->object, $e);

        $this->object->feed($this->delivery_stub, $this->consumer_stub);
    }

    /**
     * @covers \AMQPy\Listener::feed
     */
    public function testFeedConsumerException()
    {
        $consumer_payload = 'parsed (' . $this->prop_content_type . '): ' . $this->prop_message_body;

        $e = new \Exception('Test consumer exception');

        $this->consumer_stub->expects($this->at(0))
                            ->method('before')
                            ->with($this->delivery_stub, $this->object);

        $this->consumer_stub->expects($this->at(1))
                            ->method('consume')
                            ->with($consumer_payload, $this->delivery_stub, $this->object)
                            ->will($this->throwException($e));

        $this->consumer_stub->expects($this->never())
                            ->method('after');

        $this->consumer_stub->expects($this->at(2))
                            ->method('failure')
                            ->with($e, $this->delivery_stub, $this->object);

        $this->consumer_stub->expects($this->at(3))
                            ->method('always')
                            ->with(null, $consumer_payload, $this->delivery_stub, $this->object, $e);

        $this->object->feed($this->delivery_stub, $this->consumer_stub);
    }

    /**
     * @covers                   \AMQPy\Listener::feed
     * @expectedException \Exception
     * @expectedExceptionMessage Test failure exception
     */
    public function testFeedFailureException()
    {
        $consumer_payload = 'parsed (' . $this->prop_content_type . '): ' . $this->prop_message_body;

        $e         = new \Exception('Test consumer exception');
        $e_failure = new \Exception('Test failure exception');

        $this->consumer_stub->expects($this->at(0))
                            ->method('before')
                            ->with($this->delivery_stub, $this->object);

        $this->consumer_stub->expects($this->at(1))
                            ->method('consume')
                            ->with($consumer_payload, $this->delivery_stub, $this->object)
                            ->will($this->throwException($e));

        $this->consumer_stub->expects($this->never())
                            ->method('after');

        $this->consumer_stub->expects($this->at(2))
                            ->method('failure')
                            ->with($e, $this->delivery_stub, $this->object)
                            ->will($this->throwException($e_failure));

        $this->consumer_stub->expects($this->never())
                            ->method('always');

        $this->object->feed($this->delivery_stub, $this->consumer_stub);
    }

    /**
     * @covers                   \AMQPy\Listener::feed
     *
     * @expectedException \Exception
     * @expectedExceptionMessage Test after exception
     */
    public function testFeedAfterException()
    {
        $consumer_result  = 'consumer_result';
        $consumer_payload = 'parsed (' . $this->prop_content_type . '): ' . $this->prop_message_body;

        $e = new \Exception('Test after exception');

        $this->consumer_stub->expects($this->at(0))
                            ->method('before')
                            ->with($this->delivery_stub, $this->object);

        $this->consumer_stub->expects($this->at(1))
                            ->method('consume')
                            ->with($consumer_payload, $this->delivery_stub, $this->object)
                            ->will($this->returnValue($consumer_result));

        $this->consumer_stub->expects($this->at(2))
                            ->method('after')
                            ->with($consumer_result, $this->delivery_stub, $this->object)
                            ->will($this->throwException($e));

        $this->consumer_stub->expects($this->never())
                            ->method('failure');

        $this->consumer_stub->expects($this->never(3))
                            ->method('always');

        $this->object->feed($this->delivery_stub, $this->consumer_stub);
    }

    /**
     * @covers                   \AMQPy\Listener::feed
     *
     * @expectedException \Exception
     * @expectedExceptionMessage Test always exception on successful consumer
     */
    public function testFeedAlwaysExceptionOnSuccessfulConsumer()
    {
        $consumer_result  = 'consumer_result';
        $consumer_payload = 'parsed (' . $this->prop_content_type . '): ' . $this->prop_message_body;

        $e = new \Exception('Test always exception on successful consumer');

        $this->consumer_stub->expects($this->at(0))
                            ->method('before')
                            ->with($this->delivery_stub, $this->object);

        $this->consumer_stub->expects($this->at(1))
                            ->method('consume')
                            ->with($consumer_payload, $this->delivery_stub, $this->object)
                            ->will($this->returnValue($consumer_result));

        $this->consumer_stub->expects($this->at(2))
                            ->method('after')
                            ->with($consumer_result, $this->delivery_stub, $this->object);

        $this->consumer_stub->expects($this->never())
                            ->method('failure');

        $this->consumer_stub->expects($this->at(3))
                            ->method('always')
                            ->with($consumer_result, $consumer_payload, $this->delivery_stub, $this->object, null)
                            ->will($this->throwException($e));

        $this->object->feed($this->delivery_stub, $this->consumer_stub);
    }

    /**
     * @covers                   \AMQPy\Listener::feed
     *
     * @expectedException \Exception
     * @expectedExceptionMessage Test always exception on failed consumer
     */
    public function testFeedAlwaysExceptionOnFailedConsumerAndFailureHandler()
    {
        $consumer_payload = 'parsed (' . $this->prop_content_type . '): ' . $this->prop_message_body;

        $e          = new \Exception('Test always exception on failed consumer');
        $e_consumer = new \Exception('Test always exception on successful consumer');

        $this->consumer_stub->expects($this->at(0))
                            ->method('before')
                            ->with($this->delivery_stub, $this->object);

        $this->consumer_stub->expects($this->at(1))
                            ->method('consume')
                            ->with($consumer_payload, $this->delivery_stub, $this->object)
                            ->will($this->throwException($e_consumer));

        $this->consumer_stub->expects($this->never())
                            ->method('after');

        $this->consumer_stub->expects($this->at(2))
                            ->method('failure')
                            ->with($e_consumer, $this->delivery_stub, $this->object)
                            ->will($this->throwException($e));

        $this->consumer_stub->expects($this->never())
                            ->method('always');

        $this->object->feed($this->delivery_stub, $this->consumer_stub);
    }

}
 