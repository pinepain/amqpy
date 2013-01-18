<?php

/**
 * @author Ben Pinepain <pinepain@gmail.com>
 * @created 12/24/12 8:16 PM
 */

include (dirname(__FILE__) . '/../config.php');


$exchange_name = 'example.fanout';
$queue_name    = 'example.fanout.default';



$connection = new AMQPConnection($config['credentials']);
$connection->connect();

$channel = new AMQPChannel($connection);

$exchange = new AMQPExchange($channel);
$exchange->setType(AMQP_EX_TYPE_FANOUT);
$exchange->setName($exchange_name);
$exchange->declare();


$queue = new AMQPQueue($channel);
$queue->setName($queue_name);
$queue->declare();
$queue->bind($exchange_name, 'ignored-for-fanout-exchanges');


$message = str_repeat('d41d8cd98f00b204e9800998ecf8427e', 32); // 1Kb message
$size    = strlen($message);


$count = 0;
while (true) {

    $exchange->publish($message, null, AMQP_NOWAIT);
    echo "Sent message #{$count} ({$size} bytes)" . PHP_EOL;

    $count++;
    usleep(500000);
}
