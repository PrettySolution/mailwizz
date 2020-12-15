<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * CampaignSentActionListField
 * 
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com> 
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.4.5
 */
 
/**
 * This is the model class for table "{{campaign_sent_action_list_field}}".
 *
 * The followings are the available columns in table '{{campaign_sent_action_list_field}}':
 * @property string $action_id
 * @property integer $campaign_id
 * @property integer $list_id
 * @property integer $field_id
 * @property string $field_value
 * @property string $date_added
 * @property string $last_updated
 *
 * The followings are the available model relations:
 * @property List $list
 * @property Campaign $campaign
 * @property ListField $field
 */
class CampaignSentActionListField extends ActiveRecord
{
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return '{{campaign_sent_action_list_field}}';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		$rules = array(
			array('field_id, field_value', 'required'),
			array('field_id', 'numerical', 'integerOnly'=>true),
			array('field_value', 'length', 'max'=>255),
            array('field_id', 'exist', 'className' => 'ListField'),
		);
        return CMap::mergeArray($rules, parent::rules());
	}

	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		$relations = array(
			'list'       => array(self::BELONGS_TO, 'List', 'list_id'),
			'campaign'   => array(self::BELONGS_TO, 'Campaign', 'campaign_id'),
			'field'      => array(self::BELONGS_TO, 'ListField', 'field_id'),
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
			'list_id'        => Yii::t('campaigns', 'List'),
			'field_id'       => Yii::t('campaigns', 'Field'),
			'field_value'    => Yii::t('campaigns', 'Field value'),
		);
        return CMap::mergeArray($labels, parent::attributeLabels());
	}

    /**
     * @inheritdoc
     */
    public function attributeHelpTexts()
    {
        $texts = array(
            'field_id'    => Yii::t('campaigns', 'Which field to change when the campaign is sent to the subscriber.'),
			'field_value' => Yii::t('campaigns', 'The value that the custom field should get after the campaign is sent to subscriber.'),
        );
        
        return CMap::mergeArray($texts, parent::attributeHelpTexts());
    }

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return CampaignSentActionListField the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

    /**
     * @return array|mixed
     */
    public function getCustomFieldsAsDropDownOptions()
    {
        $this->list_id  = (int)$this->list_id;
        static $options = array();
        if (isset($options[$this->list_id])) {
            return $options[$this->list_id];
        }

        $typeIds   = array();
        $typeNames = array('text', 'date', 'datetime', 'textarea', 'country', 'state', 'dropdown');
        foreach ($typeNames as $typeName) {
            $type = ListFieldType::model()->findByAttributes(array('identifier' => $typeName));
            if (empty($type)) {
                continue;
            }
            $typeIds[] = $type->type_id;
        }
        
        if (empty($typeIds)) {
            return $options[$this->list_id] = array();
        }
        
        $options[$this->list_id] = array();
        $criteria = new CDbCriteria();
        $criteria->select = 'field_id, label';
        $criteria->compare('list_id', $this->list_id);
        $criteria->addInCondition('type_id', $typeIds);
        $criteria->addNotInCondition('tag', array('EMAIL'));
        $models = ListField::model()->findAll($criteria);
        foreach ($models as $model) {
            $options[$this->list_id][$model->field_id] = $model->label;
        }
        return $options[$this->list_id];
    }

    /**
     * @param CAttributeCollection $collection
     * @return string
     */
    public function getParsedFieldValueByListFieldValue(CAttributeCollection $collection)
    {
        $collection->fieldValue = $this->field_value;
        return CampaignHelper::getParsedFieldValueByListFieldValue($collection);
    }
}
