<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * WebCustomer
 * 
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com> 
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.0
 */
 
class WebCustomer extends BaseWebUser
{
    protected $_model;
    
    public function init()
    {
        parent::init();

        if ($this->getState('__customer_impersonate')) {
            Yii::app()->hooks->addFilter('customer_controller_after_render', array($this, '_showImpersonatingNotice'));
        }
        
        // in case the logged in customer has been deleted while logged in.
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
            CustomerAutoLoginToken::model()->deleteAllByAttributes(array(
                'customer_id' => (int)$this->getId(),
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
            
            if (empty($states['__customer_auto_login_token'])) {
                return false;
            }
            
            $autoLoginToken = CustomerAutoLoginToken::model()->findByAttributes(array(
                'customer_id'    => (int)$id,
                'token'            => $states['__customer_auto_login_token'],
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
        return $this->_model = Customer::model()->findByPk((int)$this->getId());
    }
    
    // not the best way i know, but clean enough.
    public function _showImpersonatingNotice($output)
    {
        $content = Yii::t('users', 'You are impersonating the customer {customerName}.', array(
            '{customerName}' => $this->getModel()->getFullName() ? $this->getModel()->getFullName() : $this->getModel()->email,
        ));
        
        $content .= '<hr />';
        
        $content .= Yii::t('users', 'Please click {linkBack} to logout in order to finish impersonating.', array(
            '{linkBack}' => CHtml::link(Yii::t('app', 'here'), array('account/logout')),
        ));
        
        $append = CHtml::tag('div', array('class' => 'impersonate-sticky-info no-print'), $content);
        
        $output = str_replace('</body>', $append . '</body>', $output);
        
        return $output;
    }
}