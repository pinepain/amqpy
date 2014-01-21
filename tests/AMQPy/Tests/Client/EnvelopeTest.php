<?php


namespace AMQPy\Tests\Client;

use AMQPy\Client\Envelope;

class EnvelopeTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @covers AMQPy\Client\Envelope::__construct
     * @covers AMQPy\Client\Envelope::getExchange
     * @covers AMQPy\Client\Envelope::getRoutingKey
     * @covers AMQPy\Client\Envelope::getDeliveryTag
     * @covers AMQPy\Client\Envelope::isRedeliver
     */
    public function testConstructor()
    {
        $exchange     = 'exchange name';
        $routing_key  = 'routing key';
        $delivery_tag = 'delivery tab';
        $is_redeliver = 'actually, should be boolean or null';

        $envelope = new Envelope($exchange, $routing_key, $delivery_tag, $is_redeliver);

        $this->assertEquals($exchange, $envelope->getExchange());
        $this->assertEquals($routing_key, $envelope->getRoutingKey());
        $this->assertEquals($delivery_tag, $envelope->getDeliveryTag());
        $this->assertEquals($is_redeliver, $envelope->isRedeliver());


    }
}
 