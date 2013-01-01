<?php
/**
 * @author Ben Pinepain <pinepain@gmail.com>
 * @created 12/27/12 4:22 PM
 */

namespace AMQPy;


use \AMQPExchange;
use \AMQPChannel;


class Exchange extends AMQPExchange {
    /**
     * @var ISerializer
     */
    private $serializer = null;

    /**
     * @var AMQPChannel
     */
    private $channel = null;

    public function getChannel() {
        return $this->channel;
    }

    public function getSerializer() {
        return $this->serializer;
    }

    public function __construct(AMQPChannel $amqp_channel, ISerializer $serializer) {
        parent::__construct($amqp_channel);
        $this->serializer = $serializer;

        $this->channel = $amqp_channel;
    }

    /**
     * Publish a message to the exchange represented by the AMQPExchange object.
     *
     * @param mixed   $message     The message to publish
     * @param string  $routing_key The routing key to which to publish.
     * @param integer $flags       One or more of AMQP_MANDATORY and AMQP_IMMEDIATE.
     * @param array   $attributes  One or more from the list:
     * <pre>
     *  $attributes = array(
     *   'content_type'
     *   'content_encoding'
     *   'message_id'
     *   'user_id'
     *   'app_id'
     *   'delivery_mode'
     *   'priority'
     *   'timestamp'
     *   'expiration'   Message TTL in microseconds
     *   'type'
     *   'reply_to'
     *   'correlation_id'
     *  )</pre>
     *
     * Note: Any of the attributes that is not in the list will be converted to custom headers
     *
     * @return boolean Returns TRUE on success or FALSE on failure.
     *
     * @throw AMQPExchangeException   Throws an exception on failure.
     * @throw AMQPChannelException    Throws an exception if the channel is not open.
     * @throw AMQPConnectionException Throws an exception if the connection to the broker was lost
     */
    public function send($message, $routing_key, $flags = AMQP_NOPARAM, array $attributes = array()) {
        // AMQP can send only string messages, so we have to serialize it and set 'content_type'
        $message                    = $this->serializer->serialize($message);
        $attributes['content_type'] = $this->serializer->getContentType();
        return parent::publish($message, $routing_key, $flags, $attributes);
    }

    public function getQueue($name, $routing_key, $flags = null, array $args = array()) {
        $queue = new Queue($this->getChannel(), $this->getSerializer());

        $queue->setName($name);

        if (null !== $flags) {
            $queue->setFlags($flags);
        }

        if (!empty($args)) {
            $queue->setArguments($args);
        }
        // NOTE: if new settings differs from existent, error will be thrown
        // this operation is idempotent, so if exchange or connection already exists it will not be created
        $queue->declare();

        // technically, queues can be bound multiple time but with different routing keys
        if (is_array($routing_key)) {
            foreach ($routing_key as $_key) {
                $queue->bind($this->getName(), $_key);
            }
        } else {
            $queue->bind($this->getName(), $routing_key);
        }
        return $queue;
    }
}
