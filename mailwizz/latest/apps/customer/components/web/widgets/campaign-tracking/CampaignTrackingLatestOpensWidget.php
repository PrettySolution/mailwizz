<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * CampaignTrackingLatestOpensWidget
 * 
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com> 
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.0
 */
 
class CampaignTrackingLatestOpensWidget extends CWidget 
{
    public $campaign;
    
    public $showDetailLinks = true;
    
    public function run() 
    {
        $campaign = $this->campaign;
        
        if ($campaign->status == Campaign::STATUS_DRAFT) {
            return;
        }

	    // 1.7.9
	    if ($campaign->option->open_tracking != CampaignOption::TEXT_YES) {
		    return;
	    }

	    // 1.7.9 - static counters
	    if ($campaign->option->opens_count >= 0) {
		    return;
	    }
        
        $criteria = new CDbCriteria();
        $criteria->select = 't.id, t.subscriber_id, t.date_added';
        $criteria->with = array(
            'subscriber' => array(
                'select'    => 'subscriber.subscriber_uid, subscriber.email, subscriber.list_id',
                'together'  => true,
                'joinType'  => 'INNER JOIN',
            ),
        );
        $criteria->compare('campaign_id', (int)$campaign->campaign_id);
        $criteria->order = 't.id DESC';
        $criteria->limit = 10;
        
        $models = CampaignTrackOpen::model()->findAll($criteria);
        if (empty($models)) {
            return;
        }
        
        $this->render('latest-opens', compact('campaign', 'models'));
    }
}