<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * CampaignOverviewCounterBoxesWidget
 * 
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com> 
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.3.7.3
 */
 
class CampaignOverviewCounterBoxesWidget extends CWidget 
{
    public $campaign;
    
    public function run() 
    {
        $campaign = $this->campaign;
        
        if ($campaign->status == Campaign::STATUS_DRAFT) {
            return;
        }
        
        $controller       = $this->controller;
        $canExportStats   = false;
        $opensLink        = 'javascript:;';
        $clicksLink       = 'javascript:;';
        $unsubscribesLink = 'javascript:;';
        $complaintsLink   = 'javascript:;';
        $bouncesLink      = 'javascript:;';
        
        if (isset($this->controller->campaignReportsController)) {
            $canExportStats   = ($campaign->customer->getGroupOption('campaigns.can_export_stats', 'yes') == 'yes');
            $opensLink        = $controller->createUrl($this->controller->campaignReportsController . '/open_unique', array('campaign_uid' => $campaign->campaign_uid));
            $clicksLink       = $controller->createUrl($this->controller->campaignReportsController . '/click', array('campaign_uid' => $campaign->campaign_uid));
            $unsubscribesLink = $controller->createUrl($this->controller->campaignReportsController . '/unsubscribe', array('campaign_uid' => $campaign->campaign_uid));
            $complaintsLink   = $controller->createUrl($this->controller->campaignReportsController . '/complain', array('campaign_uid' => $campaign->campaign_uid));
            $bouncesLink      = $controller->createUrl($this->controller->campaignReportsController . '/bounce', array('campaign_uid' => $campaign->campaign_uid));
        }
        
        $this->render('overview-counter-boxes', compact('campaign', 'canExportStats', 'opensLink', 'clicksLink', 'unsubscribesLink', 'complaintsLink', 'bouncesLink'));
    }
}