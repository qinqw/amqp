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
use examples\Conf;

/**
 * Amqp Sender 
 *
 * @category AMQP_Broker
 * @package  Qinqw\Amqp
 * @author   Kevin <qinqiwei@hotmail.com>
 * @license  Apache-2.0 https://www.apache.org/licenses/LICENSE-2.0.html 
 * @link     https://github.com/qinqw/amqp-broker
 */
class Producer extends Broker
{
    // /**
    //  * __construct
    //  *
    //  * @param array $config 
    //  * @param mixed $queue 
    //  * @param mixed $exchange 
    //  *
    //  * @return mixed 
    //  */
    // public function __construct($config, $queue='qu_etcp_sr', $exchange='ex_etcp_sr')
    // {
    //     parent::__construct($config, $queue, $exchange);
    // }

    /**
     * Send message
     *
     * @param mixed $message  
     * @param mixed $queue 
     *
     * @return mixed 
     */
    public function send($message, $queue=null)
    {
        $this->publish($message);
    }
}

$message = $argv[1];

$config['host'] = '127.0.0.1';
$config['port'] = 5672;
$config['username'] = 'guest';
$config['password'] = 'guest';

$queue = 'qu_etcp_sr';
$exchange = 'ex_etcp_sr';

$sender = new Producer($config, $queue, $exchange);

$sender->send($message);