<?php defined('MW_PATH') || exit('No direct script access allowed');    
/**
 * List_fieldsController
 * 
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com> 
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.0
 */    
class List_fieldsController extends Controller
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
     * Handles the listing of the email list custom fields.
     * This action will produce a valid ETAG for caching purposes.
     */
    public function actionIndex($list_uid)
    {
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
        
        $fields = ListField::model()->findAllByAttributes(array(
            'list_id'    => $list->list_id,
        ));
        
        if (empty($fields)) {
            return $this->renderJson(array(
                'status'    => 'error',
                'error'     => Yii::t('api', 'The subscribers list does not have any custom field defined.')
            ), 404);
        }
        
        $data = array(
            'records' => array(),
        );
        
        foreach ($fields as $field) {
            $attributes         = $field->getAttributes(array('tag', 'label', 'required', 'help_text'));
            $attributes['type'] = $field->type->getAttributes(array('name', 'identifier', 'description'));
            
            // since 1.3.6.2
            if (!empty($field->options)) {
                $attributes['options'] = array();
                foreach ($field->options as $option) {
                    $attributes['options'][$option->value] = $option->name;
                }
            }
            
            $data['records'][]  = $attributes;
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
            
            $listUid = $request->getQuery('list_uid');
            
            $sql = '
                SELECT l.list_id, AVG(UNIX_TIMESTAMP(f.last_updated)) as `timestamp` 
                    FROM {{list}} l
                INNER JOIN {{list_field}} f ON f.list_id = l.list_id 
                WHERE l.list_uid = :uid AND l.customer_id = :cid
                GROUP BY l.list_id 
            ';
            $command = Yii::app()->getDb()->createCommand($sql);
            $command->bindValue(':uid', $listUid, PDO::PARAM_STR);
            $command->bindValue(':cid', (int)Yii::app()->user->getId(), PDO::PARAM_INT);
            
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