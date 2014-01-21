<?php
namespace AMQPy\Tests\Serializers;

use AMQPy\Serializers\PlainText;
use Exception;
use PHPUnit_Framework_TestCase;
use StdClass;


/**
 * @group serializers
 */
class PlainTextTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var PlainText
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->object = new PlainText;
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
        $err     = array(
            "\\AMQPy\\Serializers\\Exceptions\\SerializerException",
            "Failed to serialize value: Incompatible type"
        );
        $closure = function () {
        };

        $ret = array(
            array("", null, ""),
            array("42", null, "42"),
            array("3.141592", null, "3.141592"),
            array("-42", null, "-42"),
            array("-3.141592", null, "-3.141592"),
            array("0", null, "0"),
            array(42, null, "42"),
            array(3.141592, null, "3.141592"),
            array(-42, null, "-42"),
            array(-3.141592, null, "-3.141592"),
            array(0, null, "0"),
            array("test string", null, "test string"),
            array(null, $err, null),
            array(true, $err, null),
            array(false, $err, null),
            array(fopen(__FILE__, 'r'), $err, null),
            array(new StdClass(), $err, null),
            array($closure, $err, null),
            array(array(), $err, null),
        );

        return $ret;
    }

    public function dataProviderParse()
    {
        $err     = array(
            "\\AMQPy\\Serializers\\Exceptions\\SerializerException",
            "Failed to parse value: Incompatible type"
        );
        $closure = function () {
        };

        $ret = array(
            array("", null, ""),
            array("42", null, "42"),
            array("3.141592", null, "3.141592"),
            array("-42", null, "-42"),
            array("-3.141592", null, "-3.141592"),
            array("0", null, "0"),
            array(42, null, 42),
            array(3.141592, null, 3.141592),
            array(-42, null, -42),
            array(-3.141592, null, -3.141592),
            array(0, null, 0),
            array("test string", null, "test string"),
            array(null, $err, null),
            array(true, $err, null),
            array(false, $err, null),
            array(fopen(__FILE__, 'r'), $err, null),
            array(new StdClass(), $err, null),
            array($closure, $err, null),
            array(array(), $err, null),
        );

        return $ret;
    }

    /**
     * @covers \AMQPy\Serializers\PlainText::serialize
     *
     * @dataProvider dataProviderSerialize
     *
     */
    public function testSerialize($value, $error, $output)
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

        $this->assertEquals($output, $serialized);
    }

    /**
     * @covers \AMQPy\Serializers\PlainText::parse
     *
     * @dataProvider dataProviderParse
     */
    public function testParse($value, $error, $output)
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
     * @covers \AMQPy\Serializers\PlainText::getContentType
     */
    public function testGetContentType()
    {
        $this->assertSame(PlainText::MIME, $this->object->getContentType());
    }
}
