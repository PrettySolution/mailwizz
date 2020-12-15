<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * ListCsvImport
 *
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.0
 */

class ListCsvImport extends ListImportAbstract
{
    
    public function rules()
    {
        $mimes   = null;
        $options = Yii::app()->options;
        if ($options->get('system.importer.check_mime_type', 'yes') == 'yes' && CommonHelper::functionExists('finfo_open')) {
            $mimes = Yii::app()->extensionMimes->get('csv')->toArray();
        }
		
        $rules = array(
            array('file', 'required', 'on' => 'upload'),
            array('file', 'file', 'types' => array('csv'), 'mimeTypes' => $mimes, 'maxSize' => $this->file_size_limit, 'allowEmpty' => true),
            array('file_name', 'length', 'is' => 44),
        );

        return CMap::mergeArray($rules, parent::rules());
    }
}
