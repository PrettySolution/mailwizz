<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * OptionCampaignExcludeIpsFromTracking
 *
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.3.5.8
 */

class OptionCampaignExcludeIpsFromTracking extends OptionBase
{
    // settings category
    protected $_categoryName = 'system.campaign.exclude_ips_from_tracking';

    public $open = '';

    public $url = '';

    public function rules()
    {
        $rules = array(
            array('open, url', 'length', 'max' => 60000),
        );

        return CMap::mergeArray($rules, parent::rules());
    }

    public function attributeLabels()
    {
        $labels = array(
            'open' => Yii::t('settings', 'Exclude from open tracking'),
            'url'  => Yii::t('settings', 'Exclude from url tracking'),
        );

        return CMap::mergeArray($labels, parent::attributeLabels());
    }

    public function attributePlaceholders()
    {
        $placeholders = array(
            'open' => "11.11.11.11, 22.22.22.22, 33.33.33.33",
            'url'  => "11.11.11.11, 22.22.22.22, 33.33.33.33",
        );

        return CMap::mergeArray($placeholders, parent::attributePlaceholders());
    }

    public function attributeHelpTexts()
    {
        $texts = array(
            'open' => Yii::t('settings', 'IPs list, separated by a comma, to exclude from open tracking'),
            'url'  => Yii::t('settings', 'IPs list, separated by a comma, to exclude from url tracking'),
        );

        return CMap::mergeArray($texts, parent::attributeHelpTexts());
    }

    protected function beforeValidate()
    {
        $keys = array('open', 'url');
        foreach ($keys as $key) {
            if (!empty($this->$key)) {
                $ips = explode(",", $this->$key);
                $_key = array();
                foreach ($ips as $ip) {
                    $ip = trim($ip);
                    if (empty($ip)) {
                        continue;
                    }
                    if (FilterVarHelper::ip($ip)) {
                        $_key[] = $ip;
                    }
                }
                $_key = array_unique($_key);
                $this->$key = implode(", ", $_key);
            }
        }

        return parent::beforeValidate();
    }
}
