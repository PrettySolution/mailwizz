<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * CampaignTopDomainsOpensClicksGraphWidget
 * 
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com> 
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.3.9.8
 */
 
class CampaignTopDomainsOpensClicksGraphWidget extends CWidget 
{
    public $campaign;
    
    public function run() 
    {
        $campaign = $this->campaign;
        
        if ($campaign->status == Campaign::STATUS_DRAFT) {
            return;
        }
        
        if ($campaign->customer->getGroupOption('campaigns.show_top_domains_opens_clicks_graph', 'yes') != 'yes') {
            return;
        }

        // 1.7.9
	    if ($campaign->option->open_tracking != CampaignOption::TEXT_YES || $campaign->option->url_tracking != CampaignOption::TEXT_YES) {
		    return;
	    }

	    // 1.7.9 - static counters
        if ($campaign->option->opens_count >= 0 || $campaign->option->clicks_count >= 0) {
        	return;
        }
        
        $cacheKey = sha1(__METHOD__ . $campaign->campaign_id . date('H'));
        if (($chartData = Yii::app()->cache->get($cacheKey)) === false) {
            $chartData = array();

            $params = array(':cid' => $campaign->campaign_id);
            
            // opens
            $query  = '
              SELECT SUBSTRING_INDEX(s.email, "@", -1) AS domain, COUNT(*) AS counter 
              FROM `{{campaign_track_open}}` t 
              INNER JOIN `{{list_subscriber}}` s ON s.subscriber_id = t.subscriber_id 
              WHERE t.campaign_id = :cid 
              GROUP BY SUBSTRING_INDEX(s.email, "@", -1) 
              ORDER BY counter DESC LIMIT 5 
            ';
            
            $rows = Yii::app()->getDb()->createCommand($query)->queryAll(true, $params);
            $data = array();
            
            foreach ($rows as $row) {
                $data[] = array($row['domain'], (int)$row['counter']);
            }
            
            $chartData[] = array(
                'label' => '&nbsp;' . Yii::t('campaigns', 'Opens'),
                'data'  => $data,
            );
            
            // clicks
            $query  = '
              SELECT SUBSTRING_INDEX(s.email, "@", -1) AS domain, COUNT(*) AS counter 
              FROM `{{campaign_url}}` t 
              INNER JOIN `{{campaign_track_url}}` ctu ON ctu.url_id = t.url_id 
              INNER JOIN `{{list_subscriber}}` s on s.subscriber_id = ctu.subscriber_id
              WHERE t.campaign_id = :cid 
              GROUP BY SUBSTRING_INDEX(s.email, "@", -1) 
              ORDER BY counter DESC 
              LIMIT 5
            ';

            $rows = Yii::app()->getDb()->createCommand($query)->queryAll(true, $params);
            $data = array();

            foreach ($rows as $row) {
                $data[] = array($row['domain'], (int)$row['counter']);
            }

            $chartData[] = array(
                'label' => '&nbsp;' . Yii::t('campaigns', 'Clicks'),
                'data'  => $data,
            );
            
            Yii::app()->cache->set($cacheKey, $chartData, 3600);
        }
        
        $hasRecords = false;
        foreach ($chartData as $data) {
            if (!empty($data['data'])) {
                $hasRecords = true;
                break;
            }
        }
        
        if (!$hasRecords) {
            return;
        }
        
        Yii::app()->clientScript->registerScriptFile(Yii::app()->apps->getBaseUrl('assets/js/flot/jquery.flot.min.js'));
        Yii::app()->clientScript->registerScriptFile(Yii::app()->apps->getBaseUrl('assets/js/flot/jquery.flot.categories.min.js'));
        Yii::app()->clientScript->registerScriptFile(Yii::app()->apps->getBaseUrl('assets/js/campaign-top-domains-opens-clicks-graph.js'));
        
        $this->render('campaign-top-domains-opens-clicks-graph', compact('chartData'));
    }
}