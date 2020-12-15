<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * List_segmentsController
 * 
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com> 
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.0
 */

class List_segmentsController extends Controller
{
    // access rules for this controller
    public function accessRules()
    {
        return array(
            // allow all authenticated users on all actions
            array('allow', 'users' => array('@')),
            // deny all rule.
            array('deny'),
        );
    }
    
    /**
     * Handles the listing of the email list segments.
     * The listing is based on page number and number of list segments per page.
     * This action will produce a valid ETAG for caching purposes.
     */
    public function actionIndex($list_uid)
    {
        $request = Yii::app()->request;
        
        $list = Lists::model()->findByAttributes(array(
            'list_uid'      => $list_uid,
            'customer_id'   => (int)Yii::app()->user->getId(),
        ));
        
        if (empty($list)) {
            return $this->renderJson(array(
                'status'    => 'error',
                'error'     => Yii::t('api', 'The subscribers list does not exist.')
            ), 404);
        }
        
        $perPage    = (int)$request->getQuery('per_page', 10);
        $page        = (int)$request->getQuery('page', 1);
        
        $maxPerPage    = 50;
        $minPerPage    = 10;
        
        if ($perPage < $minPerPage) {
            $perPage = $minPerPage;
        }
        
        if ($perPage > $maxPerPage) {
            $perPage = $maxPerPage;
        }
        
        if ($page < 1) {
            $page = 1;
        }
        
        $data = array(
            'count'         => null,
            'total_pages'   => null,
            'current_page'  => null,
            'next_page'     => null,
            'prev_page'     => null,
            'records'       => array(),
        );
        
        $criteria = new CDbCriteria();
        $criteria->select = 't.segment_id, t.segment_uid, t.name';
        $criteria->compare('t.list_id', (int)$list->list_id);
        $criteria->addNotInCondition('status', array(ListSegment::STATUS_PENDING_DELETE));
        
        $count = ListSegment::model()->count($criteria);
        
        if ($count == 0) {
            return $this->renderJson(array(
                'status'    => 'success',
                'data'      => $data
            ), 200);
        }
        
        $totalPages = ceil($count / $perPage);
        
        $data['count']          = $count;
        $data['current_page']   = $page;
        $data['next_page']      = $page < $totalPages ? $page + 1 : null;
        $data['prev_page']      = $page > 1 ? $page - 1 : null;
        $data['total_pages']    = $totalPages;
        
        $criteria->order    = 't.segment_id DESC';
        $criteria->limit    = $perPage;
        $criteria->offset   = ($page - 1) * $perPage;
        
        $segments = ListSegment::model()->findAll($criteria);

        foreach ($segments as $segment) {
            $record = $segment->getAttributes(array('segment_uid', 'name'));
            $record['subscribers_count'] = $segment->countSubscribers();
            $data['records'][] = $record;
        }
        
        return $this->renderJson(array(
            'status'    => 'success',
            'data'      => $data,
        ), 200);
    }
    
    /**
     * It will generate the timestamp that will be used to generate the ETAG for GET requests.
     */
    public function generateLastModified()
    {
        static $lastModified;
        
        if ($lastModified !== null) {
            return $lastModified;
        }
        
        $request = Yii::app()->request;
        $row = array();
        
        if ($this->action->id == 'index') {
            
            $listUid    = $request->getQuery('list_uid');
            $perPage    = (int)$request->getQuery('per_page', 10);
            $page       = (int)$request->getQuery('page', 1);
            
            $maxPerPage = 50;
            $minPerPage = 10;
            
            if ($perPage < $minPerPage) {
                $perPage = $minPerPage;
            }
            
            if ($perPage > $maxPerPage) {
                $perPage = $maxPerPage;
            }
            
            if ($page < 1) {
                $page = 1;
            }

            $list = Lists::model()->findByAttributes(array(
                'list_uid'      => $listUid,
                'customer_id'   => (int)Yii::app()->user->getId(),
            ));
            
            if (empty($list)) {
                return $lastModified = parent::generateLastModified();
            }

            $limit  = $perPage;
            $offset = ($page - 1) * $perPage;

            $sql = '
                SELECT AVG(t.last_updated) as `timestamp`
                FROM (
                     SELECT `a`.`list_id`, UNIX_TIMESTAMP(`a`.`last_updated`) as `last_updated`
                     FROM `{{list_segment}}` `a` 
                     WHERE `a`.`list_id` = :lid 
                     ORDER BY a.`segment_id` DESC 
                     LIMIT :l OFFSET :o
                ) AS t 
                WHERE `t`.`list_id` = :lid
            ';
            
            $command = Yii::app()->getDb()->createCommand($sql);
            $command->bindValue(':lid', (int)$list->list_id, PDO::PARAM_INT);
            $command->bindValue(':l', (int)$limit, PDO::PARAM_INT);
            $command->bindValue(':o', (int)$offset, PDO::PARAM_INT);
            
            $row = $command->queryRow();
        }
        
        if (isset($row['timestamp'])) {
            $timestamp = round($row['timestamp']);
            // avoid for when subscribers imported having same timestamp
            if (preg_match('/\.(\d+)/', $row['timestamp'], $matches)) {
                $timestamp += (int)$matches[1];
            }
            return $lastModified = $timestamp;
        }
        
        return $lastModified = parent::generateLastModified();
    }
}
