<?php


namespace AMQPy\Tests\Client;

use AMQPy\Client\Delivery;

class DeliveryTest extends \PHPUnit_Framework_TestCase
{

//    public function __construct($body, Envelope $envelope, Properties $properties)
//    {
//        $this->body       = $body;
//        $this->envelope   = $envelope;
//        $this->properties = $properties;
//    }

    /**
     * @covers AMQPy\Client\Delivery::__construct
     * @covers AMQPy\Client\Delivery::getBody
     * @covers AMQPy\Client\Delivery::getEnvelope
     * @covers AMQPy\Client\Delivery::getProperties
     */
    public function testConstructor()
    {

        $body = 'body content is mixed';

        $envelope_stub   = $this->getMockBuilder('AMQPy\Client\Envelope')
                                ->disableOriginalConstructor()
                                ->getMock();

        $properties_stub = $this->getMock('AMQPy\Client\Properties');

        $delivery = new Delivery($body, $envelope_stub, $properties_stub);

        $this->assertSame($body, $delivery->getBody());
        $this->assertSame($envelope_stub, $delivery->getEnvelope());
        $this->assertSame($properties_stub, $delivery->getProperties());
    }

    // constructor

    // getters
}
 