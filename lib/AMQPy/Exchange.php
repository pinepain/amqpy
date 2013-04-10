<?php
/**
 * @author Bogdan Padalko <pinepain@gmail.com>
 * @url https://github.com/pinepain/amqpy
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace AMQPy;

use AMQPExchange;
use AMQPExchangeException;

class Exchange extends AMQPExchange
{
    /**
     * @var ISerializer
     */
    private $serializer = null;

    /**
     * @var Channel
     */
    private $channel = null;

    public function getChannel()
    {
        return $this->channel;
    }

    public function getSerializer()
    {
        return $this->serializer;
    }

    public function __construct(Channel $amqp_channel, ISerializer $serializer)
    {
        parent::__construct($amqp_channel);

        $this->serializer = $serializer;
        $this->channel    = $amqp_channel;
    }

    /**
     * Publish a message to the exchange represented by the AMQPExchange object.
     *
     * @param mixed $message     The message to publish
     * @param string|null $routing_key The routing key to which to publish, ignored for fanout exchanges
     * @param integer $flags       One or more of AMQP_MANDATORY and AMQP_IMMEDIATE.
     * @param array $attributes  One or more from the list:
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
     * @throws AMQPExchangeException Throws an exception on failure.
     */
    public function send($message, $routing_key = null, $flags = AMQP_NOPARAM, array $attributes = array())
    {
        // AMQP can send only string messages, so we have to serialize it and set 'content_type'
        $message                    = $this->serializer->serialize($message);
        $attributes['content_type'] = $this->serializer->getContentType();

        if (!parent::publish($message, $routing_key, $flags, $attributes)) {
            // TODO: fix publish php-amqp method to return false when it should
            // (see https://github.com/pdezwart/php-amqp/issues/23)
            throw new AMQPExchangeException('Failed to send message');
        }
    }

    public function getQueue($name, $routing_key, $flags = null, array $args = array())
    {
        $queue = new Queue($this->getChannel(), $this->getSerializer());

        if (!empty($name)) {
            $queue->setName($name);
        }

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
