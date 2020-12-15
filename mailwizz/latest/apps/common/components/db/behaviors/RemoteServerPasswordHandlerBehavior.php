<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * RemoteServerPasswordHandlerBehavior
 * 
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com> 
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.3.5.4
 * 
 */
 
class RemoteServerPasswordHandlerBehavior extends CActiveRecordBehavior
{
    /**
     * @var 
     */
    protected $_cipher;

    /**
     * @var 
     */
    protected $_plainTextPassword;

    /**
     * @var string 
     */
    public $passwordField = 'password';

    /**
     * @return Crypt_AES
     */
    protected function getCipher()
    {
        if ($this->_cipher !== null) {
            return $this->_cipher;
        }
        
        if (!MW_COMPOSER_SUPPORT) {
            $classes = array('Base', 'Rijndael', 'AES');
            foreach ($classes as $class) {
                if (!class_exists('Crypt_' . $class, false)) {
                    require_once Yii::getPathOfAlias('common.vendors.PHPSecLib.Crypt.' . $class) . '.php';
                }
            }
            $this->_cipher = new Crypt_AES();
        } else {
            $className     = '\phpseclib\Crypt\AES';
            $this->_cipher = new $className();
        }
        $this->_cipher->iv = null;
        $this->_cipher->setKeyLength(128);
        $this->_cipher->setKey('abcdefghqrstuvwxyz123456ijklmnop');
        return $this->_cipher;
    }

    /**
     * @param CModelEvent $event
     */
    public function beforeSave($event)
    {
        $passwordField = $this->passwordField;
        if (empty($this->owner->$passwordField)) {
            return;
        }
        $this->_plainTextPassword    = $this->owner->$passwordField;
        $this->owner->$passwordField = base64_encode($this->getCipher()->encrypt($this->owner->$passwordField));
    }

    /**
     * @param CEvent $event
     */
    public function afterSave($event)
    {
        $passwordField = $this->passwordField;
        if (empty($this->owner->$passwordField)) {
            return;
        }
        $this->owner->$passwordField = $this->_plainTextPassword;
    }

    /**
     * @param CEvent $event
     */
    public function afterFind($event)
    {
        $passwordField = $this->passwordField;
        if (empty($this->owner->$passwordField)) {
            return;
        }
        
        $password = base64_decode($this->owner->$passwordField, true);
        if (base64_encode($password) !== $this->owner->$passwordField) {
            return;
        }
        
        $this->owner->$passwordField = $this->getCipher()->decrypt($password);
    }
}