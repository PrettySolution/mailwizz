<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * OptionApiIpAccess
 *
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.3.5.9
 */

class OptionApiIpAccess extends OptionBase
{
    // settings category
    protected $_categoryName = 'system.api.ip_access';

    public $allowed_ips = '';

    public $denied_ips  = '';

    public function rules()
    {
        $rules = array(
            array('allowed_ips, denied_ips', 'length', 'max' => 10000),
        );

        return CMap::mergeArray($rules, parent::rules());
    }

    public function attributeLabels()
    {
        $labels = array(
            'allowed_ips' => Yii::t('app', 'Allowed IPs'),
            'denied_ips'  => Yii::t('settings', 'Denied IPs'),
        );

        return CMap::mergeArray($labels, parent::attributeLabels());
    }

    public function attributePlaceholders()
    {
        $placeholders = array(
            'allowed_ips' => '123.123.123.123, 12.12.12.12',
            'denied_ips'  => '11.11.11.11, 22.22.22.22',
        );
        return CMap::mergeArray($placeholders, parent::attributePlaceholders());
    }

    public function attributeHelpTexts()
    {
        $texts = array(
            'allowed_ips' => Yii::t('app', 'List of IPs allowed to access the api. Separate multiple IPs by a comma'),
            'denied_ips'  => Yii::t('settings', 'List of IPs denied to access the api. Separate multiple IPs by a comma'),
        );

        return CMap::mergeArray($texts, parent::attributeHelpTexts());
    }

    protected function beforeValidate()
    {
        $keys = array('allowed_ips', 'denied_ips');
        foreach ($keys as $key) {
            $ipList = CommonHelper::getArrayFromString($this->$key);
            foreach ($ipList as $index => $ip) {
                if (!FilterVarHelper::ip($ip)) {
                    unset($ipList[$index]);
                }
            }
            $this->$key = CommonHelper::getStringFromArray($ipList);
        }

        return parent::beforeValidate();
    }
}
