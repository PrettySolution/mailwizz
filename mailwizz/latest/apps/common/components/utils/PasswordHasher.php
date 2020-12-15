<?php if ( ! defined('MW_PATH')) exit('No direct script access allowed');

/**
 * PasswordHasher
 * 
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com> 
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.0
 */
 
class PasswordHasher extends CApplicationComponent{
    
    public $iterationCount = 13;
    
    public $portableHashes = true;
    
    protected $_passwordHash;
    
    /**
     * PasswordHasher::init()
     * 
     * @return
     */
    public function init()
    {
        Yii::import('common.vendors.Openwall.*'); 
        parent::init();     
    }

    /**
     * PasswordHasher::hash()
     * 
     * @param mixed $password
     * @return string
     */
    public function hash($password)
    {
        return $this->getPasswordHash()->HashPassword($password);
    }
    
    /**
     * PasswordHasher::check()
     * 
     * @param mixed $password
     * @param mixed $hash
     * @return bool
     */
    public function check($password, $hash)
    {
        return $this->getPasswordHash()->CheckPassword($password,$hash);
    }

    /**
     * PasswordHasher::getPasswordHash()
     * 
     * @return PasswordHash
     */
    public function getPasswordHash()
    {
        if(is_object($this->_passwordHash)&&$this->_passwordHash instanceof PasswordHash) {
            return $this->_passwordHash;
        }   
        $this->_passwordHash=new PasswordHash((int)$this->iterationCount, (bool)$this->portableHashes);
        return $this->_passwordHash;
    }

    /**
     * PasswordHasher::__call()
     * 
     * @param mixed $method
     * @param mixed $args
     * @return
     */
    public function __call($method, $args)
    {
        $class=$this->getPasswordHash();
        if(method_exists($class, $method)) {
            return call_user_func_array(array($class,$method),$args);
        }
        parent::__call($method, $args);
    }
    
}