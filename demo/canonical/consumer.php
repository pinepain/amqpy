<?php

$exchange_name = 'example.fanout';
$queue_name    = 'example.fanout.default';
$route_key     = 'ignored-for-fanout';

// establish connection
$connection = new AMQPConnection();
if (!$connection->connect()) {
    echo "Failed to establish connection", PHP_EOL;
    die;
}

// create channel
$channel = new AMQPChannel($connection);

// create exchange
$exchange = new AMQPExchange($channel);
$exchange->setType(AMQP_EX_TYPE_FANOUT);
$exchange->setName($exchange_name);
$exchange->declare(); // if exchange already exists it will not be redeclared

// here we definitely need queue to be declared before we'll use it
$queue = new AMQPQueue($channel);
$queue->setName($queue_name);
$queue->declare(); // same for queue, if already declared will not be redeclared
$queue->bind($exchange_name, $route_key);
// NOTE: if exchange doesn't exists you can't bind queue to it, which is
// although quite obvious

$queue->consume(
    function (AMQPEnvelope $envelope, AMQPQueue $queue) {
        $message = $envelope->getBody();
        echo "Received {$message} and received at " . date(DATE_RFC822), PHP_EOL;
        $queue->ack($envelope->getDeliveryTag());
    }
);
