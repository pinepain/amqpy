<?php
/**
 * @author Ben Pinepain <pinepain@gmail.com>
 * @url https://github.com/pinepain/amqpy
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace AMQPy\Hooks;


use \AMQPEnvelope;
use \AMQPy\Queue;


interface IPostConsumer {
    /**
     * Post-consume hook. Invoked on each envelope receive regardless to any error in IConsumer methods
     *
     * @param AMQPEnvelope $envelope An instance representing the message pulled from the queue
     * @param Queue        $queue    Queue from which the message was consumed
     */
    public function postConsume(AMQPEnvelope $envelope, Queue $queue);
}
