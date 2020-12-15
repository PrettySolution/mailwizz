<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * LanguageUploadForm
 * 
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com> 
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.1
 */
 
class LanguageUploadForm extends FormModel
{
    public $archive;
    
    public function rules()
    {
        $mimes = null;
        if (CommonHelper::functionExists('finfo_open')) {
            $mimes = Yii::app()->extensionMimes->get('zip')->toArray();
        }
        
        $rules = array(
            array('archive', 'required'),
            array('archive', 'file', 'types' => array('zip'), 'mimeTypes' => $mimes, 'allowEmpty' => true),
        );
        
        return CMap::mergeArray($rules, parent::rules());
    }
    
    public function attributeLabels()
    {
        $labels = array(
            'archive'   => Yii::t('app', 'Archive'),
        );
        
        return CMap::mergeArray($labels, parent::attributeLabels());    
    }
    
    public function upload()
    {
        // no reason to go further if there are errors.
        if (!$this->validate()) {
            return false;
        }
        
        // we need the zip archive class, cannot work without.
        if (!class_exists('ZipArchive', false)) {
            $this->addError('archive', Yii::t('app', 'ZipArchive class required in order to unzip the file.'));
            return false;
        }
        
        $zip = new ZipArchive();
        if (!$zip->open($this->archive->tempName)) {
            $this->addError('archive', Yii::t('app', 'Cannot open the archive file.'));
            return false;
        }
        
        if (!Yii::app()->hasComponent('messages') || !(Yii::app()->getComponent('messages') instanceof CPhpMessageSource)) {
            $this->addError('archive', Yii::t('languages', 'The archive upload is only allowed for php message source.'));
            return false;
        }
        
        $languagesDir = Yii::app()->messages->basePath;
        if ((!file_exists($languagesDir) || !is_dir($languagesDir)) && !@mkdir($languagesDir, 0777, true)) {
            $this->addError('archive', Yii::t('app', 'Cannot create directory "{dirPath}". Make sure the parent directory is writable by the webserver!', array('{dirPath}' => $languagesDir)));
            return false;
        }
        
        if (!is_writable($languagesDir)) {
            $this->addError('archive', Yii::t('app', 'The directory "{dirPath}" is not writable by the webserver!', array('{dirPath}' => $languagesDir)));
            return false;
        }
        
        $existingLanguageFolders = (array)FileSystemHelper::getDirectoryNames($languagesDir);
        
        $zip->extractTo($languagesDir);
        $zip->close();
        
        $updatedLanguageFolders = (array)FileSystemHelper::getDirectoryNames($languagesDir);
        
        $newLanguages = array_diff($updatedLanguageFolders, $existingLanguageFolders);
        if (empty($newLanguages)) {
            return true;
        }
        
        $error = array();
        foreach ($newLanguages as $dirName) {
            
            try {
                $locale = Yii::app()->getLocale($dirName);
            } catch (Exception $e) {
                FileSystemHelper::deleteDirectoryContents($languagesDir . '/' . $dirName, true, 1);
                $error[] = Yii::t('languages', 'The language directory {dirName} is not valid and was deleted!', array(
                    '{dirName}' => $dirName,
                ));
                continue;
            }
            
            $languageCode = $regionCode = null;
            if (strpos($dirName, '_') !== false) {
                $languageAndLocaleCode = explode('_', $dirName);
                list($languageCode, $regionCode) = $languageAndLocaleCode;
            } else {
                $languageCode = $dirName;
            }

            $criteria = new CDbCriteria();
            $criteria->compare('language_code', $languageCode);
            if (!empty($regionCode)) {
                $criteria->compare('region_code', $regionCode);
            }
            $language = Language::model()->find($criteria);
            if (!empty($language)) {
                continue;
            }
            
            $language = new Language();
            $language->name = ucwords($locale->getLanguage($dirName));
            $language->language_code = $languageCode;
            $language->region_code = $regionCode;
            if (!$language->save()) {
                FileSystemHelper::deleteDirectoryContents($languagesDir . '/' . $dirName, true, 1);
                $error[] = Yii::t('languages', 'The language "{languageName}" cannot be saved, failure reason: ', array(
                    '{languageName}' => $language->name,
                ));
                $error[] = $language->shortErrors->getAllAsString();
                continue;
            }
        }
        
        if (!empty($error)) {
            $this->addError('archive', implode("<br />", $error));
            return false;
        }
        
        return true;
    }
}