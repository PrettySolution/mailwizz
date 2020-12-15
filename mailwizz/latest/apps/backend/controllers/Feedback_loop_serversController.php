<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * Feedback_loop_serversController
 *
 * Handles the actions for feedback loop servers related tasks
 *
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.3.3.1
 */

class Feedback_loop_serversController extends Controller
{
    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->getData('pageScripts')->add(array('src' => AssetsUrl::js('bounce-fbl-servers.js')));
        $this->onBeforeAction = array($this, '_registerJuiBs');
        parent::init();
    }

    /**
     * Define the filters for various controller actions
     * Merge the filters with the ones from parent implementation
     */
    public function filters()
    {
        $filters = array(
            'postOnly + delete, copy, enable, disable',
        );

        return CMap::mergeArray($filters, parent::filters());
    }

    /**
     * List available feedback loop servers
     */
    public function actionIndex()
    {
        $request = Yii::app()->request;
        $server  = new FeedbackLoopServer('search');
        $server->unsetAttributes();

        $server->attributes = (array)$request->getQuery($server->modelName, array());

        $this->setData(array(
            'pageMetaTitle'     => $this->data->pageMetaTitle . ' | '. Yii::t('servers', 'View feedback loop servers'),
            'pageHeading'       => Yii::t('servers', 'View feedback loop servers'),
            'pageBreadcrumbs'   => array(
                Yii::t('servers', 'Feedback loop servers') => $this->createUrl('feedback_loop_servers/index'),
                Yii::t('app', 'View all')
            )
        ));

        $this->render('list', compact('server'));
    }

    /**
     * Create a new feedback loop server
     */
    public function actionCreate()
    {
        $request    = Yii::app()->request;
        $notify     = Yii::app()->notify;
        $server     = new FeedbackLoopServer();

        if ($request->isPostRequest && ($attributes = (array)$request->getPost($server->modelName, array()))) {
            if (!$server->isNewRecord && empty($attributes['password']) && isset($attributes['password'])) {
                unset($attributes['password']);
            }
            $server->attributes = $attributes;
            if (!$server->testConnection() || !$server->save()) {
                $notify->addError(Yii::t('app', 'Your form has a few errors, please fix them and try again!'));
            } else {
                $notify->addSuccess(Yii::t('app', 'Your form has been successfully saved!'));
            }

            Yii::app()->hooks->doAction('controller_action_save_data', $collection = new CAttributeCollection(array(
                'controller'=> $this,
                'success'   => $notify->hasSuccess,
                'server'    => $server,
            )));

            if ($collection->success) {
                $this->redirect(array('feedback_loop_servers/update', 'id' => $server->server_id));
            }
        }

        $this->setData(array(
            'pageMetaTitle'     => $this->data->pageMetaTitle . ' | '. Yii::t('servers', 'Create new server'),
            'pageHeading'       => Yii::t('servers', 'Create new feedback loop server'),
            'pageBreadcrumbs'   => array(
                Yii::t('servers', 'Feedback loop servers') => $this->createUrl('feedback_loop_servers/index'),
                Yii::t('app', 'Create new'),
            )
        ));

        $this->render('form', compact('server'));
    }

    /**
     * Update existing feedback loop server
     */
    public function actionUpdate($id)
    {
        $server = FeedbackLoopServer::model()->findByPk((int)$id);
        if (empty($server)) {
            throw new CHttpException(404, Yii::t('app', 'The requested page does not exist.'));
        }

        if (!$server->getCanBeUpdated()) {
            $this->redirect(array('feedback_loop_servers/index'));
        }

        $request = Yii::app()->request;
        $notify  = Yii::app()->notify;

        if ($request->isPostRequest && ($attributes = (array)$request->getPost($server->modelName, array()))) {
            if (!$server->isNewRecord && empty($attributes['password']) && isset($attributes['password'])) {
                unset($attributes['password']);
            }
            $server->attributes = $attributes;
            if (!$server->testConnection() || !$server->save()) {
                $notify->addError(Yii::t('app', 'Your form has a few errors, please fix them and try again!'));
            } else {
                $notify->addSuccess(Yii::t('app', 'Your form has been successfully saved!'));
            }

            Yii::app()->hooks->doAction('controller_action_save_data', $collection = new CAttributeCollection(array(
                'controller'=> $this,
                'success'   => $notify->hasSuccess,
                'server'    => $server,
            )));

            if ($collection->success) {

            }
        }

        $this->setData(array(
            'pageMetaTitle'     => $this->data->pageMetaTitle . ' | '. Yii::t('servers', 'Update server'),
            'pageHeading'       => Yii::t('servers', 'Update feedback loop server'),
            'pageBreadcrumbs'   => array(
                Yii::t('servers', 'Feedback loop servers') => $this->createUrl('feedback_loop_servers/index'),
                Yii::t('app', 'Update'),
            )
        ));

        $this->render('form', compact('server'));
    }

    /**
     * Delete existing feedback loop server
     */
    public function actionDelete($id)
    {
        $server = FeedbackLoopServer::model()->findByPk((int)$id);
        if (empty($server)) {
            throw new CHttpException(404, Yii::t('app', 'The requested page does not exist.'));
        }

        if ($server->getCanBeDeleted()) {
            $server->delete();
        }

        $request = Yii::app()->request;
        $notify  = Yii::app()->notify;

        $redirect = null;
        if (!$request->getQuery('ajax')) {
            $notify->addSuccess(Yii::t('app', 'The item has been successfully deleted!'));
            $redirect = $request->getPost('returnUrl', array('feedback_loop_servers/index'));
        }

        // since 1.3.5.9
        Yii::app()->hooks->doAction('controller_action_delete_data', $collection = new CAttributeCollection(array(
            'controller' => $this,
            'model'      => $server,
            'redirect'   => $redirect,
        )));

        if ($collection->redirect) {
            $this->redirect($collection->redirect);
        }
    }

    /**
     * Run a bulk action against the fbl servers
     */
    public function actionBulk_action()
    {
        $request = Yii::app()->request;
        $notify  = Yii::app()->notify;

        $action = $request->getPost('bulk_action');
        $items  = array_unique(array_map('intval', (array)$request->getPost('bulk_item', array())));

        if ($action == FeedbackLoopServer::BULK_ACTION_DELETE && count($items)) {
            $affected = 0;
            foreach ($items as $item) {
                $server = FeedbackLoopServer::model()->findByPk((int)$item);
                if (empty($server)) {
                    continue;
                }

                if (!$server->getCanBeDeleted()) {
                    continue;
                }

                $server->delete();
                $affected++;
            }
            if ($affected) {
                $notify->addSuccess(Yii::t('app', 'The action has been successfully completed!'));
            }
        }

        $defaultReturn = $request->getServer('HTTP_REFERER', array('feedback_loop_servers/index'));
        $this->redirect($request->getPost('returnUrl', $defaultReturn));
    }

    /**
     * Create a copy of an existing fbl server
     */
    public function actionCopy($id)
    {
        $server = FeedbackLoopServer::model()->findByPk((int)$id);
        if (empty($server)) {
            throw new CHttpException(404, Yii::t('app', 'The requested page does not exist.'));
        }

        $request = Yii::app()->request;
        $notify  = Yii::app()->notify;

        if ($server->copy()) {
            $notify->addSuccess(Yii::t('servers', 'Your server has been successfully copied!'));
        } else {
            $notify->addError(Yii::t('servers', 'Unable to copy the server!'));
        }

        if (!$request->isAjaxRequest) {
            $this->redirect($request->getPost('returnUrl', array('feedback_loop_servers/index')));
        }
    }

    /**
     * Enable a server that has been previously disabled
     */
    public function actionEnable($id)
    {
        $server = FeedbackLoopServer::model()->findByPk((int)$id);
        if (empty($server)) {
            throw new CHttpException(404, Yii::t('app', 'The requested page does not exist.'));
        }

        $request = Yii::app()->request;
        $notify  = Yii::app()->notify;

        if ($server->getIsDisabled()) {
            $server->enable();
            $notify->addSuccess(Yii::t('servers', 'Your server has been successfully enabled!'));
        } else {
            $notify->addError(Yii::t('servers', 'The server must be disabled in order to enable it!'));
        }

        if (!$request->isAjaxRequest) {
            $this->redirect($request->getPost('returnUrl', array('feedback_loop_servers/index')));
        }
    }

    /**
     * Disable a server that has been previously verified
     */
    public function actionDisable($id)
    {
        $server = FeedbackLoopServer::model()->findByPk((int)$id);
        if (empty($server)) {
            throw new CHttpException(404, Yii::t('app', 'The requested page does not exist.'));
        }

        $request = Yii::app()->request;
        $notify  = Yii::app()->notify;

        if ($server->getIsActive()) {
            $server->disable();
            $notify->addSuccess(Yii::t('servers', 'Your server has been successfully disabled!'));
        } else {
            $notify->addError(Yii::t('servers', 'The server must be active in order to disable it!'));
        }

        if (!$request->isAjaxRequest) {
            $this->redirect($request->getPost('returnUrl', array('feedback_loop_servers/index')));
        }
    }

    /**
     * Export
     */
    public function actionExport()
    {
        $notify = Yii::app()->notify;

        $models = FeedbackLoopServer::model()->findAll();

        if (empty($models)) {
            $notify->addError(Yii::t('app', 'There is no item available for export!'));
            $this->redirect(array('index'));
        }

        if (!($fp = @fopen('php://output', 'w'))) {
            $notify->addError(Yii::t('app', 'Unable to access the output for writing the data!'));
            $this->redirect(array('index'));
        }

        /* Set the download headers */
        HeaderHelper::setDownloadHeaders('feedback-loop-servers.csv');

        $attributes = AttributeHelper::removeSpecialAttributes($models[0]->attributes, array('password'));
        @fputcsv($fp, array_map(array($models[0], 'getAttributeLabel'), array_keys($attributes)), ',', '"');

        foreach ($models as $model) {
            $attributes = AttributeHelper::removeSpecialAttributes($model->attributes, array('password'));
            @fputcsv($fp, array_values($attributes), ',', '"');
        }

        @fclose($fp);
        Yii::app()->end();
    }
    
    /**
     * Callback to register Jquery ui bootstrap only for certain actions
     */
    public function _registerJuiBs($event)
    {
        if (in_array($event->params['action']->id, array('create', 'update'))) {
            $this->getData('pageStyles')->mergeWith(array(
                array('src' => Yii::app()->apps->getBaseUrl('assets/css/jui-bs/jquery-ui-1.10.3.custom.css'), 'priority' => -1001),
            ));
        }
    }
}
