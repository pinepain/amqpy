<?php


namespace AMQPy\Tests\Client;

use AMQPy\Client\Properties;

class PropertiesTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Properties
     */
    protected $object;

    public function setUp()
    {
        $this->object = new Properties();
    }

    public function dataProviderPropertiesDefaults()
    {
        return [
            ['content_type', null, 'getContentType'],
            ['content_encoding', null, 'getContentEncoding',],
            ['headers', [], 'getHeaders',],
            ['delivery_mode', null, 'getDeliveryMode',],
            ['priority', null, 'getPriority',],
            ['correlation_id', null, 'getCorrelationId',],
            ['reply_to', null, 'getReplyTo',],
            ['expiration', null, 'getExpiration',],
            ['message_id', null, 'getMessageId',],
            ['timestamp', null, 'getTimestamp',],
            ['type', null, 'getType',],
            ['user_id', null, 'getUserId',],
            ['app_id', null, 'getAppId',],
        ];
    }

    public function dataProviderGettersAndSetters()
    {
        return [
            ['content_type', 'content_type value', 'getContentType', 'setContentType',],
            ['content_encoding', 'content_encoding value', 'getContentEncoding', 'setContentEncoding',],
            ['headers', ['headers' => 'value'], 'getHeaders', 'setHeaders',],
            ['delivery_mode', 'delivery_mode value', 'getDeliveryMode', 'setDeliveryMode',],
            ['priority', 'priority value', 'getPriority', 'setPriority',],
            ['correlation_id', 'correlation_id value', 'getCorrelationId', 'setCorrelationId',],
            ['reply_to', 'reply_to value', 'getReplyTo', 'setReplyTo',],
            ['expiration', 'expiration value', 'getExpiration', 'setExpiration',],
            ['message_id', 'message_id value', 'getMessageId', 'setMessageId',],
            ['timestamp', 'timestamp value', 'getTimestamp', 'setTimestamp',],
            ['type', 'type value', 'getType', 'setType',],
            ['user_id', 'user_id value', 'getUserId', 'setUserId',],
            ['app_id', 'app_id  value', 'getAppId', 'setAppId',],
        ];
    }


    /**
     * @param $property
     * @param $value
     * @param $getter
     *
     * @dataProvider dataProviderPropertiesDefaults
     */
    public function testPropertiesDefaults($property, $value, $getter)
    {
        $this->assertEquals($value, $this->object->$getter());
    }

    /**
     * @covers       AMQPy\Client\Properties::getContentType
     * @covers       AMQPy\Client\Properties::setContentType
     *
     * @covers       AMQPy\Client\Properties::getContentEncoding
     * @covers       AMQPy\Client\Properties::setContentEncoding
     *
     * @covers       AMQPy\Client\Properties::getHeaders
     * @covers       AMQPy\Client\Properties::setHeaders
     *
     * @covers       AMQPy\Client\Properties::getDeliveryMode
     * @covers       AMQPy\Client\Properties::setDeliveryMode
     *
     * @covers       AMQPy\Client\Properties::getPriority
     * @covers       AMQPy\Client\Properties::setPriority
     *
     * @covers       AMQPy\Client\Properties::getCorrelationId
     * @covers       AMQPy\Client\Properties::setCorrelationId
     *
     * @covers       AMQPy\Client\Properties::getReplyTo
     * @covers       AMQPy\Client\Properties::setReplyTo
     *
     * @covers       AMQPy\Client\Properties::getExpiration
     * @covers       AMQPy\Client\Properties::setExpiration
     *
     * @covers       AMQPy\Client\Properties::getMessageId
     * @covers       AMQPy\Client\Properties::setMessageId
     *
     * @covers       AMQPy\Client\Properties::getTimestamp
     * @covers       AMQPy\Client\Properties::setTimestamp
     *
     * @covers       AMQPy\Client\Properties::getType
     * @covers       AMQPy\Client\Properties::setType
     *
     * @covers       AMQPy\Client\Properties::getUserId
     * @covers       AMQPy\Client\Properties::setUserId
     *
     * @covers       AMQPy\Client\Properties::getAppId
     * @covers       AMQPy\Client\Properties::setAppId
     *
     * @dataProvider dataProviderGettersAndSetters
     */
    public function testGettersAndSetters($property, $value, $getter, $setter)
    {
        $this->object->$setter($value);
        $this->assertEquals($value, $this->object->$getter());
    }

    // TODO: test setHeaders directly

    /**
     * @covers AMQPy\Client\Properties::setHeaders
     * @covers AMQPy\Client\Properties::getHeaders
     */
    public function testSetHeadersArray()
    {
        $in = [
            'hello' => 'world',
            'foo'   => 'bar',
            'time'  => 123,
        ];

        $this->object->setHeaders($in);

        $this->assertEquals($in, $this->object->getHeaders());
    }

    /**
     * @covers AMQPy\Client\Properties::setHeaders
     * @covers AMQPy\Client\Properties::getHeaders
     */
    public function testSetHeadersIterable()
    {
        $in = [
            'hello' => 'world',
            'foo'   => 'bar',
            'time'  => 123,
        ];

        $this->object->setHeaders(new \ArrayIterator($in));

        $this->assertEquals($in, $this->object->getHeaders());
    }

    /**
     * @covers AMQPy\Client\Properties::setHeaders
     *
     * @expectedException \AMQPy\Client\PropertiesException
     * @expectedExceptionMessage Headers should be array or iterable, stdClass object given instead
     *
     */
    public function testSetHeadersObject()
    {
        $this->object->setHeaders(new \stdClass);
    }

    /**
     * @covers AMQPy\Client\Properties::setHeaders
     *
     * @expectedException \AMQPy\Client\PropertiesException
     * @expectedExceptionMessage Headers should be array or iterable, scalar string given instead
     *
     */
    public function testSetHeadersScalar()
    {
        $this->object->setHeaders('test');
    }

    // TODO: test setHeaders via fromArray

    /**
     * @covers AMQPy\Client\Properties::fromArray
     * @covers AMQPy\Client\Properties::toArray
     */
    public function testFromAndToArray()
    {
        $empty = [
            'content_type'     => null, // shortstr MIME content type
            'content_encoding' => null, // shortstr MIME content encoding
            'headers'          => [], // table
            'delivery_mode'    => null, // octet
            'priority'         => null, // octet
            'correlation_id'   => null, // shortstr
            'reply_to'         => null, // shortstr
            'expiration'       => null, // shortstr
            'message_id'       => null, // shortstr
            'timestamp'        => null, // timestamp
            'type'             => null, // shortstr
            'user_id'          => null, // shortstr
            'app_id'           => null, // shortstr
        ];

        $this->assertSame($empty, $this->object->toArray());

        $this->object->fromArray([]);

        $this->assertSame($empty, $this->object->toArray());

        $in = [
            'content_type'     => 'contentType',
            'content_encoding' => 'contentEncoding',
            'headers'          => ['headers' => 'value'],
            'delivery_mode'    => 'deliveryMode',
            'priority'         => 'priority',
            'correlation_id'   => 'correlationId',
            'reply_to'         => 'replyTo',
            'expiration'       => 'expiration',
            'message_id'       => 'messageId',
            'timestamp'        => 'timestamp',
            'type'             => 'type',
            'user_id'          => 'userId',
            'app_id'           => 'appId',
        ];

        $in_with_nonexistent                = $in;
        $in_with_nonexistent['nonexistent'] = 'Born to be wild';
        $this->object->fromArray($in);

        $this->assertEquals($in, $this->object->toArray());

        $this->object->fromArray([]);

        $this->assertSame($in, $this->object->toArray());
    }



}
 