<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * ListFieldCheckbox
 *
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.5.5
 */

/**
 * Class ListFieldCheckbox
 */
class ListFieldCheckbox extends ListField
{
    /**
     * @var string 
     */
    public $check_value = '1';

    /**
     * @inheritdoc
     */
    public function rules()
    {
        $rules = array(
            array('check_value', 'required'),
            array('check_value', 'length', 'min' => 1, 'max' => 255),
        );

        return CMap::mergeArray($rules, parent::rules());
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        $labels = array(
            'check_value' => Yii::t('list_fields', 'Value when checked'),
        );

        return CMap::mergeArray($labels, parent::attributeLabels());
    }

    /**
     * Returns the static model of the specified AR class.
     * Please note that you should have this exact method in all your CActiveRecord descendants!
     * @param string $className active record class name.
     * @return ListField the static model class
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
        $this->getModelMetaData()->add('check_value', (string)$this->check_value);
        return parent::beforeSave();
    }

    /**
     * @inheritdoc
     */
    protected function afterConstruct()
    {
        parent::afterConstruct();
        $this->check_value = (string)$this->getModelMetaData()->itemAt('check_value');
    }

    /**
     * @inheritdoc
     */
    protected function afterFind()
    {
        $this->check_value = (string)$this->getModelMetaData()->itemAt('check_value');
        parent::afterFind();
    }

    /**
     * @return array|mixed
     * @throws CException
     */
    public function attributeHelpTexts()
    {
        $texts = array(
            'check_value' => Yii::t('list_fields', 'The value of the field when checked.'),
        );

        return CMap::mergeArray($texts, parent::attributeHelpTexts());
    }

    /**
     * @return string
     */
    public function getCheckValue()
    {
        return isset($this->check_value) ? (string)$this->check_value : '1';
    }
}
