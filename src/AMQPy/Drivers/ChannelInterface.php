<?php


namespace AMQPy\Drivers;


interface ChannelInterface
{

    /**
     * @param $connection ConnectionInterface
     */
    public function __construct($connection);

    /**
     * @return ConnectionInterface
     */
    public function getConnection();

    /**
     * Whether driver communicates with server asynchronously.
     *
     * @return bool
     */
    public function isAsync();

    ///**
    // * Whether transaction started
    // *
    // * @return bool
    // */
    //public function isTransactional();

    /**
     * When in asynchronous mode listen for new data from AMQP broker, ignored otherwise.
     *
     * @return mixed
     */
    public function wait();

    /**
     * Whether channel is connected and active
     *
     * @return bool|null If deferred connection is used, null represent state when no connection was established yet.
     */
    public function isConnected();

    /**
     * Open new channel, if it was not opened yet
     *
     * @return bool Whether channel opened
     */
    public function connect();

    /**
     * Close channel, if it was opened
     *
     * @return bool Whether channel is closed
     */
    public function disconnect();

    /**
     * Reopen channel
     *
     * @return bool Whether channel was successfully reopened
     */
    public function reconnect();

    ///**
    // * Enable/disable flow from peer.
    // *
    // * @link https://www.rabbitmq.com/amqp-0-9-1-quickref.html#channel.flow
    // *
    // * @param bool $active If true, the peer starts sending content frames. If false, the peer stops sending content frames.
    // *
    // * @return bool  Confirms the setting of the processed flow method: true means the peer will start sending or continue to send content frames; false means it will not.
    // */
    //public function flow($active); // in fact, it from channel class, and rabbitmq doesn't support channel.flow with active=false (php-amqp doesn't support it at all due to rabbitmq-orientation)


    // channel class: deprecated to call manually
    //public function channelOpen($id = null);
    //// php-amqp doesn't support it at all
    //public function channelFlow($active);
    //// amqp signature: channel.close(reply-code reply-code, reply-text reply-text, class-id class-id, method-id method-id) ➔ close-ok
    //// php-amqplib signature: $reply_code=0, $reply_text="", $method_sig=array(0, 0)
    //// php-amqp doesn't support it at all
    //public function channelClose($id);


    // exchange class

    /**
     * Verify exchange exists, create if needed.
     *
     * @link https://www.rabbitmq.com/amqp-0-9-1-reference.html#exchange.declare
     *
     * @param string $amqp_exchange
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
    public function exchangeDeclare($amqp_exchange, $type, $passive = false, $durable = false, $auto_delete = true, $internal = false, $nowait = false, array $arguments = array());

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
    public function exchangeDestroy($exchange, $if_unused = false, $nowait = false);

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
    public function exchangeBind($destination, $source, $routing_key = "", $nowait = false, array $arguments = array());

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
    public function exchangeUnbind($destination, $source, $routing_key = "", $nowait = false, array $arguments = array());

    // queue class

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
    public function queueDeclare($queue = "", $passive = false, $durable = false, $exclusive = false, $auto_delete = true, $nowait = false, $arguments = null);

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
    public function queueDestroy($queue = "", $if_unused = false, $if_empty = false, $nowait = false);

    /**
     * Bind queue to an exchange.
     *
     * @link https://www.rabbitmq.com/amqp-0-9-1-reference.html#queue.bind
     *
     * @param        $queue
     * @param        $exchange
     * @param string $routing_key
     * @param bool   $nowait
     * @param null   $arguments
     *
     * @return bool
     */
    public function queueBind($queue, $exchange, $routing_key = "", $nowait = false, $arguments = null);

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
    public function queueUnbind($queue, $exchange, $routing_key = "", $arguments = null);

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
    public function queuePurge($queue = "", $nowait = false);

    // tx class
    /**
     * Select standard transaction mode.
     *
     * @link https://www.rabbitmq.com/amqp-0-9-1-reference.html#tx.select
     *
     * @return bool
     */
    public function txSelect();

    /**
     * Commit the current transaction.
     *
     * @link https://www.rabbitmq.com/amqp-0-9-1-reference.html#tx.commit
     *
     * @return bool
     */
    public function txCommit();

    /**
     * Abandon the current transaction.
     *
     * @link https://www.rabbitmq.com/amqp-0-9-1-reference.html#tx.rollback
     *
     * @return bool
     */
    public function txRollback();

    // confirm class
    /**
     * Sets the channel to use publisher acknowledgements.
     *
     * @link https://www.rabbitmq.com/amqp-0-9-1-reference.html#confirm.select
     *
     * @param bool $nowait
     *
     * @return bool
     */
    public function confirmSelect($nowait = false);

    // basic class
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
    public function basicAck($delivery_tag, $multiple = false);

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
    public function basicCancel($consumer_tag, $nowait = false);

    /**
     * Start a queue consumer.
     * NOTE: RabbitMQ does not support the no-local parameter of basic.consume.
     *
     * @link https://www.rabbitmq.com/amqp-0-9-1-reference.html#basic.consume
     *
     * @param string $queue
     * @param null   $callback
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
    );

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
    public function basicGet($queue = "", $no_ack = false);

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
    public function basicNack($delivery_tag, $multiple = false, $requeue = false);

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
    public function basicPublish($msg, $exchange = "", $routing_key = "", array $properties = array(), $mandatory = false, $immediate = false);


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
    public function basicQos($prefetch_count, $prefetch_size = 0, $a_global = false);

    /**
     * Redeliver unacknowledged messages.
     *
     * @link https://www.rabbitmq.com/amqp-0-9-1-reference.html#basic.recover
     *
     * @param bool $requeue NOTE: Recovery with requeue=false is not supported.
     *
     * @return bool
     */
    public function basicRecover($requeue = false);

    /**
     * Reject an incoming message.
     *
     * @link https://www.rabbitmq.com/amqp-0-9-1-reference.html#basic.reject
     *
     * @param $delivery_tag
     * @param $requeue
     *
     * @return mixed
     */
    public function basicReject($delivery_tag, $requeue = false);
}
