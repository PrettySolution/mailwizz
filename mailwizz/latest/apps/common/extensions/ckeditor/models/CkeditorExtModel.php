<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * CkeditorExtModel
 *
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 */

class CkeditorExtModel extends FormModel
{
    public $enable_filemanager_user = 0;

    public $enable_filemanager_customer = 0;

    public $filemanager_theme;

    public $default_toolbar = 'Default';

    public function rules()
    {
        $instance = Yii::app()->extensionsManager->getExtensionInstance('ckeditor');
        $rules = array(
            array('enable_filemanager_user, enable_filemanager_customer, default_toolbar', 'required'),
            array('enable_filemanager_user, enable_filemanager_customer', 'in', 'range' => array(0, 1)),
            array('default_toolbar', 'in', 'range' => $instance->getEditorToolbars()),
            array('filemanager_theme', 'match', 'pattern' => '/^[a-z\-\_]+$/i')
        );

        return CMap::mergeArray($rules, parent::rules());
    }

    public function attributeLabels()
    {
        $labels = array(
            'enable_filemanager_user'       => Yii::t('ext_ckeditor', 'Enable filemanager for users'),
            'enable_filemanager_customer'   => Yii::t('ext_ckeditor', 'Enable filemanager for customers'),
            'default_toolbar'               => Yii::t('ext_ckeditor', 'Default toolbar'),
            'filemanager_theme'             => Yii::t('ext_ckeditor', 'Filemanager theme'),
        );

        return CMap::mergeArray($labels, parent::attributeLabels());
    }

    public function attributePlaceholders()
    {
        $placeholders = array();

        return CMap::mergeArray($placeholders, parent::attributePlaceholders());
    }

    public function attributeHelpTexts()
    {
        $texts = array(
            'enable_filemanager_user'       => Yii::t('ext_ckeditor', 'Whether to enable the filemanager for users'),
            'enable_filemanager_customer'   => Yii::t('ext_ckeditor', 'Whether to enable the filemanager for customers'),
            'default_toolbar'               => Yii::t('ext_ckeditor', 'Default toolbar for all editor instances'),
            'filemanager_theme'             => Yii::t('ext_ckeditor', 'The file manager theme'),
        );

        return CMap::mergeArray($texts, parent::attributeHelpTexts());
    }

    public function getOptionsDropDown()
    {
        return array(
            0 => Yii::t('app', 'No'),
            1 => Yii::t('app', 'Yes'),
        );
    }

    public function getToolbarsDropDown()
    {
        $instance = Yii::app()->extensionsManager->getExtensionInstance('ckeditor');
        $toolbars = $instance->getEditorToolbars();
        return array_combine($toolbars, $toolbars);
    }

    public function getFilemanagerThemesDropDown()
    {
        $instance = Yii::app()->extensionsManager->getExtensionInstance('ckeditor');
        $themes   = $instance->getFilemanagerThemes();
        $options  = array('' => '');
        foreach ($themes as $theme) {
            $options[$theme['name']] = ucwords($theme['name']);
        }
        return $options;
    }

    public function populate($extensionInstance)
    {
        $this->enable_filemanager_user      = $extensionInstance->getOption('enable_filemanager_user', $this->enable_filemanager_user);
        $this->enable_filemanager_customer  = $extensionInstance->getOption('enable_filemanager_customer', $this->enable_filemanager_customer);
        $this->default_toolbar              = $extensionInstance->getOption('default_toolbar', $this->default_toolbar);
        $this->filemanager_theme            = $extensionInstance->getOption('filemanager_theme', $this->filemanager_theme);
        return $this;
    }

    public function save($extensionInstance)
    {
        $extensionInstance->setOption('enable_filemanager_user', $this->enable_filemanager_user);
        $extensionInstance->setOption('enable_filemanager_customer', $this->enable_filemanager_customer);
        $extensionInstance->setOption('default_toolbar', $this->default_toolbar);
        $extensionInstance->setOption('filemanager_theme', $this->filemanager_theme);
        return $this;
    }
}
