<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * CampaignToDeliveryServer
 * 
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com> 
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.3.2
 */

/**
 * This is the model class for table "campaign_to_delivery_server".
 *
 * The followings are the available columns in table 'campaign_to_delivery_server':
 * @property integer $campaign_id
 * @property integer $server_id
 */
class CampaignToDeliveryServer extends ActiveRecord
{
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return '{{campaign_to_delivery_server}}';
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'campaign_id'    => Yii::t('campaigns', 'Campaign'),
			'server_id'      => Yii::t('servers', 'Server'),
		);
	}

	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		$relations = array(
			'campaign'          => array(self::BELONGS_TO, 'Campaign', 'campaign_id'),
			'deliveryServer'    => array(self::BELONGS_TO, 'DeliveryServer', 'server_id'),
		);

		return CMap::mergeArray($relations, parent::relations());
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return CampaignToDeliveryServer the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

    protected function beforeSave()
    {
        if (empty($this->campaign_id) || empty($this->server_id)) {
            return false;
        }
        
        $campaign = Campaign::model()->findByPk((int)$this->campaign_id);
        if (empty($campaign)) {
            return false;
        }
        
        $server = DeliveryServer::model()->findByPk((int)$this->server_id);
        if (empty($server)) {
            return false;
        }
        
        return parent::beforeSave();
    }
}
