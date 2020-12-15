<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * WebUser
 * 
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com> 
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.0
 */
 
class WebUser extends BaseWebUser
{
    protected $_model;
    
    public function init()
    {
        parent::init();
        
        // in case the user was logged in then deleted.
        if ($this->getId() > 0 && !$this->getModel()) {
            $this->setId(null);
        }
    }
    
    /**
     * This method is invoked when {@link logout} is called.
     * If the allow auto login feature is enabled, it will destroy the auto login token.
     * 
     * @return bool
     */
    protected function beforeLogout()
    {
        if($this->allowAutoLogin) {
            UserAutoLoginToken::model()->deleteAllByAttributes(array(
                'user_id' => (int)$this->getId(),
            ));  
        }
        return true;
    }
    
    /**
     * Method called right before the user needs to be logged in.
     * If this method returns false, the user will not be logged in.
     * 
     * @param int $id the user id
     * @param array $states the user states
     * @param bool $fromCookie whether the login comes from a cookie
     */
    protected function beforeLogin($id, $states, $fromCookie)
    {
        if (!$fromCookie) {
            return true;
        }
        
        if ($this->allowAutoLogin) {
            
            if (empty($states['__user_auto_login_token'])) {
                return false;
            }
            
            $autoLoginToken = UserAutoLoginToken::model()->findByAttributes(array(
                'user_id'   => (int)$id,
                'token'     => $states['__user_auto_login_token'],
            ));
            
            if(empty($autoLoginToken)) {
                return false;
            }    
        }

        return true;
    }
    
    /**
     * Called after the user logs in.
     * 
     * @param bool $fromCookie whether the login comes from a cookie 
     */
    protected function afterLogin($fromCookie)
    {
        if ($this->getModel()) {
            $this->getModel()->updateLastLogin();
        }
    }
    
    public function getModel()
    {
        if ($this->_model !== null) {
            return $this->_model;
        }
        return $this->_model = User::model()->findByPk((int)$this->getId());
    }
}