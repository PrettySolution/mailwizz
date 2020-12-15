<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * CampaignResendGiveupQueue
 *
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.7.6
 */

/**
 * This is the model class for table "{{campaign_resend_giveup_queue}}".
 *
 * The followings are the available columns in table '{{campaign_resend_giveup_queue}}':
 * @property integer $campaign_id
 * @property string $date_added
 * @property string $last_updated
 *
 * The followings are the available model relations:
 * @property Campaign $campaign
 */
class CampaignResendGiveupQueue extends ActiveRecord
{
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return '{{campaign_resend_giveup_queue}}';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		$rules = array();
		return CMap::mergeArray($rules, parent::rules());
	}

	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		$relations = array(
			'campaign' => array(self::BELONGS_TO, 'Campaign', 'campaign_id'),
		);

		return CMap::mergeArray($relations, parent::relations());
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		$labels = array(
			'campaign_id' => Yii::t('campaigns', 'Campaign'),
		);

		return CMap::mergeArray($labels, parent::attributeLabels());
	}
	
	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return CampaignResendGiveupQueue the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
}
