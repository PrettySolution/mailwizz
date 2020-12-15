<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * FieldBuilderType
 * 
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com> 
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.0
 */
 
class FieldBuilderType extends CWidget
{
    // return instance errors.
    public $errors = array();
    
    // mail list model
    private $_list;
    
    // field type model
    private $_fieldType;
    
    // mail list subscriber model
    private $_subscriber;
    
    // counter
    private static $_index = -1;
    
    final public function setList(Lists $list) 
    {
        $this->_list = $list;
    }
    
    final public function getList()
    {
        if (!($this->_list instanceof Lists)) {
            throw new Exception('FieldBuilderType::$list must be an instance of Lists');
        }
        return $this->_list;
    }
    
    final public function setFieldType(ListFieldType $fieldType) 
    {
        $this->_fieldType = $fieldType;
    }
    
    final public function getFieldType()
    {
        if (!($this->_fieldType instanceof ListFieldType)) {
            throw new Exception('FieldBuilderType::$fieldType must be an instance of ListFieldType');
        }
        return $this->_fieldType;
    }
    
    final public function setSubscriber(ListSubscriber $subscriber) 
    {
        $this->_subscriber = $subscriber;
    }
    
    final public function getSubscriber()
    {
        if (!($this->_subscriber instanceof ListSubscriber)) {
            throw new Exception('FieldBuilderType::$subscriber must be an instance of ListSubscriber');
        }
        return $this->_subscriber;
    }
    
    final public function getIndex()
    {
        return self::$_index++;
    }
}