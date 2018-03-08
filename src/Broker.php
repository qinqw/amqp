<?php
/**
 * Amqp Broker 
 *
 * @category AMQP_Broker
 * @package  Qinqw\Amqp
 * @author   Kevin <qinqiwei@hotmail.com>
 * @license  Apache-2.0 https://www.apache.org/licenses/LICENSE-2.0.html
 * @link     https://github.com/qinqw/amqp-broker
 */

namespace Qinqw\Amqp;
/**
 * Amqp协议操作类，可以访问rabbitMQ
 * 需先安装php_amqp扩展 librabbitmq amqp.1.7.1
 *  
 * @category AMQP_Broker
 * @package  Qinqw\Amqp
 * @author   Kevin <qinqiwei@hotmail.com>
 * @date     2017-06-25
 * @license  Apache-2.0 https://www.apache.org/licenses/LICENSE-2.0.html
 * @link     https://github.com/qinqw/amqp-broker
 */
class Broker
{
    //Consumer Producer
    //Receiver Sender
    public $configs = array();
    //交换机名称
    public $exchange_name = 'qinqw.default';
    //队列名称
    public $queue_name = 'qinqw.pub';
    //路由名称
    public $route_key = 'rk_default_qinqw';

    private $_route_key_prefix = "rk_qinqw_";

    /*
     * 持久化，默认true
     */
    public $durable = true;
    /*
     * 自动删除
     * exchange is deleted when all queues have finished using it
     * queue is deleted when last consumer unsubscribes
     * 
     */
    public $autodelete = false;
    /*
     * 镜像
     * 镜像队列，打开后消息会在节点之间复制，有master和slave的概念
     */
    public $mirror = false;
    
    private $_conn = null;
    private $_exchange = null;
    private $_channel = null;
    private $_queue = null;

    /**
     * __construct
     *
     * @param mixed $configs       configs
     * @param mixed $queue_name    queue name
     * @param mixed $exchange_name exchange name
     * @param mixed $route_key     route key
     *
     * @return mixed
     */
    public function __construct($configs = array(), $queue_name = null, $exchange_name = null, $route_key = null)
    {
        $this->_setConfigs($configs);
        if (!(is_null($queue_name)||trim($queue_name)=='')) {
            $this->queue_name = $queue_name;
        }
        if (!(is_null($exchange_name)||trim($exchange_name)=='')) {
            $this->exchange_name = $exchange_name;
        }
        if (!(is_null($route_key)||trim($route_key)=='')) {
            $this->route_key = $route_key;
        } else {
            $this->route_key = $this->_route_key_prefix.$this->queue_name;
        }
    }
    
    /**
     * Config
     *
     * @param array $configs Configs
     * 
     * @return mixed 
     */
    private function _setConfigs($configs)
    {
        if (!is_array($configs)) {
            throw new \Exception('configs is not array');
        }
        if (!($configs['host'] && $configs['port'] && $configs['username'] && $configs['password'])) {
            throw new \Exception('configs is empty');
        }
        if (empty($configs['vhost'])) {
            $configs['vhost'] = '/';
        }
        $configs['login'] = $configs['username'];
        unset($configs['username']);
        $this->configs = $configs;
    }

    /**
     * 设置是否持久化
     * 
     * @param bool $durable 是否持久化 默认为true
     * 
     * @return mixed
     */
    public function setDurable($durable=true)
    {
        $this->durable = $durable;
    }

    /**
     * 设置是否自动删除
     *
     * @param bool $autodelete 是否持久化 默认为true
     * 
     * @return mixed
     */
    public function setAutoDelete($autodelete=true)
    {
        $this->autodelete = $autodelete;
    }
    /**
     * 设置是否镜像
     *
     * @param bool $mirror 是否镜像 默认为true
     * 
     * @return mixed
     */
    public function setMirror($mirror=true)
    {
        $this->mirror = $mirror;
    }

    /**
     * 打开amqp连接
     * 
     * @return mixed
     */
    private function _open()
    {
        if (!$this->_conn) {
            try {
                $this->_conn = new \AMQPConnection($this->configs);
                $this->_conn->connect();
                $this->_initConnection();
            } catch (\AMQPConnectionException $ex) {
                throw new \Exception('cannot connection rabbitmq', 500);
            }
        }
    }

    /**
     * 重置交换机，队列，路由等配置 rabbitmq连接不变
     *
     * @param string $exchange_name exchange name
     * @param string $queue_name    queue name
     * @param string $route_key     route key
     *
     * @return mixed 
     */
    public function reset($exchange_name, $queue_name, $route_key) 
    {
        $this->exchange_name = $exchange_name;
        $this->queue_name = $queue_name;
        $this->route_key = $route_key;
        $this->_initConnection();
    }

    /**
     * 初始化rabbit连接的相关配置
     * 
     * @return mixed 
     */
    private function _initConnection() 
    {
        if (empty($this->exchange_name) || empty($this->queue_name) || empty($this->route_key)) {
            throw new \Exception('rabbitmq exchange_name or queue_name or route_key is empty', 500);
        }
        $this->_channel = new \AMQPChannel($this->_conn);
        $this->_exchange = new \AMQPExchange($this->_channel);
        $this->_exchange->setName($this->exchange_name);

        $this->_exchange->setType(AMQP_EX_TYPE_DIRECT);
        if ($this->durable) {
            $this->_exchange->setFlags(AMQP_DURABLE);
        }
        if ($this->autodelete) {
            $this->_exchange->setFlags(AMQP_AUTODELETE);
        }
        $this->_exchange->declareExchange();

        $this->_queue = new \AMQPQueue($this->_channel);
        $this->_queue->setName($this->queue_name);
        if ($this->durable) {
            $this->_queue->setFlags(AMQP_DURABLE);
        } 
        if ($this->autodelete) {
            $this->_queue->setFlags(AMQP_AUTODELETE);
        }
        if ($this->mirror) {
            $this->_queue->setArgument('x-ha-policy', 'all');
        }
        $this->_queue->declareQueue();
        
        $this->_queue->bind($this->exchange_name, $this->route_key);
    }

    /**
     * Close the connection
     *
     * @return mixed 
     */
    public function close()
    {
        if ($this->_conn) {
            $this->_conn->disconnect();
        }
    }
    
    /**
     * __sleep
     *
     * @return mixed 
     */
    public function __sleep()
    {
        $this->close();
        return array_keys(get_object_vars($this));
    }

    /**
     * __destruct
     *
     * @return mixed 
     */
    public function __destruct() 
    {
        $this->close();
    }
    
    /**
     * 生产者发送消息
     *
     * @param mixed $msg Message
     *
     * @return mixed 
     */
    public function publish($msg) 
    {
        $this->_open();
        if (is_array($msg)||is_object($msg)) {
            $msg = json_encode($msg);
        } else {
            $msg = trim(strval($msg));
        }
        return $this->_exchange->publish($msg, $this->route_key);
    }

    /**
     * 循环消费
     * $fun_name = array($classobj,$function) or function name string
     * $autoack 是否自动应答
     * ‘’‘
     * function processMessage($envelope, $queue) {
     *      $msg = $envelope->getBody(); 
     *       echo $msg."\n"; //处理消息
     *       $queue->ack($envelope->getDeliveryTag());//手动应答
     *  }
     * 
     * @param func $callback Callback
     * @param bool $autoack  Auto ACK
     * 
     * @return mixed
     */
    public function consume($callback, $autoack = true)
    {
        $this->_open();
        if (!$callback || !$this->_queue) {
             return false;
        }
        while (true) {
            if ($autoack) {
                $this->_queue->consume($callback, AMQP_AUTOACK);
            } else {
                $this->_queue->consume($callback);
            } 
        }
    }

    /**
     * 单条消费
     * 
     * @param bool $autoack Auto ACK
     * 
     * @return mixed
     */
    public function get($autoack=true)
    {
        $this->_open();
        if ($autoack==true) {
            $envelope = $this->_queue->get(AMQP_AUTOACK);
            //$this->_queue->ack($envelope->getDeliveryTag());
        } else {
            $envelope = $this->_queue->get(AMQP_NOPARAM);
        }
        return $envelope;
    }

    /**
     * 手动应答
     *
     * @param mixed $delivery_tag Delivery Tag 
     *
     * @return mixed 
     */
    public function ack($delivery_tag)
    {
        return $this->_queue->ack($delivery_tag);
    }

}
