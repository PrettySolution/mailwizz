<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * CampaignForwardFriend
 *
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.3.4.7
 */

/**
 * This is the model class for table "{{campaign_forward_friend}}".
 *
 * The followings are the available columns in table '{{campaign_forward_friend}}':
 * @property integer $forward_id
 * @property integer $campaign_id
 * @property integer $subscriber_id
 * @property string $to_email
 * @property string $to_name
 * @property string $from_email
 * @property string $from_name
 * @property string $subject
 * @property string $message
 * @property string $ip_address
 * @property string $user_agent
 * @property string $date_added
 * @property string $last_updated
 *
 * The followings are the available model relations:
 * @property Campaign $campaign
 * @property ListSubscriber $subscriber
 */
class CampaignForwardFriend extends ActiveRecord
{
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return '{{campaign_forward_friend}}';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
	   $rules = array(
			array('to_email, to_name, from_email, from_name, subject', 'required'),
			array('to_email, to_name, from_email, from_name', 'length', 'max' => 150),
            array('to_email, from_email', 'email', 'validateIDN' => true),
			array('subject', 'length', 'max' => 255),
            array('message', 'length', 'max' => 10000),

			// The following rule is used by search().
			array('campaign_id, subscriber_id, to_email, to_name, from_email, from_name, subject, ip_address', 'safe', 'on'=>'search'),
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
			'forward_id'     => Yii::t('campaigns', 'Forward'),
			'campaign_id'    => Yii::t('campaigns', 'Campaign'),
			'subscriber_id'  => Yii::t('campaigns', 'Subscriber'),
			'to_email'       => Yii::t('campaigns', 'To email'),
			'to_name'        => Yii::t('campaigns', 'To name'),
			'from_email'     => Yii::t('campaigns', 'From email'),
			'from_name'      => Yii::t('campaigns', 'From name'),
			'subject'        => Yii::t('campaigns', 'Subject'),
			'message'        => Yii::t('campaigns', 'Message'),
			'ip_address'     => Yii::t('campaigns', 'Ip address'),
			'user_agent'     => Yii::t('campaigns', 'User agent'),
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

        if (!empty($this->campaign_id)) {
            if (is_numeric($this->campaign_id)) {
                $criteria->compare('t.campaign_id', $this->campaign_id);
            } else {
                $criteria->with['campaign'] = array(
                    'together'  => true,
                    'joinType'  => 'INNER JOIN',
                    'condition' => '(campaign.campaign_uid = :cmp OR campaign.name = :cmp)',
                    'params'    => array(':cmp' => $this->campaign_id)
                );
            }
        }

        if (!empty($this->subscriber_id)) {
            if (is_numeric($this->subscriber_id)) {
                $criteria->compare('t.subscriber_id', $this->subscriber_id);
            } else {
                $criteria->with['subscriber'] = array(
                    'together'  => true,
                    'joinType'  => 'INNER JOIN',
                    'condition' => '(subscriber.subscriber_uid = :sub OR subscriber.email = :sub)',
                    'params'    => array(':sub' => $this->subscriber_id)
                );
            }
        }

		$criteria->compare('t.to_email', $this->to_email, true);
		$criteria->compare('t.to_name', $this->to_name, true);
		$criteria->compare('t.from_email', $this->from_email, true);
		$criteria->compare('t.from_name', $this->from_name, true);
		$criteria->compare('t.subject', $this->subject, true);
		$criteria->compare('t.ip_address', $this->ip_address, true);

		return new CActiveDataProvider(get_class($this), array(
            'criteria'   => $criteria,
            'pagination' => array(
                'pageSize' => $this->paginationOptions->getPageSize(),
                'pageVar'  => 'page',
            ),
            'sort'=>array(
                'defaultOrder' => array(
                    't.forward_id'  => CSort::SORT_DESC,
                ),
            ),
        ));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return CampaignForwardFriend the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
}
