<?php


namespace AMQPy\Tests\Support;


use AMQPy\Support\DeliveryBuilder;

class DeliveryBuilderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers \AMQPy\Support\DeliveryBuilder::__construct
     * @covers \AMQPy\Support\DeliveryBuilder::wrap
     * @covers \AMQPy\Support\DeliveryBuilder::build
     */
    public function testConstructDefaults()
    {
        $builder = new DeliveryBuilder();

        $envelope = $this->getMock('\AMQPEnvelope', [], [], '', false);
        $envelope->expects($this->any())
                 ->method('getHeaders')
                 ->will($this->returnValue([]));

        $this->assertInstanceOf('AMQPy\Support\EnvelopeWrapper', $builder->wrap($envelope));
        $this->assertInstanceOf('AMQPy\Client\Delivery', $builder->build($envelope));
    }
}
 