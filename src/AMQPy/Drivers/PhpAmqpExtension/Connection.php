<?php


namespace AMQPy\Drivers\PhpAmqpExtension;

use AMQPy\Drivers\ConnectionInterface;

class Connection implements ConnectionInterface
{
    private $credentials;
    private $async;

    /**
     * @var \AMQPConnection
     */
    private $connection;

    public function makeClass($class)
    {
        $reflect = new \ReflectionClass($class);

        $arguments = func_get_args();

        array_shift($arguments);

        return $reflect->newInstanceArgs($arguments);
    }

    public function createChannel()
    {
        $this->connect();

        return $this->makeClass('AMQPChannel', $this->connection);
    }

    public function __construct(array $credentials = array(), $async = false)
    {
        $this->credentials = $credentials;
        $this->async       = $async;
    }

    /**
     * Return current connection's credential
     *
     * @return array
     */
    public function getCredentials()
    {
        return $this->credentials;
    }

    /**
     * Whether driver communicates with server asynchronously.
     *
     * @return bool
     */
    public function isAsync()
    {
        return $this->async;
    }

    /**
     * Connect to AMQP server
     *
     * @return bool
     */
    public function connect()
    {
        if (!$this->connection) {
            $this->connection = $this->makeClass('AMQPConnection', $this->credentials);

            return $this->connection->connect();
        }

        return $this->connection->isConnected();
    }

    /**
     * @return bool Whether connection established
     */
    public function isConnected()
    {
        return $this->connection && $this->connection->isConnected();
    }

    /**
     * Disconnect to AMQP server
     *
     * @return bool Whether disconnected from server
     */
    public function disconnect()
    {
        $return = true;

        if ($this->connection) {
            $return = $this->connection->disconnect();

            $this->connection = null;
        }

        return $return;
    }

    /**
     * Reconnect to AMQP server
     *
     * @return bool Whether connection established again
     */
    public function reconnect()
    {
        if (!$this->connection) {
            return $this->connect();
        }

        return $this->connection->reconnect();
    }

    /**
     * When in asynchronous mode listen for new data from AMQP broker, ignored otherwise.
     *
     * @return mixed
     */
    public function wait()
    {
        // we have no support for asynchronous communication in
    }
}