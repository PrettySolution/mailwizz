<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * PaymentGatewayPaypalExtModel
 *
 * @package MailWizz EMA
 * @subpackage Payment Gateway Paypal
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 */

class PaymentGatewayPaypalExtModel extends FormModel
{

    const STATUS_ENABLED = 'enabled';

    const STATUS_DISABLED = 'disabled';

    const MODE_SANDBOX = 'sandbox';

    const MODE_LIVE = 'live';

    protected $_extensionInstance;

    public $email;

    public $mode = 'sandbox';

    public $status = 'disabled';

    public $sort_order = 1;

    public function rules()
    {
        $rules = array(
            array('email, mode, status, sort_order', 'required'),
            array('email', 'email', 'validateIDN' => true),
            array('status', 'in', 'range' => array_keys($this->getStatusesDropDown())),
            array('mode', 'in', 'range' => array_keys($this->getModes())),
            array('sort_order', 'numerical', 'integerOnly' => true, 'min' => 0, 'max' => 999),
            array('sort_order', 'length', 'min' => 1, 'max' => 3),
        );

        return CMap::mergeArray($rules, parent::rules());
    }

    public function save()
    {
        $extension  = $this->getExtensionInstance();
        $attributes = array('email', 'mode', 'status', 'sort_order');
        foreach ($attributes as $name) {
            $extension->setOption($name, $this->$name);
        }
        return $this;
    }

    public function populate()
    {
        $extension  = $this->getExtensionInstance();
        $attributes = array('email', 'mode', 'status', 'sort_order');
        foreach ($attributes as $name) {
            $this->$name = $extension->getOption($name, $this->$name);
        }
        return $this;
    }

    public function attributeLabels()
    {
        $labels = array(
            'email'       => Yii::t('ext_payment_gateway_paypal', 'Email'),
            'mode'        => Yii::t('ext_payment_gateway_paypal', 'Mode'),
            'status'      => Yii::t('app', 'Status'),
            'sort_order'  => Yii::t('app', 'Sort order'),
        );

        return CMap::mergeArray($labels, parent::attributeLabels());
    }

    public function attributePlaceholders()
    {
        $placeholders = array();
        return CMap::mergeArray($placeholders, parent::attributePlaceholders());
    }

    public function attributeHelpTexts()
    {
        $texts = array(
            'email'       => Yii::t('ext_payment_gateway_paypal', 'Your paypal email address where the payments should go'),
            'mode'        => Yii::t('ext_payment_gateway_paypal', 'Whether the payments are live or run in sandbox'),
            'status'      => Yii::t('ext_payment_gateway_paypal', 'Whether this gateway is enabled and can be used for payments processing'),
            'sort_order'  => Yii::t('ext_payment_gateway_paypal', 'The sort order for this gateway'),
        );

        return CMap::mergeArray($texts, parent::attributeHelpTexts());
    }

    public function getStatusesDropDown()
    {
        return array(
            self::STATUS_DISABLED   => Yii::t('app', 'Disabled'),
            self::STATUS_ENABLED    => Yii::t('app', 'Enabled'),
        );
    }

    public function getSortOrderDropDown()
    {
        $options = array();
        for ($i = 0; $i < 100; ++$i) {
            $options[$i] = $i;
        }
        return $options;
    }

    public function getModes()
    {
        return array(
            self::MODE_SANDBOX => ucfirst(Yii::t('ext_payment_gateway_paypal', self::MODE_SANDBOX)),
            self::MODE_LIVE    => ucfirst(Yii::t('ext_payment_gateway_paypal', self::MODE_LIVE)),
        );
    }

    public function getModeUrl()
    {
        if ($this->mode == self::MODE_LIVE) {
            return 'https://www.paypal.com/cgi-bin/webscr';
        }
        return 'https://www.sandbox.paypal.com/cgi-bin/webscr';
    }

    public function setExtensionInstance($instance)
    {
        $this->_extensionInstance = $instance;
        return $this;
    }

    public function getExtensionInstance()
    {
        if ($this->_extensionInstance !== null) {
            return $this->_extensionInstance;
        }
        return $this->_extensionInstance = Yii::app()->extensionsManager->getExtensionInstance('payment-gateway-paypal');
    }
}
