<?php
error_reporting(-1);

$file = __DIR__ . '/../vendor/autoload.php';
if (!file_exists($file)) {
    throw new RuntimeException('Install dependencies to run test suite.');
}

$loader = require $file;

$loader->add('AMQPy\Helpers', __DIR__);
$loader->add('AMQPy\Tests', __DIR__);


//class Consumer extends \AMQPy\AbstractConsumer {
//    /**
//     * Process received data from queued message.
//     */
//    public function consume($payload, \AMQPy\Client\Delivery $delivery, \AMQPy\AbstractListenter $listener)
//    {
//        var_dump($payload);
//        throw new Exception;
//    }
//}
//
//$connection = new AMQPConnection();
//$connection->connect();
//
//$channel = new AMQPChannel($connection);
//
//
//$exchange = new AMQPExchange($channel);
//$exchange->setName('test');
//$exchange->setType(AMQP_EX_TYPE_TOPIC);
//$exchange->declareExchange();
//
//$queue = new AMQPQueue($channel);
//$queue->setName('test');
//$queue->setFlags(AMQP_NOPARAM);
//$queue->declareQueue();
//
//$queue->bind($exchange->getName(), 'test');
//
//$serializers = new \AMQPy\Serializers\SerializersPool(array(new \AMQPy\Serializers\PlainText()));
//
//
//$publisher = new \AMQPy\Publisher($exchange, $serializers);
//
//$publisher->publish('test message 1', 'test');
//$publisher->publish('test message 2', 'test');
//$publisher->publish('test message 3', 'test');
//
//$builder = new \AMQPy\Support\DeliveryBuilder();
//
//$listener = new \AMQPy\Listenter($queue, $serializers, $builder);
//
//
//$res = $listener->get(true);
//
//var_dump($res->getEnvelope()->getDeliveryTag());
//
//die;
//
////$listener->consume(new Consumer(), true);
//
////$queue->consume(function (AMQPEnvelope $e) {
////    var_dump($e->getBody());
////        throw new Exception($e);
////    }, AMQP_AUTOACK);
////
////die;