<?php

/**
 * @author Ben Pinepain <pinepain@gmail.com>
 * @created 12/24/12 8:16 PM
 */

include (dirname(__FILE__) . '/../config.php');


use AMQPy\Solutions\Generic;


$exchange = new Generic('example.fanout', $config);

$message = str_repeat('d41d8cd98f00b204e9800998ecf8427e', 32); // 1Kb message

$size = strlen($message);

$count = 0;
while (true) {

    $exchange->send($message);
    echo "Sent message #{$count} ({$size} bytes)" . PHP_EOL;

    $count++;
    usleep(500000);
}
