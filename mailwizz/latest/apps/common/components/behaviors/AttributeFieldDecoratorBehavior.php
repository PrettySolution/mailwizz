<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * AttributeFieldDecoratorBehavior
 * 
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com> 
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.0
 */
 
class AttributeFieldDecoratorBehavior extends CBehavior
{
    /**
     * AttributeFieldDecoratorBehavior::isOwnerAllowed()
     * 
     * @return bool
     */
    protected function isOwnerAllowed()
    {
        return $this->owner instanceof BaseActiveRecord || $this->owner instanceof BaseFormModel;
    }

    /**
     * AttributeFieldDecoratorBehavior::getAttributePlaceholder()
     * 
     * @param mixed $attribute
     * @param bool $useLabels
     * @return mixed
     */
    public function getAttributePlaceholder($attribute, $useLabels = true)
    {
        if (!$this->isOwnerAllowed()) {
            return;
        }
        
        $placeholders = (array)$this->owner->attributePlaceholders();
        
        if (isset($placeholders[$attribute])) {
            return $placeholders[$attribute];
        }
        
        if ($useLabels && $label = $this->owner->getAttributeLabel($attribute)) {
            return $label;    
        }

        return null;
    }
    
    /**
     * AttributeFieldDecoratorBehavior::getAttributeHelpText()
     * 
     * @param mixed $attribute
     * @return mixed
     */
    public function getAttributeHelpText($attribute)
    {
        if (!$this->isOwnerAllowed()) {
            return;
        }

        $helpTexts = (array)$this->owner->attributeHelpTexts();
        
        return isset($helpTexts[$attribute]) ? $helpTexts[$attribute] : null;
    }
    
    /**
     * AttributeFieldDecoratorBehavior::getHtmlOptions()
     * 
     * @param string $attribute
     * @param array $htmlOptions
     * @return array
     */
    public function getHtmlOptions($attribute, array $htmlOptions = array()) 
    {
        if (!$this->isOwnerAllowed()) {
            return $htmlOptions;
        }
        
        $htmlOptions = new CMap(CMap::mergeArray($this->_getDefaultHtmlOptions($attribute), $htmlOptions));

        // raise the event for being able to change the html options.
        $this->onHtmlOptionsSetup(new CModelEvent($this, array(
            'attribute'     => $attribute,
            'htmlOptions'   => $htmlOptions, 
        )));
        
        // place for editor instantiation
        if ($htmlOptions->contains('wysiwyg_editor_options')) {
            $wysiwygOptions = (array)$htmlOptions->itemAt('wysiwyg_editor_options');
            $htmlOptions->remove('wysiwyg_editor_options');
            // do the action to register the editor instance
            Yii::app()->hooks->doAction('wysiwyg_editor_instance', $wysiwygOptions);
        }
        
        return $htmlOptions->toArray();
    }

    /**
     * AttributeFieldDecoratorBehavior::_getDefaultHtmlOptions()
     * 
     * @param string $attribute
     * @return array
     */
    protected function _getDefaultHtmlOptions($attribute)
    {
        $options = array(
            'class'         => 'form-control',
            'placeholder'   => $this->owner->getAttributePlaceholder($attribute),
        );
        
        if ($helpText = $this->owner->getAttributeHelpText($attribute)) {
            $options = array_merge(array(
                'data-title'        => $this->owner->getAttributeLabel($attribute),
                'data-container'    => 'body', 
                'data-toggle'       => 'popover',
                'data-content'      => $helpText,
            ), $options);
            
            if (!isset($options['data-placement'])) {
                $options['data-placement'] = 'top';
            }
            
            $options['class'] .= ' has-help-text';
        }
        
        return $options;
    }
    
    /**
     * AttributeFieldDecoratorBehavior::onHtmlOptionsSetup()
     * 
     * @param mixed $event
     * @return
     */
    public function onHtmlOptionsSetup(CEvent $event)
    {
        $this->raiseEvent('onHtmlOptionsSetup', $event);
    }

}