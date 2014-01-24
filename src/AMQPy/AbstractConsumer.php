<?php

namespace AMQPy;

use AMQPy\Client\Delivery;
use Exception;

abstract class AbstractConsumer
{
    private $active = true;

    public function begin(AbstractListenter $listener)
    {
        $listener->setEndless(true);
    }

    public function end(AbstractListenter $listener, Exception $exception = null)
    {
        $listener->setEndless(false);
    }

    /**
     * Pre-consume hook. Invoked before each message get consumed.
     */
    public function before(Delivery $delivery, AbstractListenter $listener)
    {
    }

    /**
     * Process received data from queued message.
     */
    abstract public function consume($payload, Delivery $delivery, AbstractListenter $listener);

    /**
     * Handle any exception during queued message data processing.
     */
    public function failure(Exception $e, Delivery $delivery, AbstractListenter $listener)
    {
        $listener->resend($delivery);
    }

    /**
     * Post-consume hook. Invoked after each envelope consumed sucessfully
     */
    public function after($result, Delivery $delivery, AbstractListenter $listener)
    {
    }

    /**
     * Post-consume hook. Invoked after failure() or after() method.
     *
     * @param                   $result
     * @param                   $payload
     * @param Delivery          $delivery
     * @param AbstractListenter $listener
     * @param Exception         $exception
     */
    public function always($result, $payload, Delivery $delivery, AbstractListenter $listener, Exception $exception = null)
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
