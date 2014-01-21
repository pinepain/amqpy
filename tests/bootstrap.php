<?php

$file = __DIR__ . '/../vendor/autoload.php';
if (!file_exists($file)) {
    throw new RuntimeException('Install dependencies to run test suite.');
}

$loader = require $file;

$loader->add('AMQPy\Helpers', __DIR__);
$loader->add('AMQPy\Tests', __DIR__);
