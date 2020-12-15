<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * OptionCustomerQuotaCounters
 * 
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com> 
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.3.3.1
 */
 
class OptionCustomerQuotaCounters extends OptionBase
{
    // settings category
    protected $_categoryName = 'system.customer_quota_counters';
    
    // whether to count campaign emails against the sending quota
    public $campaign_emails = 'yes';
    
    // whether to count campaign test emails against the sending quota
    public $campaign_test_emails = 'yes';
    
    // whether to count template test emails against the sending quota
    public $template_test_emails = 'yes';
    
    // whether to count list emails against the sending quota
    public $list_emails = 'yes';
    
    // whether to count transactional emails against the sending quota
    public $transactional_emails = 'yes';
    
    // whether to count giveup emails for campaigns
    public $campaign_giveup_emails = 'no';

    public function rules()
    {
        $rules = array(
            array('campaign_emails, campaign_test_emails, template_test_emails, list_emails, transactional_emails, campaign_giveup_emails', 'required'),
            array('campaign_emails, campaign_test_emails, template_test_emails, list_emails, transactional_emails, campaign_giveup_emails', 'in', 'range' => array_keys($this->getYesNoOptions())),
        );
        
        return CMap::mergeArray($rules, parent::rules());    
    }
    
    public function attributeLabels()
    {
        $labels = array(
            'campaign_emails'               => Yii::t('settings', 'Count campaign emails'),
            'campaign_test_emails'          => Yii::t('settings', 'Count campaign test emails'),
            'template_test_emails'          => Yii::t('settings', 'Count template test emails'),
            'list_emails'                   => Yii::t('settings', 'Count list emails'),
            'transactional_emails'          => Yii::t('settings', 'Count transactional emails'),
            'campaign_giveup_emails'        => Yii::t('settings', 'Count campaign giveup emails'),
        );
        
        return CMap::mergeArray($labels, parent::attributeLabels());    
    }
    
    public function attributePlaceholders()
    {
        $placeholders = array(
            'campaign_emails'               => '',
            'campaign_test_emails'          => '',
            'template_test_emails'          => '',
            'list_emails'                   => '',
            'transactional_emails'          => '',
            'campaign_giveup_emails'        => '',
        );
        
        return CMap::mergeArray($placeholders, parent::attributePlaceholders());
    }
    
    public function attributeHelpTexts()
    {
        $texts = array(
            'campaign_emails'               => Yii::t('settings', 'Whether to count campaign emails against the customer sending quota'),
            'campaign_test_emails'          => Yii::t('settings', 'Whether to count campaign test emails against the customer sending quota'),
            'template_test_emails'          => Yii::t('settings', 'Whether to count template test emails against the customer sending quota'),
            'list_emails'                   => Yii::t('settings', 'Whether to count list emails against the customer sending quota'),
            'transactional_emails'          => Yii::t('settings', 'Whether to count transactional emails against the customer sending quota'),
            'campaign_giveup_emails'        => Yii::t('settings', 'Whether to count campaign giveup emails against the customer sending quota'),
        );
        
        return CMap::mergeArray($texts, parent::attributeHelpTexts());
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
