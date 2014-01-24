<?php


namespace AMQPy\Serializers;

use AMQPy\Serializers\Exceptions\SerializersPoolException;

class SerializersPool
{
    private $registered = [];

    public function __construct(array $serializers = array()) {
        foreach ($serializers as $serializer) {
            $this->register($serializer);
        }
    }

    /**
     * @param array | SerializerInterface | string $serializer Serializer object or class name or their list to create serializer from
     *
     * @return $this
     * @throws Exceptions\SerializersPoolException When try to register invalid serializer
     */
    public function register($serializer)
    {
        if (is_array($serializer)) {
            foreach ($serializer as $s) {
                $this->register($s);
            }

            return $this;
        }

        if (is_string($serializer)) {
            if (!class_exists($serializer)) {
                throw new SerializersPoolException("Serializer class '{$serializer}' not found");
            }

            $serializer = new $serializer;
        }

        if (!is_object($serializer)) {
            $serializer_type = gettype($serializer);
            throw new SerializersPoolException("Serializer should be object or class name, scalar '{$serializer_type}' given instead");
        }

        if (!is_subclass_of($serializer, 'AMQPy\Serializers\SerializerInterface')) {
            $serializer_class = get_class($serializer);
            throw new SerializersPoolException("Serializer class '{$serializer_class}' doesn't implement default serializer interface");
        }

        $this->registered[$serializer->getContentType()] = $serializer;

        return $this;
    }

    public function deregister($mime)
    {
        unset($this->registered[$mime]);

        return $this;
    }

    public function isRegistered($mime)
    {
        return isset($this->registered[$mime]);
    }

    /**
     * @param string $mime MIME to get serializer for
     *
     * @return SerializerInterface
     * @throws Exceptions\SerializersPoolException When there are no correspondent serializers
     */
    public function get($mime)
    {
        if (!$this->isRegistered($mime)) {
            throw new SerializersPoolException("There are no registered serializers for '{$mime}' type");
        }

        return $this->registered[$mime];
    }
}