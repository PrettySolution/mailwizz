<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * OptionApi
 *
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.3.8.8
 */

class OptionApi extends OptionBase
{
    // settings category
    protected $_categoryName = 'system.api';

    public $disable_signature_check = 'no';
    
    public function rules()
    {
        $rules = array(
            array('disable_signature_check', 'in', 'range' => array_keys($this->getYesNoOptions())),
        );

        return CMap::mergeArray($rules, parent::rules());
    }

    public function attributeLabels()
    {
        $labels = array(
            'disable_signature_check' => Yii::t('app', 'Disable signature check'),
        );

        return CMap::mergeArray($labels, parent::attributeLabels());
    }

    public function attributePlaceholders()
    {
        $placeholders = array();
        return CMap::mergeArray($placeholders, parent::attributePlaceholders());
    }

    public function attributeHelpTexts()
    {
        $texts = array(
            'disable_signature_check' => Yii::t('app', 'Whether to disable signature check when doing an API request'),
        );

        return CMap::mergeArray($texts, parent::attributeHelpTexts());
    }
}
