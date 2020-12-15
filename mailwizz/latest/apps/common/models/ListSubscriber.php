<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * ListSubscriber
 *
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.0
 */

/**
 * This is the model class for table "list_subscriber".
 *
 * The followings are the available columns in table 'list_subscriber':
 * @property integer $subscriber_id
 * @property integer $list_id
 * @property string $subscriber_uid
 * @property string $email
 * @property string $source
 * @property string $status
 * @property string $ip_address
 * @property string $date_added
 * @property string $last_updated
 *
 * The followings are the available model relations:
 * @property CampaignBounceLog[] $bounceLogs
 * @property CampaignDeliveryLog[] $deliveryLogs
 * @property CampaignDeliveryLog[] $deliveryLogsSent
 * @property CampaignDeliveryLogArchive[] $deliveryLogsArchive
 * @property CampaignForwardFriend[] $forwardFriends
 * @property CampaignTrackOpen[] $trackOpens
 * @property CampaignTrackUnsubscribe[] $trackUnsubscribes
 * @property CampaignTrackUrl[] $trackUrls
 * @property EmailBlacklist $emailBlacklist
 * @property ListFieldValue[] $fieldValues
 * @property Lists $list
 * @property ListSubscriberFieldCache $fieldsCache
 * @property ListSubscriberOptinHistory $optinHistory
 * @property ListSubscriberOptoutHistory $optoutHistory
 */
class ListSubscriber extends ActiveRecord
{
    const STATUS_CONFIRMED = 'confirmed';

    const STATUS_UNCONFIRMED = 'unconfirmed';

    const STATUS_UNSUBSCRIBED = 'unsubscribed';

    const STATUS_BLACKLISTED = 'blacklisted';

    const STATUS_UNAPPROVED = 'unapproved';

    const STATUS_DISABLED = 'disabled';

    const STATUS_MOVED = 'moved';

    const SOURCE_WEB = 'web';

    const SOURCE_API = 'api';

    const SOURCE_IMPORT = 'import';

    const BULK_SUBSCRIBE = 'subscribe';

    const BULK_UNSUBSCRIBE = 'unsubscribe';

    const BULK_DISABLE = 'disable';

    const BULK_DELETE = 'delete';

    const BULK_BLACKLIST = 'blacklist';

    const BULK_UNCONFIRM = 'unconfirm';

    const BULK_RESEND_CONFIRMATION_EMAIL = 'resend-confirmation-email';

    const CAMPAIGN_FILTER_ACTION_DID_OPEN = 1;

    const CAMPAIGN_FILTER_ACTION_DID_CLICK = 2;

    const CAMPAIGN_FILTER_ACTION_DID_NOT_OPEN = 3;

    const CAMPAIGN_FILTER_ACTION_DID_NOT_CLICK = 4;

    const FILTER_TIME_UNIT_DAY = 1;

    const FILTER_TIME_UNIT_WEEK = 2;

    const FILTER_TIME_UNIT_MONTH = 3;

    const FILTER_TIME_UNIT_YEAR = 4;

    /**
     * @var $_optinHistory - the optin history for the subscriber
     * We use it instead of relations because is easier to null it this way
     */
    protected $_optinHistory;

    /**
     * @var $_optoutHistory - the optout history for the subscriber
     * We use it instead of relations because is easier to null it this way
     */
    protected $_optoutHistory;

    // when select count(x) as counter
    public $counter = 0;

    // for search in multilists
    public $listIds = array();

	/**
	 * @since 1.5.2
	 * @var array 
	 */
    protected static $_listSubscriberActions = array();

	/**
	 * @since 1.6.7
	 * @var string 
	 */
	protected $subscribersCountSubscriberLastStatus = '';
    
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        $behaviors = array();

        if (!empty(Yii::app()->params['send.campaigns.command.useTempQueueTables'])) {
            $behaviors['toQueueTable'] = array(
                'class' => 'common.components.db.behaviors.SubscriberToCampaignQueueTableBehavior',
            );
        }

        return CMap::mergeArray($behaviors, parent::behaviors());
    }

    /**
     * @return string the associated database table name
     */
    public function tableName()
    {
        return '{{list_subscriber}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        $rules = array(
            array('status', 'in', 'range' => array_keys($this->getFilterStatusesList())),
            array('list_id, subscriber_uid, email, source, ip_address, status', 'safe', 'on' => 'search'),
        );
        return CMap::mergeArray($rules, parent::rules());
    }

    /**
     * @inheritdoc
     */
    public function relations()
    {
        $relations = array(
            'bounceLogs'            => array(self::HAS_MANY, 'CampaignBounceLog', 'subscriber_id'),
            'deliveryLogs'          => array(self::HAS_MANY, 'CampaignDeliveryLog', 'subscriber_id'),
            'deliveryLogsSent'      => array(self::HAS_MANY, 'CampaignDeliveryLog', 'subscriber_id'),
            'deliveryLogsArchive'   => array(self::HAS_MANY, 'CampaignDeliveryLogArchive', 'subscriber_id'),
            'forwardFriends'        => array(self::HAS_MANY, 'CampaignForwardFriend', 'subscriber_id'),
            'trackOpens'            => array(self::HAS_MANY, 'CampaignTrackOpen', 'subscriber_id'),
            'trackUnsubscribes'     => array(self::HAS_MANY, 'CampaignTrackUnsubscribe', 'subscriber_id'),
            'trackUrls'             => array(self::HAS_MANY, 'CampaignTrackUrl', 'subscriber_id'),
            'emailBlacklist'        => array(self::HAS_ONE, 'EmailBlacklist', 'subscriber_id'),
            'fieldValues'           => array(self::HAS_MANY, 'ListFieldValue', 'subscriber_id'),
            'list'                  => array(self::BELONGS_TO, 'Lists', 'list_id'),
            'fieldsCache'           => array(self::HAS_ONE, 'ListSubscriberFieldCache', 'subscriber_id'),
        );

        return CMap::mergeArray($relations, parent::relations());
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        $labels = array(
            'subscriber_id'     => Yii::t('list_subscribers', 'Subscriber'),
            'list_id'           => Yii::t('list_subscribers', 'List'),
            'subscriber_uid'    => Yii::t('list_subscribers', 'Unique ID'),
            'email'             => Yii::t('list_subscribers', 'Email'),
            'source'            => Yii::t('list_subscribers', 'Source'),
            'ip_address'        => Yii::t('list_subscribers', 'Ip address'),
        );

        return CMap::mergeArray($labels, parent::attributeLabels());
    }

    /**
     * Retrieves a list of models based on the current search/filter conditions.
     *
     * Typical usecase:
     * - Initialize the model fields with values from filter form.
     * - Execute this method to get CActiveDataProvider instance which will filter
     * models according to data in model fields.
     * - Pass data provider to CGridView, CListView or any similar widget.
     *
     * @return CActiveDataProvider the data provider that can return the models
     * based on the search/filter conditions.
     */
    public function search()
    {
        $criteria = new CDbCriteria;

        if (!empty($this->list_id)) {
            $criteria->compare('t.list_id', (int)$this->list_id);
        } elseif (!empty($this->listIds)) {
            $criteria->addInCondition('t.list_id', array_map('intval', $this->listIds));
        }

        $criteria->compare('t.subscriber_uid', $this->subscriber_uid);
        $criteria->compare('t.email', $this->email, true);
        $criteria->compare('t.source', $this->source);
        $criteria->compare('t.ip_address', $this->ip_address, true);
        $criteria->compare('t.status', $this->status);

        $criteria->order = 't.subscriber_id DESC';

        return new CActiveDataProvider(get_class($this), array(
            'criteria'      => $criteria,
            'pagination'    => array(
                'pageSize'  => $this->paginationOptions->getPageSize(),
                'pageVar'   => 'page',
            ),
            'sort'  => array(
                'defaultOrder'  => array(
                    't.subscriber_id'   => CSort::SORT_DESC,
                ),
            ),
        ));
    }

    /**
     * Returns the static model of the specified AR class.
     * Please note that you should have this exact method in all your CActiveRecord descendants!
     * @param string $className active record class name.
     * @return ListSubscriber the static model class
     */
    public static function model($className=__CLASS__)
    {
        return parent::model($className);
    }
    
    /**
     * @inheritdoc
     */
    protected function beforeSave()
    {
        if (empty($this->subscriber_uid)) {
            $this->subscriber_uid = $this->generateUid();
        }

        // since 1.6.4
        if (!empty($this->list_id) && !empty($this->list)) {
        	
        	// new subscriber
            if ($this->getIsNewRecord()) {
                
            	// new record, add to all count
                $this->list->incrementSubscribersCount();
                
                // if confirmed, also add to confirmed count
                if ($this->getIsConfirmed()) {
                    $this->list->incrementSubscribersCount(self::STATUS_CONFIRMED);
                }
            
            // existing subscriber
            } else {

            	// make sure the increment and decrement happens only once per status regardless of how many times it is called.
	            if ($this->status != $this->subscribersCountSubscriberLastStatus) {

		            // if now confirmed, but was not before
		            if ($this->getIsConfirmed() && $this->afterFindStatus !== self::STATUS_CONFIRMED) {
			            $this->list->incrementSubscribersCount(self::STATUS_CONFIRMED);

			            // if not confirmed anymore, but used to be
		            } elseif (!$this->getIsConfirmed() && $this->afterFindStatus == self::STATUS_CONFIRMED) {
			            $this->list->decrementSubscribersCount(self::STATUS_CONFIRMED);
		            }
	            }
            }

	        // update the status to current one
	        $this->subscribersCountSubscriberLastStatus = $this->status;
        }
        //

        return parent::beforeSave();
    }

    /**
     * @inheritdoc
     */
    protected function afterDelete()
    {
        // since 1.6.4
        if (!empty($this->list)) {
            $this->list->decrementSubscribersCount();
            if ($this->getIsConfirmed()) {
                $this->list->decrementSubscribersCount(self::STATUS_CONFIRMED);
            }
        }
        //

        parent::afterDelete();
    }

    /**
     * @param $subscriber_uid
     * @return mixed
     */
    public function findByUid($subscriber_uid)
    {
        return self::model()->findByAttributes(array(
            'subscriber_uid' => $subscriber_uid,
        ));
    }

    /**
     * @return string
     */
    public function generateUid()
    {
        $unique = StringHelper::uniqid();
        $exists = $this->findByUid($unique);

        if (!empty($exists)) {
            return $this->generateUid();
        }

        return $unique;
    }

    /**
     * @param array $params
     * @return bool|EmailBlacklistCheckInfo|mixed
     * @throws CException
     */
    public function getIsBlacklisted(array $params = array())
    {
        // since 1.3.5.5
        if (MW_PERF_LVL && MW_PERF_LVL & MW_PERF_LVL_DISABLE_SUBSCRIBER_BLACKLIST_CHECK) {
            return false;
        }

        // check since 1.3.4.7
        if ($this->status == self::STATUS_BLACKLISTED) {
            return new EmailBlacklistCheckInfo(array(
                'email'       => $this->email,
                'blacklisted' => true,
                'reason'      => 'Blacklisted',
            ));
        }

        $blCheckInfo = EmailBlacklist::isBlacklisted($this->email, $this, null, $params);

        // added since 1.3.4.7
        if ($blCheckInfo !== false && $this->getIsConfirmed()) {
        	$this->saveStatus(self::STATUS_BLACKLISTED);
        }

        return $blCheckInfo;
    }

    /**
     * @param null $reason
     * @return bool
     */
    public function addToBlacklist($reason = null)
    {
        if ($added = EmailBlacklist::addToBlacklist($this, $reason)) {
            $this->status = self::STATUS_BLACKLISTED;
        }
        return $added;
    }

    /**
     * @return bool
     */
    public function removeFromBlacklistByEmail()
    {
        if ($this->status == self::STATUS_BLACKLISTED) {
            return false;
        }
        
        $global   = EmailBlacklist::removeByEmail($this->email);
	    $customer = true;
	    
	    if (!empty($this->list)) {
		    $customer = CustomerEmailBlacklist::model()->deleteAllByAttributes(array(
			    'customer_id' => $this->list->customer_id,
			    'email'       => $this->email,
		    ));
	    }
	    
	    return $global && $customer;
    }

    /**
     * @return bool
     */
    public function getCanBeConfirmed()
    {
        return !in_array($this->status, array(self::STATUS_CONFIRMED, self::STATUS_BLACKLISTED));
    }

    /**
     * @return bool
     */
    public function getCanBeUnsubscribed()
    {
        return !in_array($this->status, array(self::STATUS_BLACKLISTED));
    }

    /**
     * @return bool
     */
    public function getCanBeDeleted()
    {
        return $this->getRemovable();
    }

    /**
     * @return bool
     */
    public function getCanBeEdited()
    {
        return $this->getEditable();
    }

    /**
     * @return bool
     */
    public function getCanBeApproved()
    {
        return $this->status == self::STATUS_UNAPPROVED;
    }

    /**
     * @return bool
     */
    public function getIsUnapproved()
    {
        return $this->status == self::STATUS_UNAPPROVED;
    }

    /**
     * @return bool
     */
    public function getIsConfirmed()
    {
        return $this->status == self::STATUS_CONFIRMED;
    }

    /**
     * @return bool
     */
    public function getIsUnconfirmed()
    {
        return $this->status == self::STATUS_UNCONFIRMED;
    }

    /**
     * @return bool
     */
    public function getIsUnsubscribed()
    {
        return $this->status == self::STATUS_UNSUBSCRIBED;
    }

    /**
     * @return bool
     */
    public function getIsDisabled()
    {
        return $this->status == self::STATUS_DISABLED;
    }

    /**
     * @return bool
     */
    public function getCanBeDisabled()
    {
        return $this->status == self::STATUS_CONFIRMED;
    }

    /**
     * @return bool
     */
    public function getIsMoved()
    {
        return $this->status == self::STATUS_MOVED;
    }

    /**
     * @return bool
     */
    public function getIsImported()
    {
        return $this->source == self::SOURCE_IMPORT;
    }

    /**
     * @return bool
     */
    public function getRemovable()
    {
        $removable = true;
        if (!empty($this->list_id) && !empty($this->list) && !empty($this->list->customer_id) && !empty($this->list->customer)) {
            $removable = $this->list->customer->getGroupOption('lists.can_delete_own_subscribers', 'yes') == 'yes';
        }
        return $removable;
    }

    /**
     * @return bool
     */
    public function getEditable()
    {
        $editable = true;
        if (!empty($this->list_id) && !empty($this->list) && !empty($this->list->customer_id) && !empty($this->list->customer)) {
            $editable = $this->list->customer->getGroupOption('lists.can_edit_own_subscribers', 'yes') == 'yes';
        }
        return $editable;
    }

    /**
     * @return string
     */
    public function getUid()
    {
        return $this->subscriber_uid;
    }

    /**
     * @return array
     */
    public function getStatusesList()
    {
        return array(
            self::STATUS_CONFIRMED      => Yii::t('list_subscribers', ucfirst(self::STATUS_CONFIRMED)),
            self::STATUS_UNCONFIRMED    => Yii::t('list_subscribers', ucfirst(self::STATUS_UNCONFIRMED)),
            self::STATUS_UNSUBSCRIBED   => Yii::t('list_subscribers', ucfirst(self::STATUS_UNSUBSCRIBED)),
        );
    }

    /**
     * @return array
     */
    public function getFilterStatusesList()
    {
        return array_merge($this->getStatusesList(), array(
            self::STATUS_UNAPPROVED  => Yii::t('list_subscribers', ucfirst(self::STATUS_UNAPPROVED)),
            self::STATUS_BLACKLISTED => Yii::t('list_subscribers', ucfirst(self::STATUS_BLACKLISTED)),
            self::STATUS_DISABLED    => Yii::t('list_subscribers', ucfirst(self::STATUS_DISABLED)),
            self::STATUS_MOVED       => Yii::t('list_subscribers', ucfirst(self::STATUS_MOVED)),
        ));
    }

    /**
     * @return array
     */
    public function getBulkActionsList()
    {
        $list = array(
            self::BULK_SUBSCRIBE                 => Yii::t('list_subscribers', ucfirst(self::BULK_SUBSCRIBE)),
            self::BULK_UNSUBSCRIBE               => Yii::t('list_subscribers', ucfirst(self::BULK_UNSUBSCRIBE)),
            self::BULK_UNCONFIRM                 => Yii::t('list_subscribers', ucfirst(self::BULK_UNCONFIRM)),
            self::BULK_RESEND_CONFIRMATION_EMAIL => Yii::t('list_subscribers', 'Resend confirmation email'),
            self::BULK_DISABLE                   => Yii::t('list_subscribers', ucfirst(self::BULK_DISABLE)),
            self::BULK_DELETE                    => Yii::t('list_subscribers', ucfirst(self::BULK_DELETE)),
        );

        if (!$this->getCanBeDeleted()) {
            unset($list[self::BULK_DELETE]);
        }

        return $list;
    }

    /**
     * @return array
     */
    public function getSourcesList()
    {
        return array(
            self::SOURCE_API    => Yii::t('list_subscribers', ucfirst(self::SOURCE_API)),
            self::SOURCE_IMPORT => Yii::t('list_subscribers', ucfirst(self::SOURCE_IMPORT)),
            self::SOURCE_WEB    => Yii::t('list_subscribers', ucfirst(self::SOURCE_WEB)),
        );
    }

    /**
     * @return ListSubscriber
     */
    public function getShallowCopy()
    {
        $copy = new self();
        foreach ($this->attributes as $key => $value) {
            $copy->$key = $value;
        }

        $copy->list_id        = null;
        $copy->subscriber_id  = null;
        $copy->subscriber_uid = $this->generateUid();
        $copy->date_added     = new CDbExpression('NOW()');
        $copy->last_updated   = new CDbExpression('NOW()');

        return $copy;
    }

    /**
     * Since 1.3.6.3 it will also update custom fields value.
     *
     * @param $listId
     * @param bool $doTransaction
     * @param bool $notify
     * @return bool|ListSubscriber|static
     * @throws CDbException
     * @throws CException
     */
    public function copyToList($listId, $doTransaction = true, $notify = false)
    {
        $mutexKey = __METHOD__ . ':' . $listId . ':' . $this->email;
        if (!Yii::app()->mutex->acquire($mutexKey)) {
            return false;
        }

        $listId = (int)$listId;
        if (empty($listId) || $listId == $this->list_id) {
            Yii::app()->mutex->release($mutexKey);
            return false;
        }

        static $targetLists      = array();
        static $cacheFieldModels = array();

        if (isset($targetLists[$listId]) || array_key_exists($listId, $targetLists)) {
            $targetList = $targetLists[$listId];
        } else {
            $targetList = $targetLists[$listId] = Lists::model()->findByPk($listId);
        }

        if (empty($targetList)) {
            Yii::app()->mutex->release($mutexKey);
            return false;
        }

        $subscriber = self::model()->findByAttributes(array(
            'list_id' => $targetList->list_id,
            'email'   => $this->email
        ));

        $subscriberExists = !empty($subscriber);
        if (!$subscriberExists) {
            $subscriber = $this->getShallowCopy();
            $subscriber->list_id = $targetList->list_id;
            $subscriber->addRelatedRecord('list', $targetList, false);
        }

        // 1.3.7.3
        if ($subscriber->status == self::STATUS_MOVED) {
            $subscriber->status = self::STATUS_CONFIRMED;
        }

        if ($doTransaction) {
            $transaction = Yii::app()->getDb()->beginTransaction();
        }

        try {

            $isNewRecord = $subscriber->isNewRecord;

            if ($isNewRecord && !$subscriber->save()) {
                throw new Exception(CHtml::errorSummary($subscriber));
            }

            // 1.3.8.8 - not sure about this 100%, so leave it disabled for now
            if (false && $isNewRecord && !empty($this->optinHistory)) {
                $optinHistory = clone $this->optinHistory;
                $optinHistory->subscriber_id = $subscriber->subscriber_id;
                $optinHistory->save(false);
            }

            $cacheListsKey = $this->list_id . '|' . $targetList->list_id;
            if (!isset($cacheFieldModels[$cacheListsKey])) {
                // the custom fields for source list
                $sourceFields = ListField::model()->findAllByAttributes(array(
                    'list_id' => $this->list_id,
                ));

                // the custom fields for target list
                $targetFields = ListField::model()->findAllByAttributes(array(
                    'list_id' => $targetList->list_id,
                ));

                // get only the same fields
                $_fieldModels = array();
                foreach ($sourceFields as $srcIndex => $sourceField) {
                    foreach ($targetFields as $trgIndex => $targetField) {
                        if ($sourceField->tag == $targetField->tag && $sourceField->type_id == $targetField->type_id) {
                            $_fieldModels[] = array($sourceField, $targetField);
                            unset($sourceFields[$srcIndex], $targetFields[$trgIndex]);
                            break;
                        }
                    }
                }
                $cacheFieldModels[$cacheListsKey] = $_fieldModels;
                unset($sourceFields, $targetFields, $_fieldModels);
            }
            $fieldModels = $cacheFieldModels[$cacheListsKey];

            if (empty($fieldModels)) {
                throw new Exception('No field models found, something went wrong!');
            }

            foreach ($fieldModels as $index => $models) {

                list($source, $target) = $models;

                $sourceValues = ListFieldValue::model()->findAllByAttributes(array(
                    'subscriber_id' => $this->subscriber_id,
                    'field_id'      => $source->field_id,
                ));

                ListFieldValue::model()->deleteAllByAttributes(array(
                    'subscriber_id' => $subscriber->subscriber_id,
                    'field_id'      => $target->field_id,
                ));

                foreach ($sourceValues as $sourceValue) {
                    $targetValue                = clone $sourceValue;
                    $targetValue->value_id      = null;
                    $targetValue->field_id      = $target->field_id;
                    $targetValue->subscriber_id = $subscriber->subscriber_id;
                    $targetValue->isNewRecord   = true;
                    $targetValue->date_added    = new CDbExpression('NOW()');
                    $targetValue->last_updated  = new CDbExpression('NOW()');
                    if (!$targetValue->save()) {
                        throw new Exception(CHtml::errorSummary($targetValue));
                    }
                }
                unset($models, $source, $target, $sourceValues, $sourceValue);
            }
            unset($fieldModels);

            if ($doTransaction) {
                $transaction->commit();
            }
        } catch (Exception $e) {
            if ($doTransaction) {
                $transaction->rollback();
            } elseif (!empty($subscriber->subscriber_id)) {
                $subscriber->delete();
            }
            $subscriber = false;
        }

        if ($subscriber && $notify && !$subscriberExists) {
            $subscriber->sendCreatedNotifications();
        }

        Yii::app()->mutex->release($mutexKey);

        return $subscriber;
    }

    /**
     * @param $listId
     * @param bool $doTransaction
     * @param bool $notify
     * @return bool|ListSubscriber
     * @throws CDbException
     * @throws CException
     */
    public function moveToList($listId, $doTransaction = true, $notify = false)
    {
        $mutexKey = __METHOD__ . ':' . $listId . ':' . $this->email;
        if (!Yii::app()->mutex->acquire($mutexKey)) {
            return false;
        }

        if (!($subscriber = $this->copyToList($listId, $doTransaction, $notify))) {
            Yii::app()->mutex->release($mutexKey);
            return false;
        }

        $exists = ListSubscriberListMove::model()->findByAttributes(array(
            'source_subscriber_id'  => $this->subscriber_id,
            'source_list_id'        => $this->list_id,
            'destination_list_id'   => $listId,
        ));

        if (!empty($exists)) {
            $this->saveStatus(ListSubscriber::STATUS_MOVED);
            Yii::app()->mutex->release($mutexKey);
            return $subscriber;
        }

        $move = new ListSubscriberListMove();
        $move->source_subscriber_id      = $this->subscriber_id;
        $move->source_list_id            = $this->list_id;
        $move->destination_subscriber_id = $subscriber->subscriber_id;
        $move->destination_list_id       = $listId;

        try {
            $move->save(false);
            $this->saveStatus(ListSubscriber::STATUS_MOVED);
        } catch (Exception $e) {
            Yii::app()->mutex->release($mutexKey);
            return false;
        }

        Yii::app()->mutex->release($mutexKey);
        return $subscriber;
    }

    /**
     * @param null $status
     * @return bool
     */
    public function saveStatus($status = null)
    {
        if (empty($this->subscriber_id)) {
            return false;
        }
        
        if ($status && $status == $this->status) {
            return true;
        }
        
        if ($status) {
            $this->status = $status;
        }

        // since 1.6.4
	    if ($this->status != $this->subscribersCountSubscriberLastStatus) {

		    $this->subscribersCountSubscriberLastStatus = $this->status;
		    
		    if (!empty($this->list_id) && !empty($this->list)) {
			    if ($this->getIsConfirmed()) {
				    $this->list->incrementSubscribersCount(self::STATUS_CONFIRMED);
			    } else {
				    $this->list->decrementSubscribersCount(self::STATUS_CONFIRMED);
			    }	
		    }
		    //
	    }

        $attributes = array('status' => $this->status);
        $this->last_updated = $attributes['last_updated'] = new CDbExpression('NOW()');
	    
        // 1.7.9
	    Yii::app()->hooks->doAction($this->buildHookName(array('suffix' => 'before_savestatus')), $this);
        //
	    
        $result = (bool)Yii::app()->getDb()->createCommand()->update($this->tableName(), $attributes, 'subscriber_id = :id', array(':id' => (int)$this->subscriber_id));

	    // 1.7.9
	    Yii::app()->hooks->doAction($this->buildHookName(array('suffix' => 'after_savestatus')), $this, $result);
	    //
	    
	    return $result;
    }

    /**
     * @since 1.3.5 - this should be expanded in future
     * @param $actionName
     * @return $this
     */
    public function takeListSubscriberAction($actionName)
    {
        if ($this->isNewRecord || empty($this->list_id)) {
            return $this;
        }

        if ($actionName == ListSubscriberAction::ACTION_SUBSCRIBE && $this->status != self::STATUS_CONFIRMED) {
            return $this;
        }

        if ($actionName == ListSubscriberAction::ACTION_UNSUBSCRIBE && $this->status == self::STATUS_CONFIRMED) {
            return $this;
        }

        $allowedActions = array_keys(ListSubscriberAction::model()->getActions());
        if (!in_array($actionName, $allowedActions)) {
            return $this;
        }

        // since 1.5.2 - add local cache
        $hash = $this->list_id . '_' . $actionName;
        if (!isset(self::$_listSubscriberActions[$hash])) {
            $criteria = new CDbCriteria();
            $criteria->select = 'target_list_id';
            $criteria->compare('source_list_id', (int)$this->list_id);
            $criteria->compare('source_action', $actionName);
            self::$_listSubscriberActions[$hash] = ListSubscriberAction::model()->findAll($criteria);
        }

        if (empty(self::$_listSubscriberActions[$hash])) {
            return $this;
        }

        $lists = array();
        foreach (self::$_listSubscriberActions[$hash] as $list) {
            $lists[] = $list->target_list_id;
        }

        $criteria = new CDbCriteria();
        $criteria->compare('email', $this->email);
        $criteria->addInCondition('list_id', $lists);
        $criteria->addInCondition('status', array(self::STATUS_CONFIRMED));

        self::model()->updateAll(array('status' => self::STATUS_UNSUBSCRIBED), $criteria);

        // 1.6.4
        Lists::flushSubscribersCountCacheByListsIds($lists);

        return $this;
    }

    /**
     * @return array
     * @throws CException
     */
    public function loadAllCustomFieldsWithValues()
    {
        $fields = array();
        foreach (ListField::getAllByListId($this->list_id) as $field) {
            $values = Yii::app()->getDb()->createCommand()
                ->select('value')
                ->from('{{list_field_value}}')
                ->where('subscriber_id = :sid AND field_id = :fid', array(
                    ':sid' => (int)$this->subscriber_id,
                    ':fid' => (int)$field['field_id']
                ))
                ->queryAll();

            $value = array();
            foreach ($values as $val) {
                $value[] = $val['value'];
            }
            $fields['['. $field['tag'] .']'] = CHtml::encode(implode(', ', $value));
        }

        return $fields;
    }

    /**
     * @param bool $refresh
     * @return array|mixed|string
     * @throws CException
     */
    public function getAllCustomFieldsWithValues($refresh = false)
    {
        static $fields = array();

        if (empty($this->subscriber_id)) {
            return array();
        }

        if ($refresh && isset($fields[$this->subscriber_id])) {
            unset($fields[$this->subscriber_id]);
        }

        if (isset($fields[$this->subscriber_id])) {
            return $fields[$this->subscriber_id];
        }

        $fields[$this->subscriber_id] = array();

        if (MW_PERF_LVL && MW_PERF_LVL & MW_PERF_LVL_ENABLE_SUBSCRIBER_FIELD_CACHE) {

            if (!$refresh && !empty($this->fieldsCache)) {
                return $fields[$this->subscriber_id] = $this->fieldsCache->data;
            }

            if ($refresh) {

                ListSubscriberFieldCache::model()->deleteAllByAttributes(array(
                    'subscriber_id' => $this->subscriber_id,
                ));

            }

            $data  = $this->loadAllCustomFieldsWithValues();
            $model = new ListSubscriberFieldCache();
            $model->subscriber_id = $this->subscriber_id;
            $model->data = $data;

            try {
                if (!$model->save()) {
                    throw new Exception('Not saved!');
                }
            } catch (Exception $e) {}

            $model->data = $data;
            $this->addRelatedRecord('fieldsCache', $model, false);

            return $fields[$this->subscriber_id]= $model->data;
        }

        return $fields[$this->subscriber_id] = $this->loadAllCustomFieldsWithValues();
    }

    /**
     * @param $field
     * @return mixed|null
     * @throws CException
     */
    public function getCustomFieldValue($field)
    {
        $field  = '['. strtoupper(str_replace(array('[', ']'), '', $field)) .']';
        $fields = $this->getAllCustomFieldsWithValues();
        $value  = isset($fields[$field]) || array_key_exists($field, $fields) ? $fields[$field] : null;
        unset($fields);
        return $value;
    }

    /**
     * @param Campaign $campaign
     * @return bool
     */
    public function hasOpenedCampaign(Campaign $campaign)
    {
        $criteria = new CDbCriteria();
        $criteria->compare('campaign_id', (int)$campaign->campaign_id);
        $criteria->compare('subscriber_id', (int)$this->subscriber_id);
        return CampaignTrackOpen::model()->count($criteria) > 0;
    }

    /**
     * since 1.3.6.2
     *
     * @param bool $forcefully
     * @return $this
     * @throws CException
     */
    public function handleApprove($forcefully = false)
    {
        if (!$forcefully && !$this->getCanBeApproved()) {
            return $this;
        }

        if (empty($this->list_id) || empty($this->list) || $this->list->subscriber_require_approval != Lists::TEXT_YES) {
            return $this;
        }

        $pageType = ListPageType::model()->findBySlug('subscribe-confirm-approval-email');
        if (!($server = DeliveryServer::pickServer(0, $this->list))) {
            $pageType = null;
        }

        if (empty($pageType)) {
            return $this;
        }

        $options = Yii::app()->options;
        $page    = ListPage::model()->findByAttributes(array(
            'list_id' => $this->list_id,
            'type_id' => $pageType->type_id
        ));

        $_content         = !empty($page->content) ? $page->content : $pageType->content;
        $_subject         = !empty($page->email_subject) ? $page->email_subject : $pageType->email_subject;
        $updateProfileUrl = $options->get('system.urls.frontend_absolute_url') . 'lists/' . $this->list->list_uid . '/update-profile/' . $this->subscriber_uid;
        $unsubscribeUrl   = $options->get('system.urls.frontend_absolute_url') . 'lists/' . $this->list->list_uid . '/unsubscribe/' . $this->subscriber_uid;
        $searchReplace    = array(
            '[LIST_NAME]'           => $this->list->display_name,
            '[COMPANY_NAME]'        => !empty($this->list->company) ? $this->list->company->name : null,
            '[UPDATE_PROFILE_URL]'  => $updateProfileUrl,
            '[UNSUBSCRIBE_URL]'     => $unsubscribeUrl,
            '[COMPANY_FULL_ADDRESS]'=> !empty($this->list->company) ? nl2br($this->list->company->getFormattedAddress()) : null,
            '[CURRENT_YEAR]'        => date('Y'),
        );

        $subscriberCustomFields = $this->getAllCustomFieldsWithValues();
        foreach ($subscriberCustomFields as $field => $value) {
            $searchReplace[$field] = $value;
        }

        $_content = str_replace(array_keys($searchReplace), array_values($searchReplace), $_content);
        $_subject = str_replace(array_keys($searchReplace), array_values($searchReplace), $_subject);

        // 1.5.3
        if (CampaignHelper::isTemplateEngineEnabled()) {
            $_content = CampaignHelper::parseByTemplateEngine($_content, $searchReplace);
            $_subject = CampaignHelper::parseByTemplateEngine($_subject, $searchReplace);
        }

        $params = array(
            'to'        => $this->email,
            'fromName'  => $this->list->default->from_name,
            'subject'   => $_subject,
            'body'      => $_content,
        );

        for ($i = 0; $i < 3; ++$i) {
            if ($server->setDeliveryFor(DeliveryServer::DELIVERY_FOR_LIST)->setDeliveryObject($this->list)->sendEmail($params)) {
                break;
            }
            if (!($server = DeliveryServer::pickServer($server->server_id, $this->list))) {
                break;
            }
        }

        return $this;
    }

    /**
     * since 1.3.6.2
     *
     * @param bool $forcefully
     * @return $this
     * @throws CException
     */
    public function handleWelcome($forcefully = false)
    {
        if (!$forcefully && !$this->getIsConfirmed()) {
            return $this;
        }

        if (empty($this->list_id) || empty($this->list) || $this->list->welcome_email != Lists::TEXT_YES) {
            return $this;
        }

        $pageType = ListPageType::model()->findBySlug('welcome-email');
        if (!($server = DeliveryServer::pickServer(0, $this->list))) {
            $pageType = null;
        }

        if (empty($pageType)) {
            return $this;
        }

        $options = Yii::app()->options;
        $page    = ListPage::model()->findByAttributes(array(
            'list_id' => $this->list_id,
            'type_id' => $pageType->type_id
        ));

        $_content         = !empty($page->content) ? $page->content : $pageType->content;
        $_subject         = !empty($page->email_subject) ? $page->email_subject : $pageType->email_subject;
        $updateProfileUrl = $options->get('system.urls.frontend_absolute_url') . 'lists/' . $this->list->list_uid . '/update-profile/' . $this->subscriber_uid;
        $unsubscribeUrl   = $options->get('system.urls.frontend_absolute_url') . 'lists/' . $this->list->list_uid . '/unsubscribe/' . $this->subscriber_uid;
        $searchReplace    = array(
            '[LIST_NAME]'           => $this->list->display_name,
            '[COMPANY_NAME]'        => !empty($this->list->company) ? $this->list->company->name : null,
            '[UPDATE_PROFILE_URL]'  => $updateProfileUrl,
            '[UNSUBSCRIBE_URL]'     => $unsubscribeUrl,
            '[COMPANY_FULL_ADDRESS]'=> !empty($this->list->company) ? nl2br($this->list->company->getFormattedAddress()) : null,
            '[CURRENT_YEAR]'        => date('Y'),
        );

        // since 1.3.5.9
        $subscriberCustomFields = $this->getAllCustomFieldsWithValues();
        foreach ($subscriberCustomFields as $field => $value) {
            $searchReplace[$field] = $value;
        }
        //

        $_content = str_replace(array_keys($searchReplace), array_values($searchReplace), $_content);
        $_subject = str_replace(array_keys($searchReplace), array_values($searchReplace), $_subject);

        // 1.5.3
        if (CampaignHelper::isTemplateEngineEnabled()) {
            $_content = CampaignHelper::parseByTemplateEngine($_content, $searchReplace);
            $_subject = CampaignHelper::parseByTemplateEngine($_subject, $searchReplace);
        }

        $params = array(
            'to'        => $this->email,
            'fromName'  => $this->list->default->from_name,
            'subject'   => $_subject,
            'body'      => $_content,
        );

        for ($i = 0; $i < 3; ++$i) {
            if ($server->setDeliveryFor(DeliveryServer::DELIVERY_FOR_LIST)->setDeliveryObject($this->list)->sendEmail($params)) {
                break;
            }
            if (!($server = DeliveryServer::pickServer($server->server_id, $this->list))) {
                break;
            }
        }

        return $this;
    }

    /**
     * @return array
     */
    public function getCampaignFilterActions()
    {
        return array(
            self::CAMPAIGN_FILTER_ACTION_DID_OPEN      => Yii::t('list_subscribers', 'Did open'),
            self::CAMPAIGN_FILTER_ACTION_DID_CLICK     => Yii::t('list_subscribers', 'Did click'),
            self::CAMPAIGN_FILTER_ACTION_DID_NOT_OPEN  => Yii::t('list_subscribers', 'Did not open'),
            self::CAMPAIGN_FILTER_ACTION_DID_NOT_CLICK => Yii::t('list_subscribers', 'Did not click'),
        );
    }

    /**
     * @return array
     */
    public function getFilterTimeUnits()
    {
        return array(
            self::FILTER_TIME_UNIT_DAY   => Yii::t('list_subscribers', 'Days'),
            self::FILTER_TIME_UNIT_WEEK  => Yii::t('list_subscribers', 'Weeks'),
            self::FILTER_TIME_UNIT_MONTH => Yii::t('list_subscribers', 'Months'),
            self::FILTER_TIME_UNIT_YEAR  => Yii::t('list_subscribers', 'Years'),
        );
    }

    /**
     * @param $in
     * @return string
     */
    public function getFilterTimeUnitValueForDb($in)
    {
        if ($in == self::FILTER_TIME_UNIT_DAY) {
            return 'DAY';
        }
        if ($in == self::FILTER_TIME_UNIT_WEEK) {
            return 'WEEK';
        }
        if ($in == self::FILTER_TIME_UNIT_MONTH) {
            return 'MONTH';
        }
        if ($in == self::FILTER_TIME_UNIT_YEAR) {
            return 'YEAR';
        }
        return 'MONTH';
    }

    /**
     * @return string
     */
    public function getGridViewHtmlStatus()
    {
        if ($this->getIsMoved()) {

            $moved = ListSubscriberListMove::model()->findByAttributes(array(
                'source_subscriber_id'  => $this->subscriber_id,
                'source_list_id'        => $this->list_id,
            ));

            if (!empty($moved)) {
                $url = 'javascript:;';
                if (Yii::app()->apps->isAppName('customer')) {
                    $url = Yii::app()->createUrl('list_subscribers/update', array(
                        'list_uid'       => $moved->destinationList->list_uid,
                        'subscriber_uid' => $moved->destinationSubscriber->subscriber_uid,
                    ));
                }
                $where = CHtml::link($moved->destinationList->name, $url, array('target' => '_blank', 'title' => Yii::t('app', 'View')));
                return ucfirst(Yii::t('list_subscribers', $this->status)) . ': ' . $where;
            }
        }

        return ucfirst(Yii::t('list_subscribers', $this->status));
    }

    /**
     * @return $this
     * @throws CException
     */
    public function sendCreatedNotifications()
    {
        $canContinue = false;
        if (
            !empty($this->list) &&
            !empty($this->list->customerNotification) &&
            $this->list->customerNotification->subscribe == ListCustomerNotification::TEXT_YES &&
            !empty($this->list->customerNotification->subscribe_to) &&
            ($server = DeliveryServer::pickServer(0, $this->list, array('useFor' => DeliveryServer::USE_FOR_LIST_EMAILS)))
        ) {
            $canContinue = true;
        }

        if (!$canContinue) {
            return $this;
        }

        //
        $fields = array();
        $listFields = ListField::model()->findAll(array(
            'select'    => 'field_id, label',
            'condition' => 'list_id = :lid',
            'params'    => array(':lid' => (int)$this->list->list_id),
        ));
        foreach ($listFields as $field) {
            $fieldValues = ListFieldValue::model()->findAll(array(
                'select'    => 'value',
                'condition' => 'subscriber_id = :sid AND field_id = :fid',
                'params'    => array(':sid' => (int)$this->subscriber_id, ':fid' => (int)$field->field_id),
            ));
            $values = array();
            foreach ($fieldValues as $value) {
                $values[] = $value->value;
            }
            $fields[$field->label] = implode(', ', $values);
        }
        //
        
	    $submittedData = array();
	    foreach ($fields as $key => $value) {
		    $submittedData[] = sprintf('%s: %s', $key, $value);
	    }
	    $submittedData = implode('<br />', $submittedData);

	    $options = Yii::app()->options;
	    $params  = CommonEmailTemplate::getAsParamsArrayBySlug('new-list-subscriber',
		    array(
			    'fromName'  => $this->list->default->from_name,
			    'subject'   => Yii::t('lists', 'New list subscriber!'),
		    ), array(
			    '[LIST_NAME]'      => $this->list->name,
			    '[DETAILS_URL]'    => $options->get('system.urls.customer_absolute_url') . sprintf('lists/%s/subscribers/%s/update', $this->list->list_uid, $this->subscriber_uid),
			    '[SUBMITTED_DATA]' => $submittedData,
		    )
	    );
	    
        $recipients = explode(',', $this->list->customerNotification->subscribe_to);
        $recipients = array_map('trim', $recipients);
        
        foreach ($recipients as $recipient) {
            if (!FilterVarHelper::email($recipient)) {
                continue;
            }
            $params['to'] = array($recipient => $this->list->customer->getFullName());
            $server->setDeliveryFor(DeliveryServer::DELIVERY_FOR_LIST)->setDeliveryObject($this->list)->sendEmail($params);
        }

        return $this;
    }

    /**
     * @param string $time
     * @return bool
     * @throws CException
     */
    public function getIsInactiveInTimePeriod($time = '-90 days')
    {
        // did the subscriber received any campaign at all?
        $sql = 'SELECT subscriber_id FROM {{campaign_delivery_log}} WHERE subscriber_id = :sid AND DATE(date_added) >= :da LIMIT 1';
        $row = Yii::app()->getDb()->createCommand($sql)->queryRow(true, array(
            ':sid' => $this->subscriber_id,
            ':da'  => date('Y-m-d', strtotime($time)),
        ));
        $campaignDeliveryLog        = !empty($row['subscriber_id']);
        $campaignDeliveryLogArchive = false;

        if (!$campaignDeliveryLog) {
            $sql = 'SELECT subscriber_id FROM {{campaign_delivery_log_archive}} WHERE subscriber_id = :sid AND DATE(date_added) >= :da LIMIT 1';
            $row = Yii::app()->getDb()->createCommand($sql)->queryRow(true, array(
                ':sid' => $this->subscriber_id,
                ':da'  => date('Y-m-d', strtotime($time)),
            ));
            $campaignDeliveryLogArchive = !empty($row['subscriber_id']);
        }

        if (!$campaignDeliveryLog && !$campaignDeliveryLogArchive) {
            return false;
        }
        //

        // did the subscriber opened a campaign?
        $sql = 'SELECT subscriber_id FROM {{campaign_track_open}} WHERE subscriber_id = :sid AND DATE(date_added) >= :da LIMIT 1';
        $row = Yii::app()->getDb()->createCommand($sql)->queryRow(true, array(
            ':sid' => $this->subscriber_id,
            ':da'  => date('Y-m-d', strtotime($time)),
        ));

        if (!empty($row['subscriber_id'])) {
            return false;
        }

        // did the subscriber clicked a campaign?
        $sql = 'SELECT subscriber_id FROM {{campaign_track_url}} WHERE subscriber_id = :sid AND DATE(date_added) >= :da LIMIT 1';
        $row = Yii::app()->getDb()->createCommand($sql)->queryRow(true, array(
            ':sid' => $this->subscriber_id,
            ':da'  => date('Y-m-d', strtotime($time)),
        ));

        if (!empty($row['subscriber_id'])) {
            return false;
        }

        return true;
    }

    /**
     * @since 1.3.8.8
     * @return string
     */
    public function getDisplayEmail()
    {
        if (Yii::app()->apps->isAppName('backend')) {
            return $this->email;
        }

        if ($this->isNewRecord || empty($this->list_id) || empty($this->list->customer_id)) {
            return $this->email;
        }

        $customer = $this->list->customer;
        if ($customer->getGroupOption('common.mask_email_addresses', 'no') == 'yes') {
            return StringHelper::maskEmailAddress($this->email);
        }

        if (Yii::app()->apps->isAppName('frontend') && ($campaign = Yii::app()->controller->getData('campaign'))) {
            if ($campaign->shareReports->share_reports_mask_email_addresses == CampaignOptionShareReports::TEXT_YES) {
                return StringHelper::maskEmailAddress($this->email);
            }
        }

        return $this->email;
    }

    /**
     * @return ListSubscriberOptinHistory
     */
    public function getOptinHistory()
    {
        if ($this->_optinHistory !== null) {
            return $this->_optinHistory;
        }

        return $this->_optinHistory = ListSubscriberOptinHistory::model()->findByAttributes(array(
            'subscriber_id' => (int)$this->subscriber_id,
        ));
    }

    /**
     * @since 1.3.8.8
     * @return $this
     */
    public function createOptinHistory()
    {
        if (MW_IS_CLI) {
            return $this;
        }

        try {

            if ($this->getOptinHistory()) {
                $this->removeOptinHistory();
                $this->removeOptoutHistory();
            }

            $request                        = Yii::app()->request;
            $optinHistory                   = new ListSubscriberOptinHistory();
            $optinHistory->subscriber_id    = $this->subscriber_id;
            $optinHistory->optin_ip         = $request->userHostAddress;
            $optinHistory->optin_user_agent = StringHelper::truncateLength($request->userAgent, 255);
            $optinHistory->optin_date       = new CDbExpression('NOW()');
            $optinHistory->save(false);

            $this->_optinHistory = $optinHistory;

        } catch (Exception $e) {
            Yii::log($e->getMessage(), CLogger::LEVEL_ERROR);
        }

        return $this;
    }

    /**
     * @since 1.3.8.8
     * @return $this
     */
    public function confirmOptinHistory()
    {
        if (MW_IS_CLI) {
            return $this;
        }

        try {

            if (!$this->getOptinHistory()) {
                $this->createOptinHistory();
            }

            $request                          = Yii::app()->request;
            $optinHistory                     = $this->getOptinHistory();
            $optinHistory->confirm_ip         = $request->userHostAddress;
            $optinHistory->confirm_user_agent = StringHelper::truncateLength($request->userAgent, 255);
            $optinHistory->confirm_date       = new CDbExpression('NOW()');
            $optinHistory->save(false);

        } catch (Exception $e) {
            Yii::log($e->getMessage(), CLogger::LEVEL_ERROR);
        }

        return $this;
    }

    /**
     * @return int
     */
    public function removeOptinHistory()
    {
        $this->_optinHistory = null;
        return ListSubscriberOptinHistory::model()->deleteAllByAttributes(array(
            'subscriber_id' => (int)$this->subscriber_id,
        ));
    }

    /**
     * @return mixed
     */
    public function getOptoutHistory()
    {
        if ($this->_optoutHistory !== null) {
            return $this->_optoutHistory;
        }

        return $this->_optoutHistory = ListSubscriberOptoutHistory::model()->findByAttributes(array(
            'subscriber_id' => (int)$this->subscriber_id,
        ));
    }

    /**
     * @since 1.3.9.8
     * @return $this
     */
    public function createOptoutHistory()
    {
        if (MW_IS_CLI) {
            return $this;
        }

        try {

            if ($this->getOptoutHistory()) {
                $this->removeOptoutHistory();
            }

            $request                          = Yii::app()->request;
            $optoutHistory                    = new ListSubscriberOptoutHistory();
            $optoutHistory->subscriber_id     = $this->subscriber_id;
            $optoutHistory->optout_ip         = $request->userHostAddress;
            $optoutHistory->optout_user_agent = StringHelper::truncateLength($request->userAgent, 255);
            $optoutHistory->optout_date       = new CDbExpression('NOW()');
            $optoutHistory->save(false);

            $this->_optoutHistory = $optoutHistory;

        } catch (Exception $e) {
            Yii::log($e->getMessage(), CLogger::LEVEL_ERROR);
        }

        return $this;
    }

    /**
     * @since 1.3.9.8
     * @return $this
     */
    public function confirmOptoutHistory()
    {
        if (MW_IS_CLI) {
            return $this;
        }

        try {

            if (!$this->getOptoutHistory()) {
                $this->createOptoutHistory();
            }

            $request                           = Yii::app()->request;
            $optoutHistory                     = $this->getOptoutHistory();
            $optoutHistory->confirm_ip         = $request->userHostAddress;
            $optoutHistory->confirm_user_agent = StringHelper::truncateLength($request->userAgent, 255);
            $optoutHistory->confirm_date       = new CDbExpression('NOW()');
            $optoutHistory->save(false);

        } catch (Exception $e) {
            Yii::log($e->getMessage(), CLogger::LEVEL_ERROR);
        }

        return $this;
    }

    /**
     * @return int
     */
    public function removeOptoutHistory()
    {
        $this->_optoutHistory = null;
        return ListSubscriberOptoutHistory::model()->deleteAllByAttributes(array(
            'subscriber_id' => (int)$this->subscriber_id,
        ));
    }

    /**
     * @param int $size
     * @return string
     */
    public function getAvatarUrl($size = 120)
    {
        return sprintf('https://www.gravatar.com/avatar/%s?f=y&d=mm&s=%d', md5($this->email), (int)$size);
    }

    /**
     * @return string
     * @throws CException
     */
    public function getFullName()
    {
        $subscriberName = sprintf('%s %s', $this->getCustomFieldValue('FNAME'), $this->getCustomFieldValue('LNAME'));
        $subscriberName = trim($subscriberName);
        if (!empty($subscriberName)) {
            return $subscriberName;
        }

        $subscriberName = sprintf('%s %s', $this->getCustomFieldValue('FIRST_NAME'), $this->getCustomFieldValue('LAST_NAME'));
        $subscriberName = trim($subscriberName);
        if (!empty($subscriberName)) {
            return $subscriberName;
        }

        $subscriberName = $this->getCustomFieldValue('NAME');
        if (!empty($subscriberName)) {
            return $subscriberName;
        }

        $subscriberName = '';
        if (!empty($this->email)) {
            $subscriberName = explode('@', $this->email);
            $subscriberName = $subscriberName[0];
            $subscriberName = str_replace(array('_', '-', '.'), ' ', $subscriberName);
            $subscriberName = ucwords(strtolower($subscriberName));
        }

        return $subscriberName;
    }

    /**
     * @return array
     */
    public function getEmailMxRecords()
    {
        return NetDnsHelper::getHostMxRecords($this->getEmailHostname());
    }

    /**
     * @return string
     */
    public function getEmailHostname()
    {
        if (empty($this->email) || strpos($this->email, '@') === false) {
            return '';
        }
        $hostname = explode('@', $this->email);
        return $hostname[1];
    }

    /**
     * @param null $ipAddress
     * @return bool
     */
    public function saveIpAddress($ipAddress = null)
    {
        if (empty($this->subscriber_id)) {
            return false;
        }
        if ($ipAddress && $ipAddress == $this->ip_address) {
            return true;
        }
        if ($ipAddress) {
            $this->ip_address = $ipAddress;
        }
        $attributes = array('ip_address' => $this->ip_address);
        $this->last_updated = $attributes['last_updated'] = new CDbExpression('NOW()');
        return (bool)Yii::app()->getDb()->createCommand()->update($this->tableName(), $attributes, 'subscriber_id = :id', array(':id' => (int)$this->subscriber_id));
    }

    /**
     * @return array
     * @throws CException
     */
    public function getFullData()
    {
        $data = array();

        $customFields = $this->getAllCustomFieldsWithValues();
        foreach ($customFields as $key => $value) {
            $data[str_replace(array('[', ']'), '', $key)] = $value;
        }

        foreach (array('source', 'status', 'ip_address', 'date_added') as $key) {
            $data[strtoupper($key)] = $this->$key;
        }

        $optinData = array(
            'optin_ip'          => '',
            'optin_date'        => '',
            'optin_confirm_ip'  => '',
            'optin_confirm_date'=> ''
        );
        foreach ($optinData as $key => $value) {
            $data[strtoupper($key)] = $value;
        }
        if (!empty($this->optinHistory)) {
            foreach ($optinData as $key => $value) {
                $tag = strtoupper($key);
                if (in_array($key, array('optin_confirm_ip', 'optin_confirm_date'))) {
                    $key = str_replace('optin_', '', $key);
                }
                $data[$tag] = $this->optinHistory->$key;
            }
        }

        $optoutData = array(
            'optout_ip'           => '',
            'optout_date'         => '',
            'optout_confirm_ip'   => '',
            'optout_confirm_date' => ''
        );
        foreach ($optoutData as $key => $value) {
            $data[strtoupper($key)] = $value;
        }
        if ($this->status == self::STATUS_UNSUBSCRIBED && !empty($this->optoutHistory)) {
            foreach ($optoutData as $key => $value) {
                $tag = strtoupper($key);
                if (in_array($key, array('optout_confirm_ip', 'optout_confirm_date'))) {
                    $key = str_replace('optout_', '', $key);
                }
                $data[$tag] = $this->optoutHistory->$key;
            }
        }

        // 1.9.2
        $data['EMAIL'] = $this->getDisplayEmail();

        return $data;
    }
}
