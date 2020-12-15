<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * ListSegmentCsvExport
 * 
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com> 
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.3.4.8
 */
 
class ListSegmentCsvExport extends FormModel
{
    public $list_id;
    
    public $segment_id;
    
    public $count = 0;
    
    public $is_first_batch = 1;

    public $current_page = 1;

    public function rules()
    {
        $rules = array(
            array('count, current_page, is_first_batch', 'numerical', 'integerOnly' => true),
            array('list_id, segment_id', 'unsafe'),
        );
        
        return CMap::mergeArray($rules, parent::rules());
    }
    
    public function countSubscribers()
    {
        $segment = ListSegment::model()->findByAttributes(array(
            'segment_id' => (int)$this->segment_id,
            'list_id'    => (int)$this->list_id,
        ));
        
        if (empty($segment)) {
            return 0;
        }
        
        return $segment->countSubscribers();
    }
    
    public function findSubscribers($limit = 10, $offset = 0)
    {
        $segment = ListSegment::model()->findByAttributes(array(
            'segment_id' => (int)$this->segment_id,
            'list_id'    => (int)$this->list_id,
        ));
        
        if (empty($segment)) {
            return array();
        }
        
        $subscribers = $segment->findSubscribers($offset, $limit);
        
        if (empty($subscribers)) {
            return array();
        }
        
        $criteria = new CDbCriteria();
        $criteria->select = 'field_id, tag';
        $criteria->compare('list_id', $this->list_id);
        $criteria->order = 'sort_order ASC, tag ASC';
        $fields = ListField::model()->findAll($criteria);
        
        if (empty($fields)) {
            return array();
        }
        
        $data = array();
        foreach ($subscribers as $subscriber) {
            $_data = array();
            foreach ($fields as $field) {
                $value = null;
                
                $criteria = new CDbCriteria();
                $criteria->select = 'value';
                $criteria->compare('field_id', (int)$field->field_id);
                $criteria->compare('subscriber_id', (int)$subscriber->subscriber_id);
                $valueModels = ListFieldValue::model()->findAll($criteria);

                if (!empty($valueModels)) {
                    $value = array();
                    foreach($valueModels as $valueModel) {
                        $value[] = $valueModel->value;
                    }
                    $value = implode(', ', $value);
                }
                $_data[$field->tag] = CHtml::encode($value);
            }
            $data[] = $_data;    
        }
        
        unset($subscribers, $fields, $_data, $subscriber, $field);
        
        return $data;
    }
}