<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * OptionCommon
 * 
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com> 
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.0
 */
 
class OptionCommon extends OptionBase
{
    // settings category
    protected $_categoryName = 'system.common';
    
    public $site_name;
    
    public $site_tagline;
    
    public $site_description;
    
    public $site_keywords;
    
    public $clean_urls = 0;
    
    public $site_status = 'online';
    
    public $site_offline_message = 'Application currently offline. Try again later!';
    
    public $api_status = 'online';
    
    public $backend_page_size = 10;
    
    public $customer_page_size = 10;
    
    public $check_version_update = 'yes';
    
    public $default_mailer;
    
    public $company_info;
    
    public $show_backend_timeinfo = 'no';
    
    public $show_customer_timeinfo = 'no';
    
    public $support_url = MW_SUPPORT_KB_URL;
    
    public $ga_tracking_code_id;
    
    public $use_tidy = 'yes';

    public $auto_update = 'no';
    
    public $frontend_homepage = 'yes';
    
    public function rules()
    {
        $rules = array(
            array('site_name, site_tagline, clean_urls, site_status, site_offline_message, api_status, backend_page_size, customer_page_size, default_mailer, show_backend_timeinfo, show_customer_timeinfo, use_tidy, auto_update, frontend_homepage', 'required'),
            array('site_description, site_keywords', 'safe'),
            array('clean_urls', 'in', 'range' => array(0, 1)),
            array('site_status, api_status', 'in', 'range' => array('online', 'offline')),
            array('site_offline_message, ga_tracking_code_id', 'length', 'max' => 250),
            array('backend_page_size, customer_page_size', 'in', 'range' => array_keys($this->paginationOptions->getOptionsList())),
            array('check_version_update, show_backend_timeinfo, show_customer_timeinfo, use_tidy, frontend_homepage', 'in', 'range' => array_keys($this->getYesNoOptions())),
            array('default_mailer', 'in', 'range' => array_keys($this->getSystemMailers())),
            array('company_info', 'safe'),
            array('support_url', 'url'),
        );
        
        return CMap::mergeArray($rules, parent::rules());    
    }
    
    public function attributeLabels()
    {
        $labels = array(
            'site_name'             => Yii::t('settings', 'Site name'),
            'site_tagline'          => Yii::t('settings', 'Site tagline'),
            'site_description'      => Yii::t('settings', 'Site description'),
            'site_keywords'         => Yii::t('settings', 'Site keywords'),
            'clean_urls'            => Yii::t('settings', 'Clean urls'),
            'site_status'           => Yii::t('settings', 'Site status'),
            'site_offline_message'  => Yii::t('settings', 'Site offline message'),
            'api_status'            => Yii::t('settings', 'Api status'),
            
            'backend_page_size'     => Yii::t('settings', 'Backend page size'),
            'customer_page_size'    => Yii::t('settings', 'Customer page size'),
            'check_version_update'  => Yii::t('settings', 'Check for new version automatically'),
            'default_mailer'        => Yii::t('settings', 'Default system mailer'),
            'company_info'          => Yii::t('settings', 'Company info'),
            
            'show_backend_timeinfo' => Yii::t('settings', 'Show backend time info'),
            'show_customer_timeinfo'=> Yii::t('settings', 'Show customer time info'),
            
            'support_url'           => Yii::t('settings', 'Support url'),
            'ga_tracking_code_id'   => Yii::t('settings', 'GA tracking code id'),
            
            'use_tidy'              => Yii::t('settings', 'Use Tidy'),
            'auto_update'           => Yii::t('settings', 'Application auto update'),
            'frontend_homepage'     => Yii::t('settings', 'Enable frontend homepage'),
        );
        
        return CMap::mergeArray($labels, parent::attributeLabels());    
    }
    
    public function attributePlaceholders()
    {
        $placeholders = array(
            'site_name'           => Yii::t('app', 'MailWizz'),
            'site_tagline'        => Yii::t('app', 'Email marketing application'),
            'site_description'    => '',
            'site_keywords'       => '',
            'company_info'        => '',
            'support_url'         => 'http://',
            'ga_tracking_code_id' => 'UA-0000000-0',
        );
        
        return CMap::mergeArray($placeholders, parent::attributePlaceholders());
    }
    
    public function attributeHelpTexts()
    {
        $texts = array(
            'site_name'             => Yii::t('settings', 'Your site name, will be used in places like logo, emails, etc.'),
            'site_tagline'          => Yii::t('settings', 'A very short description of your website.'),
            'site_description'      => Yii::t('settings', 'Description'),
            'site_keywords'         => Yii::t('settings', 'Keywords'),
            'clean_urls'            => Yii::t('settings', 'Enabling this will remove the index.php part of your urls.'),
            'site_status'           => Yii::t('settings', 'Whether the website is online or offline.'),
            'site_offline_message'  => Yii::t('settings', 'If the website is offline, show this message to users.'),
            'api_status'            => Yii::t('settings', 'Whether the website api is online or offline.'),
            
            'backend_page_size'     => Yii::t('settings', 'How many items to show per page in backend area'),
            'customer_page_size'    => Yii::t('settings', 'How many items to show per page in customer area'),
            'check_version_update'  => Yii::t('settings', 'Whether to check for new application version automatically'),
            'default_mailer'        => Yii::t('settings', 'Choose the default system mailer, please do your research if needed'),
            'company_info'          => Yii::t('settings', 'Your company info, used in places like payment page'),
            
            'show_backend_timeinfo' => Yii::t('settings', 'Whether to show the time info in the backend area'),
            'show_customer_timeinfo'=> Yii::t('settings', 'Whether to show the time info in the customer area'),
            
            'support_url'           => Yii::t('settings', 'Leave empty to disable the left side menu item for Support forum.'),
            'ga_tracking_code_id'   => Yii::t('settings', 'Make sure you only paste the code id, which looks like UA-0000000-0.'),
            'use_tidy'              => Yii::t('settings', 'Whether to use Tidy for email templates cleanup and formatting'),
            'auto_update'           => Yii::t('settings', 'Whether to let the application auto-update itself'),
            'frontend_homepage'     => Yii::t('settings', 'Whether to show the homepage in frontend instead of redirecting to customer area'),
        );
        
        return CMap::mergeArray($texts, parent::attributeHelpTexts());
    }

    public function getSiteStatusOptions()
    {
        return array(
            'online'    => Yii::t('settings', 'Online'),
            'offline'   => Yii::t('settings', 'Offline'),
        );
    }
    
    public function getSystemMailers()
    {
        static $list;
        if ($list !== null) {
            return $list;
        }
        $list = array();
        $mailers = Yii::app()->mailer->getAllInstances();
        foreach ($mailers as $instance) {
            $list[$instance->name] = $instance->name . ' - ' .$instance->description;
        }
        return $list;
    }
}
