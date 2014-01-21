<?php


namespace AMQPy\Tests\Serializers;

use AMQPy\Serializers\SerializersPool;

class SerializersPoolTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers AMQPy\Serializers\SerializersPool::register
     * @covers AMQPy\Serializers\SerializersPool::get
     * @covers AMQPy\Serializers\SerializersPool::isRegistered
     */
    public function testRegisterObjectSuccess()
    {
        $stub      = $this->getMock('\AMQPy\Serializers\SerializerInterface');
        $stub_mime = 'tests/mock';

        $stub->expects($this->any())
             ->method('getContentType')
             ->will($this->returnValue($stub_mime));

        $pool = new SerializersPool();

        $this->assertSame($pool, $pool->register($stub));
        $this->assertTrue($pool->isRegistered($stub_mime));
        $this->assertSame($stub, $pool->get($stub_mime));
        $this->assertFalse($pool->isRegistered('nonexitent'));
    }

    /**
     * @covers                   AMQPy\Serializers\SerializersPool::register
     *
     * @expectedException \AMQPy\Serializers\Exceptions\SerializersPoolException
     * @expectedExceptionMessage Serializer class 'Invalid' doesn't implement default serializer interface
     */
    public function testRegisterObjectFailure()
    {
        $stub = $this->getMock('stdClass', [], [], 'Invalid');

        $pool = new SerializersPool();

        $pool->register($stub);
    }

    /**
     * @covers AMQPy\Serializers\SerializersPool::register
     * @covers AMQPy\Serializers\SerializersPool::get
     * @covers AMQPy\Serializers\SerializersPool::isRegistered
     */
    public function testRegisterClassSuccess()
    {
        $serializer = '\AMQPy\Helpers\Serializers\TestSerializerImplements';
        $mime       = 'test/implements';

        $pool = new SerializersPool();

        $this->assertSame($pool, $pool->register($serializer));
        $this->assertTrue($pool->isRegistered($mime));
        $this->assertInstanceOf($serializer, $pool->get($mime));
        $this->assertFalse($pool->isRegistered('nonexitent'));

    }

    /**
     * @covers                   AMQPy\Serializers\SerializersPool::register
     *
     * @expectedException \AMQPy\Serializers\Exceptions\SerializersPoolException
     * @expectedExceptionMessage Serializer class 'AMQPy\Helpers\Serializers\TestSerializerDoesntImplement' doesn't implement default serializer interface
     */
    public function testRegisterClassNotImplementsFailure()
    {
        $serializer = '\AMQPy\Helpers\Serializers\TestSerializerDoesntImplement';
        $mime       = 'test/implements';

        $pool = new SerializersPool();
        $pool->register($serializer);
    }

    /**
     * @covers                   AMQPy\Serializers\SerializersPool::register
     *
     * @expectedException \AMQPy\Serializers\Exceptions\SerializersPoolException
     * @expectedExceptionMessage Serializer class 'nonexistent' not found
     */
    public function testRegisterClassNoexistentFailure()
    {
        $pool = new SerializersPool();
        $pool->register('nonexistent');
    }

    /**
     * @covers AMQPy\Serializers\SerializersPool::register
     * @covers AMQPy\Serializers\SerializersPool::get
     */
    public function testRegisterArraySuccess()
    {
        $stub      = $this->getMock('\AMQPy\Serializers\SerializerInterface');
        $stub_mime = 'tests/mock';

        $stub->expects($this->any())
             ->method('getContentType')
             ->will($this->returnValue($stub_mime));

        $serializer = '\AMQPy\Helpers\Serializers\TestSerializerImplements';
        $mime       = 'test/implements';

        $arr = [
            $serializer,
            $stub,
        ];

        $pool = new SerializersPool();

        $pool->register($arr);

        $this->assertEquals($stub, $pool->get($stub_mime));

        $this->assertInstanceOf($serializer, $pool->get($mime));
    }

    /**
     * @covers                   AMQPy\Serializers\SerializersPool::register
     *
     * @expectedException \AMQPy\Serializers\Exceptions\SerializersPoolException
     * @expectedExceptionMessage Serializer should be object or class name, scalar 'integer' given instead
     */
    public function testRegisterScalarFailure()
    {
        $pool = new SerializersPool();

        $pool->register(1);
    }

    /**
     * @covers AMQPy\Serializers\SerializersPool::__construct
     * @covers AMQPy\Serializers\SerializersPool::register
     * @covers AMQPy\Serializers\SerializersPool::get
     */
    public function testConstructor()
    {
        $stub      = $this->getMock('\AMQPy\Serializers\SerializerInterface');
        $stub_mime = 'tests/mock';

        $stub->expects($this->any())
             ->method('getContentType')
             ->will($this->returnValue($stub_mime));

        $serializer = '\AMQPy\Helpers\Serializers\TestSerializerImplements';
        $mime       = 'test/implements';

        $arr = [
            $serializer,
            $stub,
        ];

        $pool = new SerializersPool($arr);

        $this->assertEquals($stub, $pool->get($stub_mime));

        $this->assertInstanceOf($serializer, $pool->get($mime));
    }

    /**
     * @covers                   AMQPy\Serializers\SerializersPool::get
     *
     * @expectedException \AMQPy\Serializers\Exceptions\SerializersPoolException
     * @expectedExceptionMessage There are no registered serializers for 'nonexistent' type
     */
    public function testGetFailure()
    {
        $pool = new SerializersPool();

        $pool->get('nonexistent');
    }

    /**
     * @covers AMQPy\Serializers\SerializersPool::deregister
     */
    public function testDeregister()
    {
        $pool = new SerializersPool();

        $stub      = $this->getMock('\AMQPy\Serializers\SerializerInterface');
        $stub_mime = 'tests/mock';

        $stub->expects($this->any())
             ->method('getContentType')
             ->will($this->returnValue($stub_mime));

        $pool->register($stub);

        $this->assertTrue($pool->isRegistered($stub_mime));
        $this->assertSame($pool, $pool->deregister($stub_mime));
        $this->assertFalse($pool->isRegistered($stub_mime));

        $nonexistent_mime = 'test/nonexistent';

        $this->assertFalse($pool->isRegistered($nonexistent_mime));
        $this->assertSame($pool, $pool->deregister($nonexistent_mime));
        $this->assertFalse($pool->isRegistered($nonexistent_mime));
    }



    //__construct == register
    //register
    //deregister
    //isRegistered
    //get
}
 