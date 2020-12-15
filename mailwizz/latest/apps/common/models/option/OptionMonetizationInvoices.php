<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * OptionMonetizationInvoices
 * 
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com> 
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.3.4.8
 */
 
class OptionMonetizationInvoices extends OptionBase
{
    // settings category
    protected $_categoryName = 'system.monetization.invoices';

    public $prefix = 'MW-IN ';
    
    public $logo;
    
    public $notes;
    
    public $email_subject;
    
    public $email_content;
    
    public $color_code = '3c8dbc';

    public function rules()
    {
        $logoMimes = null;
        if (CommonHelper::functionExists('finfo_open')) {
            $logoMimes = Yii::app()->extensionMimes->get(array('png', 'jpg', 'jpeg', 'gif'))->toArray();
        }
        
        $rules = array(
            array('prefix', 'length', 'min' => 2, 'max' => 255),
            array('logo', 'file', 'types' => array('png', 'jpg', 'jpeg', 'gif'), 'mimeTypes' => $logoMimes, 'allowEmpty' => true),
            array('notes, email_content', 'length', 'min' => 2, 'max' => 10000),
            array('email_subject', 'length', 'min' => 2, 'max' => 255),
            array('color_code', 'match', 'pattern' => '/([a-z0-9]{6})/'),
            array('color_code', 'length', 'is' => 6),
        );
        
        return CMap::mergeArray($rules, parent::rules());    
    }

    public function attributeLabels()
    {
        $labels = array(
            'prefix'        => Yii::t('settings', 'Prefix'),
            'logo'          => Yii::t('settings', 'Logo'),
            'notes'         => Yii::t('settings', 'Notes'),
            'email_content' => Yii::t('settings', 'Email content'),
            'email_subject' => Yii::t('settings', 'Email subject'),
            'color_code'    => Yii::t('settings', 'Color code'),
        );
        
        return CMap::mergeArray($labels, parent::attributeLabels());    
    }
    
    public function attributePlaceholders()
    {
        $placeholders = array(
            'prefix'     => 'MW-IN ',
            'color_code' => '3c8dbc',
        );
        
        return CMap::mergeArray($placeholders, parent::attributePlaceholders());
    }
    
    public function attributeHelpTexts()
    {
        $texts = array(
            'prefix'        => Yii::t('settings', 'The prefix for generated invoices'),
            'logo'          => Yii::t('settings', 'The invoices logo'),
            'notes'         => Yii::t('settings', 'Additional notes shown in the invoice footer'),
            'email_content' => Yii::t('settings', 'When the invoice is emailed, this will be the content that will appear in the email body. Leave it empty to use defaults'),
            'email_subject' => Yii::t('settings', 'When the invoice is emailed, this will be the subject of the email. Leave it empty to use defaults'),
            'color_code'    => Yii::t('settings', '6 characters length hex color code to be used in the invoice')
        );
        
        return CMap::mergeArray($texts, parent::attributeHelpTexts());
    }

    protected function afterValidate()
    {
        parent::afterValidate();
        $this->handleUploadedLogo();
    }
    
    public function getLogoUrl($width = 230, $height = 130, $forceSize = false)
    {
        if (empty($this->logo)) {
            return;
        }
        return ImageHelper::resize($this->logo, $width, $height, $forceSize);
    }
    
    protected function handleUploadedLogo()
    {
        if ($this->hasErrors()) {
            return;
        }
        
        if (!($logo = CUploadedFile::getInstance($this, 'logo'))) {
            return;
        }
        
        $storagePath = Yii::getPathOfAlias('root.frontend.assets.files.invoices');
        if (!file_exists($storagePath) || !is_dir($storagePath)) {
            if (!@mkdir($storagePath, 0777, true)) {
                $this->addError('logo', Yii::t('settings', 'The invoices storage directory({path}) does not exists and cannot be created!', array(
                    '{path}' => $storagePath,
                )));
                return;
            }
        }
        
        $newLogoName = uniqid(rand(0, time())) . '-' . $logo->getName();
        if (!$logo->saveAs($storagePath . '/' . $newLogoName)) {
            $this->addError('logo', Yii::t('settings', 'Cannot move the avatar into the correct storage folder!'));
            return;
        }
        
        $this->logo = '/frontend/assets/files/invoices/' . $newLogoName;
    }
}
