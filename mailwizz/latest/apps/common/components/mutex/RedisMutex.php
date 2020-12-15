<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * RedisMutex
 *
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.5.0
 */

class RedisMutex extends BaseMutex
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
     * @var
     */
    protected $_mutexFabric;
    
    /**
     * Initializes mutex component implementation 
     */
    public function init()
    {
        require_once dirname(__FILE__) . '/NinjaMutexMutex.php';
        require_once dirname(__FILE__) . '/NinjaMutexMutexFabric.php';
        require_once dirname(__FILE__) . '/NinjaMutexPredisRedisLock.php';
        
        parent::init();
    }

    /**
     * Registered shutdown function to clear the locks
     */
    public function _onShutdown()
    {
        foreach ($this->_locks as $lock) {
            $this->release($lock);
        }
    }

    /**
     * @param bool $active
     */
    public function setConnectionActive($active = true)
    {
        if ($active) {
            $this->getMutexFabric();
        } elseif ($this->_client !== null) {
            $this->_client->disconnect();
        }
    }

    /**
     * Acquires lock by given name.
     * @param string $name of the lock to be acquired.
     * @param integer $timeout to wait for lock to become released.
     * @return boolean acquiring result.
     */
    protected function acquireLock($name, $timeout = 0)
    {
        return $this->getMutexFabric()->get($name)->acquireLock($timeout);
    }

    /**
     * Releases lock by given name.
     * @param string $name of the lock to be released.
     * @return boolean release result.
     */
    protected function releaseLock($name)
    {
        return $this->getMutexFabric()->get($name)->releaseLock();
    }

    /**
     * @return NinjaMutexMutexFabric
     */
    protected function getMutexFabric()
    {
        if ($this->_mutexFabric !== null) {
            return $this->_mutexFabric;
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

        return $this->_mutexFabric = new NinjaMutexMutexFabric('redis', new NinjaMutexPredisRedisLock($this->_client));
    }
}
