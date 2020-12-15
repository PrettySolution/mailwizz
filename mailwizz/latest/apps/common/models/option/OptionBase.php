<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * OptionBase
 * 
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com> 
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.3.3.1
 */
 
class OptionBase extends FormModel
{
    // settings category
    protected $_categoryName;

    protected function afterConstruct()
    {
        parent::afterConstruct();
        if ($this->_categoryName) {
            foreach ($this->getAttributes() as $attributeName => $attributeValue) {
                $this->$attributeName = Yii::app()->options->get($this->_categoryName . '.' . $attributeName, $this->$attributeName);
            }    
        }
    }
    
    public function save()
    {
        if (!$this->validate() || !$this->_categoryName) {
            return false;
        }
        
        foreach ($this->getAttributes() as $attributeName => $attributeValue) {
            Yii::app()->options->set($this->_categoryName . '.' . $attributeName, $attributeValue);
        }
        
        return true;
    }

    public function getFileSizeOptions()
    {
        $options = array();
        $size = 1024 * 1024 * 1;
        $options[$size] = Yii::t('settings', '{n} Megabyte|{n} Megabytes', 1);
        for ($i = 2; $i <= 5; ++$i) {
            $size = 1024 * 1024 * $i;
            $options[$size] = Yii::t('settings', '{n} Megabyte|{n} Megabytes', $i);
        }
        for ($i = 10; $i <= 50; ++$i) {
            if ($i % 5 == 0) {
                $size = 1024 * 1024 * $i;
                $options[$size] = Yii::t('settings', '{n} Megabyte|{n} Megabytes', $i);
            }
        }
        $size = 1024 * 1024 * 100;
        $options[$size] = Yii::t('settings', '{n} Megabyte|{n} Megabytes', 100);
        
        // 1.5.0
        $options = Yii::app()->hooks->applyFilters('get_upload_allowed_max_file_size_options', $options);
        
        return $options;
    }
    
    public function getMemoryLimitOptions()
    {
        $options = array(
            ''      => Yii::t('settings', 'System default'),
            '64M'   => Yii::t('settings', '{n} Megabytes', 64),
            '128M'  => Yii::t('settings', '{n} Megabytes', 128),
            '256M'  => Yii::t('settings', '{n} Megabytes', 256),
            '512M'  => Yii::t('settings', '{n} Megabytes', 512),
            '768M'  => Yii::t('settings', '{n} Megabytes', 768),
            '1G'    => Yii::t('settings', '{n} Gigabyte|{n} Gigabytes', 1),
            '2G'    => Yii::t('settings', '{n} Gigabyte|{n} Gigabytes', 2),
            '3G'    => Yii::t('settings', '{n} Gigabyte|{n} Gigabytes', 3),
            '4G'    => Yii::t('settings', '{n} Gigabyte|{n} Gigabytes', 4),
            '5G'    => Yii::t('settings', '{n} Gigabyte|{n} Gigabytes', 5),
        );

        // 1.5.0
        $options = Yii::app()->hooks->applyFilters('get_allowed_max_memory_limit_options', $options);
        
        return $options;
    }
}
