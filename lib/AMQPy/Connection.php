<?php
/**
 * @author Ben Pinepain <pinepain@gmail.com>
 * @url https://github.com/pinepain/amqpy
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace AMQPy;

use \AMQPConnection;

use \AMQPException;
use \AMQPConnectionException;


class Connection extends AMQPConnection {
    /**
     * @var Channel default channel
     *
     * Actually, due to single-thread nature of PHP we don't need more than one
     * channel per connection in most cases.
     */
    private $default_channel;

    /**
     * Creates an AMQPConnection instance representing a connection to an AMQP broker and immediately connect to broker
     *
     * @param array $credentials Information for connecting to the AMQP broker.
     * @param bool  $connect Should
     *
     * All the other keys are ignored.
     * Note: A connection will not be established until AMQPConnection::connect() is called.
     */
    public function __construct(array $credentials = array(), $connect = true) {
        parent::__construct($credentials);

        if ($connect) {
            $this->connect();
        }
    }

    public function getDefaultChannel() {
        if (null === $this->default_channel) {
            $this->setDefaultChannel();
        }

        return $this->default_channel;
    }

    public function setDefaultChannel(Channel $channel=null) {
        if ($channel) {
            if ($channel->getConnection() !== $this) {
                throw new AMQPConnectionException("Channel does not belong to this connection");
            }
        } else {
            $channel = $this->getChannel();
        }

        return $this->default_channel = $channel;
    }

    public function getChannel() {
        return new Channel($this);
    }

    /**
     * @param string      $name       The name of the exchange to set as string.
     * @param string      $type       The type of the exchange. This can be any of AMQP_EX_TYPE_DIRECT, AMQP_EX_TYPE_FANOUT, AMQP_EX_TYPE_HEADER or AMQP_EX_TYPE_TOPIC.
     * @param ISerializer $serializer Messages serailizer
     * @param int | null  $flags      A bitmask of flags. This call currently only considers the following flags: AMQP_DURABLE, AMQP_PASSIVE.
     * @param array       $args       An array of key/value pairs of arguments.
     *
     * @return Exchange A new instance of an Exchange object, associated with this channel.
     */
    public function getExchange($name, $type, ISerializer $serializer, $flags = null, array $args = array()) {
        $exchange = new Exchange($this->getDefaultChannel(), $serializer);

        $exchange->setName($name);
        $exchange->setType($type);

        if (null !== $flags) {
            $exchange->setFlags($flags);
        }

        if (!empty($args)) {
            $exchange->setArguments($args);
        }

        // NOTE: if new settings differs from existent, error will be thrown
        // this operation is idempotent, so if exchange or connection already exists it will not be created
        $exchange->declare();

        return $exchange;
    }

}
