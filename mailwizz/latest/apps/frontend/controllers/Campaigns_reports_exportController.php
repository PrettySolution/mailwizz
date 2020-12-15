<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * Campaigns_reports_export
 *
 * Handles the actions for exporting campaign reports
 *
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.3.7.3
 */

require_once Yii::getPathOfAlias('customer.controllers.Campaign_reports_exportController') . '.php';

class Campaigns_reports_exportController extends Campaign_reports_exportController
{
    /**
     * @inheritdoc
     */
    public function init()
    {
        $campaign_uid = Yii::app()->request->getQuery('campaign_uid');
        $session      = Yii::app()->session;
        if (!isset($session['campaign_reports_access_' . $campaign_uid])) {
            return $this->redirect(array('campaigns_reports/login', 'campaign_uid' => $campaign_uid));
        }

        $campaign = Campaign::model()->findByUid($campaign_uid);
        if (empty($campaign)) {
            unset($session['campaign_reports_access_' . $campaign_uid]);
            return $this->redirect(array('campaigns_reports/login', 'campaign_uid' => $campaign_uid));
        }
        $this->customerId = $campaign->customer_id;
        
        parent::init();
    }
    
}
