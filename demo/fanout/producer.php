<?php
/**
 * @author Ben Pinepain <pinepain@gmail.com>
 * @created 12/24/12 8:16 PM
 */

include 'bootstrap.php';


use AMQPy\Solutions\Generic;


class EchoProducer {
    public function getObject() {
        $obj           = new StdClass;
        $obj->datetime = new DateTime();

        return $obj;
    }

    public function getArray() {
        return array('test' => 'array', 42, new DateTime());
    }

    public function getResource() {
        $f = fopen('/tmp/test.file', 'w');

        return $f;
    }

    public function getString() {
        return 'test_string';
    }

    public function getInteger() {
        return 42;
    }

    public function getFloat() {
        return 42.42424242;
    }

    public function getTrue() {
        return true;
    }

    public function getFalse() {
        return false;
    }

    public function getNull() {
        return null;
    }

    public function getZero() {
        return 0;
    }

    public function getEmptystr() {
        return '';
    }
}


$exchange = new Generic('example.fanout', $config);
$producer = new EchoProducer();

$methods = get_class_methods($producer);
$count   = 0;

while (true) {

    $method = $methods[array_rand($methods)];

    $exchange->send($producer->$method());

    echo "Sent message #{$count} ({$method})" . PHP_EOL;

    $count++;
    usleep(500000);
}
