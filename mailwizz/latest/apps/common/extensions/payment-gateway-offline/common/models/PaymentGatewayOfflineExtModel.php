<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * PaymentGatewayOfflineExtModel
 * 
 * @package MailWizz EMA
 * @subpackage Payment Gateway Stripe
 * @author Serban George Cristian <cristian.serban@mailwizz.com> 
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 */
 
class PaymentGatewayOfflineExtModel extends FormModel
{
    const STATUS_ENABLED = 'enabled';
    
    const STATUS_DISABLED = 'disabled';

    protected $_extensionInstance;
    
    public $description;
    
    public $status = 'disabled';
    
    public $sort_order = 2;
    
    public function rules()
    {
        $rules = array(
            array('description', 'safe'),
            array('status', 'in', 'range' => array_keys($this->getStatusesDropDown())),
            array('sort_order', 'numerical', 'integerOnly' => true, 'min' => 0, 'max' => 999),
            array('sort_order', 'length', 'min' => 1, 'max' => 3),
        );
        
        return CMap::mergeArray($rules, parent::rules());    
    }
    
    public function attributeLabels()
    {
        $labels = array(
            'description'  => Yii::t('ext_payment_gateway_offline', 'Description'),
            'status'       => Yii::t('app', 'Status'),
            'sort_order'   => Yii::t('app', 'Sort order'),
        );
        
        return CMap::mergeArray($labels, parent::attributeLabels());    
    }
    
    public function attributePlaceholders()
    {
        $placeholders = array(
            'description'  => '',
        );
        
        return CMap::mergeArray($placeholders, parent::attributePlaceholders());
    }
    
    public function attributeHelpTexts()
    {
        $texts = array(
            'description'   => Yii::t('ext_payment_gateway_offline', 'The needed details for customers to see and to use in order to make the offline payment'),
            'status'        => Yii::t('ext_payment_gateway_offline', 'Whether this gateway is enabled and can be used for payments processing'),
            'sort_order'    => Yii::t('ext_payment_gateway_offline', 'The sort order for this gateway'),
        );
        
        return CMap::mergeArray($texts, parent::attributeHelpTexts());
    }
    
    public function save()
    {
        $extension  = $this->getExtensionInstance();
        $attributes = array('description', 'status', 'sort_order');
        foreach ($attributes as $name) {
            $extension->setOption($name, $this->$name);
        }
        return $this;
    }
    
    public function populate() 
    {
        $extension  = $this->getExtensionInstance();
        $attributes = array('description', 'status', 'sort_order');
        foreach ($attributes as $name) {
            $this->$name = $extension->getOption($name, $this->$name);
        }
        return $this;
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
        return $this->_extensionInstance = Yii::app()->extensionsManager->getExtensionInstance('payment-gateway-offline');
    }
}
