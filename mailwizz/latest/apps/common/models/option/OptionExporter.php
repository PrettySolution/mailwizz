<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * OptionExporter
 * 
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com> 
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.0
 */
 
class OptionExporter extends OptionBase
{
    // settings category
    protected $_categoryName = 'system.exporter';
    
    public $enabled = 'yes';
    
    public $process_at_once = 500;
    
    public $pause = 1; // pause between the batches
    
    public $memory_limit;
    
    public function rules()
    {
        $rules = array(
            array('enabled, process_at_once, pause', 'required'),
            array('enabled', 'in', 'range' => array_keys($this->getYesNoOptions())),
            array('process_at_once, pause', 'numerical', 'integerOnly' => true),
            array('process_at_once', 'numerical', 'min' => 5, 'max' => 10000),
            array('pause', 'numerical', 'min' => 0, 'max' => 60),
            array('memory_limit', 'in', 'range' => array_keys($this->getMemoryLimitOptions())),
        );
        
        return CMap::mergeArray($rules, parent::rules());    
    }
    
    public function attributeLabels()
    {
        $labels = array(
            'enabled'           => Yii::t('settings', 'Enabled'),
            'process_at_once'   => Yii::t('settings', 'Process at once'),
            'pause'             => Yii::t('settings', 'Pause'),
            'memory_limit'      => Yii::t('settings', 'Memory limit'),
        );
        
        return CMap::mergeArray($labels, parent::attributeLabels());    
    }
    
    public function attributePlaceholders()
    {
        $placeholders = array(
            'enabled'           => null,
            'process_at_once'   => null,
            'pause'             => null,
            'memory_limit'      => null,
        );
        
        return CMap::mergeArray($placeholders, parent::attributePlaceholders());
    }
    
    public function attributeHelpTexts()
    {
        $texts = array(
            'enabled'           => Yii::t('settings', 'Whether customers are allowed to export subscribers.'),
            'process_at_once'   => Yii::t('settings', 'How many subscribers to process at once for each batch.'),
            'pause'             => Yii::t('settings', 'How many seconds the script should "sleep" after each batch of subscribers.'),
            'memory_limit'      => Yii::t('settings', 'The maximum memory amount the export process is allowed to use while processing one batch of subscribers.'),
        );
        
        return CMap::mergeArray($texts, parent::attributeHelpTexts());
    }
}
