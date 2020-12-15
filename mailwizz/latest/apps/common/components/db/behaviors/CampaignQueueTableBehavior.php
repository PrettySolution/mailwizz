<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * CampaignQueueTableBehavior
 * 
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com> 
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.3.7.9
 * 
 */
 
class CampaignQueueTableBehavior extends CActiveRecordBehavior
{
    // cache 
    protected static $_tablesIndex = array();
    
    /**
     * @inheritdoc
     */
    public function afterDelete($event)
    {
        parent::afterDelete($event);
        
        // make sure we remove the table in case it remains there
        if ($this->owner->getIsPendingDelete()) {
            $this->dropTable();
        }
    }
    
    /**
     * @return string
     */
    public function getTableName()
    {
        return '{{campaign_queue_' . (int)$this->owner->campaign_id . '}}';
    }

    /**
     * @return bool
     * @throws CException
     */
    public function tableExists()
    {
        // check from cache
        $tableName = $this->getTableName();
        if (!empty(self::$_tablesIndex[$tableName])) {
            return true;
        }
        
        $rows = Yii::app()->db->createCommand('SHOW TABLES LIKE "'. $tableName .'"')->queryAll();
        
        // make sure we add into cache
        return self::$_tablesIndex[$tableName] = (count($rows) > 0);
    }

    /**
     * @return bool
     * @throws CDbException
     * @throws CException
     */
    public function createTable()
    {
        if ($this->tableExists()) {
            return false;
        }
        
        $db         = Yii::app()->db;
        $owner      = $this->owner;
        $schema     = $db->schema;
        $tableName  = $this->getTableName();
        $campaignId = $owner->campaign_id;
        
        if ($owner->isAutoresponder) {

            $db->createCommand($schema->createTable($tableName, array(
                'subscriber_id' => 'INT(11) NOT NULL UNIQUE',
                'send_at'       => 'DATETIME NOT NULL',
            )))->execute();
            
            $key = $schema->createIndex('subscriber_id_send_at_' . $campaignId, $tableName, array('subscriber_id', 'send_at'));
            $db->createCommand($key)->execute();
            
        } else {

            $db->createCommand($schema->createTable($tableName, array(
                'subscriber_id' => 'INT(11) NOT NULL UNIQUE',
                'failures'      => 'INT(11) NOT NULL DEFAULT 0',
            )))->execute();
            
        }
        
        $fk = $schema->addForeignKey('subscriber_id_fk_' . $campaignId, $tableName, 'subscriber_id', '{{list_subscriber}}', 'subscriber_id', 'CASCADE', 'NO ACTION');
        $db->createCommand($fk)->execute();
        
        // mark as created
        self::$_tablesIndex[$tableName] = true;
        
        return true;
    }

    /**
     * @return bool
     * @throws CDbException
     * @throws CException
     */
    public function dropTable()
    {
        if (!$this->tableExists()) {
            return false;
        }
        
        $db         = Yii::app()->db;
        $owner      = $this->owner;
        $schema     = $db->schema;
        $tableName  = $this->getTableName();
        $campaignId = $owner->campaign_id;
        
        $db->createCommand()->delete($tableName);
        
        if ($owner->isAutoresponder) {
            $db->createCommand($schema->dropIndex('subscriber_id_send_at_' . $campaignId, $tableName))->execute();
        }

        $db->createCommand($schema->dropForeignKey('subscriber_id_fk_' . $campaignId, $tableName))->execute();
        $db->createCommand($schema->dropTable($tableName))->execute();
        
        // remove from cache
        if (array_key_exists($tableName, self::$_tablesIndex)) {
            unset(self::$_tablesIndex[$tableName]);
        }
        
        return true;
    }

    /**
     * @return bool
     * @throws CDbException
     * @throws CException
     */
    public function populateTable()
    {
        if ($this->tableExists()) {
            return false;
        }

        // make sure the table is created
        $this->createTable();
        
        $offset    = 0;
        $limit     = (int)Yii::app()->params['send.campaigns.command.tempQueueTables.copyAtOnce'];
        $count     = 0;
        $max       = 0;
        $subsCache = array();
        
        $db        = Yii::app()->db;
        $owner     = $this->owner;
        $schema    = $db->getSchema();
        $tableName = $this->getTableName();
        $now       = date('Y-m-d H:i:s');

        $criteria = new CDbCriteria();
        $criteria->select = 't.subscriber_id';
        
        if ($owner->option->canSetMaxSendCount) {
            $max = $owner->option->max_send_count;
            if ($owner->option->canSetMaxSendCountRandom) {
                $criteria->order = 'RAND()';
            }
        }
        
        try {
            
            $subscribers = $owner->findSubscribers($offset, $limit, $criteria);

	        // 1.7.4
	        if ($owner->isAutoresponder) {
	            $minTimeHour   = !empty($owner->option->autoresponder_time_min_hour)   ? $owner->option->autoresponder_time_min_hour   : null;
	            $minTimeMinute = !empty($owner->option->autoresponder_time_min_minute) ? $owner->option->autoresponder_time_min_minute : null;

	            if (!empty($minTimeHour) && !empty($minTimeMinute)) {
		            $now = date(sprintf('Y-m-d %s:%s:00', $minTimeHour, $minTimeMinute));
	            }
            }
	        //
            
            while (!empty($subscribers)) {
                
                $insert = array();
                
                foreach ($subscribers as $subscriber) {
                    
                    if (!isset($subsCache[$subscriber->subscriber_id])) {

                        $insertData = array(
                            'subscriber_id' => $subscriber->subscriber_id
                        );

                        if ($owner->isAutoresponder) {
                            $insertData['send_at'] = $now;
                        }

                        $insert[] = $insertData;
                        $subsCache[$subscriber->subscriber_id] = true;
                        $count++;
                        
                    }
                    
                    if ($max > 0 && $count >= $max) {
                        break;
                    }
                }
                
                if (!empty($insert)) {
                    $schema->getCommandBuilder()->createMultipleInsertCommand($tableName, $insert)->execute();
                }
                
                if ($max > 0 && $count >= $max) {
                    break;
                }
                
                $offset      = $offset + $limit;
                $subscribers = $owner->findSubscribers($offset, $limit, $criteria);
            }
            
            unset($subscribers, $subsCache);
        
        } catch (Exception $e) {
            
            Yii::log($e->getMessage(), CLogger::LEVEL_ERROR);
            
            $this->dropTable();
            
        }
        
        return true;
    }

    /**
     * @since 1.4.4
     */
    public function handleSendingGiveups()
    {
        // make sure the table is created
        $this->createTable();

        $db        = Yii::app()->db;
        $owner     = $this->owner;
        $schema    = $db->getSchema();
        $tableName = $this->getTableName();
        $now       = date('Y-m-d H:i:s');

        while (true) {
            $query = $db->createCommand()->select('subscriber_id')->from('{{campaign_delivery_log}}');
            $query->where('campaign_id = :cid AND `status` = :status', array(
                ':cid'    => (int)$owner->campaign_id,
                ':status' => CampaignDeliveryLog::STATUS_GIVEUP,
            ));
            $query->offset(0)->limit(500);
            $rows = $query->queryAll();
            
            if (empty($rows)) {
                break;
            }
            
            $insert        = array();
            $subscriberIds = array();
            
            foreach ($rows as $row) {
                
                $subscriberIds[] = (int)$row['subscriber_id'];
                
                if ($owner->isAutoresponder) {
                    
                    $insert[] = array(
                        'subscriber_id' => $row['subscriber_id'],
                        'send_at'       => $now,
                    );
                
                } else {
                
                    $insert[] = array(
                        'subscriber_id' => $row['subscriber_id'],
                    );
                }
                
            }

            if (!empty($insert)) {
                $schema->getCommandBuilder()->createMultipleInsertCommand($tableName, $insert)->execute();
            }
            
            if (!empty($subscriberIds)) {
                $sql = 'DELETE FROM {{campaign_delivery_log}} WHERE campaign_id = :cid AND subscriber_id IN (' . implode(',', $subscriberIds) . ')';
                $db->createCommand($sql)->execute(array(':cid' => $owner->campaign_id));
            }
        }
    }

    /**
     * @param array $data
     * @param array $params
     * @return int
     * @throws CDbException
     * @throws CException
     */
    public function addSubscriber(array $data = array(), array $params = array())
    {
        // make sure the table is created
        $this->createTable();
        
        return Yii::app()->db->createCommand()->insert($this->getTableName(), $data, $params);
    }

    /**
     * @param $subscriberId
     * @return int
     * @throws CDbException
     * @throws CException
     */
    public function deleteSubscriber($subscriberId)
    {
        // make sure the table is created
        $this->createTable();
        
        return Yii::app()->db->createCommand()->delete($this->getTableName(), 'subscriber_id = :sid', array(
            ':sid' => (int)$subscriberId,
        ));
    }

    /**
     * @return int
     * @throws CDbException
     * @throws CException
     */
    public function countSubscribers()
    {
        // make sure the table is created
        $this->createTable();
        
        $db        = Yii::app()->db;
        $owner     = $this->owner;
        $tableName = $this->getTableName();

        $query = $db->createCommand()->select('count(*) as cnt')->from($tableName);
        
        if ($owner->isAutoresponder) {
            $query->where('send_at <= NOW()');
        }
        
        $row = $query->queryRow();
        
        return (int)$row['cnt'];
    }

    /**
     * @param $offset
     * @param $limit
     * @return array
     * @throws CDbException
     * @throws CException
     */
    public function findSubscribers($offset, $limit)
    {
        // make sure the table is created
        $this->createTable();

        $db        = Yii::app()->db;
        $owner     = $this->owner;
        $tableName = $this->getTableName();

        $query = $db->createCommand()->select('subscriber_id')->from($tableName);

        if ($owner->isAutoresponder) {
            $query->where('send_at <= NOW()');
        }

        $query->offset($offset)->limit($limit);

        $rows        = $query->queryAll();
        $chunks      = array_chunk($rows, 300);
        $subscribers = array();
        
        foreach ($chunks as $chunk) {
            $ids = array();
            foreach ($chunk as $row) {
                $ids[] = $row['subscriber_id'];
            }

            $criteria = new CDbCriteria();
            $criteria->addInCondition('t.subscriber_id', $ids);

            // since 1.5.2
            if ($timewarpCriteria = $this->_getTimewarpCriteria()) {
                $criteria->mergeWith($timewarpCriteria);
            }

            $models = ListSubscriber::model()->findAll($criteria);

            foreach ($models as $model) {
                $subscribers[] = $model;
            }
        }

        return $subscribers;
    }

    /**
     * @return bool
     */
    protected function _isTimewarpEnabled()
    {
        return $this->owner->getIsRegular() && $this->owner->option->getTimewarpEnabled();
    }

    /**
     * @return CDbCriteria|null
     */
    protected function _getTimewarpCriteria()
    {
        $timewarpCriteria = null;
        if ($this->_isTimewarpEnabled()) {
            $timewarpCriteria = CampaignHelper::getTimewarpCriteria($this->owner);
        }
        return $timewarpCriteria;
    }
}