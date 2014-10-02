<?php

namespace AMQPy\Drivers\PhpAmqpExtension;


// TODO: add on-demand connecting
use AMQPy\Drivers\ChannelInterface;
use AMQPy\Drivers\ConnectionInterface;

class Channel implements ChannelInterface
{
    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var \AMQPChannel
     */
    private $channel;

    /**
     * @var \AMQPQueue
     */
    private $queue;

    /**
     * @var \AMQPExchange
     */
    private $exchange;

    protected static $properties_type = array(
        "content_type"        => 'string',
        "content_encoding"    => 'string',
        "application_headers" => 'array',
        "delivery_mode"       => 'int',
        "priority"            => 'int',
        "correlation_id"      => 'string',
        "reply_to"            => 'string',
        "expiration"          => 'string',
        "message_id"          => 'string',
        "timestamp"           => 'int',
        "type"                => 'string',
        "user_id"             => 'string',
        "app_id"              => 'string',
    );

    protected static $properties_accessor = array(
        'content_type'     => 'getContentType',
        'content_encoding' => 'getContentEncoding',
        'headers'          => 'getHeaders',
        'delivery_mode'    => 'getDeliveryMode',
        'priority'         => 'getPriority',
        'correlation_id'   => 'getCorrelationId',
        'reply_to'         => 'getReplyTo',
        'expiration'       => 'getExpiration',
        'message_id'       => 'getMessageId',
        'timestamp'        => 'getTimestamp',
        'type'             => 'getType',
        'user_id'          => 'getUserId',
        'app_id'           => 'getAppId',
    );

    /**
     * @param $connection Connection
     */
    public function __construct($connection)
    {
        $this->connection = $connection;
    }

    /**
     * @return ConnectionInterface
     */
    public function getConnection()
    {
        return $this->connection;
    }

    /**
     * Whether driver communicates with server asynchronously.
     *
     * @return bool
     */
    public function isAsync()
    {
        return $this->connection->isAsync();
    }

    /**
     * Check whether channel persistent
     *
     * @return bool
     */
    public function isPersistent()
    {
        // NOTE: even on persistent php-amqp connections CHANNELS ARE NOT PERSISTENT
        return false;
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
     * Whether channel is connected and active
     *
     * @return bool|null If deferred connection is used, null represent state when no connection was established yet.
     */
    public function isConnected()
    {
        return $this->channel && $this->channel->isConnected();
    }

    /**
     * Open new channel, if it was not opened yet
     *
     * @return bool Whether channel opened
     */
    public function connect()
    {
        if (!$this->channel) {
            $this->channel = $this->connection->createChannel();
        }

        return $this->channel->isConnected();
    }

    /**
     * Close channel, if it was opened
     *
     * @return bool Whether channel is closed
     */
    public function disconnect()
    {
        // NOTE: php-amqp has no direct channel.close method calling support but it closes channel when destructor called

        $this->channel  = null;
        $this->exchange = null;
        $this->queue    = null;

        return true;
    }

    /**
     * Reopen channel
     *
     * @return bool Whether channel was successfully reopened
     */
    public function reconnect()
    {
        // not a rocket science, huh?
        return $this->disconnect() && $this->connect();
    }

    public function getActiveChannel()
    {
        if (!$this->channel) {
            $this->connect();
        }

        return $this->channel;
    }

    public function getActiveExchange()
    {
        if (!$this->exchange) {
            $this->connect();

            $this->exchange = $this->connection->makeClass('AMQPExchange', $this->channel);
        }

        return $this->exchange;
    }

    public function getActiveQueue()
    {
        if (!$this->queue) {
            $this->connect();

            $this->queue = $this->connection->makeClass('AMQPQueue', $this->channel);
        }

        return $this->queue;
    }

    /**
     * @param \AMQPEnvelope $envelope
     *
     * @return array
     */
    public function decodeEnvelope($envelope = null)
    {
        if (!$envelope) {
            return null;
        }

        $body = $envelope->getBody();

        $properties = array();
        foreach (self::$properties_accessor as $property => $accessor) {
            $properties[$property] = $envelope->$accessor();
        }

        $delivery_info = array(
            'delivery_tag'  => $envelope->getDeliveryTag(),
            'redelivered'   => $envelope->isRedelivery(),
            'exchange'      => $envelope->getExchangeName(),
            'routing_key'   => $envelope->getRoutingKey(),
            'message_count' => false, // unavailable within php-amqp
        );

        return array($body, $delivery_info, $properties);
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
     * TODO: add arguments support to php-amqp
     *
     * @param string $destination
     * @param string $source
     * @param string $routing_key
     * @param bool   $nowait
     * @param array  $arguments Ignored in current implementation
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

    /**
     * Unbind an exchange from an exchange.
     *
     * @link https://www.rabbitmq.com/amqp-0-9-1-reference.html#exchange.unbind
     *
     * @param string $destination
     * @param string $source
     * @param string $routing_key
     * @param bool   $nowait
     * @param array  $arguments
     *
     * @return bool
     */
    public function exchangeUnbind($destination, $source, $routing_key = "", $nowait = false, array $arguments = array())
    {
        return false; // TODO: add method support to php-amqp and no-wait flag
    }

    /**
     * Declare queue, create if needed.
     *
     * @link https://www.rabbitmq.com/amqp-0-9-1-reference.html#queue.declare
     *
     * @param string $queue
     * @param bool   $passive
     * @param bool   $durable
     * @param bool   $exclusive
     * @param bool   $auto_delete
     * @param bool   $nowait
     * @param null   $arguments
     *
     * @return int Queued messages count
     */
    public function queueDeclare($queue = "", $passive = false, $durable = false, $exclusive = false, $auto_delete = true, $nowait = false, $arguments = null)
    {
        $amqp_queue = $this->getActiveQueue();

        $flags = AMQP_NOPARAM
            | ($passive ? AMQP_PASSIVE : AMQP_NOPARAM)
            | ($durable ? AMQP_DURABLE : AMQP_NOPARAM)
            | ($auto_delete ? AMQP_AUTODELETE : AMQP_NOPARAM)
            | ($nowait ? AMQP_NOWAIT : AMQP_NOPARAM);

        $amqp_queue->setName($queue);
        $amqp_queue->setFlags($flags);
        $amqp_queue->setArguments($arguments);

        return $amqp_queue->declareQueue();
    }

    /**
     * Delete a queue.
     *
     * @link https://www.rabbitmq.com/amqp-0-9-1-reference.html#queue.delete
     *
     * @param string $queue
     * @param bool   $if_unused
     * @param bool   $if_empty
     * @param bool   $nowait
     *
     * @return int Deleted messages count
     */
    public function queueDestroy($queue = "", $if_unused = false, $if_empty = false, $nowait = false)
    {
        $amqp_queue = $this->getActiveQueue();

        $flags = AMQP_NOPARAM
            | ($if_unused ? AMQP_IFUNUSED : AMQP_NOPARAM)
            | ($if_empty ? AMQP_IFEMPTY : AMQP_NOPARAM)
            | ($nowait ? AMQP_NOWAIT : AMQP_NOPARAM);

        $amqp_queue->setName($queue);

        return $amqp_queue->delete($flags);
    }

    /**
     * Bind queue to an exchange.
     *
     * @link https://www.rabbitmq.com/amqp-0-9-1-reference.html#queue.bind
     *
     * @param        $queue
     * @param        $exchange
     * @param string $routing_key
     * @param bool   $nowait Ignored in current implementation
     * @param null   $arguments
     *
     * @return bool
     */
    public function queueBind($queue, $exchange, $routing_key = "", $nowait = false, $arguments = null)
    {
        $amqp_queue = $this->getActiveQueue();

        // $flags = $nowait ? AMQP_NOWAIT : AMQP_NOPARAM; TODO: add flags support to php-amqp

        $amqp_queue->setName($queue);

        return $amqp_queue->bind($exchange, $routing_key, $arguments);
    }

    /**
     * Unbind a queue from an exchange.
     *
     * @link https://www.rabbitmq.com/amqp-0-9-1-reference.html#queue.unbind
     *
     * @param        $queue
     * @param        $exchange
     * @param string $routing_key
     * @param null   $arguments
     *
     * @return bool
     */
    public function queueUnbind($queue, $exchange, $routing_key = "", $arguments = null)
    {
        $amqp_queue = $this->getActiveQueue();

        $amqp_queue->setName($queue);

        return $amqp_queue->unbind($exchange, $routing_key, $arguments);
    }

    /**
     * Purge a queue.
     *
     * @link https://www.rabbitmq.com/amqp-0-9-1-reference.html#queue.purge
     *
     * @param string $queue
     * @param bool   $nowait
     *
     * @return int  Number of messages purged.
     */
    public function queuePurge($queue = "", $nowait = false)
    {
        $amqp_queue = $this->getActiveQueue();

        // $flags = $nowait ? AMQP_NOWAIT : AMQP_NOPARAM; TODO: add flags support to php-amqp

        $amqp_queue->setName($amqp_queue);

        return $amqp_queue->purge();
    }

    /**
     * Select standard transaction mode.
     *
     * @link https://www.rabbitmq.com/amqp-0-9-1-reference.html#tx.select
     *
     * @return bool
     */
    public function txSelect()
    {
        $amqp_channel = $this->getActiveChannel();

        return $amqp_channel->startTransaction();
    }

    /**
     * Commit the current transaction.
     *
     * @link https://www.rabbitmq.com/amqp-0-9-1-reference.html#tx.commit
     *
     * @return bool
     */
    public function txCommit()
    {
        $amqp_channel = $this->getActiveChannel();

        return $amqp_channel->commitTransaction();
    }

    /**
     * Abandon the current transaction.
     *
     * @link https://www.rabbitmq.com/amqp-0-9-1-reference.html#tx.rollback
     *
     * @return bool
     */
    public function txRollback()
    {
        $amqp_channel = $this->getActiveChannel();


        return $amqp_channel->rollbackTransaction();
    }

    /**
     * Sets the channel to use publisher acknowledgements.
     *
     * @link https://www.rabbitmq.com/amqp-0-9-1-reference.html#confirm.select
     *
     * @param bool $nowait
     *
     * @return bool
     */
    public function confirmSelect($nowait = false)
    {
        return false; // TODO: add publisher acknowledgements support to php-amqp
    }

    /**
     * Acknowledge one or more messages.
     *
     * @link https://www.rabbitmq.com/amqp-0-9-1-reference.html#basic.ack
     *
     * @param      $delivery_tag
     * @param bool $multiple
     *
     * @return bool
     */
    public function basicAck($delivery_tag, $multiple = false)
    {
        $amqp_queue = $this->getActiveQueue();

        $flags = $multiple ? AMQP_MULTIPLE : AMQP_NOPARAM;

        return $amqp_queue->ack($delivery_tag, $flags);
    }

    /**
     * End a queue consumer.
     *
     * @link https://www.rabbitmq.com/amqp-0-9-1-reference.html#basic.cancel
     *
     * @param      $consumer_tag
     * @param bool $nowait
     *
     * @return bool
     */
    public function basicCancel($consumer_tag, $nowait = false)
    {
        $amqp_queue = $this->getActiveQueue();

        $flags = $nowait ? AMQP_NOWAIT : AMQP_NOPARAM;

        return $amqp_queue->cancel($consumer_tag, $flags);
    }

    /**
     * Start a queue consumer.
     * NOTE: RabbitMQ does not support the no-local parameter of basic.consume.
     *
     * @link https://www.rabbitmq.com/amqp-0-9-1-reference.html#basic.consume
     *
     * @param string $queue
     * @param null   $callback User callback to receive message. Arguments will be (string $body, array delivery_info, array properties)
     * @param string $consumer_tag
     * @param bool   $no_local
     * @param bool   $no_ack
     * @param bool   $exclusive
     * @param bool   $nowait
     * @param array  $arguments
     *
     * @return bool
     */
    public function basicConsume(
        $queue = "", $callback = null, $consumer_tag = "", $no_local = false,
        $no_ack = false, $exclusive = false, $nowait = false, $arguments = array()
    ) {
        $amqp_queue = $this->getActiveQueue();

        $flags = AMQP_NOPARAM
            | ($no_local ? AMQP_NOLOCAL : AMQP_NOPARAM)
            | ($no_ack ? AMQP_AUTOACK : AMQP_NOPARAM)
            | ($exclusive ? AMQP_EXCLUSIVE : AMQP_NOPARAM)
            | ($nowait ? AMQP_NOWAIT : AMQP_NOPARAM);

        if ($callback) {
            $wrapper = function ($envelope) use ($callback) {
                return call_user_func_array($callback, $this->decodeEnvelope($envelope));
            };
        } else {
            $wrapper = null;
        }

        $amqp_queue->consume($wrapper, $flags, $consumer_tag);

        return true;
    }

    /**
     * Direct access to a queue.
     *
     * @link https://www.rabbitmq.com/amqp-0-9-1-reference.html#basic.get
     *
     * @param string $queue
     * @param bool   $no_ack
     *
     * @return array(string $body, array delivery_info, array properties) | null
     */
    public function basicGet($queue = "", $no_ack = false)
    {
        $amqp_queue = $this->getActiveQueue();

        $flags = $no_ack ? AMQP_AUTOACK : AMQP_NOPARAM;

        $amqp_envelope = $amqp_queue->get($flags);

        return $this->decodeEnvelope($amqp_envelope);
    }

    /**
     * Reject one or more incoming messages.
     *
     * @link https://www.rabbitmq.com/amqp-0-9-1-reference.html#basic.nack
     *
     * @param string $delivery_tag
     * @param bool   $multiple
     * @param bool   $requeue
     *
     * @return bool
     */
    public function basicNack($delivery_tag, $multiple = false, $requeue = false)
    {
        $amqp_queue = $this->getActiveQueue();

        $flags = AMQP_NOPARAM
            | ($multiple ? AMQP_MULTIPLE : AMQP_NOPARAM)
            | ($requeue ? AMQP_REQUEUE : AMQP_NOPARAM);

        return $amqp_queue->nack($delivery_tag, $flags);
    }

    /**
     * Publish a message.
     *
     * @link https://www.rabbitmq.com/amqp-0-9-1-reference.html#basic.publish
     *
     * @param string $msg
     * @param array  $properties
     * @param string $exchange
     * @param string $routing_key
     * @param bool   $mandatory
     * @param bool   $immediate
     *
     * @return bool
     */
    public function basicPublish($msg, $exchange = "", $routing_key = "", array $properties = array(), $mandatory = false, $immediate = false)
    {
        $amqp_exchange = $this->getActiveExchange();

        $amqp_exchange->setName($exchange);

        $flags = AMQP_NOPARAM
            | ($mandatory ? AMQP_MANDATORY : AMQP_NOPARAM)
            | ($immediate ? AMQP_IMMEDIATE : AMQP_NOPARAM);

        // NOTE: there is a bug in php-amqp when properties has invalid type and cast to their standard type internally,
        //       original values get changed. You have being warned.

        // TODO: fix that damn bug or cast values manually to the right type here

        $properties_to_send = array();

        foreach ($properties as $name => $value) {
            if (!isset(self::$properties_type[$name])) {
                continue;
            }

            $type = self::$properties_type[$name];

            if ($type == 'int') {
                $properties_to_send[$name] = (int)$value;
            } elseif ($type == 'array') {
                $properties_to_send[$name] = (array)$value;
            } else {
                $properties_to_send[$name] = (string)$value;
            }
        }

        return $amqp_exchange->publish($msg, $routing_key, $flags, $properties_to_send);
    }

    /**
     * Specify quality of service.
     *
     * @link https://www.rabbitmq.com/amqp-0-9-1-reference.html#basic.qos
     *
     * @param int  $prefetch_count
     * @param int  $prefetch_size NOTE: Prefetch size limits are not implemented in RabbitMQ.
     * @param bool $a_global      NOTE: Has different meaning in RabbitMQ, see https://www.rabbitmq.com/consumer-prefetch.html
     *
     * @return bool
     */
    public function basicQos($prefetch_count, $prefetch_size = 0, $a_global = false)
    {
        $amqp_channel = $this->getActiveChannel();

        return $amqp_channel->qos($prefetch_size, $prefetch_count);
    }

    /**
     * Redeliver unacknowledged messages.
     * NOTE: Done via basic.nack due to lack of basic.reject in php-amqp extension.
     *
     * @link       https://www.rabbitmq.com/amqp-0-9-1-reference.html#basic.recover
     *
     * @param bool $requeue NOTE: Recovery with requeue=false is not supported.
     *
     * @return bool
     */
    public function basicRecover($requeue = false)
    {
        return $this->basicNack('', true, true);
    }

    /**
     * Reject an incoming message.
     *
     * @link https://www.rabbitmq.com/amqp-0-9-1-reference.html#basic.reject
     *
     * @param $delivery_tag
     * @param $requeue
     *
     * @return bool
     */
    public function basicReject($delivery_tag, $requeue = false)
    {
        $amqp_queue = $this->getActiveQueue();

        $flags = $requeue ? AMQP_REQUEUE : AMQP_NOPARAM;

        return $amqp_queue->reject($delivery_tag, $flags);
    }
}
