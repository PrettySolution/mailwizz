<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * SurveyFieldOption
 *
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.7.8
 */

/**
 * This is the model class for table "{{survey_field_option}}".
 *
 * The followings are the available columns in table '{{survey_field_option}}':
 * @property integer $option_id
 * @property integer $field_id
 * @property string $name
 * @property string $value
 * @property string $is_default
 * @property string $date_added
 * @property string $last_updated
 *
 * The followings are the available model relations:
 * @property SurveyField $field
 */
class SurveyFieldOption extends ActiveRecord
{
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return '{{survey_field_option}}';
	}

    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        $rules = array(
            array('name, value', 'required'),
            array('name', 'length', 'max'=>100),
            array('value', 'length', 'max'=>255),
            array('is_default', 'in', 'range' => array_keys($this->getIsDefaultOptionsArray()), 'allowEmpty' => true),
        );

        return CMap::mergeArray($rules, parent::rules());
    }

    /**
     * @return array relational rules.
     */
    public function relations()
    {
        $relations = array(
            'field' => array(self::BELONGS_TO, 'SurveyField', 'field_id'),
        );

        return CMap::mergeArray($relations, parent::relations());
    }

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        $labels = array(
            'option_id'     => Yii::t('survey_fields', 'Option'),
            'field_id'      => Yii::t('survey_fields', 'Field'),
            'name'          => Yii::t('survey_fields', 'Name'),
            'value'         => Yii::t('survey_fields', 'Value'),
            'is_default'    => Yii::t('survey_fields', 'Is default'),
        );

        return CMap::mergeArray($labels, parent::attributeLabels());
    }

    /**
     * Returns the static model of the specified AR class.
     * Please note that you should have this exact method in all your CActiveRecord descendants!
     * @param string $className active record class name.
     * @return SurveyFieldOption the static model class
     */
    public static function model($className=__CLASS__)
    {
        return parent::model($className);
    }

	/**
	 * @return array
	 */
    public function attributeHelpTexts()
    {
        $texts = array(
            'value' => null
        );

        return CMap::mergeArray($texts, parent::attributeHelpTexts());
    }

	/**
	 * @return array
	 */
    public function getIsDefaultOptionsArray()
    {
        return array(
            self::TEXT_NO    => Yii::t('app', 'No'),
            self::TEXT_YES   => Yii::t('app', 'Yes'),
        );
    }
}
