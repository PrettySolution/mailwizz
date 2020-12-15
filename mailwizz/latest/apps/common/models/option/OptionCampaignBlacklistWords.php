<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * OptionCampaignBlacklistWords
 *
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.3.5.9
 */

class OptionCampaignBlacklistWords extends OptionBase
{
    // settings category
    protected $_categoryName = 'system.campaign.blacklist_words';

    public $enabled = 'no';

    public $subject = '';

    public $content = '';

    public $notifications_to = '';

    public function rules()
    {
        $rules = array(
            array('enabled', 'required'),
            array('enabled', 'in', 'range' => array_keys($this->getYesNoOptions())),
            array('subject, content, notifications_to', 'length', 'max' => 10000),
        );

        return CMap::mergeArray($rules, parent::rules());
    }

    public function attributeLabels()
    {
        $labels = array(
            'enabled'          => Yii::t('app', 'Enabled'),
            'subject'          => Yii::t('settings', 'Subject'),
            'content'          => Yii::t('settings', 'Content'),
            'notifications_to' => Yii::t('settings', 'Notifications'),
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
            'enabled'          => Yii::t('app', 'Whether the feature is enabled'),
            'subject'          => Yii::t('settings', 'Words for campaign subject, separated by a comma'),
            'content'          => Yii::t('settings', 'Words for campaign content, separated by a comma'),
            'notifications_to' => Yii::t('settings', 'What email addresses to notify when a campaign is blocked. Separate multiple email addresses by a comma')
        );

        return CMap::mergeArray($texts, parent::attributeHelpTexts());
    }

    protected function beforeValidate()
    {
        $keys = array('subject', 'content', 'notifications_to');
        foreach ($keys as $key) {
            $data = CommonHelper::getArrayFromString($this->$key);
            if ($key == 'notifications_to') {
                foreach ($data as $index => $email) {
                    if (!FilterVarHelper::email($email)) {
                        unset($data[$index]);
                    }
                }
            }
            $this->$key = CommonHelper::getStringFromArray($data);
        }

        return parent::beforeValidate();
    }
}
