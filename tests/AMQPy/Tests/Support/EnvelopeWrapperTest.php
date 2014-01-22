<?php


namespace AMQPy\Tests\Support;

use AMQPy\Support\EnvelopeWrapper;

class EnvelopeWrapperTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @covers AMQPy\Support\EnvelopeWrapper::getOriginal
     * @covers AMQPy\Support\EnvelopeWrapper::getBody
     * @covers AMQPy\Support\EnvelopeWrapper::getProperties
     * @covers AMQPy\Support\EnvelopeWrapper::getEnvelope
     */
    public function testConstructorSuccess()
    {
    }

    /**
     * @covers AMQPy\Support\EnvelopeWrapper::__construct
     * @covers AMQPy\Support\EnvelopeWrapper::getOriginal
     */
    public function testGetOriginal()
    {
        $stub    = $this->getMock('AMQPEnvelope');
        $wrapper = new EnvelopeWrapper($stub);

        $this->assertSame($stub, $wrapper->getOriginal());
    }

    /**
     * @covers AMQPy\Support\EnvelopeWrapper::getProperties
     */
    public function testGetProperties()
    {
        $stub = $this->getMock('AMQPEnvelope');

        $stubs = [
            'getContentType'     => 'content_type',
            'getContentEncoding' => 'content_encoding',
            'getHeaders'         => ['header' => 'value'],
            'getDeliveryMode'    => 'delivery_mode',
            'getPriority'        => 'priority',
            'getCorrelationId'   => 'correlation_id',
            'getReplyTo'         => 'reply_to',
            'getExpiration'      => 'expiration',
            'getMessageId'       => 'message_id',
            'getTimestamp'       => 'timestamp',
            'getType'            => 'type',
            'getUserId'          => 'user_id',
            'getAppId'           => 'app_id',
        ];

        foreach ($stubs as $method => $returns) {
            $stub->expects($this->any())
                 ->method($method)
                 ->will($this->returnValue($returns));
        }

        $wrapper = new EnvelopeWrapper($stub);

        $props = $wrapper->getProperties();
        $this->assertInstanceOf('AMQPy\Client\Properties', $props);

        foreach ($stubs as $method => $returns) {
            $this->assertEquals($returns, $props->$method());
        }
    }

    public function testGetEnvelope()
    {
        $stub = $this->getMock('AMQPEnvelope');

        $stubs = [
            'getExchangeName' => 'exchange name',
            'getRoutingKey'   => 'routing key',
            'getDeliveryTag'  => 'delivery tag',
            'isRedelivery'    => true,
        ];

        $expected = [
            'getExchange'    => 'exchange name',
            'getRoutingKey'  => 'routing key',
            'getDeliveryTag' => 'delivery tag',
            'isRedeliver'    => true,
        ];

        foreach ($stubs as $method => $returns) {
            $stub->expects($this->any())
                 ->method($method)
                 ->will($this->returnValue($returns));
        }

        $wrapper = new EnvelopeWrapper($stub);

        $envelope = $wrapper->getEnvelope();
        $this->assertInstanceOf('AMQPy\Client\Envelope', $envelope);

        foreach ($expected as $method => $returns) {
            $this->assertEquals($returns, $envelope->$method());
        }
    }

    /**
     * @covers AMQPy\Support\EnvelopeWrapper::getBody
     */
    public function testGetBody()
    {
        $stub = $this->getMock('AMQPEnvelope');
        $body = 'body content goes here';

        $stub->expects($this->any())
             ->method('getBody')
             ->will($this->returnValue($body));

        $wrapper = new EnvelopeWrapper($stub);

        $this->assertSame($body, $wrapper->getBody());
    }
}
 