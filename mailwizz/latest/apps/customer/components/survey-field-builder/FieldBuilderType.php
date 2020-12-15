<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * FieldBuilderType
 * 
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com> 
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.7.8
 */
 
class FieldBuilderType extends CWidget
{
    // return instance errors.
    public $errors = array();
    
    // survey model
    private $_survey;
    
    // field type model
    private $_fieldType;
    
    // survey responder model
    private $_responder;
    
    // counter
    private static $_index = -1;
    
    final public function setSurvey(Survey $survey)
    {
        $this->_survey = $survey;
    }
    
    final public function getSurvey()
    {
        if (!($this->_survey instanceof Survey)) {
            throw new Exception('FieldBuilderType::$survey must be an instance of Survey');
        }
        return $this->_survey;
    }
    
    final public function setFieldType(SurveyFieldType $fieldType)
    {
        $this->_fieldType = $fieldType;
    }
    
    final public function getFieldType()
    {
        if (!($this->_fieldType instanceof SurveyFieldType)) {
            throw new Exception('FieldBuilderType::$fieldType must be an instance of SurveyFieldType');
        }
        return $this->_fieldType;
    }
    
    final public function setResponder(SurveyResponder $responder)
    {
        $this->_responder = $responder;
    }
    
    final public function getResponder()
    {
        if (!($this->_responder instanceof SurveyResponder)) {
            throw new Exception('FieldBuilderType::$responder must be an instance of SurveyResponder');
        }
        return $this->_responder;
    }
    
    final public function getIndex()
    {
        return self::$_index++;
    }
}