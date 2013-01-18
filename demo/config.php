<?php

$config = array(
                 'credentials' => array(
                     'host'     => '192.168.1.6',
                     'port'     => 5672,
                     'vhost'    => 'hcs',
                     'login'    => 'hcs',
                     'password' => 'hcs',
//                     'write_timeout'   => 3,
//                     'read_timeout'   => 0, // 0 is default
//                     'vhost'    => 'pinepain',
//                     'login'    => 'pinepain',
//                     'password' => 'hcs',
                 ),

                 'exchanges'   => array(
// -- begin of logger exchange
                     'example.fanout'        => array(
                         'type'       => AMQP_EX_TYPE_FANOUT, // send messages to all queues
                         'flags'      => AMQP_DURABLE, // as for now we use only persistent exchanges and queues
                         'serializer' => '\\AMQPy\\Serializers\\PhpNative',
                         'prefetch'   => 1, // 3 is default, but we don't allow one consumer to hold more than one message at a time

                         'messages'   => array(
//                             'flags' => AMQP_DURABLE, // if shutdown occurred messages still be in queues
//                             'attributes'          => array(
//                                 'expiration' => 5000, // microseconds, how long messages should be stored before deleted
//                             ),
                         ),

                         'queues'     => array(
                             'example.fanout.default' => array(
                                 'flags'          => AMQP_DURABLE,
                                 'args'           => array(),
                                 'routing_key'    => 'example.fanout.default',
                                 'consumer_flags' => AMQP_AUTOACK,
                             ),
                         ),
                     ),
// -- end of logger exchange

// -- begin of example topic exchange
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
                     ),
// -- end of example topic exchange
                 ),
            )
);
