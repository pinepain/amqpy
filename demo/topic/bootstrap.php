<?php
/**
 * @author Ben Pinepain <pinepain@gmail.com>
 * @created 1/20/13 @ 1:12 AM
 */

$file = __DIR__ . '/../../vendor/autoload.php';
if (!file_exists($file)) {
    throw new RuntimeException('Install dependencies to run demo.');
}

$config = array(
    'example.topic.weather' => array(
        'type'       => AMQP_EX_TYPE_TOPIC, // send messages to all queues
        'flags'      => AMQP_DURABLE, // as for now we use only persistent exchanges and queues
        'serializer' => '\\AMQPy\\Serializers\\PhpNative',
        'prefetch'   => 1, // 3 is default, but we don't allow one consumer to hold more than one message at a time

        'messages'   => array(
            'flags'      => AMQP_DURABLE, // if shutdown occurred messages still be in queues
            'attributes' => array(),
        ),

        'queues'     => array(
            'example.topic.weather.africa' => array(
                'flags'          => AMQP_DURABLE,
                'args'           => array(),
                'routing_key'    => 'example.topic.weather.africa.*',
                'consumer_flags' => AMQP_AUTOACK,
            ),

            'example.topic.weather.europe' => array(
                'flags'          => AMQP_DURABLE,
                'args'           => array(),
                'routing_key'    => 'example.topic.weather.europe.*',
                'consumer_flags' => AMQP_AUTOACK,
            ),

            'example.topic.weather.weekly' => array(
                'flags'          => AMQP_DURABLE,
                'args'           => array(),
                'routing_key'    => 'example.topic.weather.*.weekly',
                'consumer_flags' => AMQP_AUTOACK,
            ),

            'example.topic.weather.daily'  => array(
                'flags'          => AMQP_DURABLE,
                'args'           => array(),
                'routing_key'    => 'example.topic.weather.*.daily',
                'consumer_flags' => AMQP_AUTOACK,
            ),

            'example.topic.weather.all'    => array(
                'flags'          => AMQP_DURABLE,
                'args'           => array(),
                'routing_key'    => 'example.topic.weather.#',
                'consumer_flags' => AMQP_AUTOACK,
            ),
        ),

        // TODO(pineain)
    )
);
