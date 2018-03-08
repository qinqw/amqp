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
/**
 * Amqp Conf 
 *
 * @category AMQP_Broker
 * @package  Qinqw\Amqp
 * @author   Kevin <qinqiwei@hotmail.com>
 * @license  Apache-2.0 https://www.apache.org/licenses/LICENSE-2.0.html 
 * @link     https://github.com/qinqw/amqp-broker
 */
Class Conf
{
    public $config = [];
    private $_host = '127.0.0.1';
    private $_port = 5672;
    private $_username = 'guest';
    private $_password = 'guest';
    private $_vhost = '/';

    /**
     * __construct
     *
     * @param mixed $host 
     * @param mixed $port 
     * @param mixed $username 
     * @param mixed $password 
     * @param mixed $vhost 
     *
     * @return mixed 
     */
    public function __construct($host, $port, $username, $password, $vhost = '/')
    {
        $this->_host = $host;
        $this->_port = $port;
        $this->_username = $username;
        $this->_password = $password;
        $this->_vhost = $vhost;
        $config['host'] = $host;
        $config['port'] = $port;
        $config['username'] = $username;
        $config['password'] = $password;
        $config['vhost'] = $vhost;
        $this->config = $config;
    }

    /**
     * SetConfig
     *
     * @param mixed $config 
     *
     * @return mixed 
     */
    public function setConfig($config)
    {
        $this->_host = $config['host'];
        $this->_port = $config['port'];
        $this->_username = $config['username'];
        $this->_password = $config['password'];
        $this->_vhost =  $config['vhost'];
        $this->config = $config;
    }

    /**
     * Get Config
     *
     * @return mixed 
     */
    public function getConfig()
    {
        return $this->config;
    }

}