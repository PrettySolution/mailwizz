<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * CampaignFilterOpenUnopen
 *
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.3.8.8
 */

/**
 * This is the model class for table "{{campaign_filter_open_unopen}}".
 *
 * The followings are the available columns in table '{{campaign_filter_open_unopen}}':
 * @property integer $campaign_id
 * @property string $action
 * @property integer $previous_campaign_id
 *
 * The followings are the available model relations:
 * @property Campaign $campaign
 * @property Campaign $previousCampaign
 */
class CampaignFilterOpenUnopen extends ActiveRecord
{
    /**
     * flag for open
     */
    const ACTION_OPEN   = 'open';

    /**
     * flag for unopen
     */
    const ACTION_UNOPEN = 'unopen';
    
    /**
     * @return string the associated database table name
     */
    public function tableName()
    {
        return '{{campaign_filter_open_unopen}}';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        $rules = array(
            array('campaign_id, action, previous_campaign_id', 'required'),
            array('campaign_id, previous_campaign_id', 'numerical', 'integerOnly' => true),
            array('campaign_id, previous_campaign_id', 'exist', 'className' => 'Campaign', 'attributeName' => 'campaign_id'),
            array('action', 'in', 'range' => array_keys($this->getActionsList())),
        );

        return CMap::mergeArray($rules, parent::rules());
    }

    /**
     * @return array relational rules.
     */
    public function relations()
    {
        $relations = array(
            'campaign'         => array(self::BELONGS_TO, 'Campaign', 'campaign_id'),
            'previousCampaign' => array(self::BELONGS_TO, 'Campaign', 'previous_campaign_id'),
        );

        return CMap::mergeArray($relations, parent::relations());
    }

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        $labels = array(
            'campaign_id'           => Yii::t('campaigns', 'Campaign'),
            'action'                => Yii::t('campaigns', 'Action'),
            'previous_campaign_id'  => Yii::t('campaigns', 'Previous campaign'),
        );

        return CMap::mergeArray($labels, parent::attributeLabels());
    }

    /**
     * Returns the static model of the specified AR class.
     * Please note that you should have this exact method in all your CActiveRecord descendants!
     * @param string $className active record class name.
     * @return CampaignFilterOpenUnopen the static model class
     */
    public static function model($className=__CLASS__)
    {
        return parent::model($className);
    }
    
    /**
     * @return array
     */
    public function getActionsList()
    {
        return array(
            self::ACTION_OPEN   => ucfirst(Yii::t('campaigns', self::ACTION_OPEN)),
            self::ACTION_UNOPEN => ucfirst(Yii::t('campaigns', self::ACTION_UNOPEN)),
        );
    }
}