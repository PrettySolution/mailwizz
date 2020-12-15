<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * CampaignTrackUrlWebhook
 *
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.6.8
 */

/**
 * This is the model class for table "{{campaign_track_url_webhook}}".
 *
 * The followings are the available columns in table '{{campaign_track_url_webhook}}':
 * @property integer $webhook_id
 * @property integer $campaign_id
 * @property string $webhook_url
 * @property string $track_url
 * @property string $track_url_hash
 *
 * The followings are the available model relations:
 * @property Campaign $campaign
 * @property CampaignTrackUrlWebhookQueue[] $campaignTrackUrlWebhookQueues
 */
class CampaignTrackUrlWebhook extends ActiveRecord
{
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return '{{campaign_track_url_webhook}}';
	}

	/**
	 * @inheritdoc
	 */
	public function rules()
	{
		$rules = array(
			array('webhook_url, track_url', 'required'),
			array('webhook_url, track_url', 'length', 'max'=>255),
			array('webhook_url', 'url'),
		);

		return CMap::mergeArray($rules, parent::rules());
	}

	/**
	 * @inheritdoc
	 */
	public function relations()
	{
		$relations = array(
			'campaign'                      => array(self::BELONGS_TO, 'Campaign', 'campaign_id'),
			'campaignTrackUrlWebhookQueues' => array(self::HAS_MANY, 'CampaignTrackUrlWebhookQueue', 'webhook_id'),
		);

		return CMap::mergeArray($relations, parent::relations());
	}

	/**
	 * @inheritdoc
	 */
	public function attributeLabels()
	{
		$labels = array(
			'webhook_id'    => Yii::t('campaigns', 'Webhook'),
			'campaign_id'   => Yii::t('campaigns', 'Campaign'),
			'webhook_url'   => Yii::t('campaigns', 'Webhook url'),
			'track_url'     => Yii::t('campaigns', 'Url'),
		);

		return CMap::mergeArray($labels, parent::attributeLabels());
	}

	/**
	 * @inheritdoc
	 */
	protected function beforeSave() 
	{
		$this->track_url_hash = sha1($this->campaign->campaign_uid . $this->track_url);
		return parent::beforeSave();
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return CampaignTrackUrlWebhook the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
}
