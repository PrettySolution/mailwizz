<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * AllListsSubscribersFilters
 *
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.3.6.3
 */

class AllListsSubscribersFilters extends ListSubscriber
{
    /**
     * flag for view list
     */
    const ACTION_VIEW = 'view';
    
    /**
     * flag for export
     */
    const ACTION_EXPORT = 'export';

    /**
     * flag for confirm
     */
    const ACTION_CONFIRM = 'confirm';
    
    /**
     * flag for disable
     */
    const ACTION_DISABLE = 'disable';

    /**
     * flag for unsubscribe
     */
    const ACTION_UNSUBSCRIBE = 'unsubscribe';

    /**
     * flag for blacklist
     */
    const ACTION_BLACKLIST = 'blacklist';
    
    /**
     * flag for delete
     */
    const ACTION_DELETE = 'delete';

    /**
     * flag to create new list
     */
    const ACTION_CREATE_LIST = 'create-list';
    
    /**
     * @var $customer Customer
     */
    public $customer;

    /**
     * @var array $lists list id => list name
     */
    public $lists = array();

    /**
     * @var array $statuses - subscriber statuses
     */
    public $statuses = array();

    /**
     * @var array $sources - import sources
     */
    public $sources = array();

    /**
     * @var string $unique - only unique subs
     */
    public $unique;

    /**
     * @var string $uid 
     */
    public $uid;

    /**
     * @var string $ip
     */
    public $ip;

    /**
     * @var string $email
     */
    public $email;

    /**
     * @var string $action
     */
    public $action;

    /**
     * @var bool
     */
    public $hasSetFilters = false;

    /**
     * @var string 
     */
    public $campaigns_action;

    /**
     * @var array
     */
    public $campaigns;

    /**
     * @var string
     */
    public $campaigns_atuc;

    /**
     * @var string
     */
    public $campaigns_atu;

    /**
     * @var
     */
    public $date_added_start;

    /**
     * @var
     */
    public $date_added_end;
    
    /**
     * @return array
     */
    public function rules()
    {
        return array(
            array('lists', '_validateMultipleListsSelection'),
            array('statuses', '_validateMultipleStatusesSelection'),
            array('sources', '_validateMultipleSourcesSelection'),
            array('action', 'in', 'range' => array_keys($this->getActionsList())),
            array('unique', 'in', 'range' => array_keys($this->getYesNoOptions())),
            array('campaigns_action', 'in', 'range' => array_keys($this->getCampaignFilterActions())),
            array('campaigns_atu', 'in', 'range' => array_keys($this->getFilterTimeUnits())),
            array('campaigns_atuc', 'numerical', 'integerOnly' => true, 'min' => 1, 'max' => 1024),
            array('uid, email, ip, campaigns', 'safe'),
            array('date_added_start, date_added_end', 'date', 'format' => 'yyyy-M-d'),
        );
    }

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        return CMap::mergeArray(parent::attributeLabels(), array(
            'lists'          => Yii::t('list_subscribers', 'Lists'),
            'statuses'       => Yii::t('list_subscribers', 'Statuses'),
            'sources'        => Yii::t('list_subscribers', 'Sources'),
            'action'         => Yii::t('list_subscribers', 'Action'),
            'unique'         => Yii::t('list_subscribers', 'Unique'),
            'uid'            => Yii::t('list_subscribers', 'Unique ID'),
            'email'          => Yii::t('list_subscribers', 'Email'),
            'ip'             => Yii::t('list_subscribers', 'Ip Address'),

            'campaigns'         => Yii::t('list_subscribers', 'Campaigns'),
            'campaigns_action'  => Yii::t('list_subscribers', 'Campaigns Action'),
            'campaigns_atuc'    => '',
            'campaigns_atu'     => '',
            
            'date_added_start' => Yii::t('list_subscribers', 'Date added start'),
            'date_added_end'   => Yii::t('list_subscribers', 'Date added end'),
        ));
    }

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributePlaceholders()
    {
        return array(
            'uid'   => 'jm338w77e4eea',
            'email' => 'name@domain.com',
            'ip'    => '123.123.123.100',
        );
    }

    /**
     * @inheritdoc
     */
    public function beforeValidate()
    {
        return true;
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
     * @return array
     */
    public function getListsList()
    {
        static $listsList = array();
        
        if (!empty($listsList[$this->customer->customer_id])) {
            return $listsList[$this->customer->customer_id];
        }
        
        $criteria = new CDbCriteria();
        $criteria->compare('customer_id', $this->customer->customer_id);
        $criteria->addNotInCondition('status', array(Lists::STATUS_PENDING_DELETE, Lists::STATUS_ARCHIVED));
        
        $lists = Lists::model()->findAll($criteria);
        foreach ($lists as $list) {
            $listsList[$this->customer->customer_id][$list->list_id] = $list->name . '(' . $list->display_name . ')';
        }
        
        return $listsList[$this->customer->customer_id];
    }
    
    /**
     * @return array
     */
    public function getStatusesList()
    {
        return $this->getEmptySubscriberModel()->getFilterStatusesList();
    }

    /**
     * @return array
     */
    public function getSourcesList()
    {
        return $this->getEmptySubscriberModel()->getSourcesList();
    }

    /**
     * @return array
     */
    public function getActionsList()
    {
        $actions = array(
            self::ACTION_VIEW        => Yii::t('list_subscriber', ucfirst(self::ACTION_VIEW)),
            self::ACTION_EXPORT      => Yii::t('list_subscriber', ucfirst(self::ACTION_EXPORT)),
            self::ACTION_CREATE_LIST => Yii::t('list_subscriber', 'Create list'),
            self::ACTION_CONFIRM     => Yii::t('list_subscriber', ucfirst(self::ACTION_CONFIRM)),
            self::ACTION_DISABLE     => Yii::t('list_subscriber', ucfirst(self::ACTION_DISABLE)),
            self::ACTION_UNSUBSCRIBE => Yii::t('list_subscriber', ucfirst(self::ACTION_UNSUBSCRIBE)),
            self::ACTION_BLACKLIST   => Yii::t('list_subscriber', ucfirst(self::ACTION_BLACKLIST)),
            self::ACTION_DELETE      => Yii::t('list_subscriber', ucfirst(self::ACTION_DELETE)),
        );

        $canExport = $this->customer->getGroupOption('lists.can_export_subscribers', 'yes') == 'yes';
        if (!$canExport) {
            unset($actions[self::ACTION_EXPORT]);
        }
        
        $canBlacklist = $this->customer->getGroupOption('lists.can_use_own_blacklist', 'no') == 'yes';
        if (!$canBlacklist) {
            unset($actions[self::ACTION_BLACKLIST]);
        }

        $canDelete = $this->customer->getGroupOption('lists.can_delete_own_subscribers', 'yes') == 'yes';
        if (!$canDelete) {
            unset($actions[self::ACTION_DELETE]);
        }

        $canCreateList = $this->customer->getGroupOption('lists.can_create_list_from_filters', 'yes') == 'yes';
        if (!$canCreateList) {
            unset($actions[self::ACTION_CREATE_LIST]);
        }
        
        return $actions;
    }

	/**
	 * @return string
	 */
	public function getIsViewAction()
	{
		return empty($this->action) || $this->action == self::ACTION_VIEW;
	}
	
    /**
     * @return string
     */
    public function getIsExportAction()
    {
        return $this->action == self::ACTION_EXPORT;
    }

    /**
     * @return string
     */
    public function getIsConfirmAction()
    {
        return $this->action == self::ACTION_CONFIRM;
    }

    /**
     * @return string
     */
    public function getIsUnsubscribeAction()
    {
        return $this->action == self::ACTION_UNSUBSCRIBE;
    }

    /**
     * @return string
     */
    public function getIsDisableAction()
    {
        return $this->action == self::ACTION_DISABLE;
    }

    /**
     * @return bool
     */
    public function getIsBlacklistAction()
    {
        return $this->action == self::ACTION_BLACKLIST;
    }
    
    /**
     * @return bool
     */
    public function getIsDeleteAction()
    {
        return $this->action == self::ACTION_DELETE;
    }

    /**
     * @return bool
     */
    public function getIsCreateListAction()
    {
        return $this->action == self::ACTION_CREATE_LIST;
    }

    /**
     * @return ListSubscriber
     */
    public function getEmptySubscriberModel()
    {
        static $subscriber;
        if ($subscriber !== null) {
            return $subscriber;
        }
        return $subscriber = new ListSubscriber();
    }

    /**
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function getSubscribers($limit = 1000, $offset = 0)
    {
        $criteria = $this->buildSubscribersCriteria();
        $criteria->limit  = $limit;
        $criteria->offset = $offset;
        return ListSubscriber::model()->findAll($criteria);
    }

    /**
     * @param $isCount bool 
     * @return CDbCriteria
     */
    public function buildSubscribersCriteria($isCount = false)
    {
        $lists = $this->lists;
        if (empty($this->lists)) {
            $lists = array_keys($this->getListsList());
        }
        $lists = array_filter(array_unique(array_map('intval', $lists)));
        if (empty($lists)) {
            $lists = array(0);
        }
        
        $criteria = new CDbCriteria();
        
        $criteria->addInCondition('t.list_id', $lists);
        $criteria->compare('t.subscriber_uid', $this->uid, true);
        
        // 1.3.7.1
        if (!empty($this->email)) {
            if (strpos($this->email, ',') !== false) {
                $emails = CommonHelper::getArrayFromString($this->email, ',');
                foreach ($emails as $index => $email) {
                    if (!FilterVarHelper::email($email)) {
                        unset($emails[$index]);
                    }
                }
                if (!empty($emails)) {
                    $criteria->addInCondition('t.email', $emails);
                }
            } else {
                $criteria->compare('t.email', $this->email, true);
            }
        }
        //
        
        $criteria->compare('t.ip_address', $this->ip, true);
        
        if (!empty($this->statuses) && is_array($this->statuses)) {
            $criteria->addInCondition('t.status', $this->statuses);
        }

        if (!empty($this->sources) && is_array($this->sources)) {
            $criteria->addInCondition('t.source', $this->sources);
        }
        
        if (!empty($this->date_added_start)) {
            $criteria->compare('DATE(t.date_added)', '>=' . date('Y-m-d', strtotime($this->date_added_start)));
        }
        
        if (!empty($this->date_added_end)) {
            $criteria->compare('DATE(t.date_added)', '<=' . date('Y-m-d', strtotime($this->date_added_end)));
        }

        if (!empty($this->campaigns_action)) {
            $action = $this->campaigns_action;
            
            $campaignIds = array();
            if (!empty($this->campaigns) && is_array($this->campaigns)) {
                $campaignIds = array_filter(array_unique(array_map('intval', $this->campaigns)));
            }
            if (empty($campaignIds)) {
                $campaignIds = array_keys($this->getCampaignsList());
            }
            if (empty($campaignIds)) {
                $campaignIds = array(0);
            }
            
            $atu  = $this->getFilterTimeUnitValueForDb((int)$this->campaigns_atu);
            $atuc = (int)$this->campaigns_atuc;
            $atuc = $atuc > 1024 ? 1024 : $atuc;
            $atuc = $atuc < 0 ? 0 : $atuc;

            if (in_array($action, array(self::CAMPAIGN_FILTER_ACTION_DID_OPEN, self::CAMPAIGN_FILTER_ACTION_DID_NOT_OPEN))) {
                $rel = array(
                    'select'   => false,
                    'together' => true,
                );

                if ($action == self::CAMPAIGN_FILTER_ACTION_DID_OPEN) {
                    $rel['joinType']  = 'INNER JOIN';
                    $rel['condition'] = 'trackOpens.campaign_id IN (' . implode(',', $campaignIds) . ')';
                    if (!empty($atuc)) {
                        $rel['condition'] .= sprintf(' AND trackOpens.date_added >= DATE_SUB(NOW(), INTERVAL %d %s)', $atuc, $atu);
                    }
                } else {
                    $rel['on']        = 'trackOpens.campaign_id IN (' . implode(',', $campaignIds) . ')';
                    $rel['joinType']  = 'LEFT OUTER JOIN';
                    $rel['condition'] = 'trackOpens.subscriber_id IS NULL';
                    if (!empty($atuc)) {
                        $rel['condition'] .= sprintf(' OR (trackOpens.subscriber_id IS NOT NULL AND (SELECT date_added FROM {{campaign_track_open}} WHERE subscriber_id = trackOpens.subscriber_id ORDER BY date_added DESC LIMIT 1) <= DATE_SUB(NOW(), INTERVAL %d %s))', $atuc, $atu);
                    }
                }

                $criteria->with['trackOpens'] = $rel;
            }

            if (in_array($action, array(self::CAMPAIGN_FILTER_ACTION_DID_CLICK, self::CAMPAIGN_FILTER_ACTION_DID_NOT_CLICK))) {

                $ucriteria = new CDbCriteria();
                $ucriteria->select = 'url_id';
                $ucriteria->addInCondition('campaign_id', $campaignIds);
                $models = CampaignUrl::model()->findAll($ucriteria);
                $urlIds = array();
                foreach ($models as $model) {
                    $urlIds[] = $model->url_id;
                }
                
                if (empty($urlIds)) {
                    $urlIds = array(0);
                }

                $rel = array(
                    'select'   => false,
                    'together' => true,
                );

                if ($action == self::CAMPAIGN_FILTER_ACTION_DID_CLICK) {
                    $rel['joinType']  = 'INNER JOIN';
                    $rel['condition'] = 'trackUrls.url_id IN (' . implode(',', $urlIds) . ')';
                    if (!empty($atuc)) {
                        $rel['condition'] .= sprintf(' AND trackUrls.date_added >= DATE_SUB(NOW(), INTERVAL %d %s)', $atuc, $atu);
                    }
                } else {
                    $rel['on']        = 'trackUrls.url_id IN (' . implode(',', $urlIds) . ')';
                    $rel['joinType']  = 'LEFT OUTER JOIN';
                    $rel['condition'] = 'trackUrls.subscriber_id IS NULL';
                    if (!empty($atuc)) {
                        $rel['condition'] .= sprintf(' OR (trackUrls.subscriber_id IS NOT NULL AND (SELECT date_added FROM {{campaign_track_url}} WHERE subscriber_id = trackUrls.subscriber_id ORDER BY date_added DESC LIMIT 1) <= DATE_SUB(NOW(), INTERVAL %d %s))', $atuc, $atu);
                    }
                }

                $criteria->with['trackUrls'] = $rel;
                $this->unique = self::TEXT_YES;
            }

	        if (in_array($action, array(self::CAMPAIGN_FILTER_ACTION_DID_OPEN, self::CAMPAIGN_FILTER_ACTION_DID_NOT_OPEN, self::CAMPAIGN_FILTER_ACTION_DID_CLICK, self::CAMPAIGN_FILTER_ACTION_DID_NOT_CLICK))) {
		        $criteria->with['deliveryLogs'] = array(
			        'joinType'  => 'LEFT JOIN',
		        );
		        $criteria->with['deliveryLogsArchive'] = array(
			        'joinType'  => 'LEFT JOIN',
		        );
		        $criteria->addCondition('(
	                EXISTS(SELECT subscriber_id FROM {{campaign_delivery_log}} WHERE subscriber_id = t.subscriber_id LIMIT 1)
	                OR
	                EXISTS(SELECT subscriber_id FROM {{campaign_delivery_log_archive}} WHERE subscriber_id = t.subscriber_id LIMIT 1)
	            )');
	        }
        }

        if ($this->unique == self::TEXT_YES) {
            $criteria->group = 't.email';
        } else {
            $criteria->group = '';
        }
        
        $criteria->order  = 't.subscriber_id DESC';

        // 1.5.0
        if ($isCount && $this->unique == self::TEXT_YES) {
            $criteria->select = 'COUNT(DISTINCT(t.email)) as count';
            $criteria->group  = '';
        }
        
        return $criteria;
    }

    /**
     * @return CActiveDataProvider
     */
    public function getActiveDataProvider()
    {
        return new CActiveDataProvider(get_class($this), array(
            'criteria'      => $this->buildSubscribersCriteria(),
            'countCriteria' => $this->buildSubscribersCriteria(true),
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
     * @return array
     */
    public function getCampaignsList()
    {
        $lists = array_keys($this->getListsList());
        if (empty($lists)) {
            $lists = array(0);
        }
        
        $criteria = new CDbCriteria();
        $criteria->select = 'campaign_id, name';
        $criteria->addInCondition('list_id', $lists);
        $criteria->addInCondition('status', array(Campaign::STATUS_SENT, Campaign::STATUS_SENDING));
        $criteria->order = 'campaign_id DESC';
        $models = Campaign::model()->findAll($criteria);

        $campaigns = array();
        foreach ($models as $campaign) {
            $campaigns[$campaign->campaign_id] = $campaign->name;
        }
        
        return $campaigns;
    }

    /**
     * @param array $subscribersIds
     */
    public function confirmSubscribersByIds(array $subscribersIds = array())
    {
        try {
            $subscribersIds       = array_filter(array_unique(array_map('intval', $subscribersIds)));
            $canMarkBlAsConfirmed = $this->customer->getGroupOption('lists.can_mark_blacklisted_as_confirmed', 'no') === 'yes';
            
            // get all blacklisted subscribers
            $command     = Yii::app()->db->createCommand();
            $subscribers = $command->select('email')->from('{{list_subscriber}}')->where(array('and',
                array('in', 'subscriber_id', $subscribersIds),
                array('in', 'status', array(ListSubscriber::STATUS_BLACKLISTED)),
            ))->queryAll();
            
            if (!empty($subscribers)) {
                
                $emails = array();
                foreach ($subscribers as $subscriber) {
                    $emails[] = $subscriber['email'];
                }
                
                $emails = array_chunk($emails, 100);
                
                foreach ($emails as $emailsChunk) {

                    // delete from customer blacklist
	                Yii::app()->db->createCommand()->delete('{{customer_email_blacklist}}', array('and',
		                array('in', 'email', $emailsChunk),
		                array('in', 'customer_id', array($this->customer->customer_id)),
	                ));

                    if (!$canMarkBlAsConfirmed) {
                        continue;
                    }

	                // delete from global blacklist if allowed.
	                Yii::app()->db->createCommand()->delete('{{email_blacklist}}', array('and',
		                array('in', 'email', $emailsChunk),
	                ));
                }    
            }
            
            // statuses that are not allowed to be marked confirmed
            $notInStatus = array(
                ListSubscriber::STATUS_CONFIRMED,
                ListSubscriber::STATUS_UNSUBSCRIBED,
            );
            
            $command = Yii::app()->db->createCommand();
            $command->update('{{list_subscriber}}', array(
                'status'        => ListSubscriber::STATUS_CONFIRMED,
                'last_updated'  => new CDbExpression('NOW()'),
            ), array('and',
                array('in', 'subscriber_id', $subscribersIds),
                array('not in', 'status', $notInStatus),
            ));

            // 1.3.8.8 - remove from moved table
            $_criteria = new CDbCriteria();
            $_criteria->addInCondition('source_subscriber_id', $subscribersIds);
            ListSubscriberListMove::model()->deleteAll($_criteria);
            
        } catch (Exception $e) {

        }

        // since 1.6.4
        Lists::flushSubscribersCountCacheBySubscriberIds($subscribersIds);
    }
    
    /**
     * @param array $subscribersIds
     */
    public function unsubscribeSubscribersByIds(array $subscribersIds = array())
    {
        $subscribersIds = array_filter(array_unique(array_map('intval', $subscribersIds)));
        try {
            $command = Yii::app()->db->createCommand();
            $command->update('{{list_subscriber}}', array(
                'status'        => ListSubscriber::STATUS_UNSUBSCRIBED,
                'last_updated'  => new CDbExpression('NOW()'),
            ), array('and',
                array('in', 'subscriber_id', $subscribersIds),
                array('in', 'status', array(ListSubscriber::STATUS_CONFIRMED)),
            ));
        } catch (Exception $e) {

        }

        // since 1.6.4
        Lists::flushSubscribersCountCacheBySubscriberIds($subscribersIds);
    }
    
    /**
     * @param array $subscribersIds
     */
    public function disableSubscribersByIds(array $subscribersIds = array())
    {
        $subscribersIds = array_filter(array_unique(array_map('intval', $subscribersIds)));
        try {
            $command = Yii::app()->db->createCommand();
            $command->update('{{list_subscriber}}', array(
                'status'        => ListSubscriber::STATUS_DISABLED,
                'last_updated'  => new CDbExpression('NOW()'),
            ), array('and', 
                array('in', 'subscriber_id', $subscribersIds), 
                array('in', 'status', array(ListSubscriber::STATUS_CONFIRMED))
            ));
        } catch (Exception $e) {
        
        }

        // since 1.6.4
        Lists::flushSubscribersCountCacheBySubscriberIds($subscribersIds);
    }
    
    /**
     * @param array $subscribers
     */
    public function blacklistSubscribers(array $subscribers = array())
    {
        $subscribersIds = array();
        foreach ($subscribers as $index => $subscriber) {
            if (!isset($subscriber['subscriber_id'], $subscriber['email'])) {
                unset($subscribers[$index]);
                continue;
            }
            $subscribersIds[] = $subscriber['subscriber_id'];
        }

        $subscribersIds = array_filter(array_unique(array_map('intval', $subscribersIds)));
        
        try {
            $command = Yii::app()->db->createCommand();
            $command->update('{{list_subscriber}}', array(
                'status'        => ListSubscriber::STATUS_BLACKLISTED,
                'last_updated'  => new CDbExpression('NOW()'),
            ), array('and',
                array('in', 'subscriber_id', $subscribersIds),
                array('not in', 'status', array(ListSubscriber::STATUS_BLACKLISTED, ListSubscriber::STATUS_MOVED))
            ));

            foreach ($subscribers as $subscriber) {
                try {
                    $customerEmailBlacklist = new CustomerEmailBlacklist();
                    $customerEmailBlacklist->customer_id = $this->customer->customer_id;
                    $customerEmailBlacklist->email       = $subscriber['email'];
                    $customerEmailBlacklist->save();
                } catch(Exception $e) {}
            }

        } catch (Exception $e) {

        }

        // since 1.6.4
        Lists::flushSubscribersCountCacheBySubscriberIds($subscribersIds);
    }

    /**
     * @param array $subscribersIds
     * @return int
     */
    public function deleteSubscribersByIds(array $subscribersIds = array())
    {
        $subscribersIds = array_filter(array_unique(array_map('intval', $subscribersIds)));

        // since 1.6.4
        Lists::flushSubscribersCountCacheBySubscriberIds($subscribersIds);

        $command = Yii::app()->db->createCommand();
        $subscribers = $command->select('email')->from('{{list_subscriber}}')->where(array('and',
            array('in', 'subscriber_id', $subscribersIds),
            array('in', 'status', array(ListSubscriber::STATUS_BLACKLISTED)),
        ))->queryAll();

        if (!empty($subscribers)) {
            $emails = array();
            foreach ($subscribers as $subscriber) {
                $emails[] = $subscriber['email'];
            }
            $emails = array_chunk($emails, 100);
            foreach ($emails as $emailsChunk) {
                $command = Yii::app()->db->createCommand();
                $command->delete('{{customer_email_blacklist}}', array('and',
                    array('in', 'email', $emailsChunk),
                    array('in', 'customer_id', array($this->customer->customer_id)),
                ));
            }
        }
        
        $count = 0;
        try {
            $command = Yii::app()->db->createCommand();
            $count   = $command->delete('{{list_subscriber}}', array('and',
                array('in', 'subscriber_id', $subscribersIds),
            ));
        } catch (Exception $e) {

        }

        return $count;
    }

    /**
     * @return string
     */
    public function getDatePickerFormat()
    {
        return 'yy-mm-dd';
    }

    /**
     * @return array|string
     */
    public function getDatePickerLanguage()
    {
        $language = Yii::app()->getLanguage();
        if (strpos($language, '_') === false) {
            return $language;
        }
        $language = explode('_', $language);
        
        return $language[0];
    }
    
    /**
     * @param $attribute
     * @param $params
     */
    public function _validateMultipleListsSelection($attribute, $params)
    {
        $values = $this->$attribute;
        if (empty($values) || !is_array($values)) {
            $values = array();
        }
        
        $lists = array_keys($this->getListsList());
        
        foreach ($values as $index => $value) {
            if (!in_array($value, $lists)) {
                $this->addError($attribute, Yii::t('list_subscribers', 'Invalid list identifier!'));
                break;
            }
        }
    }

    /**
     * @param $attribute
     * @param $params
     */
    public function _validateMultipleStatusesSelection($attribute, $params)
    {
        $values = $this->$attribute;
        if (empty($values) || !is_array($values)) {
            return;
        }
        
	    $this->$attribute = $values = array_filter(array_unique(array_values($values)));
	    if (empty($values)) {
		    return;
	    }
	    
        $statuses = array_keys($this->getStatusesList());

        foreach ($values as $index => $value) {
            if (!in_array($value, $statuses)) {
                $this->addError($attribute, Yii::t('list_subscribers', 'Invalid subscriber status!'));
                break;
            }
        }
    }

    /**
     * @param $attribute
     * @param $params
     */
    public function _validateMultipleSourcesSelection($attribute, $params)
    {
        $values = $this->$attribute;
        if (empty($values) || !is_array($values)) {
            return;
        }
        
	    $this->$attribute = $values = array_filter(array_unique(array_values($values)));
        if (empty($values)) {
        	return;
        }

        $statuses = array_keys($this->getSourcesList());
        foreach ($values as $index => $value) {
            if (!in_array($value, $statuses)) {
                $this->addError($attribute, Yii::t('list_subscribers', 'Invalid list source!'));
                break;
            }
        }
    }
}
