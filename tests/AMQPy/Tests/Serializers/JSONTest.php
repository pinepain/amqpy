<?php
namespace AMQPy\Tests\Serializers;

use AMQPy\Serializers\JSON;
use Exception;
use StdClass;

/**
 * @group serializers
 */
class JSONTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var JSON
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->object = new JSON;
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
        $closure = function () {
        };

        $warn = array(
            "\\AMQPy\\Serializers\\Exceptions\\SerializerException",
            'Failed to serialize value: Type is not supported'
        );

        $f1 = fopen(__FILE__, 'r');
        $f2 = fopen(__FILE__, 'r');

        $ret = array(
            array(0, null, '0'),
            array('0', null, '"0"'),
            array(null, null, 'null'),
            array(true, null, 'true'),
            array(false, null, 'false'),
            array(42, null, '42'),
            array("42", null, '"42"'),
            array(-42, null, '-42'),
            array("-42", null, '"-42"'),
            array(3.141592, null, '3.141592'),
            array("3.141592", null, '"3.141592"'),
            array(-3.141592, null, '-3.141592'),
            array("-3.141592", null, '"-3.141592"'),
            array(new StdClass(), null, '{}'),
            array(array(), null, '[]'),
            array('', null, '""'),
            array('\n', null, '"\\\\n"'),
            array("\n", null, '"\n"'),
            array($f1, null, 'null', $warn),
            array($f2, null, 'null', $warn),
            array($closure, null, '{}'),
        );

        return $ret;

    }

    public function dataProviderParse()
    {
        $err = array(
            "\\AMQPy\\Serializers\\Exceptions\\SerializerException",
            "Failed to parse value: %s"
        );

        $closure = function () {
        };

        $f1 = fopen(__FILE__, 'r');
        $f2 = fopen(__FILE__, 'r');

        $ret = array(
            array('0', null, 0),
            array('"0"', null, '0'),
            array('null', null, null),
            array('true', null, true),
            array('false', null, false),
            array('42', null, 42),
            array('"42"', null, "42"),
            array('-42', null, -42),
            array('"-42"', null, "-42"),
            array('3.141592', null, 3.141592),
            array('"3.141592"', null, "3.141592"),
            array('-3.141592', null, -3.141592),
            array('"-3.141592"', null, "-3.141592"),
            array('{}', null, array()),
            array('[]', null, array()),
            array('""', null, ''),
            array('"\\\\n"', null, '\n'),
            array('"\n"', null, "\n"),
            // bad syntax
            array('"0', $err),
            array('nul', $err),
            array('NULL', $err),
            array('42"', $err),
            array('-', $err),
            array('{', $err),
            array(']', $err),
            array('"', $err),
        );

        return $ret;
    }

    /**
     * @covers       AMQPy\Serializers\JSON::serialize
     * @covers       AMQPy\Serializers\JSON::isErrorOccurred
     * @covers       AMQPy\Serializers\JSON::getLastError
     *
     * @dataProvider dataProviderSerialize
     *
     */
    public function testSerialize($value, $error, $output, $warning = null)
    {
        if (!empty($warning)) {
            $this->setExpectedException($warning[0], $warning[1]);
        }

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
     * @covers       AMQPy\Serializers\JSON::parse
     * @covers       AMQPy\Serializers\JSON::isErrorOccurred
     * @covers       AMQPy\Serializers\JSON::getLastError
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

        $this->assertSame($output, $parsed);
    }

    /**
     * @covers AMQPy\Serializers\JSON::contentType
     */
    public function testGetContentType()
    {
        $this->assertSame(JSON::MIME, $this->object->contentType());
    }
}
