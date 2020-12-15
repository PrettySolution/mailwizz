<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * ArchiveCampaignsDeliveryLogsCommand
 * 
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com> 
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.3.4.9
 * 
 * NOTE: THIS IS EXPERIMENTAL AND NOT TESTED ENOUGH!
 */
 
class ArchiveCampaignsDeliveryLogsCommand extends ConsoleCommand 
{
    /**
     * @return int
     */
    public function actionIndex()
    {
        $result = 1;
        
        try {

            Yii::app()->hooks->doAction('console_command_archive_campaigns_delivery_logs_before_process', $this);

            $result = $this->process();

            Yii::app()->hooks->doAction('console_command_archive_campaigns_delivery_logs_after_process', $this);
            
        } catch (Exception $e) {

            $this->stdout(__LINE__ . ': ' .  $e->getMessage());
            Yii::log($e->getMessage(), CLogger::LEVEL_ERROR);
        }
        
        return $result;
    }

    /**
     * @return int
     * @throws CDbException
     * @throws CException
     */
    protected function process()
    {
        $db       = Yii::app()->getDb();
        $txData   = $db->createCommand("SHOW VARIABLES LIKE 'tx_isolation'")->queryAll();
        $isoLevel = null;
        
        foreach ($txData as $row) {
            if ($row['Variable_name'] == 'tx_isolation') {
                $isoLevel = str_replace(array('-', '_'), ' ', $row['Value']);
                break;
            }
        }
        
        if (empty($isoLevel)) {
            return 1;
        }
        
        $sql  = 'SELECT campaign_id FROM {{campaign}} WHERE `status` = :st AND `delivery_logs_archived` = :dla ORDER BY campaign_id ASC';
        $rows = $db->createCommand($sql)->queryAll(true, array(':st' => Campaign::STATUS_SENT, ':dla' => Campaign::TEXT_NO));
        
        if (empty($rows)) {
            return 0;
        }
        
        try {
            $db->createCommand('SET SESSION TRANSACTION ISOLATION LEVEL READ UNCOMMITTED')->execute();
        } catch (Exception $e) {
            return 0;
        }

        foreach ($rows as $row) {
            // make sure the campaign is still there and the same
            $sql  = 'SELECT campaign_id FROM {{campaign}} WHERE campaign_id = :cid AND delivery_logs_archived = :dla';
            $_row = $db->createCommand($sql)->queryRow(true, array(':cid' => $row['campaign_id'], ':dla' => Campaign::TEXT_NO));
            if (empty($_row)) {
                continue;
            }
            
            $transaction = $db->beginTransaction();
            try {
                $sql = '
                    INSERT INTO {{campaign_delivery_log_archive}} (campaign_id, subscriber_id, server_id, message, processed, retries, max_retries, email_message_id, delivery_confirmed, status, date_added)
                    SELECT campaign_id, subscriber_id, server_id, message, processed, retries, max_retries, email_message_id, delivery_confirmed, status, date_added
                    FROM {{campaign_delivery_log}}
                    WHERE campaign_id = :cid
                ';
                $db->createCommand($sql)->execute(array(':cid' => (int)$row['campaign_id']));
                
                $sql = 'UPDATE {{campaign}} SET delivery_logs_archived = :dla WHERE campaign_id = :cid';
                $db->createCommand($sql)->execute(array(':dla' => Campaign::TEXT_YES, ':cid' => (int)$row['campaign_id']));
                
                $sql = 'DELETE FROM {{campaign_delivery_log}} WHERE campaign_id = :cid';
                $db->createCommand($sql)->execute(array(':cid' => (int)$row['campaign_id'])); 
                
                $transaction->commit();      
            } catch (Exception $e) {
                $transaction->rollback();
            }
        }
        
        $db->createCommand(sprintf("SET SESSION TRANSACTION ISOLATION LEVEL %s", $isoLevel))->execute();

        return 0;
    }
}
