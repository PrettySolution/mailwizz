<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * ListSubscriberBulkFromSource
 * 
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com> 
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.3.4.2
 */
 
class ListSubscriberBulkFromSource extends ListSubscriber
{
    public $bulk_from_file;
    
    public $bulk_from_text;
    
    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        $mimes   = null;
        $options = Yii::app()->options;
        if ($options->get('system.importer.check_mime_type', 'yes') == 'yes' && function_exists('finfo_open')) {
            $mimes = Yii::app()->extensionMimes->get('csv')->toArray();
        }
        
        $rules = array(
            array('status', 'in', 'range' => array_keys($this->getBulkActionsList())),
            array('bulk_from_file, bulk_from_text', 'safe'),
            array('bulk_from_file', 'file', 'types' => array('csv'), 'mimeTypes' => $mimes, 'maxSize' => 5242880, 'allowEmpty' => true),
        );
        
        return $rules;
    }
    
    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        $labels = array(
            'bulk_from_file'   => Yii::t('list_subscribers', 'From file'),
            'bulk_from_text'   => Yii::t('list_subscribers', 'From text'),
            'status'           => Yii::t('list_subscribers', 'Action'),
        );
        
        return CMap::mergeArray(parent::attributeLabels(), $labels);
    }
    
    /**
     * @return array customized attribute help text (name=>help text)
     */
    public function attributeHelpTexts()
    {
        $texts = array(
            'bulk_from_file'   => Yii::t('list_subscribers', 'Bulk action from CSV file, one email address per row and/or separated by a comma.'),
            'bulk_from_text'   => Yii::t('list_subscribers', 'Bulk action from text area, one email address per line and/or separated by a comma.'),
        );
        
        return CMap::mergeArray($texts, parent::attributeHelpTexts());
    }
    
    /**
     * @return array customized attribute placeholders (name=>placeholder)
     */
    public function attributePlaceholders()
    {
        $placeholders = array(
            'bulk_from_file'   => '',
            'bulk_from_text'   => '',
        );
        
        return CMap::mergeArray($placeholders, parent::attributePlaceholders());
    }


    /**
     * Returns the static model of the specified AR class.
     * Please note that you should have this exact method in all your CActiveRecord descendants!
     * @param string $className active record class name.
     * @return Customer the static model class
     */
    public static function model($className=__CLASS__)
    {
        return parent::model($className);
    }
    
}
