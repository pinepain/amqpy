<?php

namespace AMQPy;

use AMQPy\Client\Delivery;
use Exception;

class Listener extends AbstractListener
{
    public function feed(Delivery $delivery, AbstractConsumer $consumer)
    {
        $consumer->before($delivery, $this);

        $consumer_exception = null;
        $consume_result     = null;
        $consume_payload    = null;

        try {
            $consume_payload = $this->getSerializers()
                                    ->get($delivery->getProperties()->getContentType())
                                    ->parse($delivery->getBody());

            $consume_result = $consumer->consume($consume_payload, $delivery, $this);
        } catch (Exception $e) {
            $consumer_exception = $e;
        }

        if ($consumer_exception) {
            $consumer->failure($consumer_exception, $delivery, $this);
        } else {
            $consumer->after($consume_result, $delivery, $this);
        }

        $consumer->always($consume_result, $consume_payload, $delivery, $this, $consumer_exception);
    }
}