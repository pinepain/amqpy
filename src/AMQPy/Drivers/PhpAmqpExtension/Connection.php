<?php


namespace AMQPy\Drivers\PhpAmqpExtension;

use AMQPy\Drivers\ConnectionInterface;

class Connection implements ConnectionInterface
{
    private $credentials;
    private $async;
    private $persistent;

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

    public function __construct(array $credentials = array(), $async = false, $persistent = false)
    {
        $this->credentials = $credentials;
        $this->async       = $async;
        $this->persistent  = $persistent;
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
     * Check whether connection persistent
     *
     * @return bool
     */
    public function isPersistent()
    {
        return $this->persistent;
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

            if ($this->isPersistent()) {
                return $this->connection->pconnect();
            } else {
                return $this->connection->connect();
            }
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
     * Disconnect from AMQP server
     *
     * @param bool $forever Should persistent connection be disconnected
     *
     * @return bool Whether disconnected from server
     */
    public function disconnect($forever = false)
    {
        $return = true;

        if ($this->connection) {
            if ($this->isPersistent() && $forever) {
                $return = $this->connection->pdisconnect();
            } else {
                $return = $this->connection->disconnect();
            }

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

        if ($this->isPersistent()) {
            return $this->connection->preconnect();
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