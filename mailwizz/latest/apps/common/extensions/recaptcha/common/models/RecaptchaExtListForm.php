<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * RecaptchaExtListForm
 * 
 * @package MailWizz EMA
 * @subpackage Recaptcha
 * @author Serban George Cristian <cristian.serban@mailwizz.com> 
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 */
 
class RecaptchaExtListForm extends FormModel
{
	/**
	 * @var string 
	 */
    public $enabled = 'yes';

	/**
	 * @var string 
	 */
    public $list_uid = '';

	/**
	 * @return array
	 */
    public function rules()
    {
        $rules = array(
            array('enabled', 'in', 'range' => array_keys($this->getYesNoOptions())),
        );
        return CMap::mergeArray($rules, parent::rules());    
    }

	/**
	 * @return array
	 */
    public function attributeLabels()
    {
        $labels = array(
            'enabled' => Yii::t('app', 'Enabled'),
        );
        return CMap::mergeArray($labels, parent::attributeLabels());    
    }

	/**
	 * @return array
	 */
    public function attributeHelpTexts()
    {
        $texts = array(
            'enabled' => Yii::t('app', 'Whether the feature is enabled'),
        );
        return CMap::mergeArray($texts, parent::attributeHelpTexts());
    }

	/**
	 * @return $this
	 */
    public function save()
    {
        $extension  = $this->getExtensionInstance();
        $attributes = array(
            'enabled',
        );
        foreach ($attributes as $name) {
            $extension->setOption('lists.' . $this->list_uid . '.' . $name, $this->$name);
        }
        return $this;
    }

	/**
	 * @return $this
	 */
    public function populate() 
    {
        $extension  = $this->getExtensionInstance();
        $attributes = array(
            'enabled',
        );
        foreach ($attributes as $name) {
            $this->$name = $extension->getOption('lists.' . $this->list_uid . '.' . $name, $this->$name);
        }
        return $this;
    }

	/**
	 * @return mixed
	 */
    public function getExtensionInstance()
    {
        return Yii::app()->extensionsManager->getExtensionInstance('recaptcha');
    }

	/**
	 * @return bool
	 */
    public function getIsEnabled()
    {
    	return $this->enabled !== self::TEXT_NO;
    }
}
