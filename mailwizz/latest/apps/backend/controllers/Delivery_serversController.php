<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * Delivery_serversController
 *
 * Handles the actions for delivery servers related tasks
 *
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.0
 */

class Delivery_serversController extends Controller
{
    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->getData('pageScripts')->add(array('src' => AssetsUrl::js('delivery-servers.js')));
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
            'postOnly + delete, validate, copy, enable, disable',
        );

        return CMap::mergeArray($filters, parent::filters());
    }
    
    /**
     * List all available delivery servers
     */
    public function actionIndex()
    {
        $request    = Yii::app()->request;
        $server     = new DeliveryServer('search');
        $server->unsetAttributes();

        $server->attributes = (array)$request->getQuery($server->modelName, array());

        $this->setData(array(
            'pageMetaTitle'     => $this->data->pageMetaTitle . ' | '. Yii::t('servers', 'View delivery servers'),
            'pageHeading'       => Yii::t('servers', 'View delivery servers'),
            'pageBreadcrumbs'   => array(
                Yii::t('servers', 'Delivery servers') => $this->createUrl('delivery_servers/index'),
                Yii::t('app', 'View all')
            )
        ));

        $types     = DeliveryServer::getTypesMapping();
        $csvImport = new DeliveryServerCsvImport();

        $this->render('list', compact('server', 'types', 'csvImport'));
    }

    /**
     * Create a new delivery server
     */
    public function actionCreate($type)
    {
        $types = DeliveryServer::getTypesMapping();

        if (!isset($types[$type])) {
            throw new CHttpException(500, Yii::t('servers', 'Server type not allowed.'));
        }

        $request    = Yii::app()->request;
        $notify     = Yii::app()->notify;
        $modelClass = $types[$type];
        $server     = new $modelClass();
        $server->type = $type;

        if (($failureMessage = $server->requirementsFailed())) {
            $notify->addWarning($failureMessage);
            $this->redirect(array('delivery_servers/index'));
        }

        $policy   = new DeliveryServerDomainPolicy();
        $policies = array();

        if ($request->isPostRequest && ($attributes = (array)$request->getPost($server->modelName, array()))) {
            if (!$server->isNewRecord && empty($attributes['password']) && isset($attributes['password'])) {
                unset($attributes['password']);
            }
            $server->attributes = $attributes;

            if ($policiesAttributes = (array)$request->getPost($policy->modelName, array())) {
                foreach ($policiesAttributes as $attributes) {
                    $policyModel = new DeliveryServerDomainPolicy();
                    $policyModel->attributes = $attributes;
                    $policies[] = $policyModel;
                }
            }

            if (!$server->save()) {
                $notify->addError(Yii::t('app', 'Your form has a few errors, please fix them and try again!'));
            } else {
                if (!empty($policies)) {
                    foreach ($policies as $policyModel) {
                        $policyModel->server_id = $server->server_id;
                        $policyModel->save();
                    }
                }
                $notify->addSuccess(Yii::t('app', 'Your form has been successfully saved!'));
            }

            Yii::app()->hooks->doAction('controller_action_save_data', $collection = new CAttributeCollection(array(
                'controller'=> $this,
                'success'   => $notify->hasSuccess,
                'server'    => $server,
            )));

            if ($collection->success) {
                $this->redirect(array('delivery_servers/update', 'type' => $type, 'id' => $server->server_id));
            }
        }

        $this->setData(array(
            'pageMetaTitle'     => $this->data->pageMetaTitle . ' | '. Yii::t('servers', 'Create new server'),
            'pageHeading'       => Yii::t('servers', 'Create new delivery server'),
            'pageBreadcrumbs'   => array(
                Yii::t('servers', 'Delivery servers') => $this->createUrl('delivery_servers/index'),
                Yii::t('app', 'Create new'),
            )
        ));
        
        // 1.3.9.5
        $view = Yii::app()->hooks->applyFilters('delivery_servers_form_view_file', 'form-' . $type, $server, $this);

        $this->render($view, compact('server', 'policy', 'policies'));
    }

    /**
     * Update existing delivery server
     */
    public function actionUpdate($type, $id)
    {
        $types = DeliveryServer::getTypesMapping();

        if (!isset($types[$type])) {
            throw new CHttpException(500, Yii::t('servers', 'Server type not allowed.'));
        }

        $server = DeliveryServer::model($types[$type])->findByAttributes(array(
            'server_id' => (int)$id,
            'type'      => $type,
        ));

        if (empty($server)) {
            throw new CHttpException(404, Yii::t('app', 'The requested page does not exist.'));
        }

        if (!$server->getCanBeUpdated()) {
            $this->redirect(array('delivery_servers/index'));
        }

        $request = Yii::app()->request;
        $notify = Yii::app()->notify;

        if (($failureMessage = $server->requirementsFailed())) {
            $notify->addWarning($failureMessage);
            $this->redirect(array('delivery_servers/index'));
        }

        $policy   = new DeliveryServerDomainPolicy();
        $policies = DeliveryServerDomainPolicy::model()->findAllByAttributes(array('server_id' => $server->server_id));

        if ($request->isPostRequest && ($attributes = (array)$request->getPost($server->modelName, array()))) {
            if (!$server->isNewRecord && empty($attributes['password']) && isset($attributes['password'])) {
                unset($attributes['password']);
            }
            $server->additional_headers = array();
            $server->attributes         = $attributes;

            $policies = array();
            if ($policiesAttributes = (array)$request->getPost($policy->modelName, array())) {
                foreach ($policiesAttributes as $attributes) {
                    $policyModel = new DeliveryServerDomainPolicy();
                    $policyModel->attributes = $attributes;
                    $policies[] = $policyModel;
                }
            }

            if (!$server->save()) {
                $notify->addError(Yii::t('app', 'Your form has a few errors, please fix them and try again!'));
            } else {
                DeliveryServerDomainPolicy::model()->deleteAllByAttributes(array('server_id' => $server->server_id));
                if (!empty($policies)) {
                    foreach ($policies as $policyModel) {
                        $policyModel->server_id = $server->server_id;
                        $policyModel->save();
                    }
                }
                $notify->addSuccess(Yii::t('app', 'Your form has been successfully saved!'));
            }

            Yii::app()->hooks->doAction('controller_action_save_data', $collection = new CAttributeCollection(array(
                'controller'=> $this,
                'success'   => $notify->hasSuccess,
                'server'    => $server,
            )));

            if ($collection->success) {
                $this->redirect(array('delivery_servers/update', 'type' => $type, 'id' => $server->server_id));
            }
        }

        $this->setData(array(
            'pageMetaTitle'     => $this->data->pageMetaTitle . ' | '. Yii::t('servers', 'Update server'),
            'pageHeading'       => Yii::t('servers', 'Update delivery server'),
            'pageBreadcrumbs'   => array(
                Yii::t('servers', 'Delivery servers') => $this->createUrl('delivery_servers/index'),
                Yii::t('app', 'Update'),
            )
        ));

        // 1.3.9.5
        $view = Yii::app()->hooks->applyFilters('delivery_servers_form_view_file', 'form-' . $type, $server, $this);

        $this->render($view, compact('server', 'policy', 'policies'));
    }

    /**
     * Delete existing delivery server
     */
    public function actionDelete($id)
    {
        $_server = DeliveryServer::model()->findByPk((int)$id);
        if (empty($_server)) {
            throw new CHttpException(404, Yii::t('app', 'The requested page does not exist.'));
        }

        $mapping = DeliveryServer::getTypesMapping();
        if (!isset($mapping[$_server->type])) {
            throw new CHttpException(404, Yii::t('app', 'The requested page does not exist.'));
        }

        $server = DeliveryServer::model($mapping[$_server->type])->findByPk((int)$_server->server_id);
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
            $redirect = $request->getPost('returnUrl', array('delivery_servers/index'));
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
     * Run a bulk action against the delivery servers
     */
    public function actionBulk_action()
    {
        $mapping = DeliveryServer::getTypesMapping();
        $request = Yii::app()->request;
        $notify  = Yii::app()->notify;
        
        $action = $request->getPost('bulk_action');
        $items  = array_unique(array_map('intval', (array)$request->getPost('bulk_item', array())));
        
        if ($action == DeliveryServer::BULK_ACTION_DELETE && count($items)) {
            $affected = 0;
            foreach ($items as $item) {
                $_server = DeliveryServer::model()->findByPk((int)$item);
                if (!isset($mapping[$_server->type])) {
                    continue;
                }

                $server = DeliveryServer::model($mapping[$_server->type])->findByPk((int)$_server->server_id);
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

        $defaultReturn = $request->getServer('HTTP_REFERER', array('delivery_servers/index'));
        $this->redirect($request->getPost('returnUrl', $defaultReturn));
    }

    /**
     * Validate a delivery server.
     * The delivery server will stay inactive until validation by email.
     * While delivery server is inactive it cannot be used to send emails
     */
    public function actionValidate($id)
    {
        $request = Yii::app()->request;
        $notify  = Yii::app()->notify;

        if (!($email = $request->getPost('email'))) {
            throw new CHttpException(500, Yii::t('servers', 'The email address is missing.'));
        }

        $_server = DeliveryServer::model()->findByPk((int)$id);

        if (empty($_server)) {
            throw new CHttpException(404, Yii::t('app', 'The requested page does not exist.'));
        }

        if (!FilterVarHelper::email($email)) {
            throw new CHttpException(500, Yii::t('app', 'The email address you provided does not seem to be valid.'));
        }

        $mapping = DeliveryServer::getTypesMapping();
        if (!isset($mapping[$_server->type])) {
            throw new CHttpException(404, Yii::t('app', 'The requested page does not exist.'));
        }

        $server = DeliveryServer::model($mapping[$_server->type])->findByPk((int)$_server->server_id);
        $server->confirmation_key = sha1(uniqid(rand(0, time()), true));
        $server->save(false);
        
	    $params = CommonEmailTemplate::getAsParamsArrayBySlug('delivery-server-validation',
		    array(
			    'to'      => $email,
			    'subject' => Yii::t('servers', 'Please validate this server.'),
		    ), array(
			    '[HOSTNAME]'         => $server->hostname,
			    '[CONFIRMATION_URL]' => $this->createAbsoluteUrl('delivery_servers/confirm', array('key' => $server->confirmation_key)),
			    '[CONFIRMATION_KEY]' => $server->confirmation_key,
		    )
	    );
	    
        $params = $server->getParamsArray($params);

        if ($server->sendEmail($params)) {
            $notify->addSuccess(Yii::t('servers', 'Please check your mailbox to confirm the server.'));
            $redirect = array('delivery_servers/index');
        } else {
            $dump = Yii::t('servers', 'Internal failure, maybe due to missing functions like {functions}!', array('{functions}' => 'proc_open'));
            if ($log = $server->getMailer()->getLog()) {
                $dump = $log;
            }
            if (preg_match('/\+\+\sSwift_SmtpTransport\sstarted.*/s', $dump, $matches)) {
                $dump = $matches[0];
            }
            $dump = CHtml::encode(str_replace("\n\n", "\n", $dump));
            $dump = nl2br($dump);
            $notify->addError(Yii::t('servers', 'Cannot send the confirmation email using the data you provided.'));
            $notify->addWarning(Yii::t('servers', 'Here is a transcript of the error message:') . '<hr />');
            $notify->addWarning($dump);

            $redirect = array('delivery_servers/update', 'type' => $server->type, 'id' => $server->server_id);
        }

        $this->redirect($redirect);
    }

    /**
     * Confirm the validation of a delivery server.
     * This is accessed from the validation email and changes
     * the status of a delivery server from inactive in active thus allowing the application to send
     * emails using this server
     */
    public function actionConfirm($key)
    {
        $_server = DeliveryServer::model()->findByAttributes(array(
            'confirmation_key' => $key,
        ));

        if (empty($_server)) {
            throw new CHttpException(404, Yii::t('app', 'The requested page does not exist.'));
        }

        $mapping = DeliveryServer::getTypesMapping();
        if (!isset($mapping[$_server->type])) {
            throw new CHttpException(404, Yii::t('app', 'The requested page does not exist.'));
        }

        $request = Yii::app()->request;
        $notify = Yii::app()->notify;
        $server = DeliveryServer::model($mapping[$_server->type])->findByPk((int)$_server->server_id);

        if (empty($server)) {
            throw new CHttpException(404, Yii::t('app', 'The requested page does not exist.'));
        }

        $server->status = DeliveryServer::STATUS_ACTIVE;
        $server->confirmation_key = null;
        $server->save(false);

        if (!empty($server->hostname)) {
            $notify->addSuccess(Yii::t('servers', 'You have successfully confirmed the server {serverName}.', array(
                '{serverName}' => $server->hostname,
            )));
        } else {
            $notify->addSuccess(Yii::t('servers', 'The server has been successfully confirmed!'));
        }

        $this->redirect(array('delivery_servers/index'));
    }

    /**
     * Create a copy of an existing delivery server
     */
    public function actionCopy($id)
    {
        $_server = DeliveryServer::model()->findByPk((int)$id);
        if (empty($_server)) {
            throw new CHttpException(404, Yii::t('app', 'The requested page does not exist.'));
        }

        $mapping = DeliveryServer::getTypesMapping();
        if (!isset($mapping[$_server->type])) {
            throw new CHttpException(404, Yii::t('app', 'The requested page does not exist.'));
        }

        $server = DeliveryServer::model($mapping[$_server->type])->findByPk((int)$_server->server_id);
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
            $this->redirect($request->getPost('returnUrl', array('delivery_servers/index')));
        }
    }

    /**
     * Enable a server that has been previously disabled
     */
    public function actionEnable($id)
    {
        $_server = DeliveryServer::model()->findByPk((int)$id);
        if (empty($_server)) {
            throw new CHttpException(404, Yii::t('app', 'The requested page does not exist.'));
        }

        $mapping = DeliveryServer::getTypesMapping();
        if (!isset($mapping[$_server->type])) {
            throw new CHttpException(404, Yii::t('app', 'The requested page does not exist.'));
        }

        $server = DeliveryServer::model($mapping[$_server->type])->findByPk((int)$_server->server_id);
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
            $this->redirect($request->getPost('returnUrl', array('delivery_servers/index')));
        }
    }

    /**
     * Disable a server that has been previously verified
     */
    public function actionDisable($id)
    {
        $_server = DeliveryServer::model()->findByPk((int)$id);
        if (empty($_server)) {
            throw new CHttpException(404, Yii::t('app', 'The requested page does not exist.'));
        }

        $mapping = DeliveryServer::getTypesMapping();
        if (!isset($mapping[$_server->type])) {
            throw new CHttpException(404, Yii::t('app', 'The requested page does not exist.'));
        }

        $server = DeliveryServer::model($mapping[$_server->type])->findByPk((int)$_server->server_id);
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
            $this->redirect($request->getPost('returnUrl', array('delivery_servers/index')));
        }
    }

    /**
     * Export existing delivery servers
     */
    public function actionExport()
    {
        $notify = Yii::app()->notify;

        $models = DeliveryServer::model()->findAll();

        if (empty($models)) {
            $notify->addError(Yii::t('app', 'There is no item available for export!'));
            $this->redirect(array('index'));
        }

        if (!($fp = @fopen('php://output', 'w'))) {
            $notify->addError(Yii::t('app', 'Unable to access the output for writing the data!'));
            $this->redirect(array('index'));
        }

        /* Set the download headers */
        HeaderHelper::setDownloadHeaders('delivery-servers.csv');

        $attributes = AttributeHelper::removeSpecialAttributes($models[0]->attributes, array('password'));
        @fputcsv($fp, array_keys($attributes), ',', '"');

        foreach ($models as $model) {
            $attributes = AttributeHelper::removeSpecialAttributes($model->attributes, array('password'));
            @fputcsv($fp, array_values($attributes), ',', '"');
        }

        @fclose($fp);
        Yii::app()->end();
    }

    /**
     * Import new delivery servers
     */
    public function actionImport()
    {
        set_time_limit(0);

        $request  = Yii::app()->request;
        $notify   = Yii::app()->notify;
        $redirect = array('delivery_servers/index');

        if (!$request->isPostRequest) {
            $this->redirect($redirect);
        }

        ini_set('auto_detect_line_endings', true);
        
        $import = new DeliveryServerCsvImport('import');
        $import->file = CUploadedFile::getInstance($import, 'file');

        if (!$import->validate() || empty($import->file)) {
            $notify->addError(Yii::t('app', 'Your form has a few errors, please fix them and try again!'));
            $notify->addError($import->shortErrors->getAllAsString());
            $this->redirect($redirect);
        }

        $file = new SplFileObject($import->file->tempName);
        $file->setFlags(SplFileObject::READ_CSV | SplFileObject::SKIP_EMPTY | SplFileObject::DROP_NEW_LINE | SplFileObject::READ_AHEAD);
        $columns = $file->current(); // the header

        if (empty($columns)) {
            $notify->addError(Yii::t('app', 'Your form has a few errors, please fix them and try again!'));
            $notify->addError(Yii::t('servers', 'Your file does not contain the header with the fields title!'));
            $this->redirect($redirect);
        }

        $ioFilter     = Yii::app()->ioFilter;
        $columnCount  = count($columns);
        $totalRecords = 0;
        $totalImport  = 0;

	    $mapping = DeliveryServer::getTypesMapping();
	    
        while (!$file->eof()) {

            $row = $file->fgetcsv();
            if (empty($row)) {
                continue;
            }

            $row = $ioFilter->stripPurify($row);
            $rowCount = count($row);

            if ($rowCount == 0) {
                continue;
            }

            ++$totalRecords;

            if ($columnCount > $rowCount) {
                $fill = array_fill($rowCount, $columnCount - $rowCount, '');
                $row  = array_merge($row, $fill);
            } elseif ($rowCount > $columnCount) {
                $row = array_slice($row, 0, $columnCount);
            }

            $attributes = array_combine($columns, $row);
            
            $model = new DeliveryServer();
            if (isset($attributes['type']) && isset($mapping[$attributes['type']])) {
            	$className = $mapping[$attributes['type']];
	            $model = new $className();
            }
            
            $model->attributes = $attributes;
            if ($model->save()) {
                $totalImport++;
            }
            unset($model, $data);
        }

        $notify->addSuccess(Yii::t('servers', 'Your file has been successfuly imported, from {count} records, {total} were imported!', array(
            '{count}'   => $totalRecords,
            '{total}'   => $totalImport,
        )));

        $this->redirect($redirect);
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
