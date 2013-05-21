<?php

use AMQPy\IConsumer;
use AMQPy\Queue;
use AMQPy\Solutions\Generic;

include (dirname(__FILE__) . '/../../init_web.php');
include (dirname(__FILE__) . '/../config.php');


class DemoReceiver implements IConsumer
{
    private $counter = 0;
    private $radio_station;

    private function count()
    {
        return $this->counter++;
    }

    public function __construct()
    {
        $available_channels = array(
            'europe',
            'africa',
            'daily',
            'weekly',
            'all'
        );

        if (!in_array($_SERVER['argv'][1], $available_channels)) {
            $this->radio_station = 'all';
        } else {
            $this->radio_station = $_SERVER['argv'][1];
        }

        echo "Receiver listen to {$this->radio_station} radio station", PHP_EOL;
    }

    public function getRadioStation()
    {
        return $this->radio_station;
    }

    public function consume($payload, AMQPEnvelope $envelope, Queue $queue)
    {

        if (rand(0, 100) > 95) {
            throw new Exception('some atmospheric disturbances');
        } elseif (rand(0, 100) > 85) {
            throw new Exception('low battery charge');
        } elseif (rand(0, 100) > 75) {
            throw new Exception('low audio quality');
        }

        echo "Received forecast #{$this->count()} from {$this->getRadioStation(
        )} radio station: {$payload}, route key({$envelope->getRoutingKey()})", PHP_EOL;
    }

    public function except(Exception $e, AMQPEnvelope $envelope, Queue $queue)
    {
        echo "Failed to receive forecast #{$this->count()} from {$this->getRadioStation(
        )} radio station due to {$e->getMessage()}", PHP_EOL;
    }

    public function preConsume(Queue $queue)
    {
    }

    public function postConsume(Queue $queue)
    {
    }
}

$exchange = new Generic('example.topic.weather', Config::get('amqp_params'));
$receiver = new DemoReceiver();

$exchange->listen($receiver, "example.topic.weather.{$receiver->getRadioStation()}");