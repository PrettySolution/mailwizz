<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * DeleteCampaignDeliveryLogsCommand
 *
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.4.4
 */

class DeleteCampaignDeliveryLogsCommand extends ConsoleCommand
{
    /**
     * Start point
     */
    public function actionIndex()
    {
        $daysBack        = (int)Yii::app()->params['campaign.delivery.logs.delete.days_back'];
        $campaignsAtOnce = (int)Yii::app()->params['campaign.delivery.logs.delete.process_campaigns_at_once'];
        $logsAtOnce      = (int)Yii::app()->params['campaign.delivery.logs.delete.process_logs_at_once'];
   
        while (true) {

            $this->stdout(sprintf('Loading %d campaigns to delete their delivery logs...', $campaignsAtOnce));
            
            $campaigns = $this->getCampaigns($campaignsAtOnce, $daysBack);
            if (empty($campaigns)) {
                $this->stdout('No campaign found for deleting its delivery logs!');
                break;
            }

            
            foreach ($campaigns as $campaign) {
        
                try {
                    
                    $this->stdout(sprintf('Processing campaign with ID %d which finished at %s', $campaign->campaign_id, $campaign->finishedAt));
                    
                    $campaign->getStats()->disableCache();
                    $processedCount         = $campaign->getStats()->getProcessedCount();
                    $deliverySuccessCount   = $campaign->getStats()->getDeliverySuccessCount();
                    $deliveryErrorCount     = $campaign->getStats()->getDeliveryErrorCount();
                    $industryProcessedCount = $campaign->getStats()->getIndustryProcessedCount();
                    $campaign->getStats()->enableCache();
                    
                    $this->stdout(sprintf('The count for campaign with ID %d is %d.', $campaign->campaign_id, $processedCount));

                    $this->stdout(sprintf('Updating the columns for the campaign with ID %d...', $campaign->campaign_id));
                    Yii::app()->db->createCommand()->update('{{campaign_option}}', array(
                        'processed_count'           => $processedCount,
                        'delivery_success_count'    => $deliverySuccessCount,
                        'delivery_error_count'      => $deliveryErrorCount,
                        'industry_processed_count'  => $industryProcessedCount,
                    ), 'campaign_id = :cid', array(
                        ':cid' => (int)$campaign->campaign_id
                    ));
                    
                    $this->stdout(sprintf('Deleting the delivery logs for the campaign with ID %d...', $campaign->campaign_id));
	                $model = $campaign->deliveryLogsArchived ? CampaignDeliveryLogArchive::model() : CampaignDeliveryLog::model();
                    while (true) {
                        $sql  = sprintf('DELETE FROM `%s` WHERE campaign_id = :cid LIMIT %d', $model->tableName(), $logsAtOnce);
                        $rows = Yii::app()->db->createCommand($sql)->execute(array(
                            ':cid' => $campaign->campaign_id,
                        ));
                        if (!$rows) {
                            break;
                        }
                    }
                    
                    $this->stdout(sprintf('Processing the campaign with ID %d finished successfully.', $campaign->campaign_id) . PHP_EOL);
                    
                } catch (Exception $e) {
                
                    Yii::log($e->getMessage(), CLogger::LEVEL_ERROR);
                    $this->stdout(sprintf('Processing the campaign with ID %d failed with %s.', $campaign->campaign_id, $e->getMessage()) . PHP_EOL);
                
                }
                
            }
            
        }

        $this->stdout('Done!');
    }

    /**
     * @param int $limit
     * @param int $daysBack
     * @return Campaign[]
     */
    protected function getCampaigns($limit = 100, $daysBack = 3)
    {
        $criteria = new CDbCriteria();
        $criteria->compare('t.status', Campaign::STATUS_SENT);
        $criteria->addCondition(sprintf('t.finished_at IS NOT NULL AND t.finished_at != "0000-00-00 00:00:00" AND DATE(t.finished_at) < DATE_SUB(NOW(), INTERVAL %d DAY)', (int)$daysBack));
        $criteria->with['option'] = array(
            'joinType'  => 'INNER JOIN',
            'together'  => true,
            'select'    => false,
            'condition' => 'option.processed_count = -1', 
        );
        $criteria->limit = $limit;
        
        return Campaign::model()->findAll($criteria);
    }
}