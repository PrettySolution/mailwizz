<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * DeliveryServerCsvImport
 * 
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com> 
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.3.5
 */
 
class DeliveryServerCsvImport extends FormModel
{
    public $file;
    
    public $file_name;

    public $file_size_limit = 5242880; // 5 mb by default

    public function rules()
    {
        $mimes   = null;
        $options = Yii::app()->options;
        if ($options->get('system.importer.check_mime_type', 'yes') == 'yes' && CommonHelper::functionExists('finfo_open')) {
            $mimes = Yii::app()->extensionMimes->get('csv')->toArray();
        }

        $rules = array(
            // array('file', 'required', 'on' => 'upload'),
            array('file', 'unsafe'),
            array('file', 'file', 'types' => array('csv'), 'mimeTypes' => $mimes, 'maxSize' => $this->file_size_limit, 'allowEmpty' => true),
            array('file_name', 'length', 'is' => 44),
        );
        
        return CMap::mergeArray($rules, parent::rules());
    }
    
    public function upload()
    {
        // no reason to go further if there are errors.
        if (!$this->validate()) {
            return false;
        }
        
        $filePath = Yii::getPathOfAlias('common.runtime.delivery-server-import') . '/';
        if (!file_exists($filePath) && !@mkdir($filePath, 0777, true)) {
            $this->addError('file', Yii::t('servers', 'Unable to create target directory!'));
            return false;    
        }
        
        $this->file_name = sha1(uniqid(rand(0, time()), true)) . '.csv';
        
        if (!$this->file->saveAs($filePath . $this->file_name)) {
            $this->file_name = null;
            $this->addError('file', Yii::t('servers', 'Unable to move the uploaded file!'));
            return false;
        }
        
        if (!StringHelper::fixFileEncoding($filePath . $this->file_name)) {
             @unlink($filePath . $this->file_name);
             $this->addError('file', Yii::t('servers', 'Your uploaded file is not using the UTF-8 charset. Please save it in UTF-8 then upload it again.'));
             $this->file_name = null;
             return false;
         }
        
        return true;
    }
}