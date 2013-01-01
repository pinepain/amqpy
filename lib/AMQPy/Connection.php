<?php
/**
 * @author Ben Pinepain <pinepain@gmail.com>
 * @created 12/26/12 3:35 PM
 */

namespace AMQPy;

use \AMQPChannel;
use \AMQPConnection;

use \AMQPException;
use \AMQPConnectionException;


class Connection extends AMQPConnection {
    /**
     * @var AMQPChannel default channel
     *
     * Actually, due to single-thread nature of PHP we don't need more than one
     * channel per connection in most cases.
     */
    private $channel;

    /**
     * Creates an AMQPConnection instance representing a connection to an AMQP broker and immediately connect to broker
     *
     * The credentials is an optional array of credential information for connecting to the AMQP broker.
     * $credentials = array(
     *  'host'     => amqp.host     The host to connect too. Note: Max 1024 characters.
     *  'port'     => amqp.port     Port on the host.
     *  'vhost'    => amqp.vhost    The virtual host on the host. Note: Max 128 characters.
     *  'login'    => amqp.login    The login name to use. Note: Max 128 characters.
     *  'password' => amqp.password Password. Note: Max 128 characters.
     *  'timeout'  => amqp.timeout   NOTE: non-documented. Amount of time, in seconds (may be float), after which this instance of an AMQPConnection object times out a request to the broker.
     * )
     *
     * All the other keys are ignored.
     * Note: A connection will not be established until AMQPConnection::connect() is called.
     *
     * @param array $credentials
     *
     * @throws AMQPException Throws an exception on parameter parsing failures, and option errors.
     * @throws AMQPConnectionException Thorws an exception on connection establishing with the AMQP broker failure.
     */
    public function __construct(array $credentials = array()) {
        parent::__construct($credentials);
        $this->setTimeout(0);

        $connected = $this->connect();
        if (!$connected) {
            throw new AMQPConnectionException('Failed to establish connection with the AMQP broker');
        }
    }

    public function getChannel() {
        if (null === $this->channel) {
            $this->channel = new AMQPChannel($this);
        }

        return $this->channel;
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
        $exchange = new Exchange($this->getChannel(), $serializer);

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
