<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * OptionCustomerServers
 * 
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com> 
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.3.3.1
 */
 
class OptionCustomerServers extends OptionBase
{
    // settings category
    protected $_categoryName = 'system.customer_servers';
    
    // to hold the server types
    protected $_serverTypesList;
    
    // whether the customer is forced to have bounce servers
    public $must_add_bounce_server = 'yes';
    
    // the maximum number of allowed delivery servers for a customer
    public $max_delivery_servers = 0;
    
    // the maximum number of allowed bounce servers for a customer
    public $max_bounce_servers = 0;
    
    // maximum number of fbl servers
    public $max_fbl_servers = 0;

    // maximum number of email box monitors
    public $max_email_box_monitors = 0;
    
    // whether customer can select delivery servers when create a campaign
    public $can_select_delivery_servers_for_campaign = 'no';
    
    // whether customers can use system servers for sending
    public $can_send_from_system_servers = 'yes';
    
    // custom headers
    public $custom_headers;
    
    // allowed server types
    public $allowed_server_types = array();
    
    public function rules()
    {
        $rules = array(
            array('max_delivery_servers, max_bounce_servers, max_fbl_servers, max_email_box_monitors, must_add_bounce_server, can_select_delivery_servers_for_campaign, can_send_from_system_servers', 'required'),
            array('must_add_bounce_server, can_select_delivery_servers_for_campaign, can_send_from_system_servers', 'in', 'range' => array_keys($this->getYesNoOptions())),
            array('max_delivery_servers, max_bounce_servers, max_fbl_servers, max_email_box_monitors', 'numerical', 'integerOnly' => true, 'min' => -1, 'max' => 100),
            array('allowed_server_types, custom_headers', 'safe'),
        );
        
        return CMap::mergeArray($rules, parent::rules());    
    }
    
    public function attributeLabels()
    {
        $labels = array(
            'must_add_bounce_server'                    => Yii::t('settings', 'Must add bounce server'),
            'max_delivery_servers'                      => Yii::t('settings', 'Max. delivery servers'),
            'max_bounce_servers'                        => Yii::t('settings', 'Max. bounce servers'),
            'max_fbl_servers'                           => Yii::t('settings', 'Max. feedback loop servers'),
            'max_email_box_monitors'                    => Yii::t('settings', 'Max. email box monitors'),
            'can_select_delivery_servers_for_campaign'  => Yii::t('settings', 'Can select delivery servers for campaigns'),
            'can_send_from_system_servers'              => Yii::t('settings', 'Can send from system servers'),
            'allowed_server_types'                      => Yii::t('settings', 'Allowed server types'),
            'custom_headers'                            => Yii::t('settings', 'Custom headers'),
        );
        
        return CMap::mergeArray($labels, parent::attributeLabels());    
    }
    
    public function attributePlaceholders()
    {
        $placeholders = array(
            'must_add_bounce_server'                    => '',
            'max_delivery_servers'                      => '',
            'max_bounce_servers'                        => '',
            'max_fbl_servers'                           => '',
            'max_email_box_monitors'                    => '',
            'can_select_delivery_servers_for_campaign'  => '',
            'can_send_from_system_servers'              => '',
            'allowed_server_types'                      => '',
            'custom_headers'                            => 'X-Header-A: 1111' . PHP_EOL . 'X-Header-B: 2222' . PHP_EOL . 'X-Header-B: 3333'
        );
        
        return CMap::mergeArray($placeholders, parent::attributePlaceholders());
    }
    
    public function attributeHelpTexts()
    {
        $texts = array(
            'must_add_bounce_server'                    => Yii::t('settings', 'Whether customers are forced to add a bounce server for each delivery server'),
            'max_delivery_servers'                      => Yii::t('settings', 'How many delivery servers a customer is allowed to add, set to -1 for unlimited'),
            'max_bounce_servers'                        => Yii::t('settings', 'How many bounce servers a customer is allowed to add, set to -1 for unlimited'),
            'max_fbl_servers'                           => Yii::t('settings', 'How many feedback loop servers a customer is allowed to add, set to -1 for unlimited'),
            'max_email_box_monitors'                    => Yii::t('settings', 'How many email box monitors a customer is allowed to add, set to -1 for unlimited'),
            'can_select_delivery_servers_for_campaign'  => Yii::t('settings', 'Whether customers are able to select what delivery servers to use in campaigns'),
            'can_send_from_system_servers'              => Yii::t('settings', 'Whether customers can use the system servers for sending emails. If they have their own servers, this is used as a fallback mechanism when their servers are unavailable'),
            'allowed_server_types'                      => Yii::t('settings', 'What types of servers are customers allowed to add. This is matched against core server types'),
            'custom_headers'                            => Yii::t('settings', 'Custom headers that apply to all delivery servers. Please make sure you write one HeaderName:HeaderValue per line. Please note that all headers must start with the X- prefix'),
        );
        
        return CMap::mergeArray($texts, parent::attributeHelpTexts());
    }

    protected function beforeValidate()
    {
        if (!is_array($this->allowed_server_types)) {
            $this->allowed_server_types = array();
        }
        
        $allServerTypes = $this->getServerTypesList();
        $allowedServerTypes = array();
        
        foreach ($this->allowed_server_types as $type => $answer) {
            if ($answer == 'yes' && isset($allServerTypes[$type])) {
                $allowedServerTypes[] = $type;
            }
        }

        $this->allowed_server_types = $allowedServerTypes;
        $this->custom_headers = DeliveryServerHelper::getOptionCustomerCustomHeadersStringFromString($this->custom_headers);
        
        return parent::beforeValidate();
    }
    
    public function getServerTypesList()
    {
        if ($this->_serverTypesList !== null) {
            return $this->_serverTypesList;
        }
        return $this->_serverTypesList = DeliveryServer::getTypesList();
    }
    
    public function getQuotaPercentageList()
    {
        static $list = array();
        if (!empty($list)) {
            return $list;
        }
        
        for ($i = 1; $i <= 95; ++$i) {
            if ($i % 5 == 0) {
                $list[$i] = $i;
            }
        }
        
        return $list;
    }
}
