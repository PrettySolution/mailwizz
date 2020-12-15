<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * SurveySegmentCondition
 * 
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com> 
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.7.8
 */
 
/**
 * This is the model class for table "survey_segment_condition".
 *
 * The followings are the available columns in table 'survey_segment_condition':
 * @property integer $condition_id
 * @property integer $segment_id
 * @property integer $operator_id
 * @property integer $field_id
 * @property string $value
 * @property string $date_added
 * @property string $last_updated
 *
 * The followings are the available model relations:
 * @property SurveySegmentOperator $operator
 * @property SurveySegment $segment
 * @property SurveyField $field
 */
class SurveySegmentCondition extends ActiveRecord
{
    /**
     * @return string the associated database table name
     */
    public function tableName()
    {
        return '{{survey_segment_condition}}';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        $rules = array(
            array('field_id, operator_id, value', 'required'),
            array('field_id, operator_id', 'numerical', 'integerOnly' => true),
            array('value', 'length', 'max'=>255),
        );
        
        return CMap::mergeArray($rules, parent::rules());
    }

    /**
     * @return array relational rules.
     */
    public function relations()
    {
        $relations = array(
            'operator'  => array(self::BELONGS_TO, 'SurveySegmentOperator', 'operator_id'),
            'segment'   => array(self::BELONGS_TO, 'SurveySegment', 'segment_id'),
            'field'     => array(self::BELONGS_TO, 'SurveyField', 'field_id'),
        );
        
        return CMap::mergeArray($relations, parent::relations());
    }
    
    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        $labels = array(
            'condition_id'  => Yii::t('survey_segments', 'Condition'),
            'segment_id'    => Yii::t('survey_segments', 'Segment'),
            'operator_id'   => Yii::t('survey_segments', 'Operator'),
            'field_id'      => Yii::t('survey_segments', 'Field'),
            'value'         => Yii::t('survey_segments', 'Value'),
        );
        
        return CMap::mergeArray($labels, parent::attributeLabels());
    }

    /**
     * Returns the static model of the specified AR class.
     * Please note that you should have this exact method in all your CActiveRecord descendants!
     * @param string $className active record class name.
     * @return SurveySegmentCondition the static model class
     */
    public static function model($className=__CLASS__)
    {
        return parent::model($className);
    }

	/**
	 * @return array
	 */
    public function getOperatorsDropDownArray()
    {
        static $_options = array();
        if (!empty($_options)) {
            return $_options;
        }
        
        $operators = SurveySegmentOperator::model()->findAll();
        foreach ($operators as $operator) {
            $_options[$operator->operator_id] = Yii::t('survey_segments', $operator->name);
        }
        
        return $_options;
    }

	/**
	 * @return mixed|string
	 */
    public function getParsedValue()
    {
        $tags  = self::getValueTags();
        $value = trim($this->value);
        foreach ($tags as $data) {
            $value = call_user_func_array($data['callback'], array($data, $value, $this));
        }
        return $value;
    }

	/**
	 * @return array|null
	 */
    public static function getValueTags()
    {
        static $tags;
        if ($tags === null) {
            $tags = array(
                array(
                    'tag'         => '[EMPTY]',
                    'description' => Yii::t('survey_segments', 'It will be transformed into an empty value'),
                    'callback'    => array(__CLASS__, '_parseEmptyValueTag')
                ),
                array(
                    'tag'         => '[DATETIME]',
                    'description' => Yii::t('survey_segments', 'It will be transformed into the current date/time in the format of Y-m-d H:i:s (i.e: {datetime})', array('{datetime}' => date('Y-m-d H:i:s'))),
                    'callback'    => array(__CLASS__, '_parseDatetimeValueTag'),
                ),
                array(
                    'tag'         => '[DATE]',
                    'description' => Yii::t('survey_segments', 'It will be transformed into the current date in the format of Y-m-d (i.e: {date})', array('{date}' => date('Y-m-d'))),
                    'callback'    => array(__CLASS__, '_parseDateValueTag'),
                ),
            );
            $tags = (array)Yii::app()->hooks->applyFilters('survey_segment_condition_value_tags', $tags);
            foreach ($tags as $index => $data) {
                if (!isset($data['tag'], $data['description'], $data['callback']) || !is_callable($data['callback'], false)) {
                    unset($tags[$index]);
                }
            }
            ksort($tags);
        }
        return $tags;
    }

	/**
	 * @param $data
	 * @param $value
	 * @param $condition
	 *
	 * @return mixed
	 */
    public static function _parseEmptyValueTag($data, $value, $condition)
    {
        if ($data['tag'] != $value) {
            return $value;
        }        
        return str_replace($data['tag'], '', $value);
    }

	/**
	 * @param $data
	 * @param $value
	 * @param $condition
	 *
	 * @return mixed
	 */
    public static function _parseDatetimeValueTag($data, $value, $condition)
    {
        if ($data['tag'] != $value) {
            return $value;
        } 
        return str_replace($data['tag'], date('Y-m-d H:i:s'), $value);   
    }

	/**
	 * @param $data
	 * @param $value
	 * @param $condition
	 *
	 * @return mixed
	 */
    public static function _parseDateValueTag($data, $value, $condition)
    {
        if ($data['tag'] != $value) {
            return $value;
        } 
        return str_replace($data['tag'], date('Y-m-d'), $value); 
    }
}
