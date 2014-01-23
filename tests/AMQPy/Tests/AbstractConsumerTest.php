<?php


namespace AMQPy\Tests;

use AMQPy\AbstractConsumer;

class AbstractConsumerTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var AbstractConsumer
     */
    private $object;

    public function setUp()
    {
        $this->object = $this->getMockForAbstractClass('AMQPy\AbstractConsumer');

        $this->object->expects($this->any())
                     ->method('consume')
                     ->will($this->returnValue(null));
    }

    /**
     * @covers AMQPy\AbstractConsumer::begin
     */
    public function testBegin()
    {
        $listener = $this->getMock('AMQPy\AbstractListenter', [], [], '', false);

        $listener->expects($this->at(0))
                 ->method('setEndless')
                 ->with($this->equalTo(true));

        $this->object->begin($listener);
    }

    /**
     * @covers AMQPy\AbstractConsumer::end
     */
    public function testEnd()
    {
        $listener = $this->getMock('AMQPy\AbstractListenter', [], [], '', false);

        $listener->expects($this->at(0))
                 ->method('setEndless')
                 ->with($this->equalTo(false));

        $this->object->end($listener);
    }

    /**
     * @covers AMQPy\AbstractConsumer::before
     * @covers AMQPy\AbstractConsumer::consume
     * @covers AMQPy\AbstractConsumer::after
     * @covers AMQPy\AbstractConsumer::always
     */
    public function testBeforeThenConsumeThenAfter()
    {
        $listener = $this->getMock('AMQPy\AbstractListenter', [], [], '', false);
        $delivery = $this->getMock('\AMQPy\Client\Delivery', [], [], '', false);

        $this->object->before($delivery, $listener);
        $this->object->consume('mixed payload goes here', $delivery, $listener);
        $this->object->after('consumer result', $delivery, $listener);
        $this->object->always('consumer result', 'mixed payload goes here', $delivery, $listener);
    }

    /**
     * @covers AMQPy\AbstractConsumer::failure
     */
    public function testFailure()
    {
        $listener = $this->getMock('AMQPy\AbstractListenter', [], [], '', false);
        $delivery = $this->getMock('\AMQPy\Client\Delivery', [], [], '', false);

        $listener->expects($this->at(0))
                 ->method('resend')
                 ->with($this->equalTo($delivery));

        $e = $this->getMock('\Exception', [], [], '', false);

        $this->object->failure($e, $delivery, $listener);
    }

    /**
     * @covers AMQPy\AbstractConsumer::active
     * @covers AMQPy\AbstractConsumer::activate
     * @covers AMQPy\AbstractConsumer::stop
     */
    public function testActiveAndRelated() {

        $this->assertTrue($this->object->active());

        $this->assertFalse($this->object->stop());
        $this->assertFalse($this->object->stop());
        $this->assertFalse($this->object->active());

        $this->assertTrue($this->object->activate());
        $this->assertTrue($this->object->activate());
        $this->assertTrue($this->object->active());

        $this->assertFalse($this->object->stop());
        $this->assertFalse($this->object->active());

        $this->assertTrue($this->object->activate());
        $this->assertTrue($this->object->active());
    }
}
 