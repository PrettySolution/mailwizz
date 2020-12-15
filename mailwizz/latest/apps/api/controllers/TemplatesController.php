<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * TemplatesController
 *
 * Handles the CRUD actions for templates that will be used in campaigns.
 *
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.0
 */

class TemplatesController extends Controller
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
     * Handles the listing of the templates.
     * The listing is based on page number and number of templates per page.
     * This action will produce a valid ETAG for caching purposes.
     */
    public function actionIndex()
    {
        $request = Yii::app()->request;

        $perPage    = (int)$request->getQuery('per_page', 10);
        $page       = (int)$request->getQuery('page', 1);
        $filter     = (array)$request->getQuery('filter', array());
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
        $criteria->compare('t.customer_id', (int)Yii::app()->user->getId());
        
        // 1.4.4
        if (!empty($filter) && !empty($filter['name'])) {
            $criteria->compare('t.name', $filter['name'], true);
        }
        
        $count = CustomerEmailTemplate::model()->count($criteria);

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

        $criteria->order    = 't.template_id DESC';
        $criteria->limit    = $perPage;
        $criteria->offset   = ($page - 1) * $perPage;

        $templates = CustomerEmailTemplate::model()->findAll($criteria);

        foreach ($templates as $template) {
            $attributes = $template->getAttributes(array('template_uid', 'name'));

            $attributes['screenshot'] = null;
            if (!empty($template->screenshot)) {
                $attributes['screenshot'] = Yii::app()->apps->getAppUrl('frontend', $template->screenshot, true, true);
            }

            $data['records'][] = $attributes;
        }

        return $this->renderJson(array(
            'status'    => 'success',
            'data'      => $data
        ), 200);
    }

    /**
     * Handles the listing of a single template.
     * This action will produce a valid ETAG for caching purposes.
     */
    public function actionView($template_uid)
    {
        $template = CustomerEmailTemplate::model()->findByAttributes(array(
            'template_uid'  => $template_uid,
            'customer_id'   => (int)Yii::app()->user->getId(),
        ));

        if (empty($template)) {
            return $this->renderJson(array(
                'status'    => 'error',
                'error'     => Yii::t('api', 'The template does not exist.')
            ), 404);
        }

        $attributes = $template->getAttributes(array('name', 'content'));

        $attributes['screenshot'] = null;
        if (!empty($template->screenshot)) {
            $attributes['screenshot'] = Yii::app()->apps->getAppUrl('frontend', $template->screenshot, true, true);
        }

        $data = array(
            'record' => $attributes
        );

        return $this->renderJson(array(
            'status'    => 'success',
            'data'      => $data,
        ), 200);
    }

    /**
     * Handles the creation of a new template for campaigns.
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

        $attributes = (array)$request->getPost('template', array());
        $template = new CustomerEmailTemplate();
        $template->attributes = $attributes;
        $template->customer_id = (int)Yii::app()->user->getId();

        if (!empty($attributes['archive'])) {
            $archivePath = FileSystemHelper::getTmpDirectory() . '/' . StringHelper::random() . '.zip';
            $archiveContent = @base64_decode($attributes['archive']);

            unset($attributes['archive']);

            if (empty($archiveContent)) {
                return $this->renderJson(array(
                    'status'    => 'error',
                    'error'     => Yii::t('api', 'It does not seem that you have selected an archive.')
                ), 422);
            }

            // http://www.garykessler.net/library/file_sigs.html
            $magicNumbers   = array('504B0304');
            $substr         = CommonHelper::functionExists('mb_substr') ? 'mb_substr' : 'substr';
            $firstBytes     = strtoupper(bin2hex($substr($archiveContent, 0, 4)));

            if (!in_array($firstBytes, $magicNumbers)) {
                return $this->renderJson(array(
                    'status'    => 'error',
                    'error'     => Yii::t('api', 'Your archive does not seem to be a valid zip file.')
                ), 422);
            }

            if (!@file_put_contents($archivePath, $archiveContent)) {
                return $this->renderJson(array(
                    'status'    => 'error',
                    'error'     => Yii::t('api', 'Cannot write archive in the temporary location.')
                ), 422);
            }

            $_FILES['archive'] = array(
                'name'      => basename($archivePath),
                'type'      => 'application/zip',
                'tmp_name'  => $archivePath,
                'error'     => 0,
                'size'      => filesize($archivePath),
            );

            $archiveTemplate = new CustomerEmailTemplate('upload');
            $archiveTemplate->customer_id = (int)Yii::app()->user->getId();
            $archiveTemplate->archive     = CUploadedFile::getInstanceByName('archive');
            $archiveTemplate->name        = $template->name;

            if (!$archiveTemplate->validate() || !$archiveTemplate->uploader->handleUpload()) {
                return $this->renderJson(array(
                    'status'    => 'error',
                    'error'     => $archiveTemplate->shortErrors->getAll()
                ), 422);
            }

            return $this->renderJson(array(
                'status'        => 'success',
                'template_uid'  => $archiveTemplate->template_uid,
            ), 201);
        }

        if (!empty($attributes['content'])) {
            $template->content = @base64_decode($attributes['content']);
        }

        if (!$template->save()) {
            return $this->renderJson(array(
                'status'    => 'error',
                'error'     => $template->shortErrors->getAll(),
            ), 422);
        }

        return $this->renderJson(array(
            'status'        => 'success',
            'template_uid'  => $template->template_uid,
        ), 201);
    }

    /**
     * Handles the updating of an existing template for campaigns.
     *
     * @param $template_uid The template unique id.
     */
    public function actionUpdate($template_uid)
    {
        $request = Yii::app()->request;

        if (!$request->isPutRequest) {
            return $this->renderJson(array(
                'status'    => 'error',
                'error'     => Yii::t('api', 'Only PUT requests allowed for this endpoint.')
            ), 400);
        }

        $template = CustomerEmailTemplate::model()->findByAttributes(array(
            'template_uid'  => $template_uid,
            'customer_id'   => (int)Yii::app()->user->getId(),
        ));

        if (empty($template)) {
            return $this->renderJson(array(
                'status'    => 'error',
                'error'     => Yii::t('api', 'The template does not exist.')
            ), 404);
        }

        $attributes = (array)$request->getPut('template', array());
        $template->attributes = $attributes;
        $template->customer_id = (int)Yii::app()->user->getId();

        if (!empty($attributes['archive'])) {
            $archivePath = FileSystemHelper::getTmpDirectory() . '/' . StringHelper::random() . '.zip';
            $archiveContent = @base64_decode($attributes['archive']);

            unset($attributes['archive']);

            if (empty($archiveContent)) {
                return $this->renderJson(array(
                    'status'    => 'error',
                    'error'     => Yii::t('api', 'It does not seem that you have selected an archive.')
                ), 422);
            }

            // http://www.garykessler.net/library/file_sigs.html
            $magicNumbers   = array('504B0304');
            $substr         = CommonHelper::functionExists('mb_substr') ? 'mb_substr' : 'substr';
            $firstBytes     = strtoupper(bin2hex($substr($archiveContent, 0, 4)));

            if (!in_array($firstBytes, $magicNumbers)) {
                return $this->renderJson(array(
                    'status'    => 'error',
                    'error'     => Yii::t('api', 'Your archive does not seem to be a valid zip file.')
                ), 422);
            }

            if (!@file_put_contents($archivePath, $archiveContent)) {
                return $this->renderJson(array(
                    'status'    => 'error',
                    'error'     => Yii::t('api', 'Cannot write archive in the temporary location.')
                ), 422);
            }

            $_FILES['archive'] = array(
                'name'      => basename($archivePath),
                'type'      => 'application/zip',
                'tmp_name'  => $archivePath,
                'error'     => 0,
                'size'      => filesize($archivePath),
            );

            $template->setScenario('upload');
            $template->archive = CUploadedFile::getInstanceByName('archive');

            if (!$template->validate() || !$template->uploader->handleUpload()) {
                return $this->renderJson(array(
                    'status'    => 'error',
                    'error'     => $template->shortErrors->getAll()
                ), 422);
            }

            return $this->renderJson(array(
                'status'        => 'success',
                'template_uid'  => $template->template_uid,
            ), 201);
        }

        if (empty($template->content) || !empty($attributes['content'])) {
            $template->content = @base64_decode($attributes['content']);
        }

        if (!$template->save()) {
            return $this->renderJson(array(
                'status'    => 'error',
                'error'     => $template->shortErrors->getAll(),
            ), 422);
        }

        return $this->renderJson(array(
            'status' => 'success',
        ), 200);
    }

    /**
     * Handles deleting an existing template for campaigns.
     *
     * @param $template_uid The template unique id.
     */
    public function actionDelete($template_uid)
    {
        $request = Yii::app()->request;

        if (!$request->isDeleteRequest) {
            return $this->renderJson(array(
                'status'    => 'error',
                'error'     => Yii::t('api', 'Only DELETE requests allowed for this endpoint.')
            ), 400);
        }

        $template = CustomerEmailTemplate::model()->findByAttributes(array(
            'template_uid'  => $template_uid,
            'customer_id'   => (int)Yii::app()->user->getId(),
        ));

        if (empty($template)) {
            return $this->renderJson(array(
                'status'    => 'error',
                'error'     => Yii::t('api', 'The template does not exist.')
            ), 404);
        }

        $template->delete();

        // since 1.3.5.9
        Yii::app()->hooks->doAction('controller_action_delete_data', $collection = new CAttributeCollection(array(
            'controller' => $this,
            'model'      => $template,
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
        $row = array();

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
                     FROM `{{customer_email_template}}` `a`
                     WHERE `a`.`customer_id` = :cid
                     ORDER BY a.`template_id` DESC
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

            $sql = 'SELECT UNIX_TIMESTAMP(t.last_updated) as `timestamp` FROM `{{customer_email_template}}` t WHERE `t`.`template_uid` = :uid AND `t`.`customer_id` = :cid LIMIT 1';
            $command = Yii::app()->getDb()->createCommand($sql);
            $command->bindValue(':uid', $request->getQuery('template_uid'), PDO::PARAM_STR);
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
