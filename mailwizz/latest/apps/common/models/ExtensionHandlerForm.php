<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * ExtensionHandlerForm
 *
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.0
 */

class ExtensionHandlerForm extends FormModel
{
    public $archive;

    public function rules()
    {
        $mimes = null;
        if (CommonHelper::functionExists('finfo_open')) {
            $mimes = Yii::app()->extensionMimes->get('zip')->toArray();
        }

        $rules = array(
            // array('archive', 'required', 'on' => 'upload'),
            array('archive', 'unsafe'),
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
        if (empty($this->archive) || !$zip->open($this->archive->tempName)) {
            $this->addError('archive', Yii::t('app', 'Cannot open the archive file.'));
            return false;
        }

        $extensionsDir = Yii::getPathOfAlias('extensions');
        if ((!file_exists($extensionsDir) || !is_dir($extensionsDir)) && !@mkdir($extensionsDir, 0777, true)) {
            $this->addError('archive', Yii::t('app', 'Cannot create directory "{dirPath}". Make sure the parent directory is writable by the webserver!', array('{dirPath}' => $extensionsDir)));
            return false;
        }

        if (!is_writable($extensionsDir)) {
            $this->addError('archive', Yii::t('app', 'The directory "{dirPath}" is not writable by the webserver!', array('{dirPath}' => $extensionsDir)));
            return false;
        }

        $zip->extractTo($extensionsDir);
        $zip->close();

        return true;
    }

    public function getDataProvider($coreExtensions = false)
    {
        $em = Yii::app()->extensionsManager;
        $extensions = ($coreExtensions === false) ? $em->extensions : $em->coreExtensions;

        $exts = array();
        foreach ($extensions as $extDirName => $ext) {
            $description = CHtml::encode($ext->description);
            $name = CHtml::encode($ext->name);
            if ($ext->isEnabled && $ext->pageUrl) {
                $name = CHtml::link($name, $ext->pageUrl);
            }
            $exts[] = array(
                'id'            => $ext->dirName,
                'name'          => $name,
                'description'   => $description,
                'version'       => $ext->version,
                'author'        => $ext->email ? CHtml::link(CHtml::encode($ext->author), 'mailto:'.CHtml::encode($ext->email)) : CHtml::encode($ext->author),
                'website'       => $ext->website ? CHtml::link(Yii::t('extensions', 'Visit website'), CHtml::encode($ext->website), array('target' => '_blank')) : null,
                'enabled'       => $ext->isEnabled,
                'pageUrl'       => $ext->pageUrl,
                'canBeDeleted'  => $ext->canBeDeleted,
                'canBeDisabled' => $ext->canBeDisabled,
                'mustUpdate'    => $ext->getMustUpdate(),
            );
        }

        return new CArrayDataProvider($exts, array(
            'pagination' => array(
                'pageSize' => 50,
            ),
        ));
    }
}
