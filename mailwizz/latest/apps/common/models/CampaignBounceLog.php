<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * CampaignBounceLog
 * 
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com> 
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.0
 */
 
/**
 * This is the model class for table "campaign_bounce_log".
 *
 * The followings are the available columns in table 'campaign_bounce_log':
 * @property string $log_id
 * @property integer $campaign_id
 * @property integer $subscriber_id
 * @property string $message
 * @property string $bounce_type
 * @property string $processed
 * @property string $date_added
 *
 * The followings are the available model relations:
 * @property Campaign $campaign
 * @property ListSubscriber $subscriber
 */
class CampaignBounceLog extends ActiveRecord
{
    const BOUNCE_INTERNAL = 'internal';
    
    const BOUNCE_SOFT = 'soft';

    const BOUNCE_HARD = 'hard';

    public $customer_id;

    public $list_id;

    public $segment_id;

    /**
     * @return string the associated database table name
     */
    public function tableName()
    {
        return '{{campaign_bounce_log}}';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        $rules = array(
            array('bounce_type', 'safe', 'on' => 'customer-search'),
            array('customer_id, campaign_id, list_id, segment_id, subscriber_id, message, processed, bounce_type', 'safe', 'on' => 'search'),
        );

        return CMap::mergeArray($rules, parent::rules());
    }

    /**
     * @return array relational rules.
     */
    public function relations()
    {
        $relations = array(
            'campaign' => array(self::BELONGS_TO, 'Campaign', 'campaign_id'),
            'subscriber' => array(self::BELONGS_TO, 'ListSubscriber', 'subscriber_id'),
        );
        return CMap::mergeArray($relations, parent::relations());
    }

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        $labels = array(
            'log_id' => Yii::t('campaigns', 'Log'),
            'campaign_id' => Yii::t('campaigns', 'Campaign'),
            'subscriber_id' => Yii::t('campaigns', 'Subscriber'),
            'message' => Yii::t('campaigns', 'Message'),
            'processed' => Yii::t('campaigns', 'Processed'),
            'bounce_type' => Yii::t('campaigns', 'Bounce type'),

            // search
            'customer_id' => Yii::t('campaigns', 'Customer'),
            'list_id' => Yii::t('campaigns', 'List'),
            'segment_id' => Yii::t('campaigns', 'Segment'),
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
    public function customerSearch()
    {
        $criteria = new CDbCriteria;
        $criteria->compare('campaign_id', (int)$this->campaign_id);
        $criteria->compare('bounce_type', $this->bounce_type);

        return new CActiveDataProvider(get_class($this), array(
            'criteria' => $criteria,
            'pagination' => array(
                'pageSize' => $this->paginationOptions->getPageSize(),
                'pageVar' => 'page',
            ),
            'sort' => array(
                'defaultOrder' => array(
                    'log_id' => CSort::SORT_DESC,
                ),
            ),
        ));
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
        $criteria->select = 't.message, t.processed, t.bounce_type, t.date_added';
        $criteria->with = array(
            'campaign' => array(
                'select' => 'campaign.name, campaign.list_id, campaign.segment_id',
                'joinType' => 'INNER JOIN',
                'together' => true,
                'with' => array(
                    'list' => array(
                        'select' => 'list.name',
                        'joinType' => 'INNER JOIN',
                        'together' => true,
                    ),
                    'customer' => array(
                        'select' => 'customer.customer_id, customer.first_name, customer.last_name',
                        'joinType' => 'INNER JOIN',
                        'together' => true,
                    ),
                ),
            ),
            'subscriber' => array(
                'select' => 'subscriber.email',
                'joinType' => 'INNER JOIN',
                'together' => true,
            ),
        );

        if ($this->customer_id && is_numeric($this->customer_id)) {
            $criteria->with['campaign']['with']['customer'] = array_merge($criteria->with['campaign']['with']['customer'], array(
                'condition' => 'customer.customer_id = :customerId',
                'params' => array(':customerId' => $this->customer_id),
            ));
        } elseif ($this->customer_id && is_string($this->customer_id)) {
            $criteria->with['campaign']['with']['customer'] = array_merge($criteria->with['campaign']['with']['customer'], array(
                'condition' => 'CONCAT(customer.first_name, " ", customer.last_name) LIKE :customerName',
                'params' => array(':customerName' => '%' . $this->customer_id . '%'),
            ));
        }

        if ($this->campaign_id && is_numeric($this->campaign_id)) {
            $criteria->with['campaign'] = array_merge($criteria->with['campaign'], array(
                'condition' => 'campaign.campaign_id = :campaignId',
                'params' => array(':campaignId' => $this->campaign_id),
            ));
        } elseif ($this->campaign_id && is_string($this->campaign_id)) {
            $criteria->with['campaign'] = array_merge($criteria->with['campaign'], array(
                'condition' => 'campaign.name LIKE :campaignName',
                'params' => array(':campaignName' => '%' . $this->campaign_id . '%'),
            ));
        }

        if ($this->list_id && is_numeric($this->list_id)) {
            $criteria->with['campaign']['with']['list'] = array_merge($criteria->with['campaign']['with']['list'], array(
                'condition' => 'list.list_id = :listId',
                'params' => array(':listId' => $this->list_id),
            ));
        } elseif ($this->list_id && is_string($this->list_id)) {
            $criteria->with['campaign']['with']['list'] = array_merge($criteria->with['campaign']['with']['list'], array(
                'condition' => 'list.name LIKE :listName',
                'params' => array(':listName' => '%' . $this->list_id . '%'),
            ));
        }

        if ($this->segment_id && is_numeric($this->segment_id)) {
            $criteria->with['campaign']['with']['segment'] = array(
                'condition' => 'segment.segment_id = :segmentId',
                'params' => array(':segmentId' => $this->segment_id),
            );
        } elseif ($this->segment_id && is_string($this->segment_id)) {
            $criteria->with['campaign']['with']['segment'] = array(
                'condition' => 'segment.name LIKE :segmentId',
                'params' => array(':segmentId' => '%' . $this->segment_id . '%'),
            );
        }

        if ($this->subscriber_id && is_numeric($this->subscriber_id)) {
            $criteria->with['subscriber'] = array_merge($criteria->with['subscriber'], array(
                'condition' => 'subscriber.subscriber_id = :subscriberId',
                'params' => array(':subscriberId' => $this->subscriber_id),
            ));
        } elseif ($this->subscriber_id && is_string($this->subscriber_id)) {
            $criteria->with['subscriber'] = array_merge($criteria->with['subscriber'], array(
                'condition' => 'subscriber.email LIKE :subscriberId',
                'params' => array(':subscriberId' => '%' . $this->subscriber_id . '%'),
            ));
        }

        $criteria->compare('t.message', $this->message, true);
        $criteria->compare('t.processed', $this->processed);
        $criteria->compare('t.bounce_type', $this->bounce_type);

        $criteria->order = 't.log_id DESC';

        return new CActiveDataProvider(get_class($this), array(
            'criteria' => $criteria,
            'pagination' => array(
                'pageSize' => $this->paginationOptions->getPageSize(),
                'pageVar' => 'page',
            ),
            'sort' => array(
                'defaultOrder' => array(
                    't.log_id' => CSort::SORT_DESC,
                ),
            ),
        ));
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
    public function searchLight()
    {
        $criteria = new CDbCriteria;
        $criteria->order = 't.log_id DESC';

        return new CActiveDataProvider(get_class($this), array(
            'criteria' => $criteria,
            'pagination' => array(
                'pageSize' => $this->paginationOptions->getPageSize(),
                'pageVar' => 'page',
            ),
            'sort' => array(
                'defaultOrder' => array(
                    't.log_id' => CSort::SORT_DESC,
                ),
            ),
        ));
    }

    /**
     * Returns the static model of the specified AR class.
     * Please note that you should have this exact method in all your CActiveRecord descendants!
     * @param string $className active record class name.
     * @return CampaignBounceLog the static model class
     */
    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    /**
     * @inheritdoc
     */
    protected function beforeSave()
    {
        if ($this->looksLikeInternalBounce()) {
            $this->bounce_type = self::BOUNCE_INTERNAL;
        }
        
        return parent::beforeSave();
    }
    
    public function getBounceTypesArray()
    {
	    $types = array(
            self::BOUNCE_INTERNAL => Yii::t('campaigns', self::BOUNCE_INTERNAL),
            self::BOUNCE_SOFT     => Yii::t('campaigns', self::BOUNCE_SOFT),
            self::BOUNCE_HARD     => Yii::t('campaigns', self::BOUNCE_HARD),
        );
        
        return Yii::app()->hooks->applyFilters('campaign_bounce_logs_get_bounce_types_list', $types);
    }
    
    public function looksLikeInternalBounce()
    {
        if ($this->bounce_type == self::BOUNCE_INTERNAL) {
            return true;
        }

        if (empty($this->message)) {
            return false;
        }
        
        $rules = array(
            '/unsolicited mail/i',
            '/(spam|block(ed)?)/i',
            '/(DNSBL|RBL|CDRBL|Blacklist)/i'
        );
        
        foreach ($rules as $rule) {
            if (preg_match($rule, $this->message)) {
                return true;
            }
        }
        
        return false;
    }
}
