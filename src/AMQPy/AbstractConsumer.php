<?php

namespace AMQPy;

use AMQPy\Client\Delivery;
use Exception;

abstract class AbstractConsumer
{
    private $active = true;

    public function begin(Listenter $listener)
    {
        $listener->setEndlessOn();
    }

    public function end(Listenter $listener, Exception $exception = null)
    {
        $listener->setEndlessOff();
    }

    /**
     * Pre-consume hook. Invoked before each message get consumed.
     */
    public function before(Delivery $delivery, Listenter $listener)
    {
    }

    /**
     * Process received data from queued message.
     */
    abstract public function consume($payload, Delivery $delivery, Listenter $listener);

    /**
     * Handle any exception during queued message data processing.
     */
    public function failure(Exception $e, Delivery $delivery, Listenter $listener)
    {
        $listener->resend($delivery);
    }

    /**
     * Post-consume hook. Invoked after each envelope consumed regardless to any error
     */
    public function after(Delivery $delivery, Listenter $listener, Exception $exception = null)
    {
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
