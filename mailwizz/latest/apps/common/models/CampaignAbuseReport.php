<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * CampaignAbuseReport
 * 
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com> 
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.3.5
 */
 
/**
 * This is the model class for table "{{campaign_abuse_report}}".
 *
 * The followings are the available columns in table '{{campaign_abuse_report}}':
 * @property integer $report_id
 * @property integer $customer_id
 * @property integer $campaign_id
 * @property integer $list_id
 * @property integer $subscriber_id
 * @property string $customer_info
 * @property string $campaign_info
 * @property string $list_info
 * @property string $subscriber_info
 * @property string $reason
 * @property string $log
 * @property string $ip_address
 * @property string $user_agent
 * @property string $date_added
 * @property string $last_updated
 *
 * The followings are the available model relations:
 * @property Campaign $campaign
 * @property Customer $customer
 * @property List $list
 * @property ListSubscriber $subscriber
 */
class CampaignAbuseReport extends ActiveRecord
{
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return '{{campaign_abuse_report}}';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		$rules = array(
			array('reason', 'required'),
            array('reason', 'length', 'max' => 255),
            
            array('customer_id, campaign_id, list_id, customer_info, campaign_info, list_info, subscriber_info, reason, log, ip_address, user_agent', 'safe', 'on' => 'search'),
		);
        
        return CMap::mergeArray($rules, parent::rules());
	}

	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		$relations = array(
			'campaign'   => array(self::BELONGS_TO, 'Campaign', 'campaign_id'),
			'customer'   => array(self::BELONGS_TO, 'Customer', 'customer_id'),
			'list'       => array(self::BELONGS_TO, 'List', 'list_id'),
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
			'report_id'       => Yii::t('campaigns', 'Report'),
			'customer_id'     => Yii::t('campaigns', 'Customer'),
			'campaign_id'     => Yii::t('campaigns', 'Campaign'),
			'list_id'         => Yii::t('campaigns', 'List'),
			'subscriber_id'   => Yii::t('campaigns', 'Subscriber'),
			'customer_info'   => Yii::t('campaigns', 'Customer'),
			'campaign_info'   => Yii::t('campaigns', 'Campaign'),
			'list_info'       => Yii::t('campaigns', 'List'),
			'subscriber_info' => Yii::t('campaigns', 'Subscriber'),
			'reason'          => Yii::t('campaigns', 'Reason'),
            'log'             => Yii::t('campaigns', 'Log'),
            'ip_address'      => Yii::t('campaigns', 'Ip address'),
            'user_agent'      => Yii::t('campaigns', 'User agent'),
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
		$criteria=new CDbCriteria;
        
        $criteria->compare('customer_id', $this->customer_id);
        $criteria->compare('campaign_id', $this->campaign_id);
        $criteria->compare('list_id', $this->list_id);
        
		$criteria->compare('customer_info', $this->customer_info, true);
		$criteria->compare('campaign_info', $this->campaign_info, true);
		$criteria->compare('list_info', $this->list_info, true);
		$criteria->compare('subscriber_info', $this->subscriber_info, true);
		$criteria->compare('reason', $this->reason, true);

        $criteria->compare('ip_address', $this->ip_address, true);
        $criteria->compare('user_agent', $this->user_agent, true);
        
        $criteria->order = 'report_id DESC';
		
		return new CActiveDataProvider(get_class($this), array(
            'criteria'      => $criteria,
            'pagination'    => array(
                'pageSize'  => $this->paginationOptions->getPageSize(),
                'pageVar'   => 'page',
            ),
            'sort'  => array(
                'defaultOrder'  => array(
                    'report_id'   => CSort::SORT_DESC,
                ),
            ),
        ));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return CampaignAbuseReport the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
    
    public function addLog($log)
    {
        $this->log .= '[' . date('Y-m-d H:i:s') . '] - ' . $log . "\n";
        return $this;
    }
}
