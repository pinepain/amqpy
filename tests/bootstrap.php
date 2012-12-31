<?php
/**
 * @author Ben Pinepain <pinepain@gmail.com>
 * @created 12/31/12 @ 7:09 PM
 */

$file = __DIR__.'/../vendor/autoload.php';
if (!file_exists($file)) {
    throw new RuntimeException('Install dependencies to run test suite.');
}

$autoload = require_once $file;
