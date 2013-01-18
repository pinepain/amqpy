<?php
/**
 * @author Ben Pinepain <pinepain@gmail.com>
 * @created 12/24/12 8:16 PM
 */

include (dirname(__FILE__) . '/../config.php');


use AMQPy\IConsumer;
use AMQPy\Queue;
use AMQPy\Solutions\Generic;


class EchoConsumer implements IConsumer {
    private $counter = 0;

    private function count() {
        return $this->counter++;
    }


    public function consume($payload, AMQPEnvelope $envelope, Queue $queue) {

        if (rand(0, 100) > 75) {
            // throw exception with probability 0.25
            throw new Exception('Random exception');
        }

        echo "Received payload # {$this->count()} ", gettype($payload), PHP_EOL;
    }

    public function except(Exception $e, AMQPEnvelope $envelope, Queue $queue) {
        echo "Failed to process payload # {$this->count()} due to exception: {$e->getMessage()}", PHP_EOL;
    }

    public function preConsume(Queue $queue) {
    }

    public function postConsume(Queue $queue) {
    }
}

$exchange = new Generic('example.fanout', $config);

$exchange->listen(new EchoConsumer(), 'example.fanout.default');