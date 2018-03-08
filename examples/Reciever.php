<?php
/**
 * Amqp Broker Demo
 *
 * @category AMQP_Broker
 * @package  Qinqw\Amqp
 * @author   Kevin <qinqiwei@hotmail.com>
 * @license  Apache-2.0 https://www.apache.org/licenses/LICENSE-2.0.html 
 * @link     https://github.com/qinqw/amqp-broker
 */

namespace examples;
require_once 'bootstrap.php';

use Qinqw\Amqp\Broker;

/**
 * Amqp Receiver 
 *
 * @category AMQP_Broker
 * @package  Qinqw\Amqp
 * @author   Kevin <qinqiwei@hotmail.com>
 * @license  Apache-2.0 https://www.apache.org/licenses/LICENSE-2.0.html 
 * @link     https://github.com/qinqw/amqp-broker
 */
class Consumer extends Broker
{
}

$config['host'] = '127.0.0.1';
$config['port'] = 5672;
$config['username'] = 'guest';
$config['password'] = 'guest';

$queue = 'qu_etcp_sr';
$exchange = 'ex_etcp_sr';


$callback = function ($envelope, $queue) {
    $msg = $envelope->getBody(); 
    echo $msg."\n"; //处理消息
    $queue->ack($envelope->getDeliveryTag());//手动应答
};

$reciever = new Consumer($config, $queue, $exchange);
$reciever->consume($callback, false);