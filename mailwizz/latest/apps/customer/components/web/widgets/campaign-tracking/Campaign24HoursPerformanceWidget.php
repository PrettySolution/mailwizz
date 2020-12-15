<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * Campaign24HoursPerformanceWidget
 * 
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com> 
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.3.8.9
 */
 
class Campaign24HoursPerformanceWidget extends CWidget 
{
    public $campaign;
    
    public function run() 
    {
        $campaign = $this->campaign;
        
        if ($campaign->status == Campaign::STATUS_DRAFT) {
            return;
        }
        
        if ($campaign->customer->getGroupOption('campaigns.show_24hours_performance_graph', 'yes') != 'yes') {
            return;
        }
        
        $subscriber = new ListSubscriber();
        $dateFormatter = $subscriber->dateTimeFormatter;
        
        $cacheKey = sha1(__METHOD__ . $campaign->campaign_id . date('H'));
        if (($chartData = Yii::app()->cache->get($cacheKey)) === false) {
            $chartData = array();

            $params = array(':cid' => $campaign->campaign_id);
            
            // opens
            $query  = '
              SELECT COUNT(DISTINCT (`subscriber_id`)) as counter, DATE_FORMAT(date_added, \'%Y-%m-%d %H:00:00\') as hr 
                FROM `{{campaign_track_open}}` 
                WHERE date_added >= DATE_SUB(NOW(), INTERVAL 24 HOUR) AND campaign_id = :cid 
                GROUP BY hr ORDER BY hr ASC 
                LIMIT 24
            ';
            
            $rows = Yii::app()->getDb()->createCommand($query)->queryAll(true, $params);
            $data = array();
            
            foreach ($rows as $row) {
                $data[] = array((int)(strtotime($dateFormatter->convertDateTime($row['hr'])) * 1000), (int)$row['counter'], );
            }
            
            $chartData[] = array(
                'label' => '&nbsp;' . Yii::t('campaigns', 'Opens'),
                'data'  => $data,
            );
            
            // clicks
            $query  = '
              SELECT COUNT(DISTINCT(t.subscriber_id)) as counter, DATE_FORMAT(t.date_added, \'%Y-%m-%d %H:00:00\') as hr 
              FROM `{{campaign_track_url}}` t 
              INNER JOIN `{{campaign_url}}` cu ON cu.url_id = t.url_id 
              WHERE t.date_added >= DATE_SUB(NOW(), INTERVAL 24 HOUR) AND cu.campaign_id = :cid GROUP BY hr ORDER BY hr ASC 
              LIMIT 24
            ';

            $rows = Yii::app()->getDb()->createCommand($query)->queryAll(true, $params);
            $data = array();

            foreach ($rows as $row) {
                $data[] = array((int)(strtotime($dateFormatter->convertDateTime($row['hr'])) * 1000), (int)$row['counter']);
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
        Yii::app()->clientScript->registerScriptFile(Yii::app()->apps->getBaseUrl('assets/js/flot/jquery.flot.resize.min.js'));
        Yii::app()->clientScript->registerScriptFile(Yii::app()->apps->getBaseUrl('assets/js/flot/jquery.flot.crosshair.min.js'));
        Yii::app()->clientScript->registerScriptFile(Yii::app()->apps->getBaseUrl('assets/js/flot/jquery.flot.time.min.js'));
        Yii::app()->clientScript->registerScriptFile(Yii::app()->apps->getBaseUrl('assets/js/campaign-24hours-performance.js'));
        
        $this->render('24hours-performance', compact('chartData'));
    }
}