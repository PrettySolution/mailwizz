<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * ListFieldConsentCheckbox
 *
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.5.5
 */

/**
 * Class ListFieldConsentCheckbox
 */
class ListFieldConsentCheckbox extends ListField
{
    /**
     * @var string 
     */
    public $consent_text = 'I give my consent to [COMPANY_NAME] to send me occasional newsletters using the information i have provided in this form.';
    
    /**
     * @inheritdoc
     */
    public function rules()
    {
        $rules = array(
            array('consent_text', 'required'),
            array('consent_text', 'length', 'min' => 1, 'max' => 255),
        );

        return CMap::mergeArray($rules, parent::rules());
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        $labels = array(
            'consent_text' => Yii::t('list_fields', 'The consent text'),
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
    protected function afterConstruct()
    {
        $this->required = self::TEXT_YES;
        parent::afterConstruct();
    }

    /**
     * @inheritdoc
     */
    protected function beforeSave()
    {
        $this->getModelMetaData()->add('consent_text', (string)$this->consent_text);
        return parent::beforeSave();
    }

    /**
     * @inheritdoc
     */
    protected function afterFind()
    {
        $this->consent_text = (string)$this->getModelMetaData()->itemAt('consent_text');
        parent::afterFind();
    }

    /**
     * @return array|mixed
     * @throws CException
     */
    public function attributeHelpTexts()
    {
        $texts = array(
            'consent_text' => Yii::t('list_fields', 'The consent text shown to the subscriber.'),
        );

        return CMap::mergeArray($texts, parent::attributeHelpTexts());
    }
}
