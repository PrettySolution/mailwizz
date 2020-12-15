<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * EmailBoxMonitor
 * 
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com> 
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.4.5
 */
 
/**
 * This is the model class for table "email_box_monitor".
 *
 * The followings are the available columns in table 'email_box_monitor':
 * @property integer $server_id
 * @property integer $customer_id
 * @property string $hostname
 * @property string $username
 * @property string $password
 * @property string $email
 * @property string $service
 * @property integer $port
 * @property string $protocol
 * @property string $validate_ssl
 * @property string $locked
 * @property string $meta_data
 * @property string $status
 * @property string $date_added
 * @property string $last_updated
 * 
 * The followings are the available model relations:
 * @property Customer $customer
 */
class EmailBoxMonitor extends BounceServer
{
    /**
     * Conditions list
     */
    const CONDITION_CONTAINS = 'contains';

    /**
     * Actions list
     */
    const ACTION_UNSUBSCRIBE           = 'unsubscribe';
    const ACTION_BLACKLIST             = 'blacklist';
    const ACTION_UNCONFIRM             = 'unconfirm';
    const ACTION_DELETE                = 'delete';
    const ACTION_MOVE_TO_LIST          = 'move to list';
    const ACTION_COPY_TO_LIST          = 'copy to list';
	const ACTION_STOP_CAMPAIGN_GROUP   = 'stop campaign group';
    
    /**
     * Identify list
     */
    const IDENTIFY_SUBSCRIBERS_BY_EMAIL     = 'by email address';
    const IDENTIFY_SUBSCRIBERS_BY_UID       = 'by subscriber uid';
    const IDENTIFY_SUBSCRIBERS_UID_OR_EMAIL = 'by subscriber uid or email address';
    
    /**
     * @inheritdoc
     */
    public function tableName()
    {
        return '{{email_box_monitor}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        $rules = array(
            array('hostname, username, password, port, service, protocol, validate_ssl', 'required'),

            array('hostname, username, password', 'length', 'min' => 3, 'max'=>150),
            array('email', 'email', 'validateIDN' => true),
            array('port', 'numerical', 'integerOnly'=>true),
            array('port', 'length', 'min'=> 2, 'max' => 5),
            array('protocol', 'in', 'range' => array_keys($this->getProtocolsArray())),
            array('customer_id', 'exist', 'className' => 'Customer', 'attributeName' => 'customer_id', 'allowEmpty' => true),
            array('locked', 'in', 'range' => array_keys($this->getYesNoOptions())),
            
            array('disable_authenticator, search_charset', 'length', 'max' => 50),
            array('delete_all_messages', 'in', 'range' => array_keys($this->getYesNoOptions())),
            
            array('conditions, identifySubscribersBy', 'required'),
            array('conditions', '_validateConditions'),
            array('identifySubscribersBy', 'in', 'range' => array_keys($this->getIdentifySubscribersByList())),
            
            array('hostname, username, service, port, protocol, status, customer_id', 'safe', 'on' => 'search'),
        );

        return CMap::mergeArray($rules, parent::rules());
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        $labels = array(
            'identifySubscribersBy' => Yii::t('servers', 'How to identify subscribers'),
        );

        return CMap::mergeArray($labels, parent::attributeLabels());
    }

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeHelpTexts()
    {
        $labels = array(
            'identifySubscribersBy' => Yii::t('servers', 'Subscriber UID means we will only identify subscribers who reply to a certain email campaign thus subscribers in a particular list. Email address means we will match subscribers with the given email address in all lists.'),
        );

        return CMap::mergeArray($labels, parent::attributeHelpTexts());
    }
    
    /**
     * Returns the static model of the specified AR class.
     * Please note that you should have this exact method in all your CActiveRecord descendants!
     * @param string $className active record class name.
     * @return EmailBoxMonitor the static model class
     */
    public static function model($className=__CLASS__)
    {
        return parent::model($className);
    }
    
    /**
     * @param array $params
     * @return bool
     */
    protected function _processRemoteContents(array $params = array())
    {
        $mutexKey = sha1('imappop3box' . serialize($this->getAttributes(array('hostname', 'username', 'password'))) . date('Ymd'));
        if (!Yii::app()->mutex->acquire($mutexKey, 5)) {
            return false;
        }
        
        try {

            if (!$this->getIsActive()) {
                throw new Exception('The server is inactive!', 1);
            }

            $conditions = $this->getConditions();
            if (empty($conditions)) {
                throw new Exception('There are no conditions defined!', 1);
            }

            // 1.4.4
            $logger = !empty($params['logger']) && is_callable($params['logger']) ? $params['logger'] : null;

            // put proper status
            $this->saveStatus(self::STATUS_CRON_RUNNING);
            
            // make sure the BounceHandler class is loaded
            Yii::import('common.vendors.BounceHandler.*');

            $options         = Yii::app()->options;
            $processLimit    = (int)$options->get('system.cron.process_email_box_monitors.emails_at_once', 500);
            $processDaysBack = (int)$options->get('system.cron.process_email_box_monitors.days_back', 3);

            // close the db connection because it will time out!
            Yii::app()->getDb()->setActive(false);

	        $connectionStringSearchReplaceParams = array();
	        if (!empty($params['mailbox'])) {
		        $connectionStringSearchReplaceParams['[MAILBOX]'] = $params['mailbox'];
	        }
	        $connectionString = $this->getConnectionString($connectionStringSearchReplaceParams);
	        
            $bounceHandler = new BounceHandler($connectionString, $this->username, $this->password, array(
                'deleteMessages'    => true,
                'deleteAllMessages' => $this->getDeleteAllMessages(),
                'processLimit'      => $processLimit,
                'searchCharset'     => $this->getSearchCharset(),
                'imapOpenParams'    => $this->getImapOpenParams(),
                'processDaysBack'   => $processDaysBack,
                'logger'            => $logger,
            ));

            // 1.4.4
            if ($logger) {
	            $mailbox = isset($connectionStringSearchReplaceParams['[MAILBOX]']) ? $connectionStringSearchReplaceParams['[MAILBOX]'] : $this->mailBox;
	            call_user_func($logger, sprintf('Searching for results in the "%s" mailbox...', $mailbox));
            }

            // fetch the results
            $results = $bounceHandler->getSearchResults();

            // 1.4.4
            if ($logger) {
                call_user_func($logger, sprintf('Found %d results.', count($results)));
            }

            // re-open the db connection
            Yii::app()->getDb()->setActive(true);

            // done
            if (empty($results)) {
                $this->saveStatus(self::STATUS_ACTIVE);
                throw new Exception('No results!', 1);
            }

            foreach ($results as $result) {

                if ($logger) {
                    call_user_func($logger, sprintf('Processing message id: %s!', $result));
                }

                // load the full message
                $message = (string)imap_fetchbody($bounceHandler->getConnection(), $result, "");
                if (empty($message)) {
                    if ($logger) {
                        call_user_func($logger, sprintf('Cannot fetch content for message id: %s!', $result));
                    }
                    if ($this->getDeleteAllMessages()) {
                        imap_delete($bounceHandler->getConnection(), "$result:$result");
                    }
                    continue;
                }
                
                $condition = array();
                foreach ($conditions as $_condition) {
                    if ($_condition['condition'] == self::CONDITION_CONTAINS && (empty($_condition['value']) || stripos($message, (string)$_condition['value']) !== false)) {
                        $condition = $_condition;
                        break;
                    }
                }
                
                if (empty($condition)) {
                    if ($logger) {
                        call_user_func($logger, sprintf('Cannot find conditions to apply for message id: %s!', $result));
                    }
                    if ($this->getDeleteAllMessages()) {
                        imap_delete($bounceHandler->getConnection(), "$result:$result");
                    }
                    continue;
                }

                if ($logger) {
                    call_user_func($logger, sprintf('Following action will be taken against message %s: %s!', $result, $condition['action']));
                }
                
                // get the header info
                $headerInfo = imap_headerinfo($bounceHandler->getConnection(), $result);
                if (empty($headerInfo) || empty($headerInfo->from) || empty($headerInfo->from[0]->mailbox) || empty($headerInfo->from[0]->host)) {
                    if ($logger) {
                        call_user_func($logger, sprintf('Cannot fetch header info for message id: %s!', $result));
                    }
                    if ($this->getDeleteAllMessages()) {
                        imap_delete($bounceHandler->getConnection(), "$result:$result");
                    }
                    continue;
                }
                $fromAddress = $headerInfo->from[0]->mailbox . '@' . $headerInfo->from[0]->host;

                if ($logger) {
                    call_user_func($logger, sprintf('Message %s targets following email address: %s!', $result, $fromAddress));
                }
                
                $subscribers           = array();
                $identifySubscribersBy = $this->getIdentifySubscribersBy();
                $subscriberUidPattern  = '/Subscriber\-Uid:\s?([a-z0-9]{13})/ix';
                $campaignUidPattern    = '/Campaign\-Uid:\s?([a-z0-9]{13})/ix';
                $trackingOpenPattern   = '/\/([a-z0-9]{13})\/track\-opening\/([a-z0-9]{13})/ix';
                
                $subscriber = $campaign = null;
                if ($identifySubscribersBy == self::IDENTIFY_SUBSCRIBERS_BY_UID || $identifySubscribersBy == self::IDENTIFY_SUBSCRIBERS_UID_OR_EMAIL) {
                    
                    $subscriberUid = $campaignUid = '';
                    
                    if (preg_match($subscriberUidPattern, $message, $matches)) {
                        $subscriberUid = $matches[1];
                        if (preg_match($campaignUidPattern, $message, $matches)) {
                            $campaignUid = $matches[1];
                        }
                    } elseif (preg_match($trackingOpenPattern, $message, $matches)) {
                        $subscriberUid = $matches[2];
                        $campaignUid   = $matches[1];
                    }
                    
                    if ($subscriberUid) {
                        $subscriber = ListSubscriber::model()->findByAttributes(array(
                            'status'         => ListSubscriber::STATUS_CONFIRMED,
                            'subscriber_uid' => $subscriberUid,
                        ));
                    }
                    
                    if ($campaignUid) {
                        $campaign = Campaign::model()->findByAttributes(array(
                            'campaign_uid' => $campaignUid,
                        ));
                    }
                }
                
                if ($identifySubscribersBy == self::IDENTIFY_SUBSCRIBERS_BY_UID) {

                    if (!empty($subscriber)) {
                        $subscribers[] = $subscriber;
                    }
                
                } elseif ($identifySubscribersBy == self::IDENTIFY_SUBSCRIBERS_BY_EMAIL) {
                    
                    $criteria = new CDbCriteria();
                    $criteria->compare('t.email', $fromAddress);
                    $criteria->compare('t.status', ListSubscriber::STATUS_CONFIRMED);
                    if (!empty($this->customer_id) && !empty($this->customer)) {
                        $criteria->addInCondition('t.list_id', $this->customer->getAllListsIds());
                    }
                    $subscribers = ListSubscriber::model()->findAll($criteria);
                    
                } elseif ($identifySubscribersBy == self::IDENTIFY_SUBSCRIBERS_UID_OR_EMAIL) {

                    if (!empty($subscriber)) {
                        $subscribers[] = $subscriber;
                    }
                    
                    if (empty($subscribers)) {
                        $criteria = new CDbCriteria();
                        $criteria->compare('t.email', $fromAddress);
                        $criteria->compare('t.status', ListSubscriber::STATUS_CONFIRMED);
                        if (!empty($this->customer_id) && !empty($this->customer)) {
                            $criteria->addInCondition('t.list_id', $this->customer->getAllListsIds());
                        }
                        $subscribers = ListSubscriber::model()->findAll($criteria);
                    }
                    
                }

                if (empty($subscribers)) {
                    if ($logger) {
                        call_user_func($logger, sprintf('No subscriber found for message id: %s!', $result));
                    }
                    if ($this->getDeleteAllMessages()) {
                        imap_delete($bounceHandler->getConnection(), "$result:$result");
                    }
                    continue;
                }

                if ($logger) {
                    call_user_func($logger, sprintf('Found %d email addresses for message %s which we will %s!', count($subscribers), $result, $condition['action']));
                }

                foreach ($subscribers as $subscriber) {
                    
                    if ($condition['action'] == self::ACTION_UNSUBSCRIBE) {
                        
                        $subscriber->saveStatus(ListSubscriber::STATUS_UNSUBSCRIBED);
                        
                        if ($campaign) {
                            $count = CampaignTrackUnsubscribe::model()->countByAttributes(array(
                                'campaign_id'   => $campaign->campaign_id,
                                'subscriber_id' => $subscriber->subscriber_id,
                            ));
                            if (empty($count)) {
                                $trackUnsubscribe                = new CampaignTrackUnsubscribe();
                                $trackUnsubscribe->campaign_id   = $campaign->campaign_id;
                                $trackUnsubscribe->subscriber_id = $subscriber->subscriber_id;
                                $trackUnsubscribe->note          = 'Unsubscribed via Email Box Monitor!';
                                $trackUnsubscribe->save(false);
                            }
                        }
                    
                    } elseif ($condition['action'] == self::ACTION_UNCONFIRM) {
                    
                        $subscriber->saveStatus(ListSubscriber::STATUS_UNCONFIRMED);
                    
                    } elseif ($condition['action'] == self::ACTION_BLACKLIST) {
                    
                        $subscriber->saveStatus(self::ACTION_BLACKLIST);
                    
                    } elseif ($condition['action'] == self::ACTION_DELETE) {
                       
                        $subscriber->delete();
                    
                    } elseif ($condition['action'] == self::ACTION_COPY_TO_LIST && !empty($condition['list_id'])) {
                        
                        $subscriber->copyToList($condition['list_id']);
                    
                    } elseif ($condition['action'] == self::ACTION_MOVE_TO_LIST && !empty($condition['list_id'])) {
                        
                        $subscriber->moveToList($condition['list_id']);
                    
                    } elseif ($condition['action'] == self::ACTION_STOP_CAMPAIGN_GROUP && !empty($condition['campaign_group_id'])) {
                    	
                    	try {

		                    $block = new CampaignGroupBlockSubscriber();
		                    $block->group_id        = (int)$condition['campaign_group_id'];
		                    $block->subscriber_id   = (int)$subscriber->subscriber_id;
		                    $block->save(false);
	                    
                    	} catch (Exception $e) {
                    		
	                    }
                    }
                }

                if ($bounceHandler->deleteMessages) {
                    imap_delete($bounceHandler->getConnection(), "$result:$result");
                }
            }

            $bounceHandler->closeConnection();

            // mark the server as active once again
            $this->saveStatus(self::STATUS_ACTIVE);
            
        } catch (Exception $e) {
            
            if ($e->getCode() == 0) {
                Yii::log($e->getMessage(), CLogger::LEVEL_ERROR);
            }
        }

        Yii::app()->mutex->release($mutexKey);

        return true;
    }

    /**
     * @return array
     */
    public function getConditionsList()
    {
        return array(
            self::CONDITION_CONTAINS => Yii::t('servers', ucfirst(self::CONDITION_CONTAINS)),
        );
    }

    /**
     * @return array
     */
    public function getActionsList()
    {
        $options = array(
            self::ACTION_UNSUBSCRIBE => Yii::t('servers', ucfirst(self::ACTION_UNSUBSCRIBE)),
            self::ACTION_BLACKLIST   => Yii::t('servers', ucfirst(self::ACTION_BLACKLIST)),
            self::ACTION_UNCONFIRM   => Yii::t('servers', ucfirst(self::ACTION_UNCONFIRM)),
            self::ACTION_DELETE      => Yii::t('servers', ucfirst(self::ACTION_DELETE)),
        );

        if (MW_IS_CLI || Yii::app()->apps->isAppName('customer')) {
            $options = CMap::mergeArray($options, array(
                self::ACTION_MOVE_TO_LIST          => Yii::t('servers', ucfirst(self::ACTION_MOVE_TO_LIST)),
                self::ACTION_COPY_TO_LIST          => Yii::t('servers', ucfirst(self::ACTION_COPY_TO_LIST)),
                self::ACTION_STOP_CAMPAIGN_GROUP   => Yii::t('servers', ucfirst(self::ACTION_STOP_CAMPAIGN_GROUP)),
            ));
        }
        
        return $options;
    }

    /**
     * @param $value
     * @return $this
     */
    public function setConditions($value)
    {
        $this->getModelMetaData()->add('conditions', (array)$this->filterConditions($value));
        return $this;
    }

    /**
     * @return array
     */
    public function getConditions()
    {
        return $this->filterConditions($this->getModelMetaData()->itemAt('conditions'));
    }

    /**
     * @param $attribute
     * @param $params
     */
    public function _validateConditions($attribute, $params)
    {
        $value = $this->getConditions();
        if (empty($value)) {
            $this->addError($attribute, Yii::t('servers', 'Please enter at least one valid condition'));
            return;
        }
    }

    /**
     * @return array
     */
    public function getIdentifySubscribersByList()
    {
        $options = array(
            self::IDENTIFY_SUBSCRIBERS_BY_EMAIL     => Yii::t('servers', ucfirst(self::IDENTIFY_SUBSCRIBERS_BY_EMAIL)),
            self::IDENTIFY_SUBSCRIBERS_BY_UID       => Yii::t('servers', ucfirst(self::IDENTIFY_SUBSCRIBERS_BY_UID)),
            self::IDENTIFY_SUBSCRIBERS_UID_OR_EMAIL => Yii::t('servers', ucfirst(self::IDENTIFY_SUBSCRIBERS_UID_OR_EMAIL)),
        );
        
        return $options;
    }

    /**
     * @param $value
     * @return $this
     */
    public function setIdentifySubscribersBy($value)
    {
        if (empty($value) || !is_string($value) || !in_array($value, array_keys($this->getIdentifySubscribersByList()))) {
            $value = self::IDENTIFY_SUBSCRIBERS_BY_EMAIL;
        }
        $this->getModelMetaData()->add('identify_subscribers_by', (string)$value);
        return $this;
    }

    /**
     * @return string
     */
    public function getIdentifySubscribersBy()
    {
        $value = (string)$this->getModelMetaData()->itemAt('identify_subscribers_by');
        if (empty($value) || !is_string($value) || !in_array($value, array_keys($this->getIdentifySubscribersByList()))) {
            $value = self::IDENTIFY_SUBSCRIBERS_BY_EMAIL;
        }
        return $value;
    }

    /**
     * @return array
     */
    public function getCustomerEmailListsAsOptions()
    {
        if (!Yii::app()->apps->isAppName('customer')) {
            return array();
        }
        
        $options = array();
        $models = Lists::model()->findAll(array(
            'select'    => 'list_id, name',
            'condition' => 'customer_id = :cid',
            'params'    => array(':cid' => (int)Yii::app()->customer->getId()),
        ));
        
        foreach ($models as $model) {
            $options[$model->list_id] = $model->name;
        }
        
        return $options;
    }

	/**
	 * @return array
	 */
	public function getCustomerCampaignGroupsAsOptions()
	{
		if (!Yii::app()->apps->isAppName('customer')) {
			return array();
		}

		$options = array();
		$models = CampaignGroup::model()->findAll(array(
			'select'    => 'group_id, name',
			'condition' => 'customer_id = :cid',
			'params'    => array(':cid' => (int)Yii::app()->customer->getId()),
		));

		foreach ($models as $model) {
			$options[$model->group_id] = $model->name;
		}

		return $options;
	}

    /**
     * @return bool
     */
    public function getConditionsContainEmailList()
    {
        $conditions = $this->getModelMetaData()->itemAt('conditions');
        $conditions = is_array($conditions) ? $conditions : array();
        if (empty($conditions)) {
            return false;
        }
        
        $actions = array(self::ACTION_COPY_TO_LIST, self::ACTION_MOVE_TO_LIST);
        foreach ($conditions as $condition) {
            if (empty($condition['action'])) {
                continue;
            }
            if (in_array($condition['action'], $actions) && !empty($condition['list_id'])) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param $value
     * @return array
     */
    protected function filterConditions($value)
    {
        if (empty($value) || !is_array($value)) {
            return array();
        }

        // reset the indexes because we're sending high indexes from js
        $value = array_values($value);

        // 1.8.8
	    $hashes = array();
	    
        $hasMove = false;
        foreach ($value as $index => $val) {
            
            if (!is_array($val) || empty($val['condition']) || /* empty($val['value']) || */ empty($val['action'])) {
                unset($value[$index]);
                continue;
            }
            
            if (!is_string($val['condition']) || !in_array($val['condition'], array_keys($this->getConditionsList()))) {
                unset($value[$index]);
                continue;
            }
            
            if (!is_string($val['value']) || strlen($val['value']) > 500) {
                unset($value[$index]);
                continue;
            }
            
            if (!is_string($val['action']) || !in_array($val['action'], array_keys($this->getActionsList()))) {
                unset($value[$index]);
                continue;
            }
            
            // 1.8.8
            $hash = sha1(json_encode($val));
            if (isset($hashes[$hash])) {
	            unset($value[$index]);
            	continue;
            }
            $hashes[$hash] = true;
            //
            
            if (in_array($val['action'], array(self::ACTION_MOVE_TO_LIST, self::ACTION_COPY_TO_LIST))) {

	            $value[$index]['campaign_group_id'] = 0;
	            
                if ($val['action'] == self::ACTION_MOVE_TO_LIST && $hasMove) {
                    unset($value[$index]);
                    continue;
                }

                if ($val['action'] == self::ACTION_MOVE_TO_LIST) {
                    $hasMove = true;
                }
                
                if (empty($val['list_id']) || !is_numeric($val['list_id']) || $val['list_id'] == 0) {
                    unset($value[$index]);
                    continue;
                }

                $attributes = array(
                    'list_id' => (int)$val['list_id'],
                );
                if (Yii::app()->apps->isAppName('customer')) {
                    $attributes['customer_id'] = (int)Yii::app()->customer->getId();
                }
                $list = Lists::model()->findByAttributes($attributes);
                    
                if (empty($list)) {
                    unset($value[$index]);
                    continue;
                }

            } elseif ($val['action'] == self::ACTION_STOP_CAMPAIGN_GROUP) {

	            $value[$index]['list_id'] = 0;
	            
	            if (empty($val['campaign_group_id']) || !is_numeric($val['campaign_group_id']) || $val['campaign_group_id'] == 0) {
		            unset($value[$index]);
		            continue;
	            }

	            $attributes = array(
		            'group_id' => (int)$val['campaign_group_id'],
	            );
	            if (Yii::app()->apps->isAppName('customer')) {
		            $attributes['customer_id'] = (int)Yii::app()->customer->getId();
	            }
	            $group = CampaignGroup::model()->findByAttributes($attributes);

	            if (empty($group)) {
		            unset($value[$index]);
		            continue;
	            }

            } else {

                $value[$index]['list_id']           = 0;
	            $value[$index]['campaign_group_id'] = 0;
            }
        }
        
        
        return $value;
    }
}
