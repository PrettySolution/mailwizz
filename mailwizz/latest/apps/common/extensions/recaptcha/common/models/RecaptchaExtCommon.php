<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * RecaptchaExtCommon
 * 
 * @package MailWizz EMA
 * @subpackage Recaptcha
 * @author Serban George Cristian <cristian.serban@mailwizz.com> 
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 */
 
class RecaptchaExtCommon extends FormModel
{
    public $enabled = 'no';

    public $enabled_for_list_forms = 'no';
    
    public $enabled_for_registration = 'no';
    
    public $enabled_for_login = 'no';

    public $enabled_for_forgot = 'no';

    public $site_key;

    public $secret_key;

    public function rules()
    {
        $rules = array(
            array('site_key, secret_key', 'safe'),
            array('enabled, enabled_for_list_forms, enabled_for_registration, enabled_for_login, enabled_for_forgot', 'in', 'range' => array_keys($this->getYesNoOptions())),
        );
        return CMap::mergeArray($rules, parent::rules());    
    }
    
    public function attributeLabels()
    {
        $labels = array(
            'enabled'                  => Yii::t('app', 'Enabled'),
            'enabled_for_list_forms'   => Yii::t('ext_recaptcha', 'Enabled for list forms'),
            'enabled_for_registration' => Yii::t('ext_recaptcha', 'Enable for registration'),
            'enabled_for_login'        => Yii::t('ext_recaptcha', 'Enable for login'),
            'enabled_for_forgot'       => Yii::t('ext_recaptcha', 'Enable for forgot password'),
            'site_key'                 => Yii::t('ext_recaptcha', 'Site key'),
            'secret_key'               => Yii::t('ext_recaptcha', 'Secret key'),
        );
        return CMap::mergeArray($labels, parent::attributeLabels());    
    }
    
    public function attributePlaceholders()
    {
        $placeholders = array(
            'site_key'   => '6LegYwsTBBBCCPdpjWct69ScnOMG9ZRv2vy8Xbbj',
            'secret_key' => '6LegYwsTBBBCCxQmCT54Q_0bIwZH94ogQwNQCpE',
        );
        return CMap::mergeArray($placeholders, parent::attributePlaceholders());
    }
    
    public function attributeHelpTexts()
    {
        $texts = array(
            'enabled'                  => Yii::t('app', 'Whether the feature is enabled'),
            'enabled_for_list_forms'   => Yii::t('ext_recaptcha', 'Whether the feature is enabled for list forms'),
            'enabled_for_registration' => Yii::t('ext_recaptcha', 'Whether the feature is enabled for registration'),
            'enabled_for_login'        => Yii::t('ext_recaptcha', 'Whether the feature is enabled for login'),
            'enabled_for_forgot'       => Yii::t('ext_recaptcha', 'Whether the feature is enabled for forgot password'),
            'site_key'                 => Yii::t('ext_recaptcha', 'The site key for recaptcha service'),
            'secret_key'               => Yii::t('ext_recaptcha', 'The secret key for recaptcha service'),
        );
        return CMap::mergeArray($texts, parent::attributeHelpTexts());
    }
    
    public function save()
    {
        $extension  = $this->getExtensionInstance();
        $attributes = array(
            'enabled', 'enabled_for_list_forms', 'enabled_for_registration', 'enabled_for_login', 'enabled_for_forgot', 
            'site_key', 'secret_key'
        );
        foreach ($attributes as $name) {
            $extension->setOption($name, $this->$name);
        }
        return $this;
    }
    
    public function populate() 
    {
        $extension  = $this->getExtensionInstance();
        $attributes = array(
            'enabled', 'enabled_for_list_forms', 'enabled_for_registration', 'enabled_for_login', 'enabled_for_forgot', 
            'site_key', 'secret_key'
        );
        foreach ($attributes as $name) {
            $this->$name = $extension->getOption($name, $this->$name);
        }
        return $this;
    }
    
    public function getExtensionInstance()
    {
        return Yii::app()->extensionsManager->getExtensionInstance('recaptcha');
    }
}
