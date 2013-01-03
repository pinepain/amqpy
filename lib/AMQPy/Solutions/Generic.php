<?php
/**
 * @author Ben Pinepain <pinepain@gmail.com>
 * @url https://github.com/pinepain/amqpy
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace AMQPy\Solutions;

use AMQPy\Connection;
use AMQPy\Queue;
use AMQPy\IConsumer;

use \AMQPException;


class Generic {
    private $settings = array();

    private $connection;
    private $exchange;

    private $queues = array();
    private $consumer_flags = array();

    /**
     * Establish connection to broker, create exchange and queues if they was not created and bind queues to exchange
     *
     * @param string $exchange Exchange name
     * @param array  $settings Entry point settings
     *
     * @throws AMQPException When serializer given in config does not exists
     */
    public function __construct($exchange, array $settings) {
        $_s = $settings;
        $_e = $settings['exchanges'][$exchange];

        if (!isset($_e['messages']['attributes'])) {
            $_e['messages']['attributes'] = array();
        }

        $_s['exchange'] = $_e;
        $this->settings = $_s;

        // establish connection
        $this->connection = new Connection($_s['credentials']);

        if (isset($settings['prefetch'])) {
            $this->connection->getChannel()->setPrefetchCount($_s['prefetch']);
        }

        if (!class_exists($_e['serializer'])) {
            throw new AMQPException('Serializer does not exists');
        }

        $this->exchange = $this->connection->getExchange($exchange,
                                                         $_e['type'],
                                                         new $_e['serializer'],
                                                         $_e['flags'],
                                                         isset($_e['args']) ? $_e['args'] : array()
        );

        // force init queues associated with this exchange
        foreach ($this->queues as $name => $queue) {
            $this->getQueue($name);
        }
    }

    /**
     * Send message to exchange
     *
     * @param mixed $message     Message data to send.
     * @param null  $routing_key Routing key to deliver message. Ignored for 'fanout' exchanges.
     *                           If none given default will be used
     */
    public function send($message, $routing_key = null) {
        $_m = $this->settings['exchanges']['messages'];
        $this->exchange->send($message, $routing_key, $_m['flags'], $_m['attributes']);
    }

    /**
     * Attach consumer to process payload from queue
     *
     * @param IConsumer $consumer Consumer to process payload and handle possible errors
     * @param string    $queue    Queue name to attach consumer to;
     */
    public function listen(IConsumer $consumer, $queue) {
        $q  = $this->getQueue($queue);
        $_q = $this->settings['exchanges']['queues'][$queue];

        $q->listen($consumer, $_q['consumer_flags']);
    }

    /**
     * Get queue associated with given exchange
     *
     * @param string $name Queue name to get
     *
     * @return Queue
     * @throws AMQPException When queue not found or could not been initialized
     */
    public function getQueue($name) {
        if (!isset($this->queues[$name])) {

            $_q = $this->settings['exchanges']['queues'];

            if (!isset($_q[$name])) {
                throw new AMQPException('Queue does not exists');
            }

            $_s = $_q[$name];

            if (!isset($_s['routing_key'])) {
                $_s['routing_key'] = '#'; // listen for all messages
            }


            if (!isset($settings['consumer_flags'])) {
                $_s['consumer_flags'] = AMQP_NOPARAM;
            }

            $this->consumer_flags[$name] = $settings['consumer_flags'];

            $this->queues[$name] = $this->exchange->getQueue(
                $name,
                $_s['routing_key'],
                $_s['flags'],
                isset($_s['args']) ? $_s['args'] : array()
            );
        }

        return $this->queues[$name];
    }


    public function getConnection() {
        return $this->connection;
    }

    public function getExchange() {
        return $this->exchange;
    }

    public function getQueues() {
        return $this->queues;
    }
}
