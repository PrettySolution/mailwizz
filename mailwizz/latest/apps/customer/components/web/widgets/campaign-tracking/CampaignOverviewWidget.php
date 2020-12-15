<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * CampaignOverviewWidget
 * 
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com> 
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.3.7.3
 */
 
class CampaignOverviewWidget extends CWidget 
{
    public $campaign;
    
    public function run() 
    {
        $campaign = $this->campaign;
        
        if ($campaign->status == Campaign::STATUS_DRAFT) {
            return;
        }

        $options        = Yii::app()->options;
        $webVersionUrl  = $options->get('system.urls.frontend_absolute_url');
        $webVersionUrl .= 'campaigns/' . $campaign->campaign_uid;
        $forwardsUrl    = 'javascript:;';
        $abusesUrl      = 'javascript:;';
        $recipientsUrl  = 'javascript:;';
        $shareReports   = null;
        
        if (Yii::app()->apps->isAppName('customer')) {
            $shareReports   = $campaign->shareReports;
            $forwardsUrl    = array('campaign_reports/forward_friend', 'campaign_uid' => $campaign->campaign_uid);
            $abusesUrl      = array('campaign_reports/abuse_reports', 'campaign_uid' => $campaign->campaign_uid);
            $recipientsUrl  = array('campaign_reports/delivery', 'campaign_uid' => $campaign->campaign_uid);
        } elseif (Yii::app()->apps->isAppName('frontend')) {
            $forwardsUrl    = array('campaigns_reports/forward_friend', 'campaign_uid' => $campaign->campaign_uid);
            $abusesUrl      = array('campaigns_reports/abuse_reports', 'campaign_uid' => $campaign->campaign_uid);
            $recipientsUrl  = array('campaigns_reports/delivery', 'campaign_uid' => $campaign->campaign_uid);
        }
        
        $recurringInfo = null;
        if ($recurring = $campaign->isRecurring) {
            Yii::import('common.vendors.JQCron.*');
            $cron = new JQCron($recurring);
            $recurringInfo = $cron->getText(LanguageHelper::getAppLanguageCode());
        }
        
        $this->render('overview', compact('campaign', 'webVersionUrl', 'recurringInfo', 'shareReports', 'forwardsUrl', 'abusesUrl', 'recipientsUrl'));
    }
}