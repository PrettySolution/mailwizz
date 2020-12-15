<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * ModelMetaDataBehavior
 * 
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com> 
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.0
 */
 
class ModelMetaDataBehavior extends CActiveRecordBehavior
{
    private $_modelMetaData;
    
    /**
     * ModelMetaDataBehavior::getModelMetaData()
     * 
     * @return CMap
     */
    public function getModelMetaData()
    {
        if(empty($this->_modelMetaData) || !($this->_modelMetaData instanceof CMap)) {
            $this->_modelMetaData = new CMap();
        }
        
        if ($this->owner instanceof ActiveRecord && $this->owner->hasAttribute('meta_data') && !empty($this->owner->meta_data) && $this->_modelMetaData->getCount() == 0) {
            $this->_modelMetaData->mergeWith((array)(@unserialize($this->owner->meta_data)));    
        }
        
        return $this->_modelMetaData;
    }
    
    /**
     * ModelMetaDataBehavior::setModelMetaData()
     * 
     * @param string $key
     * @param mixed $value
     * @return ModelMetaDataBehavior
     */
    public function setModelMetaData($key, $value)
    {
        $this->getModelMetaData()->add($key, $value);
        return $this;
    }

    /**
     * @return $this
     */
    public function saveModelMetaData()
    {
        if ($this->owner instanceof ActiveRecord && $this->owner->hasAttribute('meta_data')) {
            $metaData = @serialize($this->getModelMetaData()->toArray());
            $this->owner->setAttribute('meta_data', $metaData);
            $this->owner->saveAttributes(array(
                'meta_data' => $metaData,
            ));
        }
        return $this;
    }

    /**
     * ModelMetaDataBehavior::beforeSave()
     * 
     * @param CModelEvent $event
     */
    public function beforeSave($event)
    {
        if ($this->owner instanceof ActiveRecord && $this->owner->hasAttribute('meta_data')) {
            $this->owner->setAttribute('meta_data', @serialize($this->getModelMetaData()->toArray()));    
        }
    }


}