<?php

namespace AMQPy\Drivers;


// TODO: add on-demand connecting
class PhpAmqpExtension implements DriverInterface
{
    private $credentials = [];

    /**
     * @var \AMQPConnection
     */
    private $connection;

    /**
     * @var \AMQPChannel
     */
    private $channel;

    /**
     * @var \AMQPQueue[]
     */
    private $queues = [];

    /**
     * @var \AMQPExchange
     */
    private $exchange;

    public function isAsync()
    {
        return false;
    }

    /**
     * Whether driver run actions in deferred way whenever it possible and not mission-critical.
     *
     * @return bool
     */
    public function isDeferred()
    {
        return true;
    }

    /**
     * When in asynchronous mode listen for new data from AMQP broker, ignored otherwise.
     *
     * @return mixed
     */
    public function wait()
    {
    }

    /**
     * Connect to AMQP server
     *
     * @param array $credentials
     *
     * @return bool Whether connection established
     */
    public function connect(array $credentials)
    {
        if ($this->isConnected()) {
            $this->disconnect();
        }

        $this->credentials = $credentials;
    }

    public function disconnect()
    {
        if ($this->connection->isConnected()) {
            $this->connection->disconnect();
        }

        $this->connection = null;
        $this->refreshInternals();
    }

    /**
     * @return bool
     */
    public function isConnected()
    {
        if ($this->channel) {
            return $this->channel->isConnected();
        } elseif ($this->connection) {
            return $this->connection->isConnected();
        }

        return false;
    }

    protected function refreshInternals()
    {
        $this->exchange = null;
        $this->queues   = [];
        $this->channel  = null;
    }

    protected function getActiveConnection()
    {
        if (!$this->connection) {
            $this->connection = new \AMQPConnection($this->credentials);
            $this->connection->connect();
        } elseif (!$this->connection->isConnected()) {
            $this->refreshInternals();
            $this->connection->reconnect();
        }

        return $this->connection;
    }

    protected function getActiveChannel()
    {
        if (!$this->channel) {
            $this->channel = new \AMQPChannel($this->getActiveConnection());
        } elseif (!$this->channel->isConnected()) {
            $this->refreshInternals();
            $this->channel = $this->getActiveChannel();
        }

        return $this->channel;
    }

    protected function getActiveExchange()
    {
        if (!$this->exchange || !$this->exchange->getChannel()->isConnected()) {
            $this->exchange = new \AMQPExchange($this->getActiveChannel());
        }

        return $this->exchange;
    }

    /**
     * Verify exchange exists, create if needed.
     *
     * @link https://www.rabbitmq.com/amqp-0-9-1-reference.html#exchange.declare
     *
     * @param string $exchange
     * @param string $type
     * @param bool   $passive
     * @param bool   $durable
     * @param bool   $auto_delete
     * @param bool   $internal
     * @param bool   $nowait
     * @param array  $arguments
     *
     * @return bool
     */
    public function exchangeDeclare($exchange, $type, $passive = false, $durable = false, $auto_delete = true, $internal = false, $nowait = false, array $arguments = [])
    {
        $amqp_exchange = $this->getActiveExchange();

        $flags = AMQP_NOPARAM
            | ($passive ? AMQP_PASSIVE : AMQP_NOPARAM)
            | ($durable ? AMQP_DURABLE : AMQP_NOPARAM)
            | ($auto_delete ? AMQP_AUTODELETE : AMQP_NOPARAM)
            | ($internal ? AMQP_INTERNAL : AMQP_NOPARAM)
            | ($nowait ? AMQP_NOWAIT : AMQP_NOPARAM);

        $amqp_exchange->setName($exchange);
        $amqp_exchange->setType($type);
        $amqp_exchange->setFlags($flags);
        $amqp_exchange->setArguments($arguments);

        return $amqp_exchange->declareExchange();
    }

    /**
     * Delete an exchange.
     *
     * @link https://www.rabbitmq.com/amqp-0-9-1-reference.html#exchange.delete
     *
     * @param string $exchange
     * @param bool   $if_unused
     * @param bool   $nowait
     *
     * @return bool
     */
    public function exchangeDestroy($exchange, $if_unused = false, $nowait = false)
    {
        $flags = AMQP_NOPARAM
            | ($if_unused ? AMQP_IFUNUSED : AMQP_NOPARAM)
            | ($nowait ? AMQP_NOWAIT : AMQP_NOPARAM);

        return $this->getActiveExchange()->delete($exchange, $flags);
    }

    /**
     * Bind exchange to an exchange.
     *
     * @link https://www.rabbitmq.com/amqp-0-9-1-reference.html#exchange.bind
     *
     * @param string $destination
     * @param string $source
     * @param string $routing_key
     * @param bool   $nowait
     * @param array  $arguments
     *
     * @return bool
     */
    public function exchangeBind($destination, $source, $routing_key = "", $nowait = false, array $arguments = array())
    {
        $flags = $nowait ? AMQP_NOWAIT : AMQP_NOPARAM;

        $amqp_exchange = $this->getActiveExchange();
        $amqp_exchange->setName($source);

        return $amqp_exchange->bind($destination, $routing_key, $flags);
    }


}