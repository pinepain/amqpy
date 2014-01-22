<?php


namespace AMQPy\Tests;


use AMQPy\Publisher;

class PublisherTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Publisher
     */
    private $object;

    protected function setUp()
    {
        $exchange_stub = $this->getMockBuilder('\AMQPExchange')
                              ->disableOriginalConstructor()
                              ->getMock();

        $exchange_stub->expects($this->any())
                      ->method('publish')
                      ->will(
                      $this->returnCallback(

                           function ($message, $routing_key, $flags, $attributes) {

                               $attributes = '[' . http_build_query($attributes, '', ', ', PHP_QUERY_RFC3986) . ']';

                               echo 'send:', PHP_EOL;

                               foreach (compact('message', 'routing_key', 'attributes', 'flags') as $name => $value) {
                                   echo '    ', $name . ': ', $value, PHP_EOL;
                               }
                           }
                      )
            );

        $serializer_stub = $this->getMock('\AMQPy\Serializers\SerializerInterface');
        $serializer_stub->expects($this->any())
                        ->method('serialize')
                        ->will(
                        $this->returnCallback(
                             function ($message) {
                                 return 'serialized: ' . $message;
                             }
                        )
            );

        $pool_stub = $this->getMock('\AMQPy\Serializers\SerializersPool');

        $pool_stub->expects($this->any())
                  ->method('get')
            //->with('test/serializer')
                  ->will($this->returnValue($serializer_stub));

        $this->object = new Publisher($exchange_stub, $pool_stub);
    }

    /**
     * @covers AMQPy\Publisher::__construct
     * @covers AMQPy\Publisher::getExchange
     * @covers AMQPy\Publisher::getSerializers
     */
    public function testConstruct()
    {
        $exchange_stub = $this->getMockBuilder('\AMQPExchange')
                              ->disableOriginalConstructor()
                              ->getMock();

        $pool_stub = $this->getMock('\AMQPy\Serializers\SerializersPool');

        $publisher = new Publisher($exchange_stub, $pool_stub);

        $this->assertSame($exchange_stub, $publisher->getExchange());
        $this->assertSame($pool_stub, $publisher->getSerializers());
    }

    /**
     * @covers AMQPy\Publisher::publish
     */
    public function testPublishNoProperties()
    {
        $message     = 'message';
        $routing_key = 'rounting.key';
        $properties  = null;
        $flags       = AMQP_NOPARAM;

        $output =
            "send:\n" .
            "    message: serialized: message\n" .
            "    routing_key: rounting.key\n" .
            "    attributes: [content_type=text%2Fplain]\n" .
            "    flags: 0\n";

        $this->expectOutputString($output);
        $this->object->publish($message, $routing_key, $properties, $flags);
    }

    /**
     * @covers AMQPy\Publisher::publish
     */
    public function testPublishEmptyContentTypeProperties()
    {
        $message     = 'message';
        $routing_key = 'rounting.key';
        $properties  = $this->getMock('\AMQPy\Client\Properties');
        $flags       = AMQP_NOPARAM;

        $output =
            "send:\n" .
            "    message: serialized: message\n" .
            "    routing_key: rounting.key\n" .
            "    attributes: [content_type=text%2Fplain]\n" .
            "    flags: 0\n";

        $this->expectOutputString($output);
        $this->object->publish($message, $routing_key, $properties, $flags);
    }

    /**
     * @covers AMQPy\Publisher::publish
     */
    public function testPublishWithContentTypeProperties()
    {
        $message     = 'message';
        $routing_key = 'rounting.key';
        $properties  = $this->getMock('\AMQPy\Client\Properties');

        $properties->expects($this->any())
                   ->method('getContentType')
                   ->will($this->returnValue('test/content'));

        $flags = AMQP_NOPARAM;

        $output =
            "send:\n" .
            "    message: serialized: message\n" .
            "    routing_key: rounting.key\n" .
            "    attributes: [content_type=test%2Fcontent]\n" .
            "    flags: 0\n";

        $this->expectOutputString($output);
        $this->object->publish($message, $routing_key, $properties, $flags);
    }
}
 