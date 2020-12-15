<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * CustomerIdentity
 *
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.0
 */

class CustomerIdentity extends BaseUserIdentity
{
	/**
	 * @var bool 
	 */
    public $impersonate = false;

	/**
	 * @return bool
	 * @throws CException
	 */
    public function authenticate()
    {
        $customer = Customer::model()->findByAttributes(array(
            'email' => $this->email,
        ));

        if (empty($customer)) {
            $this->errorCode = Yii::t('customers', 'Invalid login credentials.');
            return !$this->errorCode;
        }
        
        // since 1.3.9.5
        if (!$this->impersonate && in_array($customer->status, array(Customer::STATUS_PENDING_DISABLE, Customer::STATUS_DISABLED))) {
            $status = $customer->status;
            $customer->saveStatus(Customer::STATUS_ACTIVE);
            Yii::app()->hooks->doAction('customer_login_with_disabled_account', new CAttributeCollection(array(
                'customer'      => $customer,
                'identity'      => $this,
                'initialStatus' => $status,
            )));
        }
        
        if ($customer->status != Customer::STATUS_ACTIVE) {
            $this->errorCode = Yii::t('customers', 'Invalid login credentials.');
            return !$this->errorCode;
        }

        if (!$this->impersonate && !Yii::app()->passwordHasher->check($this->password, $customer->password)) {
            $this->errorCode = Yii::t('customers', 'Invalid login credentials.');
            return !$this->errorCode;
        }

        $this->setId($customer->customer_id);
        $this->setAutoLoginToken($customer);

        $this->errorCode = self::ERROR_NONE;
        return !$this->errorCode;
    }

	/**
	 * @param Customer $customer
	 *
	 * @return $this
	 */
    public function setAutoLoginToken(Customer $customer)
    {
        $token = sha1(uniqid(rand(0, time()), true));
        $this->setState('__customer_auto_login_token', $token);

        CustomerAutoLoginToken::model()->deleteAllByAttributes(array(
            'customer_id' => (int)$customer->customer_id,
        ));

        $autologinToken              = new CustomerAutoLoginToken();
        $autologinToken->customer_id = (int)$customer->customer_id;
        $autologinToken->token       = $token;
        $autologinToken->save();

        return $this;
    }

}
