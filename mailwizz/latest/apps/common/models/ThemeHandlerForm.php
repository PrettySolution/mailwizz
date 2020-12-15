<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * ThemeHandlerForm
 * 
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com> 
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.0
 */
 
class ThemeHandlerForm extends FormModel
{
    public $archive;

    public function rules()
    {
        $mimes = null;
        if (CommonHelper::functionExists('finfo_open')) {
            $mimes = Yii::app()->extensionMimes->get('zip')->toArray();
        }
        
        $rules = array(
            array('archive', 'required', 'on' => 'upload'),
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
    
    public function upload($appName)
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
        
        $themesDir = Yii::getPathOfAlias('root.' . $appName . '.themes');
        if ((!file_exists($themesDir) || !is_dir($themesDir)) && !@mkdir($themesDir, 0777, true)) {
            $this->addError('archive', Yii::t('app', 'Cannot create directory "{dirPath}". Make sure the parent directory is writable by the webserver!', array('{dirPath}' => $themesDir)));
            return false;
        }
        
        if (!is_writable($themesDir)) {
            $this->addError('archive', Yii::t('app', 'The directory "{dirPath}" is not writable by the webserver!', array('{dirPath}' => $themesDir)));
            return false;
        }
        
        $zip->extractTo($themesDir);
        $zip->close();
        
        return true;
    }
    
    public function getDataProvider($appName)
    {
        $manager = Yii::app()->themeManager;
        $themesInstances = $manager->getThemesInstances($appName);

        $themes = array();
        foreach ($themesInstances as $theme) {
            $description = CHtml::encode($theme->description);
            $name        = CHtml::encode($theme->name);
            $pageUrl     = null;
            
            if ($manager->isThemeEnabled($theme->dirName, $appName)) {
                if (!$theme->pageUrl) {
                    $className  = get_class($theme);
                    $reflection = new ReflectionClass($className);
                    if($reflection->getMethod('settingsPage')->class == $className) {
                        $pageUrl = Yii::app()->createUrl('theme/settings', array('app' => $appName, 'theme' => $theme->dirName));
                    }
                } else {
                    $pageUrl = $theme->pageUrl;
                } 
                if ($pageUrl) {
                    $name = CHtml::link($name, $pageUrl); 
                }
            }

            $themes[] = array(
                'id'            => $theme->dirName,
                'name'          => $name,
                'description'   => $description,
                'version'       => $theme->version,
                'author'        => $theme->email ? CHtml::link(CHtml::encode($theme->author), 'mailto:'.CHtml::encode($theme->email)) : CHtml::encode($theme->author),
                'website'       => $theme->website ? CHtml::link(Yii::t('themes', 'Visit website'), CHtml::encode($theme->website), array('target' => '_blank')) : null,
                'enabled'       => $manager->isThemeEnabled($theme->dirName, $appName),
                'pageUrl'       => $pageUrl,
                'enableUrl'     => Yii::app()->createUrl('theme/enable',   array('app' => $appName, 'name' => $theme->dirName)),
                'disableUrl'    => Yii::app()->createUrl('theme/disable',  array('app' => $appName, 'name' => $theme->dirName)),
                'deleteUrl'     => Yii::app()->createUrl('theme/delete',   array('app' => $appName, 'name' => $theme->dirName)),
            );
        }
        
        return new CArrayDataProvider($themes);
    }
}