<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * CampaignSentActionSubscriber
 * 
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com> 
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.4.1
 */
 
/**
 * This is the model class for table "{{campaign_sent_action_subscriber}}".
 *
 * The followings are the available columns in table '{{campaign_sent_action_subscriber}}':
 * @property string $action_id
 * @property integer $campaign_id
 * @property integer $list_id
 * @property string $action
 * @property string $date_added
 * @property string $last_updated
 *
 * The followings are the available model relations:
 * @property Campaign $campaign
 * @property List $list
 */
class CampaignSentActionSubscriber extends ActiveRecord
{
    const ACTION_COPY = 'copy';
    
    const ACTION_MOVE = 'move';
    
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return '{{campaign_sent_action_subscriber}}';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		$rules = array(
			array('action, list_id', 'required'),
			array('action', 'length', 'max'=>5),
            array('action', 'in', 'range' => array_keys($this->getActions())),
            array('list_id', 'numerical', 'integerOnly' => true),
            array('list_id', 'exist', 'className' => 'Lists'),
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
			'list'     => array(self::BELONGS_TO, 'List', 'list_id'),
		);
        
        return CMap::mergeArray($relations, parent::relations());
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		$labels = array(
			'action_id'      => Yii::t('campaigns', 'Action'),
			'campaign_id'    => Yii::t('campaigns', 'Campaign'),
			'list_id'        => Yii::t('campaigns', 'To list'),
			'action'         => Yii::t('campaigns', 'Action'),
		);
        
        return CMap::mergeArray($labels, parent::attributeLabels());
	}
    
    public function attributeHelpTexts()
    {
        $texts = array(
            'list_id'   => Yii::t('campaigns', 'The target list for the selected action'),
			'action'    => Yii::t('campaigns', 'What action to take against the subscriber when he opens the campaign'),
        );
        
        return CMap::mergeArray($texts, parent::attributeHelpTexts());
    }
    
	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return CampaignOpenActionSubscriber the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
    
    public function getActions()
    {
        return array(
            self::ACTION_COPY => ucfirst(Yii::t('app', self::ACTION_COPY)),
            self::ACTION_MOVE => ucfirst(Yii::t('app', self::ACTION_MOVE)),
        );
    }
}
