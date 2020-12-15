<?php
/**
 * RedisCache
 *
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.5.0
 */

class RedisCache extends CCache
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
     * @var \Doctrine\Common\Cache\PredisCache
     */
    protected $_cache;

    /**
     * @var \Predis\Client
     */
    protected $_client;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        
        // disable the serializer
        $this->serializer = false;
    }

    /**
     * @return \Doctrine\Common\Cache\PredisCache
     */
    public function getCache()
    {
        if ($this->_cache !== null) {
            return $this->_cache;
        }

        $clientClass   = '\Predis\Client';
        $this->_client = new $clientClass(array(
            'scheme'             => 'tcp',
            'host'               => $this->hostname,
            'port'               => $this->port,
            'database'           => $this->database,
            'password'           => $this->password,
            'read_write_timeout' => 0,
        ));

        $className = '\Doctrine\Common\Cache\PredisCache';
        return $this->_cache = new $className($this->_client);
    }

    /**
     * @param bool $active
     */
    public function setConnectionActive($active = true)
    {
        if ($active) {
            $this->getCache();
        } elseif ($this->_client !== null) {
            $this->_client->disconnect();
        }
    }

    /**
     * @inheritdoc
     */
    protected function getValue($key)
    {
        return $this->getCache()->fetch($key);
    }
    
    /**
     * @inheritdoc
     */
    protected function setValue($key, $value, $expire = 0)
    {
        return $this->getCache()->save($key, $value, $expire);
    }

    /**
     * @inheritdoc
     */
    protected function addValue($key, $value, $expire = 0)
    {
        if ($this->getCache()->contains($key)) {
            return false; 
        }
        return $this->getCache()->save($key, $value, $expire);
    }

    /**
     * @inheritdoc
     */
    protected function deleteValue($key)
    {
        return $this->getCache()->delete($key);
    }

    /**
     * @inheritdoc
     */
    protected function flushValues()
    {
        return $this->getCache()->flushAll();
    }
}