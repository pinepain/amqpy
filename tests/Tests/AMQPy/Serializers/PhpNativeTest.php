<?php
namespace Tests\AMQPy\Serializers;

use AMQPy\Serializers\PhpNative;
use Exception;
use StdClass;

/**
 * @group serializers
 */
class PhpNativeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var PhpNative
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->object = new PhpNative;
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
    }

    public function dataProviderSerialize()
    {
        $err     = array("Exception", "Serialization of 'Closure' is not allowed");
        $closure = function () {
        };

        $f1 = fopen(__FILE__, 'r');
        $f2 = fopen(__FILE__, 'r');

        $ret = array(
            array(0, null, 'i:0;'),
            array('0', null, 's:1:"0";'),
            array(null, null, 'N;'),
            array(true, null, 'b:1;'),
            array(false, null, 'b:0;'),
            array(42, null, 'i:42;'),
            array("42", null, 's:2:"42";'),
            array(-42, null, 'i:-42;'),
            array("-42", null, 's:3:"-42";'),
            array(3.141592, null, 'd:3.141592%d;', true),
            array("3.141592", null, 's:8:"3.141592";'),
            array(-3.141592, null, 'd:-3.141592%d;', true),
            array("-3.141592", null, 's:9:"-3.141592";'),
            array(new StdClass(), null, 'O:8:"stdClass":0:{}'),
            array(array(), null, "a:0:{}"),
            array('', null, 's:0:"";'),
            array('\n', null, 's:2:"\n";'),
            array("\n", null, "s:1:\"\n\";"),
            array($f1, null, 'i:0;'),
            array($f2, null, 'i:0;'),
            array($closure, $err, null),
        );

        return $ret;

    }

    public function dataProviderParse()
    {
        $err   = array(
            "\\AMQPy\\Serializers\\Exceptions\\SerializerException",
            "Failed to parse value: String is not unserializable"
        );
        $err_2 = array(
            "\\AMQPy\\Serializers\\Exceptions\\SerializerException",
            "Failed to parse value: Incompatible type"
        );

        $closure = function () {
        };

        $f1 = fopen(__FILE__, 'r');
        $f2 = fopen(__FILE__, 'r');

        $ret = array(
            array('i:0;', null, 0),
            array('s:1:"0";', null, '0'),
            array('N;', null, null),
            array('b:1;', null, true),
            array('b:0;', null, false),
            array('i:42;', null, 42),
            array('s:2:"42";', null, "42"),
            array('i:-42;', null, -42),
            array('s:3:"-42";', null, "-42"),
            array('d:3.141592;', null, 3.141592),
            array('s:8:"3.141592";', null, "3.141592"),
            array('d:-3.141592;', null, -3.141592),
            array('s:9:"-3.141592";', null, "-3.141592"),
            array('O:8:"stdClass":0:{}', null, new StdClass()),
            array("a:0:{}", null, array()),
            array('s:0:"";', null, ''),
            array('s:2:"\n";', null, '\n'),
            array("s:1:\"\n\";", null, "\n"),
            array('S:2:"42";', null, "42"),
            // bad data
            array('i:0', $err),
            array('d:1:"0";', $err),
            array('a;', $err),
            array('b;-1;', $err),
            array('b:4;', $err),
            array('i-42;', $err),
            array('D:-42;', $err),
            array('[:3:"-42";', $err),
            array('s:3.141592;', $err),
            array('i:8:"3.141592";', $err),
            array('i:-3.141592;', $err),
            array('s:4:"-3.141592";', $err),
            array('O:11:"stdClass":0:{}', $err),
            array("a:4:{}", $err),
            array('s:33:"";', $err),
            array('s:1:"\n";', $err),
            array("s:3:\"\n\";", $err),
            // bad format
            array(0, $err_2),
            array(null, $err_2),
            array(true, $err_2),
            array(false, $err_2),
            array(42, $err_2),
            array(-42, $err_2),
            array(3.141592, $err_2),
            array(-3.141592, $err_2),
            array(new StdClass(), $err_2),
            array(array(), $err_2),
            array($f1, $err_2),
            array($f2, $err_2),
            array($closure, $err_2),
        );
        return $ret;
    }

    /**
     * @covers AMQPy\Serializers\PhpNative::serialize
     * @dataProvider dataProviderSerialize
     *
     */
    public function testSerialize($value, $error, $output, $match_format = false)
    {
        try {
            $serialized = $this->object->serialize($value);
        } catch (Exception $e) {
            if (!empty($error)) {
                $this->assertInstanceOf($error[0], $e);
                $this->assertStringMatchesFormat($error[1], $e->getMessage());
                return;
            } else {
                throw $e;
            }
        }

        if ($match_format) {
            $this->assertStringMatchesFormat($output, $serialized);
        } else {
            $this->assertEquals($output, $serialized);
        }
    }

    /**
     * @covers AMQPy\Serializers\PhpNative::parse
     *
     * @dataProvider dataProviderParse
     */
    public function testParse($value, $error, $output = null)
    {
        try {
            $parsed = $this->object->parse($value);
        } catch (Exception $e) {
            if (!empty($error)) {
                $this->assertInstanceOf($error[0], $e);
                $this->assertStringMatchesFormat($error[1], $e->getMessage());
                return;
            } else {
                throw $e;
            }
        }

        $this->assertEquals($output, $parsed);
    }

    /**
     * @covers AMQPy\Serializers\PhpNative::getContentType
     */
    public function testGetContentType()
    {
        $this->assertSame(PhpNative::MIME, $this->object->getContentType());
    }
}
