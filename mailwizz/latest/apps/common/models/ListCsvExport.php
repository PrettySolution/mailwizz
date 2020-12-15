<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * ListCsvExport
 * 
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com> 
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.0
 */
 
class ListCsvExport extends FormModel
{
    public $list_id;

    public $segment_id;
    
    public $count = 0;
    
    public $is_first_batch = 1;

    public $current_page = 1;
    
    private $_list;
    
    private $_segment;
    
    public function rules()
    {
        $rules = array(
            array('count, current_page, is_first_batch', 'numerical', 'integerOnly' => true),
            array('list_id, segment_id', 'unsafe'),
        );
        
        return CMap::mergeArray($rules, parent::rules());
    }

    /**
     * @return string
     */
    public function countSubscribers()
    {
        if (!empty($this->segment_id)) {
            $count = $this->countSubscribersByListSegment();
        } else {
            $count = $this->countSubscribersByList();
        }
        
        return $count;
    }

    /**
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function findSubscribers($limit = 10, $offset = 0)
    {
        if (!empty($this->segment_id)) {
            $subscribers = $this->findSubscribersByListSegment($offset, $limit);
        } else {
            $subscribers = $this->findSubscribersByList($offset, $limit);
        }
        
        if (empty($subscribers)) {
            return array();
        }
        
        $data = array();
        foreach ($subscribers as $subscriber) {
            $data[] = $subscriber->getFullData();
        }
        
        return $data;
    }

    /**
     * @return string
     */
    protected function countSubscribersByListSegment()
    {
        $criteria = new CDbCriteria();
        $criteria->compare('t.list_id', (int)$this->list_id);

        return $this->getSegment()->countSubscribers($criteria);
    }

    /**
     * @param int $offset
     * @param int $limit
     * @return array
     */
    protected function findSubscribersByListSegment($offset = 0, $limit = 100)
    {
        $criteria = new CDbCriteria();
        $criteria->select = 't.list_id, t.subscriber_id, t.subscriber_uid, t.email, t.status, t.ip_address, t.source, t.date_added';
        $criteria->compare('t.list_id', (int)$this->list_id);
        
        return $this->getSegment()->findSubscribers($offset, $limit, $criteria);
    }

    /**
     * @return string
     */
    protected function countSubscribersByList()
    {
        $criteria = new CDbCriteria();
        $criteria->compare('t.list_id', (int)$this->list_id);
        
        return ListSubscriber::model()->count($criteria);
    }

    /**
     * @param int $offset
     * @param int $limit
     * @return static[]
     */
    protected function findSubscribersByList($offset = 0, $limit = 100)
    {
        $criteria = new CDbCriteria();
        $criteria->select = 't.list_id, t.subscriber_id, t.subscriber_uid, t.email, t.status, t.ip_address, t.source, t.date_added';
        $criteria->compare('t.list_id', (int)$this->list_id);
        $criteria->offset = $offset;
        $criteria->limit  = $limit;

        return ListSubscriber::model()->findAll($criteria);
    }

    /**
     * @return static
     */
    public function getList()
    {
        if ($this->_list !== null) {
            return $this->_list;
        }
        return $this->_list = Lists::model()->findByPk((int)$this->list_id);
    }

    /**
     * @return static
     */
    public function getSegment()
    {
        if ($this->_segment !== null) {
            return $this->_segment;
        }
        return $this->_segment = ListSegment::model()->findByPk((int)$this->segment_id);
    }
}