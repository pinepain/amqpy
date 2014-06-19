<?php


namespace AMQPy\Drivers;


interface DriverInterface
{
    // Driver-specific options

    /**
     * Whether driver communicates with server asynchronously.
     *
     * @return bool
     */
    public function isAsync();

    /**
     * Whether driver run actions in deferred way whenever it possible and not mission-critical.
     *
     * @return bool
     */
    public function isDeferred();

    /**
     * When in asynchronous mode listen for new data from AMQP broker, ignored otherwise.
     *
     * @return mixed
     */
    public function wait();

    // connection-related methods

    /**
     * Connect to AMQP server
     *
     * @param array $credentials
     *
     * @return bool|null Whether connection established or null if deferred
     */
    public function connect(array $credentials);

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
    public function disconnect();

    /**
     * @return bool Whether connection established
     */
    public function isConnected();

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
     * @param array  $arguments
     *
     * @return mixed
     */
    public function exchangeUnbind($destination, $source, $routing_key = "", array $arguments = array());

    // queue class
    public function queueDeclare($queue = "", $passive = false, $durable = false, $exclusive = false, $auto_delete = true, $nowait = false, $arguments = null);

    public function queueDestroy($queue = "", $if_unused = false, $if_empty = false, $nowait = false);

    public function queueBind($queue, $exchange, $routing_key = "", $nowait = false, $arguments = null);

    public function queueUnbind($queue, $exchange, $routing_key = "", $arguments = null);

    public function queuePurge($queue = "", $nowait = false);

    // tx class
    public function txSelect();

    public function txCommit();

    public function txRollback();

    // confirm class
    public function confirmSelect($nowait = false);

    // basic class
    public function basicAck($delivery_tag, $multiple = false);

    public function basicCancel($consumer_tag, $nowait = false);

    // NOTE: RabbitMQ does not support the no-local parameter of basic.consume.
    public function basicConsume(
        $queue = "", $consumer_tag = "", $no_local = false,
        $no_ack = false, $exclusive = false, $nowait = false,
        $callback = null, $ticket = null, $arguments = array()
    );


    // TODO basic.deliver(consumer-tag consumer-tag, delivery-tag delivery-tag, redelivered redelivered, exchange-name exchange, shortstr routing-key)
    // public function basicDeliver(); // ($args, $msg)
    public function basicGet($queue = "", $no_ack = false, $ticket = null);

    public function basicNack($delivery_tag, $multiple = false, $requeue = false);

    public function basicPublish($msg, $exchange = "", $routing_key = "", $mandatory = false, $immediate = false, $ticket = null);

    public function basicQos($prefetch_size, $prefetch_count, $a_global);

    public function basicRecover($requeue = false);

    public function basicReject($delivery_tag, $requeue);
    //public function basicReturn();
}
