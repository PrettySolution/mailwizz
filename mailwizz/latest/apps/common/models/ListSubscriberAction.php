<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * ListSubscriberAction
 * 
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com> 
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.3.5
 */
 
/**
 * This is the model class for table "{{list_subscriber_action}}".
 *
 * The followings are the available columns in table '{{list_subscriber_action}}':
 * @property integer $action_id
 * @property integer $source_list_id
 * @property string $source_action
 * @property integer $target_list_id
 * @property string $target_action
 *
 * The followings are the available model relations:
 * @property Lists $sourceList
 * @property Lists $targetList
 */
class ListSubscriberAction extends ActiveRecord
{
    const ACTION_SUBSCRIBE = 'subscribe';
    
    const ACTION_UNSUBSCRIBE = 'unsubscribe';
    
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return '{{list_subscriber_action}}';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		$rules = array(
			array('target_list_id', 'required'),
			array('target_list_id', 'numerical', 'integerOnly' => true),
            array('target_list_id', 'exist', 'className' => 'Lists', 'attributeName' => 'list_id'),
		);
        
        return CMap::mergeArray($rules, parent::rules());
	}

	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		$relations = array(
			'sourceList' => array(self::BELONGS_TO, 'Lists', 'source_list_id'),
			'targetList' => array(self::BELONGS_TO, 'Lists', 'target_list_id'),
		);
        
        return CMap::mergeArray($relations, parent::relations());
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		$labels = array(
			'action_id'      => Yii::t('lists', 'Action'),
			'source_list_id' => Yii::t('lists', 'Source list'),
			'source_action'  => Yii::t('lists', 'Source action'),
			'target_list_id' => Yii::t('lists', 'Target list'),
			'target_action'  => Yii::t('lists', 'Target action'),
		);
        
        return CMap::mergeArray($labels, parent::attributeLabels());
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return ListSubscriberAction the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
    
    public function getActions()
    {
        return array(
            self::ACTION_SUBSCRIBE   => Yii::t('lists', ucfirst(self::ACTION_SUBSCRIBE)),
            self::ACTION_UNSUBSCRIBE => Yii::t('lists', ucfirst(self::ACTION_UNSUBSCRIBE)),
        );
    }
}
