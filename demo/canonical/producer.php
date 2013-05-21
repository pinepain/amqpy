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
// NOTE: if exchage with the same name but with different type or/and different
// flags exists exception will be thrown. Same for queues.

// create queue and bind it to exhange. We have to do that before we will actually
// use it to make it store messages for us immediately. If you will not create it
// here and you don't need to process already published message it's OK to crete
// queue before you'll need it
$queue = new AMQPQueue($channel);
$queue->setName($queue_name);
$queue->declare();
$queue->bind($exchange_name, $route_key);


$count = 0;
while (true) {
    $message = "test message #{$count} created at " . date(DATE_RFC822);
    $exchange->publish($message, null, AMQP_NOWAIT);
    echo "Sent {$message}" . PHP_EOL;

    $count++;
    usleep(500000);
}
