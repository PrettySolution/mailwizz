<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * OptionCampaignAttachment
 * 
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com> 
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.3.2
 */
 
class OptionCampaignAttachment extends OptionBase
{
    // settings category
    protected $_categoryName = 'system.campaign.attachments';
    
    public $enabled = 'no';
    
    public $allowed_file_size = 1048576; // 1 mb by default
    
    public $allowed_files_count = 5;
    
    public $allowed_extensions = array();
    
    public $allowed_mime_types = array();

    public function rules()
    {
        $rules = array(
            array('enabled, allowed_file_size, allowed_files_count', 'required'),
            array('enabled', 'in', 'range' => array_keys($this->getEnabledOptions())),
            array('allowed_file_size', 'in', 'range' => array_keys($this->getFileSizeOptions())),
            array('allowed_files_count', 'numerical', 'integerOnly' => true, 'min' => 1, 'max' => 50),
            array('allowed_extensions, allowed_mime_types', 'safe'),
        );
        
        return CMap::mergeArray($rules, parent::rules());    
    }
    
    public function attributeLabels()
    {
        $labels = array(
            'enabled'               => Yii::t('settings', 'Enabled'),
            'allowed_file_size'     => Yii::t('settings', 'Allowed file size'),
            'allowed_files_count'   => Yii::t('settings', 'Allowed files count'),
            'allowed_extensions'    => Yii::t('settings', 'Allowed extensions'),
            'allowed_mime_types'    => Yii::t('settings', 'Allowed mime types'),
        );
        
        return CMap::mergeArray($labels, parent::attributeLabels());    
    }
    
    public function attributePlaceholders()
    {
        $placeholders = array(
            'enabled'               => '',
            'allowed_file_size'     => '',
            'allowed_files_count'   => Yii::t('settings', 'i.e: 5'),
            'allowed_extensions'    => Yii::t('settings', 'i.e: png'),
            'allowed_mime_types'    => Yii::t('settings', 'i.e: image/png'),
        );
        
        return CMap::mergeArray($placeholders, parent::attributePlaceholders());
    }
    
    public function attributeHelpTexts()
    {
        $texts = array(
            'enabled'               => Yii::t('settings', 'Wheather this feature is enabled and customers can add attachments'),
            'allowed_file_size'     => Yii::t('settings', 'Maximum size of a file allowed for upload'),
            'allowed_files_count'   => Yii::t('settings', 'Maximum number of files allowed for upload'),
            'allowed_extensions'    => Yii::t('settings', 'Only allow uploading of files having this extension'),
            'allowed_mime_types'    => Yii::t('settings', 'Only allow uploading of files having the above extensions and these mime types'),
        );
        
        return CMap::mergeArray($texts, parent::attributeHelpTexts());
    }

    protected function beforeValidate()
    {
        if (!is_array($this->allowed_extensions)) {
            $this->allowed_extensions = array();
        }
        
        if (!is_array($this->allowed_mime_types)) {
            $this->allowed_mime_types = array();
        }
        
        $this->allowed_extensions = array_unique($this->allowed_extensions);
        $this->allowed_mime_types = array_unique($this->allowed_mime_types);
        
        $errors = array();
        foreach ($this->allowed_extensions as $index => $ext) {
            $ext = trim($ext);
            if (empty($ext)) {
                unset($this->allowed_extensions[$index]);
                continue;
            }
            if (!preg_match('/([a-z]){2,5}/', $ext)) {
                $errors[] = Yii::t('settings', 'The extension "{ext}" does not seem to be valid!', array(
                    '{ext}' => CHtml::encode($ext),
                ));
            }
        }
        if (!empty($errors)) {
            $this->addError('allowed_extensions', implode('<br />', $errors));
        }
        
        $errors = array();
        foreach ($this->allowed_mime_types as $index => $mime) {
            $mime = trim($mime);
            if (empty($mime)) {
                unset($this->allowed_mime_types[$index]);
                continue;
            }
            if (!preg_match('/([a-z\-\.\/\_])/', $mime)) {
                $errors[] = Yii::t('settings', 'The mime type "{mime}" does not seem to be valid!', array(
                    '{mime}' => CHtml::encode($mime),
                ));
            }
        }
        if (!empty($errors)) {
            $this->addError('allowed_mime_types', implode('<br />', $errors));
        }
        
        return parent::beforeValidate();
    }
    
    public function getEnabledOptions()
    {
        return $this->getYesNoOptions();
    }
}
