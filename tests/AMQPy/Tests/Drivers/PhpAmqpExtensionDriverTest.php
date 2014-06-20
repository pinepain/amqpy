<?php


namespace AMQPy\Tests\Drivers;

use AMQPy\Drivers\PhpAmqpExtensionDriver;
use Mockery as m;

class PhpAmqpExtensionDriverTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers \AMQPy\Drivers\PhpAmqpExtensionDriver::makeClass
     *
     * @group  internals
     */
    public function testMakeClassWithDefaultArguments()
    {
        $driver = new PhpAmqpExtensionDriver();

        $this->assertInstanceOf('StdClass', $driver->makeClass('StdClass'));
    }

    /**
     * @covers \AMQPy\Drivers\PhpAmqpExtensionDriver::makeClass
     *
     * @group  internals
     */
    public function testMakeClassWithPresetArguments()
    {
        $driver = new PhpAmqpExtensionDriver();

        $datetime = '1999-01-01 01:01:01';
        $instance = $driver->makeClass('DateTime', array($datetime));

        $this->assertInstanceOf('DateTime', $instance);
        $this->assertEquals($datetime, $instance->format('Y-m-d H:i:s'));
    }

    public function testGetActiveConnectionWhenNoConnectionExists()
    {

        $connection = m::mock('stdClass');


    }
}
 