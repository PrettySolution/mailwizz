<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * OptionEmailTemplate
 * 
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com> 
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.0
 */
 
class OptionEmailTemplate extends OptionBase
{
    // settings category
    protected $_categoryName = 'system.email_templates';

	/**
	 * @var 
	 */
    public $common;

	/**
	 * @return array
	 */
    public function rules()
    {
        $rules = array(
            array('common', 'required', 'on' => 'common'),
        );
        
        return CMap::mergeArray($rules, parent::rules());    
    }

	/**
	 * @return array
	 */
    public function attributeLabels()
    {
        $labels = array(
            'common'    => Yii::t('settings', 'Common template'),
        );
        
        return CMap::mergeArray($labels, parent::attributeLabels());    
    }

	/**
	 * @return array
	 */
    public function attributePlaceholders()
    {
        $placeholders = array(
            'common' => null,
        );
        
        return CMap::mergeArray($placeholders, parent::attributePlaceholders());
    }

	/**
	 * @return array
	 */
    public function attributeHelpTexts()
    {
        $texts = array(
            'common' => Yii::t('settings', 'The "common" template is used when sending notifications, password reset emails, etc.'),
        );
        
        return CMap::mergeArray($texts, parent::attributeHelpTexts());
    }

	/**
	 * @return bool
	 */
    protected function beforeValidate()
    {
        if ($this->scenario == 'common' && strpos($this->common, '[CONTENT]') === false) {
            $this->addError('common', Yii::t('settings', 'The "[CONTENT]" tag is required but it has not been found in the content.'));
        }
        return parent::beforeValidate();
    }

	/**
	 * @param $id
	 *
	 * @return mixed
	 */
    public static function getTypeById($id)
    {
    	$types = self::getTypesList();
    	foreach ($types as $type) {
    		if ($type['id'] == $id) {
    			return $type;
		    }
	    }
	    return $types[0];
    }

	/**
	 * @return array
	 */
    public static function getTypesList()
    {
    	return array(
    		array('id' => 'common', 'name' => 'Common layout'),
	    );
    }
}
