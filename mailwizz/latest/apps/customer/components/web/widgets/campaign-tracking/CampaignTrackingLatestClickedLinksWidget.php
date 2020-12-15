<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * CampaignTrackingLatestClickedLinksWidget
 * 
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com> 
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.0
 */
 
class CampaignTrackingLatestClickedLinksWidget extends CWidget 
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
	    if ($campaign->option->url_tracking != CampaignOption::TEXT_YES) {
		    return;
	    }

	    // 1.7.9 - static counters
	    if ($campaign->option->clicks_count >= 0) {
		    return;
	    }
        
        $criteria = new CDbCriteria();
        $criteria->select = 't.url_id, t.subscriber_id, t.date_added';
        $criteria->with = array(
            'url' => array(
                'select'    => 'url.url_id, url.destination',
                'together'  => true,
                'joinType'  => 'INNER JOIN',
                'condition' => 'url.campaign_id = :cid',
                'params'    => array(':cid' => $campaign->campaign_id),
            ),
            'subscriber' => array(
                'select'    => 'subscriber.subscriber_uid, subscriber.email, subscriber.list_id',
                'together'  => true,
                'joinType'  => 'INNER JOIN',
            ),
        );
        $criteria->order = 't.id DESC';
        $criteria->limit = 10;
        
        $models = CampaignTrackUrl::model()->findAll($criteria);
        if (empty($models)) {
            return;
        }
        
        $this->render('latest-clicked-links', compact('campaign', 'models'));
    }
}