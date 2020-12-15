<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * BaseFormModel
 * 
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com> 
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.0
 */
 
class BaseFormModel extends CFormModel
{
    const TEXT_YES = 'yes';
    
    const TEXT_NO = 'no';
    
    private $_modelName;
    
    /**
     * BaseFormModel::rules()
     * 
     * @return array
     */
    public function rules()
    {
        $hooks  = Yii::app()->hooks;
        $apps   = Yii::app()->apps;
        $filter = $apps->getCurrentAppName() . '_model_'.strtolower(get_class($this)).'_'.strtolower(__FUNCTION__);
        $rules  = $hooks->applyFilters($filter, new CList());
        
        $this->onRules(new CModelEvent($this, array(
            'rules' => $rules,
        )));
        
        return $rules->toArray();
    }
    
    /**
     * BaseFormModel::onRules()
     * 
     * @param mixed $event
     * @return
     */
    public function onRules(CModelEvent $event)
    {
        $this->raiseEvent('onRules', $event);
    }
    
    /**
     * BaseFormModel::behaviors()
     * 
     * @return array
     */
    public function behaviors()
    {
        $behaviors = CMap::mergeArray(parent::behaviors(), array(
            'shortErrors' => array(
                'class' => 'common.components.behaviors.AttributesShortErrorsBehavior'
            ),
            'fieldDecorator' => array(
                'class' => 'common.components.behaviors.AttributeFieldDecoratorBehavior'
            ),
            'paginationOptions' => array(
                'class' => 'common.components.behaviors.PaginationOptionsBehavior'
            ),
        ));

        $behaviors = new CMap($behaviors);
        
        $hooks  = Yii::app()->hooks;
        $apps   = Yii::app()->apps;
        $filter = $apps->getCurrentAppName() . '_model_'.strtolower(get_class($this)).'_'.strtolower(__FUNCTION__);
        
        $behaviors = $hooks->applyFilters($filter, $behaviors);
        
        $this->onBehaviors(new CModelEvent($this, array(
            'behaviors' => $behaviors,
        )));
        
        return $behaviors->toArray();
    }

    /**
     * BaseFormModel::onBehaviors()
     * 
     * @param mixed $event
     * @return
     */
    public function onBehaviors(CModelEvent $event)
    {
        $this->raiseEvent('onBehaviors', $event);
    }
    
    /**
     * BaseFormModel::attributeLabels()
     * 
     * @return array
     */
    public function attributeLabels()
    {
        $labels = new CMap(array(
            'status'        => Yii::t('app', 'Status'),
            'date_added'    => Yii::t('app', 'Date added'),
            'last_updated'  => Yii::t('app', 'Last updated'),
        ));
        
        $hooks  = Yii::app()->hooks;
        $apps   = Yii::app()->apps;
        $filter = $apps->getCurrentAppName() . '_model_'.strtolower(get_class($this)).'_'.strtolower(__FUNCTION__);
        $labels = $hooks->applyFilters($filter, $labels);
        
        $this->onAttributeLabels(new CModelEvent($this, array(
            'labels' => $labels,
        )));
        
        return $labels->toArray();
    }
    
    /**
     * BaseFormModel::onAttributeLabels()
     * 
     * @param mixed $event
     * @return
     */
    public function onAttributeLabels(CModelEvent $event)
    {
        $this->raiseEvent('onAttributeLabels', $event);
    }
    
    /**
     * BaseFormModel::attributeHelpTexts()
     * 
     * @return array
     */
    public function attributeHelpTexts()
    {
        $hooks  = Yii::app()->hooks;
        $apps   = Yii::app()->apps;
        $filter = $apps->getCurrentAppName() . '_model_'.strtolower(get_class($this)).'_'.strtolower(__FUNCTION__);
        $texts  = $hooks->applyFilters($filter, new CMap());
        
        $this->onAttributeHelpTexts(new CModelEvent($this, array(
            'texts' => $texts,
        )));
        
        return $texts->toArray();
    }

    /**
     * BaseFormModel::onAttributeHelpTexts()
     * 
     * @param mixed $event
     * @return
     */
    public function onAttributeHelpTexts(CModelEvent $event)
    {
        $this->raiseEvent('onAttributeHelpTexts', $event);
    }
    
    /**
     * BaseFormModel::attributePlaceholders()
     * 
     * @return array
     */
    public function attributePlaceholders()
    {
        $hooks  = Yii::app()->hooks;
        $apps   = Yii::app()->apps;
        $filter = $apps->getCurrentAppName() . '_model_'.strtolower(get_class($this)).'_'.strtolower(__FUNCTION__);
        
        $placeholders = $hooks->applyFilters($filter, new CMap());
        
        $this->onAttributePlaceholders(new CModelEvent($this, array(
            'placeholders' => $placeholders,
        )));
        
        return $placeholders->toArray();
    }

    /**
     * BaseFormModel::onAttributePlaceholders()
     * 
     * @param mixed $event
     * @return
     */
    public function onAttributePlaceholders(CModelEvent $event)
    {
        $this->raiseEvent('onAttributePlaceholders', $event);
    }
     
    /**
     * BaseFormModel::getModelName()
     * 
     * @return string
     */
    public function getModelName()
    {
        if ($this->_modelName === null) {
            $this->_modelName = get_class($this);
        }
        return $this->_modelName;
    }
    
    public function getYesNoOptions()
    {
        return array(
            self::TEXT_YES  => ucfirst(Yii::t('app', self::TEXT_YES)),
            self::TEXT_NO   => ucfirst(Yii::t('app', self::TEXT_NO)),
        );
    }
} 