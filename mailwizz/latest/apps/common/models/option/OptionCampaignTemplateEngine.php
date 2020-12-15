<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * OptionCampaignTemplateEngine
 *
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.3.6.2
 */

class OptionCampaignTemplateEngine extends OptionBase
{
    // settings category
    protected $_categoryName = 'system.campaign.template_engine';

    public $enabled = 'no';

    public function rules()
    {
        $rules = array(
            array('enabled', 'required'),
            array('enabled', 'in', 'range' => array_keys($this->getYesNoOptions())),
        );

        return CMap::mergeArray($rules, parent::rules());
    }

    public function attributeLabels()
    {
        $labels = array(
            'enabled' => Yii::t('settings', 'Enabled'),
        );

        return CMap::mergeArray($labels, parent::attributeLabels());
    }

    public function attributePlaceholders()
    {
        $placeholders = array(
            'enabled' => Yii::t('settings', 'Whether the feature is enabled.'),
        );
        return CMap::mergeArray($placeholders, parent::attributePlaceholders());
    }

    public function attributeHelpTexts()
    {
        $texts = array();
        return CMap::mergeArray($texts, parent::attributeHelpTexts());
    }
}
