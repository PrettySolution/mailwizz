<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * OptionTwoFactorAuth
 *
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.6.6
 */

class OptionTwoFactorAuth extends OptionBase
{
	/**
	 * @var string the settings category
	 */
    protected $_categoryName = 'system.2fa';

	/**
	 * @var string 
	 */
    public $enabled = 'no';

	/**
	 * @var string 
	 */
	public $companyName = '';

	/**
	 * @inheritdoc
	 */
    public function rules()
    {
        $rules = array(
        	array('enabled, companyName', 'required'),
            array('enabled', 'in', 'range' => array_keys($this->getYesNoOptions())),
	        array('companyName', 'length', 'min' => 3, 'max' => 255),
        );

        return CMap::mergeArray($rules, parent::rules());
    }

	/**
	 * @inheritdoc
	 */
    public function attributeLabels()
    {
        $labels = array(
            'enabled'       => Yii::t('app', 'Enabled'),
	        'companyName'   => Yii::t('settings', 'Company name'),
        );

        return CMap::mergeArray($labels, parent::attributeLabels());
    }

	/**
	 * @inheritdoc
	 */
    public function attributePlaceholders()
    {
        $placeholders = array();
        return CMap::mergeArray($placeholders, parent::attributePlaceholders());
    }

	/**
	 * @inheritdoc
	 */
    public function attributeHelpTexts()
    {
        $texts = array(
            'enabled'     => Yii::t('settings', 'Whether 2FA is enabled system wide'),
	        'companyName' => Yii::t('settings', 'It is shown in the authenticator app for easier identification'),
        );

        return CMap::mergeArray($texts, parent::attributeHelpTexts());
    }

	/**
	 * @return bool
	 */
    public function getIsEnabled()
    {
    	return $this->enabled === self::TEXT_YES && version_compare(PHP_VERSION, '7.0', '>=');
    }
}
