<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * SurveyFieldText
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
class SurveyFieldText extends SurveyField
{
    /**
     * @var int
     */
    public $min_length = 1;

    /**
     * @var int
     */
    public $max_length = 255;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        $rules = array(
            array('min_length, max_length', 'required'),
            array('min_length, max_length', 'numerical', 'integerOnly' => true, 'min' => 1, 'max' => 255),
            array('min_length', 'compare', 'compareAttribute' => 'max_length', 'operator' => '<'),
            array('max_length', 'compare', 'compareAttribute' => 'min_length', 'operator' => '>')
        );

        return CMap::mergeArray($rules, parent::rules());
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        $labels = array(
            'min_length' => Yii::t('survey_fields', 'Minimum length'),
            'max_length' => Yii::t('survey_fields', 'Maximum length'),
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
    protected function beforeSave()
    {
        $this->getModelMetaData()->add('min_length', (int)$this->min_length);
        $this->getModelMetaData()->add('max_length', (int)$this->max_length);
        return parent::beforeSave();
    }

    /**
     * @inheritdoc
     */
    protected function afterFind()
    {
        $this->min_length = (int)$this->getModelMetaData()->itemAt('min_length');
        $this->max_length = (int)$this->getModelMetaData()->itemAt('max_length');
        parent::afterFind();
    }

    /**
     * @return array|mixed
     * @throws CException
     */
    public function attributeHelpTexts()
    {
        $texts = array(
            'min_length' => Yii::t('survey_fields', 'Minimum length of the text'),
            'max_length' => Yii::t('survey_fields', 'Maximum length of the text'),
        );

        return CMap::mergeArray($texts, parent::attributeHelpTexts());
    }
}
