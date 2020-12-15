<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * OptionCampaignWebhooks
 *
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.6.8
 */

class OptionCampaignWebhooks extends OptionBase
{
	/**
	 * Settings category
	 * 
	 * @var string 
	 */
    protected $_categoryName = 'system.campaign.webhooks';

	/**
	 * @var string 
	 */
    public $enabled = 'no';

	/**
	 * @inheritdoc
	 */
    public function rules()
    {
        $rules = array(
            array('enabled', 'required'),
            array('enabled', 'in', 'range' => array_keys($this->getYesNoOptions())),
        );

        return CMap::mergeArray($rules, parent::rules());
    }

	/**
	 * @inheritdoc
	 */
    public function attributeLabels()
    {
        $labels = array(
            'enabled' => Yii::t('settings', 'Enabled'),
        );

        return CMap::mergeArray($labels, parent::attributeLabels());
    }

	/**
	 * @inheritdoc
	 */
    public function attributePlaceholders()
    {
        $placeholders = array(
            'enabled' => Yii::t('settings', 'Whether the feature is enabled.'),
        );
        return CMap::mergeArray($placeholders, parent::attributePlaceholders());
    }

	/**
	 * @inheritdoc
	 */
    public function attributeHelpTexts()
    {
        $texts = array(
	        'enabled' => Yii::t('settings', 'Whether webhooks can be added to campaigns so that when a campaign is opened, or a link inside a campaign is clicked, to send webhook requests to given urls'),
        );
        return CMap::mergeArray($texts, parent::attributeHelpTexts());
    }
}
