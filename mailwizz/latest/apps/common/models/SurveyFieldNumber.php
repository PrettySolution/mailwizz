<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * SurveyFieldNumber
 *
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.7.8
 */

/**
 * Class SurveyFieldText
 */
class SurveyFieldNumber extends SurveyField
{
    /**
     * Flag for integer and float
     */
    const VALUE_TYPE_INTEGER_AND_FLOAT = 0;

    /**
     * Flag for integer only
     */
    const VALUE_TYPE_INTEGER_ONLY = 1;

    /**
     * Scenario for integer and float
     */
    const SCENARIO_INTEGER_AND_FLOAT = 'integer-and-float';

    /**
     * Scenario for integer only
     */
    const SCENARIO_INTEGER_ONLY = 'integer-only';

    /**
     * Max int and float value
     */
    const MAX_VALUE = 99999999;

    /**
     * @var int
     */
    public $min_value = 1;

    /**
     * @var int
     */
    public $max_value = self::MAX_VALUE;

    /**
     * @var int
     */
    public $step_size = 1;

    /**
     * @var bool
     */
    public $integer_only = false;

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->scenario = self::SCENARIO_INTEGER_AND_FLOAT;
        if ($this->integer_only) {
            $this->scenario = self::SCENARIO_INTEGER_ONLY;
        }

        parent::init();
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        $rules = array(
            array('min_value, max_value, step_size, integer_only', 'required'),
            array('integer_only', 'in', 'range' => array_keys($this->getValuesTypeList())),
            array('min_value, max_value, step_size', 'numerical', 'integerOnly' => true, 'min' => 1, 'max' => self::MAX_VALUE, 'on' => self::SCENARIO_INTEGER_ONLY),
            array('min_value, max_value, step_size', 'numerical', 'min' => 1, 'max' => self::MAX_VALUE, 'on' => self::SCENARIO_INTEGER_AND_FLOAT),
        );

        return CMap::mergeArray($rules, parent::rules());
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        $labels = array(
            'min_value'    => Yii::t('survey_fields', 'Minimum value'),
            'max_value'    => Yii::t('survey_fields', 'Maximum value'),
            'step_size'    => Yii::t('survey_fields', 'Step size'),
            'integer_only' => Yii::t('survey_fields', 'Value type'),
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
     * @inheritdoc
     */
    protected function beforeValidate()
    {
        return parent::beforeValidate();
    }

    /**
     * @inheritdoc
     */
    protected function beforeSave()
    {
        $this->getModelMetaData()->add('min_value', $this->getMinValue());
        $this->getModelMetaData()->add('max_value', $this->getMaxValue());
        $this->getModelMetaData()->add('step_size', $this->step_size);
        $this->getModelMetaData()->add('integer_only', (bool)$this->integer_only);
        return parent::beforeSave();
    }

    /**
     * @inheritdoc
     */
    protected function afterFind()
    {
        $this->min_value    = $this->getModelMetaData()->itemAt('min_value');
        $this->max_value    = $this->getModelMetaData()->itemAt('max_value');
        $this->step_size    = $this->getModelMetaData()->itemAt('step_size');
        $this->integer_only = (bool)$this->getModelMetaData()->itemAt('integer_only');

        parent::afterFind();
    }

    /**
     * @return array|mixed
     * @throws CException
     */
    public function attributeHelpTexts()
    {
        $texts = array(
            'min_value' => Yii::t('survey_fields', 'Minimum value of the number input'),
            'max_value' => Yii::t('survey_fields', 'Maximum value of the number input'),
            'step_size' => Yii::t('survey_fields', 'Step size of the number input'),
        );

        return CMap::mergeArray($texts, parent::attributeHelpTexts());
    }

    /**
     * @return array
     */
    public function getValuesTypeList()
    {
        return array(
            self::VALUE_TYPE_INTEGER_AND_FLOAT => Yii::t('survey_fields', 'Integer and float'),
            self::VALUE_TYPE_INTEGER_ONLY      => Yii::t('survey_fields', 'Integer only'),
        );
    }

    /**
     * @return float|int
     */
    public function getMinValue()
    {
        return $this->integer_only ? (int)$this->min_value : (float)$this->min_value;
    }

    /**
     * @return float|int
     */
    public function getMaxValue()
    {
        return $this->integer_only ? (int)$this->max_value : (float)$this->max_value;
    }

    /**
     * @return int|string
     */
    public function getStepSize()
    {
        return $this->integer_only ? (int)$this->step_size : 'any';
    }
}
