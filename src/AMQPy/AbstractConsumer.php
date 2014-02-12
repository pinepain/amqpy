<?php

namespace AMQPy;

use AMQPy\Client\Delivery;
use Exception;

abstract class AbstractConsumer
{
    private $active = true;

    public function begin(AbstractListener $listener)
    {
        $listener->setEndless(true);
    }

    public function end(AbstractListener $listener, Exception $exception = null)
    {
        $listener->setEndless(false);
    }

    /**
     * Pre-consume hook. Invoked before each message get consumed.
     */
    public function before(Delivery $delivery, AbstractListener $listener)
    {
    }

    /**
     * Process received data from queued message.
     */
    abstract public function consume($payload, Delivery $delivery, AbstractListener $listener);

    /**
     * Handle any exception during queued message data processing.
     */
    public function failure(Exception $e, Delivery $delivery, AbstractListener $listener)
    {
        $listener->resend($delivery);
    }

    /**
     * Post-consume hook. Invoked after each envelope consumed sucessfully
     */
    public function after($result, Delivery $delivery, AbstractListener $listener)
    {
    }

    /**
     * Post-consume hook. Invoked after failure() or after() method.
     *
     * @param                   $result
     * @param                   $payload
     * @param Delivery          $delivery
     * @param AbstractListener $listener
     * @param Exception         $exception
     *
     * @throws Exception
     */
    public function always($result, $payload, Delivery $delivery, AbstractListener $listener, Exception $exception = null)
    {
        if ($exception) {
            throw $exception;
        }
    }

    public function active()
    {
        return $this->active;
    }

    public function activate()
    {
        return $this->active = true;
    }

    public function stop()
    {
        return $this->active = false;
    }
}
