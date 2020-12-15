<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * OptionCampaignMisc
 *
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.3.5.9
 */

class OptionCampaignMisc extends OptionBase
{
    // settings category
    protected $_categoryName = 'system.campaign.misc';

    public $not_allowed_from_domains = '';

	public $not_allowed_from_patterns = '';
    
    public function rules()
    {
        $rules = array(
            array('not_allowed_from_domains, not_allowed_from_patterns', 'length', 'max' => 60000),
        );

        return CMap::mergeArray($rules, parent::rules());
    }

    public function attributeLabels()
    {
        $labels = array(
            'not_allowed_from_domains'   => Yii::t('settings', 'Not allowed FROM domains'),
            'not_allowed_from_patterns'  => Yii::t('settings', 'Not allowed FROM regex patterns'),
        );

        return CMap::mergeArray($labels, parent::attributeLabels());
    }

    public function attributePlaceholders()
    {
        $placeholders = array(
            'not_allowed_from_domains'  => 'yahoo.com, gmail.com, aol.com',
            'not_allowed_from_patterns' => "/^(.*)@yahoo\.com$/i\n/^name@(.*)\.com$/i\n/^name@goo(.*)\.(com|net|org)$/i",
        );
        return CMap::mergeArray($placeholders, parent::attributePlaceholders());
    }

    public function attributeHelpTexts()
    {
        $texts = array(
            'not_allowed_from_domains'  => Yii::t('settings', 'List of domain names that are not allowed to be used in the campaign FROM email address. Separate multiple domains by a comma'),
            'not_allowed_from_patterns' => Yii::t('settings', 'List of regex patterns that are not allowed to be used in the campaign FROM email address. Add each pattern on it\'s own line. Please make sure your patterns are valid!'),
        );

        return CMap::mergeArray($texts, parent::attributeHelpTexts());
    }

    protected function beforeValidate()
    {
        $domains = CommonHelper::getArrayFromString($this->not_allowed_from_domains);
        foreach ($domains as $index => $domain) {
            if (!FilterVarHelper::url('http://' . $domain)) {
                unset($domains[$index]);
            }
        }
        $this->not_allowed_from_domains = CommonHelper::getStringFromArray($domains);

	    $patterns = CommonHelper::getArrayFromString($this->not_allowed_from_patterns, "\n");
	    $patterns = array_filter(array_unique(array_map('trim', $patterns)));
	    $this->not_allowed_from_patterns = CommonHelper::getStringFromArray($patterns, "\n");

        return parent::beforeValidate();
    }
}
