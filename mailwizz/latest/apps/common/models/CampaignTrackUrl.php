<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * CampaignTrackUrl
 * 
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com> 
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.0
 */
 
/**
 * This is the model class for table "campaign_track_url".
 *
 * The followings are the available columns in table 'campaign_track_url':
 * @property string $id
 * @property string $url_id
 * @property integer $subscriber_id
 * @property integer $location_id
 * @property string $ip_address
 * @property string $user_agent
 * @property string $date_added
 *
 * The followings are the available model relations:
 * @property ListSubscriber $subscriber
 * @property IpLocation $ipLocation
 * @property CampaignUrl $url
 */
class CampaignTrackUrl extends ActiveRecord
{
    public $counter = 0;
    
    /**
     * @return string the associated database table name
     */
    public function tableName()
    {
        return '{{campaign_track_url}}';
    }

	/**
	 * @inheritdoc
	 */
    public function rules()
    {
        $rules = array();
        return CMap::mergeArray($rules, parent::rules());
    }

	/**
	 * @inheritdoc
	 */
    public function relations()
    {
        $relations = array(
            'subscriber' => array(self::BELONGS_TO, 'ListSubscriber', 'subscriber_id'),
            'ipLocation' => array(self::BELONGS_TO, 'IpLocation', 'location_id'),
            'url' => array(self::BELONGS_TO, 'CampaignUrl', 'url_id'),
        );
        
        return CMap::mergeArray($relations, parent::relations());
    }

	/**
	 * @inheritdoc
	 */
    public function attributeLabels()
    {
        $labels = array(
            'id'            => Yii::t('campaigns', 'ID'),
            'url_id'        => Yii::t('campaigns', 'Url'),
            'subscriber_id' => Yii::t('campaigns', 'Subscriber'),
            'location_id'   => Yii::t('campaigns', 'Location'),
            'ip_address'    => Yii::t('campaigns', 'Ip Address'),
            'user_agent'    => Yii::t('campaigns', 'User Agent'),
            'clicked_times' => Yii::t('campaigns', 'Clicked times'),
        );
        
        return CMap::mergeArray($labels, parent::attributeLabels());
    }

    /**
     * Returns the static model of the specified AR class.
     * Please note that you should have this exact method in all your CActiveRecord descendants!
     * @param string $className active record class name.
     * @return CampaignTrackUrl the static model class
     */
    public static function model($className=__CLASS__)
    {
        return parent::model($className);
    }
    
    public function getIpWithLocationForGrid()
    {
        if (empty($this->ipLocation)) {
            return $this->ip_address;
        }
        
        return $this->ip_address .' <br />('.$this->ipLocation->getLocation().')';
    }
}
