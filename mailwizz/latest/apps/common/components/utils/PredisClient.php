<?php

class RedisClient extends CApplicationComponent
{
    /**
     * @var string
     */
    public $hostname = '127.0.0.1';

    /**
     * @var int
     */
    public $port = 6379;

    /**
     * @var int
     */
    public $database = 1;

    /**
     * @var string
     */
    public $password;

    /**
     * @var
     */
    protected $_client;

    /**
     * @inheritdoc
     */
    public function init()
    {
        $clientClass   = '\Predis\Client';
        $this->_client = new $clientClass(array(
            'scheme'             => 'tcp',
            'host'               => $this->hostname,
            'port'               => $this->port,
            'database'           => $this->database,
            'password'           => $this->password,
            'read_write_timeout' => 0,
        ));

        parent::init();
    }

    /**
     * @return \Predis\Client
     */
    public function getClient()
    {
        return $this->_client;
    }
}