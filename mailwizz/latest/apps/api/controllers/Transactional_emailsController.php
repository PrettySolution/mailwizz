<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * Transactional_emailsController
 *
 * Handles the CRUD actions for transactional emails.
 *
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.3.4.5
 */

class Transactional_emailsController extends Controller
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
     * Handles the listing of the transactional emails.
     * The listing is based on page number and number of templates per page.
     * This action will produce a valid ETAG for caching purposes.
     */
    public function actionIndex()
    {
        $request = Yii::app()->request;

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
        $criteria->compare('customer_id', (int)Yii::app()->user->getId());

        $count = TransactionalEmail::model()->count($criteria);

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

        $criteria->order    = 't.email_id DESC';
        $criteria->limit    = $perPage;
        $criteria->offset   = ($page - 1) * $perPage;

        $emails = TransactionalEmail::model()->findAll($criteria);

        foreach ($emails as $email) {
            $attributes = $email->getAttributes();
            unset($attributes['email_id']);
            $data['records'][] = $attributes;
        }

        return $this->renderJson(array(
            'status'    => 'success',
            'data'      => $data
        ), 200);
    }

    /**
     * Handles the listing of a single email.
     * This action will produce a valid ETAG for caching purposes.
     */
    public function actionView($email_uid)
    {
        $email = TransactionalEmail::model()->findByAttributes(array(
            'email_uid'   => $email_uid,
            'customer_id' => (int)Yii::app()->user->getId(),
        ));

        if (empty($email)) {
            return $this->renderJson(array(
                'status'    => 'error',
                'error'     => Yii::t('api', 'The email does not exist.')
            ), 404);
        }

        $attributes = $email->getAttributes();
        unset($attributes['email_id']);

        $data = array(
            'record' => $attributes
        );

        return $this->renderJson(array(
            'status'    => 'success',
            'data'      => $data,
        ), 200);
    }

    /**
     * Handles the creation of a new transactional email.
     */
    public function actionCreate()
    {
        $request = Yii::app()->request;

        if (!$request->isPostRequest) {
            return $this->renderJson(array(
                'status'    => 'error',
                'error'     => Yii::t('api', 'Only POST requests allowed for this endpoint.')
            ), 400);
        }
        
        $attributes = (array)$request->getPost('email', array());

        $email = new TransactionalEmail();
        $email->attributes  = $attributes;
        $email->body        = !empty($email->body) ? @base64_decode($email->body) : null;
        $email->plain_text  = !empty($email->plain_text) ? @base64_decode($email->plain_text) : null;
        $email->customer_id = (int)Yii::app()->user->getId();

        if (!$email->save()) {
            return $this->renderJson(array(
                'status'    => 'error',
                'error'     => $email->shortErrors->getAll(),
            ), 422);
        }

        return $this->renderJson(array(
            'status'     => 'success',
            'email_uid'  => $email->email_uid,
        ), 201);
    }

    /**
     * Handles deleting an existing transactional email.
     *
     * @param $email_uid The email unique id.
     */
    public function actionDelete($email_uid)
    {
        $request = Yii::app()->request;

        if (!$request->isDeleteRequest) {
            return $this->renderJson(array(
                'status'    => 'error',
                'error'     => Yii::t('api', 'Only DELETE requests allowed for this endpoint.')
            ), 400);
        }

        $email = TransactionalEmail::model()->findByAttributes(array(
            'email_uid'   => $email_uid,
            'customer_id' => (int)Yii::app()->user->getId(),
        ));

        if (empty($email)) {
            return $this->renderJson(array(
                'status'    => 'error',
                'error'     => Yii::t('api', 'The email does not exist.')
            ), 404);
        }

        $email->delete();

        // since 1.3.5.9
        Yii::app()->hooks->doAction('controller_action_delete_data', $collection = new CAttributeCollection(array(
            'controller' => $this,
            'model'      => $email,
        )));

        return $this->renderJson(array(
            'status' => 'success',
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
        $row     = array();

        if ($this->action->id == 'index') {

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

            $sql = '
                SELECT AVG(t.last_updated) as `timestamp`
                FROM (
                     SELECT `a`.`customer_id`, UNIX_TIMESTAMP(`a`.`last_updated`) as `last_updated`
                     FROM `{{transactional_email}}` `a`
                     WHERE `a`.`customer_id` = :cid
                     ORDER BY a.`email_id` DESC
                     LIMIT :l OFFSET :o
                ) AS t
                WHERE `t`.`customer_id` = :cid
            ';

            $command = Yii::app()->getDb()->createCommand($sql);
            $command->bindValue(':cid', (int)Yii::app()->user->getId(), PDO::PARAM_INT);
            $command->bindValue(':l', (int)$limit, PDO::PARAM_INT);
            $command->bindValue(':o', (int)$offset, PDO::PARAM_INT);

            $row = $command->queryRow();

        } elseif ($this->action->id == 'view') {

            $sql = 'SELECT UNIX_TIMESTAMP(t.last_updated) as `timestamp` FROM `{{transactional_email}}` t WHERE `t`.`email_uid` = :uid AND `t`.`customer_id` = :cid LIMIT 1';
            $command = Yii::app()->getDb()->createCommand($sql);
            $command->bindValue(':uid', $request->getQuery('email_uid'), PDO::PARAM_STR);
            $command->bindValue(':cid', (int)Yii::app()->user->getId(), PDO::PARAM_INT);

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
