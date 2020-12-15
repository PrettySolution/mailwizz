<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * CountriesController
 * 
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com> 
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.0
 */

class CountriesController extends Controller
{
    public $cacheableActions = array('index', 'zones');
    
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
     * Handles the listing of the countries.
     * The listing is based on page number and number of countries per page.
     * This action will produce a valid ETAG for caching purposes.
     */
    public function actionIndex()
    {
        $request    = Yii::app()->request;
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
        
        $data = array(
            'count'         => null,
            'total_pages'   => null,
            'current_page'  => null,
            'next_page'     => null,
            'prev_page'     => null,
            'records'       => array(),
        );
        
        $criteria = new CDbCriteria();
        $criteria->select = 't.country_id, t.name, t.code';
        $count = Country::model()->count($criteria);
        
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
        
        $criteria->order = 't.name ASC';
        $criteria->limit = $perPage;
        $criteria->offset= ($page - 1) * $perPage;
        
        $countries = Country::model()->findAll($criteria);
        
        foreach ($countries as $country) {
            $record = $country->getAttributes(array('country_id', 'name', 'code'));
            $data['records'][] = $record;
        }
        
        return $this->renderJson(array(
            'status'    => 'success',
            'data'      => $data
        ), 200);
    }
    
    /**
     * Handles the listing of the country zones.
     * The listing is based on page number and number of zones per page.
     * This action will produce a valid ETAG for caching purposes.
     */
    public function actionZones($country_id)
    {
        $request    = Yii::app()->request;
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
        
        $data = array(
            'count'         => null,
            'total_pages'   => null,
            'current_page'  => null,
            'next_page'     => null,
            'prev_page'     => null,
            'records'       => array(),
        );
        
        $criteria = new CDbCriteria();
        $criteria->select = 't.zone_id, t.name, t.code';
        $criteria->compare('country_id', (int)$country_id);
        $count = Zone::model()->count($criteria);
        
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
        
        $criteria->order    = 't.name ASC';
        $criteria->limit    = $perPage;
        $criteria->offset   = ($page - 1) * $perPage;
        
        $zones = Zone::model()->findAll($criteria);
        
        foreach ($zones as $zone) {
            $record = $zone->getAttributes(array('zone_id', 'name', 'code'));
            $data['records'][] = $record;
        }
        
        return $this->renderJson(array(
            'status'    => 'success',
            'data'      => $data
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
        
        $request    = Yii::app()->request;
        $row        = array();
        
        if ($this->action->id == 'index' || $this->action->id == 'zones') {
        
            $country_id = (int)$request->getQuery('country_id');
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
            
            $limit  = $perPage;
            $offset = ($page - 1) * $perPage;    
        }
        
        if ($this->action->id == 'index') {
        
            $sql = '
                SELECT AVG(t.last_updated) as `timestamp`
                FROM (
                SELECT UNIX_TIMESTAMP(`a`.`last_updated`) as `last_updated`
                FROM `{{country}}` `a` 
                WHERE 1 
                ORDER BY a.`name` ASC 
                LIMIT :l OFFSET :o
                ) AS t 
                WHERE 1
            ';
            
            $command = Yii::app()->getDb()->createCommand($sql);
            $command->bindValue(':l', (int)$limit, PDO::PARAM_INT);
            $command->bindValue(':o', (int)$offset, PDO::PARAM_INT);
            
            $row = $command->queryRow();
        
        } elseif ($this->action->id == 'zones') {
        
            $sql = '
                SELECT AVG(t.last_updated) as `timestamp`
                FROM (
                SELECT `a`.`country_id`, UNIX_TIMESTAMP(`a`.`last_updated`) as `last_updated`
                FROM `{{zone}}` `a` 
                WHERE `a`.`country_id` = :cid
                ORDER BY a.`name` ASC 
                LIMIT :l OFFSET :o
                ) AS t 
                WHERE `t`.`country_id` = :cid
            ';
            
            $command = Yii::app()->getDb()->createCommand($sql);
            $command->bindValue(':cid', (int)$country_id, PDO::PARAM_INT);
            $command->bindValue(':l', (int)$limit, PDO::PARAM_INT);
            $command->bindValue(':o', (int)$offset, PDO::PARAM_INT);
            
            $row = $command->queryRow();
        
        }
        
        if (isset($row['timestamp'])) {
            $timestamp = round($row['timestamp']);
            if (preg_match('/\.(\d+)/', $row['timestamp'], $matches)) {
                $timestamp += (int)$matches[1];
            }
            return $lastModified = $timestamp;
        }
        
        return $lastModified = parent::generateLastModified();
    }
}
