<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * ListsController
 *
 * Handles the CRUD actions for lists.
 *
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.0
 */

class ListsController extends Controller
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
     * Handles the listing of the email lists.
     * The listing is based on page number and number of lists per page.
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
        $criteria->compare('customer_id', (int)Yii::app()->user->getId());
        $criteria->addNotInCondition('status', array(Lists::STATUS_PENDING_DELETE, Lists::STATUS_ARCHIVED));

        $count = Lists::model()->count($criteria);

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

        $criteria->order    = 't.list_id DESC';
        $criteria->limit    = $perPage;
        $criteria->offset   = ($page - 1) * $perPage;

        $lists = Lists::model()->findAll($criteria);

        foreach ($lists as $list) {
            $general = $defaults = $notifications = $company = array();
            $general = $list->getAttributes(array('list_uid', 'name', 'display_name', 'description'));
            if (!empty($list->default)) {
                $defaults = $list->default->getAttributes(array('from_name', 'reply_to', 'subject'));
            }
            if (!empty($list->customerNotification)) {
                $notifications = $list->customerNotification->getAttributes(array('subscribe', 'unsubscribe', 'subscribe_to', 'unsubscribe_to'));
            }
            if (!empty($list->company)) {
                $company = $list->company->getAttributes(array('name', 'address_1', 'address_2', 'zone_name', 'city', 'zip_code', 'phone', 'address_format'));
                if (!empty($list->company->country)) {
                    $company['country'] = $list->company->country->getAttributes(array('country_id', 'name', 'code'));
                }
                if (!empty($list->company->zone)) {
                    $company['zone'] = $list->company->zone->getAttributes(array('zone_id', 'name', 'code'));
                }
            }
            $record = array(
                'general'       => $general,
                'defaults'      => $defaults,
                'notifications' => $notifications,
                'company'       => $company,
            );
            $data['records'][] = $record;
        }

        return $this->renderJson(array(
            'status'    => 'success',
            'data'      => $data
        ), 200);
    }

    /**
     * Handles the listing of a single email list.
     * This action will produce a valid ETAG for caching purposes.
     */
    public function actionView($list_uid)
    {
        if (!($list = $this->loadListByUid($list_uid))) {
            return $this->renderJson(array(
                'status'    => 'error',
                'error'     => Yii::t('api', 'The list does not exist.')
            ), 404);
        }

        $general = $defaults = $notifications = $company = array();
        $general = $list->getAttributes(array('list_uid', 'name', 'display_name', 'description'));
        if (!empty($list->default)) {
            $defaults = $list->default->getAttributes(array('from_name', 'reply_to', 'subject'));
        }
        if (!empty($list->customerNotification)) {
            $notifications = $list->customerNotification->getAttributes(array('subscribe', 'unsubscribe', 'subscribe_to', 'unsubscribe_to'));
        }
        if (!empty($list->company)) {
            $company = $list->company->getAttributes(array('name', 'address_1', 'address_2', 'zone_name', 'city', 'zip_code', 'phone', 'address_format'));
            if (!empty($list->company->country)) {
                $company['country'] = $list->company->country->getAttributes(array('country_id', 'name', 'code'));
            }
            if (!empty($list->company->zone)) {
                $company['zone'] = $list->company->zone->getAttributes(array('zone_id', 'name', 'code'));
            }
        }

        $record = array(
            'general'       => $general,
            'defaults'      => $defaults,
            'notifications' => $notifications,
            'company'       => $company,
        );

        $data = array('record' => $record);

        return $this->renderJson(array(
            'status'    => 'success',
            'data'      => $data,
        ), 200);
    }

    /**
     * Handles the creation of a new email list.
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

        $general        = (array)$request->getPost('general', array());
        $defaults       = (array)$request->getPost('defaults', array());
        $notifications  = (array)$request->getPost('notifications', array());
        $company        = (array)$request->getPost('company', array());
        $customer       = Yii::app()->user->getModel();

        if (($maxLists = (int)$customer->getGroupOption('lists.max_lists', -1)) > -1) {
            $criteria = new CDbCriteria();
            $criteria->compare('customer_id', (int)$customer->customer_id);
            $criteria->addNotInCondition('status', array(Lists::STATUS_PENDING_DELETE));
            $listsCount = Lists::model()->count($criteria);
            if ($listsCount >= $maxLists) {
                return $this->renderJson(array(
                    'status'    => 'error',
                    'error'     => Yii::t('api', 'You have reached the maximum number of allowed lists.')
                ), 422);
            }
        }

        $listModel = new Lists();
        $listModel->attributes = $general;
        if (!$listModel->validate()) {
            return $this->renderJson(array(
                'status'    => 'error',
                'error'     => array(
                    'general' => $listModel->shortErrors->getAll()
                ),
            ), 422);
        }

        $defaultsModel = new ListDefault();
        $defaultsModel->attributes = $defaults;
        if (!$defaultsModel->validate()) {
            return $this->renderJson(array(
                'status'    => 'error',
                'error'     => array(
                    'defaults' => $defaultsModel->shortErrors->getAll()
                ),
            ), 422);
        }

        $notificationsModel = new ListCustomerNotification();
        $notificationsModel->attributes = $notifications;
        if (!$notificationsModel->validate()) {
            return $this->renderJson(array(
                'status'    => 'error',
                'error'     => array(
                    'notifications' => $notificationsModel->shortErrors->getAll()
                ),
            ), 422);
        }

        $companyModel = new ListCompany();
        if (!empty($customer->company)) {
            $companyModel->mergeWithCustomerCompany($customer->company);
        }

        if (isset($company['country'])) {
            if (empty($company['country_id'])) {
                $country = Country::model()->findByAttributes(array('name' => $company['country']));
                if (!empty($country)) {
                    $company['country_id'] = $country->country_id;
                }
            }
            unset($company['country']);
        }

        if (isset($company['zone'])) {
            if (isset($company['country_id'])) {
                $zone = Zone::model()->findByAttributes(array(
                    'country_id'    => $company['country_id'],
                    'name'          => $company['zone']
                ));
                if (!empty($zone)) {
                    $company['zone_id'] = $zone->zone_id;
                }
            }
            unset($company['zone']);
        }

        $companyModel->attributes = $company;
        if (!$companyModel->validate()) {
            return $this->renderJson(array(
                'status' => 'error',
                'error'  => array(
                    'company' => $companyModel->shortErrors->getAll()
                ),
            ), 422);
        }

        // at this point there should be no more errors.
        $listModel->customer_id = $customer->customer_id;
        $listModel->attachBehavior('listDefaultFields', array(
            'class' => 'customer.components.db.behaviors.ListDefaultFieldsBehavior',
        ));

        $models = array($listModel, $defaultsModel, $notificationsModel, $companyModel);

        foreach ($models as $model) {
            if (!($model instanceof Lists)) {
                $model->list_id = $listModel->list_id;
            }
            $model->save(false);
        }

        if ($logAction = Yii::app()->user->getModel()->asa('logAction')) {
            $logAction->listCreated($listModel);
        }

        return $this->renderJson(array(
            'status'    => 'success',
            'list_uid'  => $listModel->list_uid,
        ), 201);
    }

    /**
     * Handles the updating of an existing email list.
     *
     * @param $list_uid The email list unique id.
     */
    public function actionUpdate($list_uid)
    {
        $request = Yii::app()->request;

        if (!$request->isPutRequest) {
            return $this->renderJson(array(
                'status'    => 'error',
                'error'     => Yii::t('api', 'Only PUT requests allowed for this endpoint.')
            ), 400);
        }

        if (!($listModel = $this->loadListByUid($list_uid))) {
            return $this->renderJson(array(
                'status'    => 'error',
                'error'     => Yii::t('api', 'The list does not exist.')
            ), 404);
        }

        $general        = (array)$request->getPut('general', array());
        $defaults       = (array)$request->getPut('defaults', array());
        $notifications  = (array)$request->getPut('notifications', array());
        $company        = (array)$request->getPut('company', array());
        $customer       = Yii::app()->user->getModel();

        $listModel->attributes = $general;
        if (!$listModel->validate()) {
            return $this->renderJson(array(
                'status'    => 'error',
                'error'     => array(
                    'general' => $listModel->shortErrors->getAll()
                ),
            ), 422);
        }

        $defaultsModel = !empty($listModel->default) ? $listModel->default : new ListDefault();
        $defaultsModel->attributes = $defaults;
        if (!$defaultsModel->validate()) {
            return $this->renderJson(array(
                'status'    => 'error',
                'error'     => array(
                    'defaults' => $defaultsModel->shortErrors->getAll()
                ),
            ), 422);
        }

        $notificationsModel = !empty($listModel->customerNotification) ? $listModel->customerNotification : new ListCustomerNotification();
        $notificationsModel->attributes = $notifications;
        if (!$notificationsModel->validate()) {
            return $this->renderJson(array(
                'status'    => 'error',
                'error'     => array(
                    'notifications' => $notificationsModel->shortErrors->getAll()
                ),
            ), 422);
        }

        $companyModel = !empty($listModel->company) ? $listModel->company : new ListCompany();
        if (!empty($customer->company)) {
            $companyModel->mergeWithCustomerCompany($customer->company);
        }

        if (isset($company['country'])) {
            if (empty($company['country_id'])) {
                $country = Country::model()->findByAttributes(array('name' => $company['country']));
                if (!empty($country)) {
                    $company['country_id'] = $country->country_id;
                }
            }
            unset($company['country']);
        }

        if (isset($company['zone'])) {
            if (isset($company['country_id'])) {
                $zone = Zone::model()->findByAttributes(array(
                    'country_id'    => $company['country_id'],
                    'name'          => $company['zone']
                ));
                if (!empty($zone)) {
                    $company['zone_id'] = $zone->zone_id;
                }
            }
            unset($company['zone']);
        }

        $companyModel->attributes = $company;
        if (!$companyModel->validate()) {
            return $this->renderJson(array(
                'status'    => 'error',
                'error'     => array(
                    'company' => $companyModel->shortErrors->getAll()
                ),
            ), 422);
        }

        // at this point there should be no more errors.
        $models = array($listModel, $defaultsModel, $notificationsModel, $companyModel);

        foreach ($models as $model) {
            if (!($model instanceof Lists)) {
                $model->list_id = $listModel->list_id;
            }
            $model->save(false);
        }

        if ($logAction = Yii::app()->user->getModel()->asa('logAction')) {
            $logAction->listUpdated($listModel);
        }

        return $this->renderJson(array(
            'status' => 'success',
        ), 200);
    }

    /**
     * Handles copying of an existing email list.
     *
     * @param $list_uid The email list unique id.
     */
    public function actionCopy($list_uid)
    {
        $request = Yii::app()->request;

        if (!$request->isPostRequest) {
            return $this->renderJson(array(
                'status'    => 'error',
                'error'     => Yii::t('api', 'Only POST requests allowed for this endpoint.')
            ), 400);
        }

        if (!($list = $this->loadListByUid($list_uid))) {
            return $this->renderJson(array(
                'status' => 'error',
                'error'  => Yii::t('api', 'The list does not exist.')
            ), 404);
        }

        if (!($newList = $list->copy())) {
            return $this->renderJson(array(
                'status' => 'error',
                'error'  => Yii::t('api', 'Unable to copy the list.'),
            ), 422);
        }

        return $this->renderJson(array(
            'status'   => 'success',
            'list_uid' => $newList->list_uid,
        ), 201);
    }

    /**
     * Handles deleting of an existing email list.
     *
     * @param $list_uid The email list unique id.
     */
    public function actionDelete($list_uid)
    {
        $request = Yii::app()->request;

        if (!$request->isDeleteRequest) {
            return $this->renderJson(array(
                'status'    => 'error',
                'error'     => Yii::t('api', 'Only DELETE requests allowed for this endpoint.')
            ), 400);
        }

        if (!($list = $this->loadListByUid($list_uid))) {
            return $this->renderJson(array(
                'status'    => 'error',
                'error'     => Yii::t('api', 'The list does not exist.')
            ), 404);
        }

        $list->delete();

        if ($logAction = Yii::app()->user->getModel()->asa('logAction')) {
            $logAction->listDeleted($list);
        }

        // since 1.3.5.9
        Yii::app()->hooks->doAction('controller_action_delete_data', $collection = new CAttributeCollection(array(
            'controller' => $this,
            'model'      => $list,
        )));

        return $this->renderJson(array(
            'status' => 'success',
        ), 200);
    }

    public function loadListByUid($list_uid)
    {
        $criteria = new CDbCriteria();
        $criteria->compare('list_uid', $list_uid);
        $criteria->compare('customer_id', (int)Yii::app()->user->getId());
        $criteria->addNotInCondition('status', array(Lists::STATUS_PENDING_DELETE, Lists::STATUS_ARCHIVED));
        return Lists::model()->find($criteria);
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
                     SELECT `a`.`customer_id`, `a`.`status`, UNIX_TIMESTAMP(`a`.`last_updated`) as `last_updated`
                     FROM `{{list}}` `a`
                     WHERE `a`.`customer_id` = :cid AND `a`.`status` = :st
                     ORDER BY a.`list_id` DESC
                     LIMIT :l OFFSET :o
                ) AS t
                WHERE `t`.`customer_id` = :cid AND `t`.`status` = :st
            ';

            $command = Yii::app()->getDb()->createCommand($sql);
            $command->bindValue(':cid', (int)Yii::app()->user->getId(), PDO::PARAM_INT);
            $command->bindValue(':l', (int)$limit, PDO::PARAM_INT);
            $command->bindValue(':o', (int)$offset, PDO::PARAM_INT);
            $command->bindValue(':st', Lists::STATUS_ACTIVE, PDO::PARAM_STR);

            $row = $command->queryRow();

        } elseif ($this->action->id == 'view') {

            $sql = 'SELECT UNIX_TIMESTAMP(t.last_updated) as `timestamp` FROM `{{list}}` t WHERE `t`.`list_uid` = :uid AND `t`.`customer_id` = :cid AND `t`.`status` = :st LIMIT 1';
            $command = Yii::app()->getDb()->createCommand($sql);
            $command->bindValue(':uid', $request->getQuery('list_uid'), PDO::PARAM_STR);
            $command->bindValue(':cid', (int)Yii::app()->user->getId(), PDO::PARAM_INT);
            $command->bindValue(':st', Lists::STATUS_ACTIVE, PDO::PARAM_STR);

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
