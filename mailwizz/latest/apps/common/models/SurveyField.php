<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * SurveyField
 *
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.7.8
 */

/**
 * This is the model class for table "{{survey_field}}".
 *
 * The followings are the available columns in table '{{survey_field}}':
 * @property integer $field_id
 * @property integer $type_id
 * @property integer $survey_id
 * @property string $label
 * @property string $default_value
 * @property string $help_text
 * @property string $description
 * @property string $required
 * @property string $visibility
 * @property string $meta_data
 * @property integer $sort_order
 * @property string $date_added
 * @property string $last_updated
 *
 * The followings are the available model relations:
 * @property Survey $survey
 * @property SurveyFieldType $type
 * @property SurveyFieldOption[] $options
 * @property SurveyFieldOption[] $option
 * @property SurveyFieldValue[] $values
 * @property SurveyFieldValue[] $value
 * @property SurveySegmentCondition[] $segmentConditions
 */
class SurveyField extends ActiveRecord
{
    /**
     * Flag
     */
    const VISIBILITY_VISIBLE = 'visible';

    /**
     * Flag
     */
    const VISIBILITY_HIDDEN = 'hidden';

    /**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return '{{survey_field}}';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		$rules = array(
            array('type_id, label, required, visibility, sort_order', 'required'),

            array('type_id', 'numerical', 'integerOnly' => true, 'min' => 1),
            array('type_id', 'exist', 'className' => 'SurveyFieldType'),
            array('label, help_text, description, default_value', 'length', 'min' => 1, 'max' => 255),
            array('required', 'in', 'range' => array_keys($this->getRequiredOptionsArray())),
            array('visibility', 'in', 'range' => array_keys($this->getVisibilityOptionsArray())),
            array('sort_order', 'numerical', 'min' => -100, 'max' => 100),
		);

        return CMap::mergeArray($rules, parent::rules());
	}

	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		$relations = array(
			'survey'            => array(self::BELONGS_TO, 'Survey', 'survey_id'),
			'type'              => array(self::BELONGS_TO, 'SurveyFieldType', 'type_id'),
			'options'           => array(self::HAS_MANY, 'SurveyFieldOption', 'field_id'),
            'option'            => array(self::HAS_ONE, 'SurveyFieldOption', 'field_id'),
            'values'            => array(self::HAS_MANY, 'SurveyFieldValue', 'field_id'),
            'value'             => array(self::HAS_ONE, 'SurveyFieldValue', 'field_id'),
            'segmentConditions' => array(self::HAS_MANY, 'SurveySegmentCondition', 'field_id'),
		);
        return CMap::mergeArray($relations, parent::relations());
	}

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        $labels = array(
            'field_id'      => Yii::t('survey_fields', 'Field'),
            'type_id'       => Yii::t('survey_fields', 'Type'),
            'survey_id'     => Yii::t('survey_fields', 'List'),
            'label'         => Yii::t('survey_fields', 'Label'),
            'default_value' => Yii::t('survey_fields', 'Default value'),
            'help_text'     => Yii::t('survey_fields', 'Help text'),
            'Description'   => Yii::t('survey_fields', 'Description'),
            'required'      => Yii::t('survey_fields', 'Required'),
            'visibility'    => Yii::t('survey_fields', 'Visibility'),
            'sort_order'    => Yii::t('survey_fields', 'Sort order'),
        );

        return CMap::mergeArray($labels, parent::attributeLabels());
    }

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return SurveyField the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

    /**
     * @return array|mixed
     * @throws CException
     */
    public function attributeHelpTexts()
    {
        $texts = array(
            'label'         => Yii::t('survey_fields', 'This is what your responders will see above the input field.'),
            'default_value' => Yii::t('survey_fields', 'In case this field is not required and you need a default value for it.'),
            'help_text'     => Yii::t('survey_fields', 'If you need to describe this field to your responders.'),
            'description'   => Yii::t('survey_fields', 'Additional description for this field to show to your responders.'),
            'required'      => Yii::t('survey_fields', 'Whether this field must be filled in in order to submit the subscription form.'),
            'visibility'    => Yii::t('survey_fields', 'Hidden fields are not shown to responders.'),
            'sort_order'    => Yii::t('survey_fields', 'Decide the order of the fields shown in the form.'),
        );

        return CMap::mergeArray($texts, parent::attributeHelpTexts());
    }

    /**
     * @return array
     */
    public function getRequiredOptionsArray()
    {
        return array(
            self::TEXT_YES   => Yii::t('app', 'Yes'),
            self::TEXT_NO    => Yii::t('app', 'No'),
        );
    }

    /**
     * @return array
     */
    public function getVisibilityOptionsArray()
    {
        return array(
            self::VISIBILITY_VISIBLE    => Yii::t('app', 'Visible'),
            self::VISIBILITY_HIDDEN     => Yii::t('app', 'Hidden'),
        );
    }

    /**
     * @return array
     */
    public function getSortOrderOptionsArray()
    {
        static $_opts = array();
        if (!empty($_opts)) {
            return $_opts;
        }

        for ($i = -100; $i <= 100; ++$i) {
            $_opts[$i] = $i;
        }

        return $_opts;
    }

    /**
     * @since 1.7.8
     * @param $surveyId
     * @return mixed
     */
    public static function getAllBySurveyId($surveyId)
    {
        static $fields = array();
        if (!isset($fields[$surveyId])) {
            $fields[$surveyId] = array();
            $criteria = new CDbCriteria();
            $criteria->select = 't.field_id';
            $criteria->compare('t.survey_id', $surveyId);
            $models = self::model()->findAll($criteria);
            foreach ($models as $model) {
                $fields[$surveyId][] = $model->getAttributes(array('field_id'));
            }
        }
        return $fields[$surveyId];
    }

	/**
	 * @return string
	 */
    public function getTag()
    {
        return 'field-' . $this->field_id;
    }
}
