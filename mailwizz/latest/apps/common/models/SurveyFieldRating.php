<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * SurveyFieldRating
 *
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.7.8
 */

/**
 * Class SurveyFieldRating
 */
class SurveyFieldRating extends SurveyField
{
    /**
     * @var int
     */
    public $max_stars = 10;

    /**
     * @var string
     */
    public $icon_lib = 'fa fa-2x';

    /**
     * @var string
     */
    public $active_icon = 'fa-star';

    /**
     * @var string
     */
    public $inactive_icon = 'fa-star-o';

    /**
     * @var string
     */
    public $clearable_text = '';

    /**
     * @var string
     */
    public $clearable_icon = 'fa-trash-o';

    /**
     * @inheritdoc
     */
    public function rules()
    {
        $rules = array(
            array('max_stars', 'required'),
            array('max_stars', 'numerical', 'integerOnly' => true, 'min' => 3, 'max' => 10),
        );

        return CMap::mergeArray($rules, parent::rules());
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        $labels = array(
            'max_stars' => Yii::t('survey_fields', 'Maximum stars'),
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
        $this->getModelMetaData()->add('max_stars', (int)$this->max_stars);
        return parent::beforeSave();
    }

    /**
     * @inheritdoc
     */
    protected function afterFind()
    {
        $this->max_stars = (int)$this->getModelMetaData()->itemAt('max_stars');
        parent::afterFind();
    }

    /**
     * @return array|mixed
     * @throws CException
     */
    public function attributeHelpTexts()
    {
        $texts = array(
            'max_stars' => Yii::t('survey_fields', 'Maximum stars of the rating input'),
        );

        return CMap::mergeArray($texts, parent::attributeHelpTexts());
    }
}
