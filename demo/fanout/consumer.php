<?php

use AMQPy\AbstractConsumer;
use AMQPy\AMQPQueue;
use AMQPy\Solutions\Generic;

include 'bootstrap.php';


class EchoConsumer extends  AbstractConsumer
{
    private $counter = 0;

    private function count()
    {
        return $this->counter++;
    }

    public function consume($payload, AMQPEnvelope $envelope, AMQPQueue $queue)
    {

        if (rand(0, 100) > 90) {
            // throw exception with probability 0.1
            throw new Exception('Random exception');
        }

        echo "Received payload # {$this->count()} ", gettype($payload), PHP_EOL;
    }

    public function failure(Exception $e, AMQPEnvelope $envelope, AMQPQueue $queue)
    {
        echo "Failed to process payload # {$this->count()} due to exception: {$e->getMessage()}", PHP_EOL;
    }

    public function before(AMQPEnvelope $envelope, AMQPQueue $queue)
    {
        echo "Method ", __METHOD__, " called before consuming", PHP_EOL;
    }

    public function after(AMQPEnvelope $envelope, AMQPQueue $queue)
    {
        echo "Method ", __METHOD__, " called after consuming", PHP_EOL;
        $queue->cancel(); // stop consumer to receive envelopes from server
    }
}

$exchange = new Generic('example.fanout', $config);

$exchange->listen(new EchoConsumer(), 'example.fanout.default');
