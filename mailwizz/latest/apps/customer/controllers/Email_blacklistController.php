<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * Email_blacklistController
 *
 * Handles the actions for customer email blacklist related tasks
 *
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.3.6.2
 */

class Email_blacklistController extends Controller
{
    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        
        $customer = Yii::app()->customer->getModel();
        if ($customer->getGroupOption('lists.can_use_own_blacklist', 'no') != 'yes') {
            $this->redirect(array('dashboard/index'));
        }
        
        $this->getData('pageScripts')->add(array('src' => AssetsUrl::js('email-blacklist.js')));
    }

    /**
     * Define the filters for various controller actions
     * Merge the filters with the ones from parent implementation
     */
    public function filters()
    {
        $filters = array(
            'postOnly + delete, delete_all',
        );

        return CMap::mergeArray($filters, parent::filters());
    }

    /**
     * List all suppressed emails.
     * Delivery to suppressed emails is denied
     */
    public function actionIndex()
    {
        $request = Yii::app()->request;
        $email = new CustomerEmailBlacklist('search');
        $email->unsetAttributes();

        // for filters.
        $email->attributes  = (array)$request->getQuery($email->modelName, array());
        $email->customer_id = (int)Yii::app()->customer->getId(); 
        
        $this->setData(array(
            'pageMetaTitle'     => $this->data->pageMetaTitle . ' | '. Yii::t('email_blacklist', 'Blacklist'),
            'pageHeading'       => Yii::t('email_blacklist', 'Blacklist'),
            'pageBreadcrumbs'   => array(
                Yii::t('email_blacklist', 'Blacklist') => $this->createUrl('email_blacklist/index'),
                Yii::t('app', 'View all')
            )
        ));

        $this->render('list', compact('email'));
    }

    /**
     * Add a new email in the blacklist
     */
    public function actionCreate()
    {
        $request = Yii::app()->request;
        $notify  = Yii::app()->notify;
        $email   = new CustomerEmailBlacklist();

        if ($request->isPostRequest && ($attributes = (array)$request->getPost($email->modelName, array()))) {
            $email->attributes  = $attributes;
            $email->customer_id = (int)Yii::app()->customer->getId();
            
            if (!$email->save()) {
                $notify->addError(Yii::t('app', 'Your form has a few errors, please fix them and try again!'));
            } else {
                $notify->addSuccess(Yii::t('app', 'Your form has been successfully saved!'));
            }

            Yii::app()->hooks->doAction('controller_action_save_data', $collection = new CAttributeCollection(array(
                'controller' => $this,
                'success'    => $notify->hasSuccess,
                'email'      => $email,
            )));

            if ($collection->success) {
                $this->redirect(array('email_blacklist/index'));
            }
        }

        $this->setData(array(
            'pageMetaTitle'   => $this->data->pageMetaTitle . ' | '. Yii::t('email_blacklist', 'Blacklist'),
            'pageHeading'     => Yii::t('email_blacklist', 'Add a new email address to blacklist.'),
            'pageBreadcrumbs' => array(
                Yii::t('email_blacklist', 'Blacklist') => $this->createUrl('email_blacklist/index'),
                Yii::t('app', 'Create new'),
            )
        ));

        $this->render('form', compact('email'));
    }

    /**
     * Update an existing email from the blacklist
     */
    public function actionUpdate($email_uid)
    {
        $email = CustomerEmailBlacklist::model()->findByAttributes(array(
            'email_uid'   => $email_uid,
            'customer_id' => (int)Yii::app()->customer->getId(),
        ));

        if (empty($email)) {
            throw new CHttpException(404, Yii::t('app', 'The requested page does not exist.'));
        }

        $request = Yii::app()->request;
        $notify  = Yii::app()->notify;

        if ($request->isPostRequest && ($attributes = (array)$request->getPost($email->modelName, array()))) {
            $email->attributes  = $attributes;
            $email->customer_id = (int)Yii::app()->customer->getId();
            if (!$email->save()) {
                $notify->addError(Yii::t('app', 'Your form has a few errors, please fix them and try again!'));
            } else {
                $notify->addSuccess(Yii::t('app', 'Your form has been successfully saved!'));
            }

            Yii::app()->hooks->doAction('controller_action_save_data', $collection = new CAttributeCollection(array(
                'controller'=> $this,
                'success'   => $notify->hasSuccess,
                'email'     => $email,
            )));

            if ($collection->success) {
                $this->redirect(array('email_blacklist/index'));
            }
        }

        $this->setData(array(
            'pageMetaTitle'     => $this->data->pageMetaTitle . ' | '. Yii::t('email_blacklist', 'Blacklist'),
            'pageHeading'       => Yii::t('email_blacklist', 'Update blacklisted email address.'),
            'pageBreadcrumbs'   => array(
                Yii::t('email_blacklist', 'Blacklist') => $this->createUrl('email_blacklist/index'),
                Yii::t('app', 'Update'),
            )
        ));

        $this->render('form', compact('email'));
    }

    /**
     * Delete an email from the blacklist.
     */
    public function actionDelete($email_uid)
    {
        $email = CustomerEmailBlacklist::model()->findByAttributes(array(
            'email_uid'   => $email_uid,
            'customer_id' => (int)Yii::app()->customer->getId(),
        ));

        if (empty($email)) {
            throw new CHttpException(404, Yii::t('app', 'The requested page does not exist.'));
        }

        $email->delete();

        $request = Yii::app()->request;
        $notify  = Yii::app()->notify;

        $redirect = null;
        if (!$request->getQuery('ajax')) {
            $notify->addSuccess(Yii::t('app', 'The item has been successfully deleted!'));
            $redirect = $request->getPost('returnUrl', array('email_blacklist/index'));
        }
        
        Yii::app()->hooks->doAction('controller_action_delete_data', $collection = new CAttributeCollection(array(
            'controller' => $this,
            'email'      => $email,
            'redirect'   => $redirect,
        )));

        if ($collection->redirect) {
            $this->redirect($collection->redirect);
        }
    }

    /**
     * Run a bulk action against the suppressed list of emails
     */
    public function actionBulk_action()
    {
        $request = Yii::app()->request;
        $notify  = Yii::app()->notify;
        $action  = $request->getPost('bulk_action');
        $items   = array_unique((array)$request->getPost('bulk_item', array()));

        if ($action == CustomerEmailBlacklist::BULK_ACTION_DELETE && count($items)) {
            $affected = 0;
            foreach ($items as $item) {
                $email = CustomerEmailBlacklist::model()->findByAttributes(array(
                    'email_uid'   => $item,
                    'customer_id' => (int)Yii::app()->customer->getId(),
                ));
                
                if (empty($email)) {
                    continue;
                }

                $email->delete();
                $affected++;
            }
            if ($affected) {
                $notify->addSuccess(Yii::t('app', 'The action has been successfully completed!'));
            }
        }

        $defaultReturn = $request->getServer('HTTP_REFERER', array('email_blacklist/index'));
        $this->redirect($request->getPost('returnUrl', $defaultReturn));
    }

    /**
     * Delete all the emails from the suppression list
     */
    public function actionDelete_all()
    {
        $criteria = new CDbCriteria();
        $criteria->select = 'email_id, customer_id, email';
        $criteria->compare('customer_id', (int)Yii::app()->customer->getId());
        $criteria->limit  = 500;

        $models = CustomerEmailBlacklist::model()->findAll($criteria);
        while (!empty($models)) {
            foreach ($models as $model) {
                $model->delete();
            }
            $models = CustomerEmailBlacklist::model()->findAll($criteria);
        }

        $request = Yii::app()->request;
        $notify  = Yii::app()->notify;

        if (!$request->getQuery('ajax')) {
            $notify->addSuccess(Yii::t('app', 'Your items have been successfully deleted!'));
            $this->redirect($request->getPost('returnUrl', array('email_blacklist/index')));
        }
    }

    /**
     * Export existing suppressed emails
     */
    public function actionExport()
    {
        set_time_limit(0);
        
        $notify   = Yii::app()->notify;
        $redirect = array('email_blacklist/index');

        if (!($fp = @fopen('php://output', 'w'))) {
            $notify->addError(Yii::t('email_blacklist', 'Cannot open export temporary file!'));
            $this->redirect($redirect);
        }
        
        /* Set the download headers */
        HeaderHelper::setDownloadHeaders('email-blacklist-' . date('Y-m-d-h-i-s') . '.csv');

        // columns
        $columns = array(
            Yii::t('email_blacklist', 'Email'),
            Yii::t('email_blacklist', 'Reason'),
            Yii::t('email_blacklist', 'Date added')
        );
        fputcsv($fp, $columns, ',', '"');

        // rows
        $limit  = 500;
        $offset = 0;

        /** @var CustomerEmailBlacklist[] $models */
        $models = $this->getBlacklistedModels($limit, $offset);
        while (!empty($models)) {
            foreach ($models as $model) {
                $row = array($model->getDisplayEmail(), $model->reason, $model->dateAdded);
                fputcsv($fp, $row, ',', '"');
            }
            if (connection_status() != 0) {
                @fclose($fp);
                Yii::app()->end();
            }
            $offset = $offset + $limit;
            $models = $this->getBlacklistedModels($limit, $offset);
        }

        @fclose($fp);
        Yii::app()->end();
    }

    /**
     * @param int $limit
     * @param int $offset
     * @return static[]
     */
    protected function getBlacklistedModels($limit = 100, $offset = 0)
    {
        $criteria = new CDbCriteria;
        $criteria->select = 't.customer_id, t.email, t.reason, t.date_added';
        $criteria->compare('customer_id', (int)Yii::app()->customer->getId());
        $criteria->limit    = (int)$limit;
        $criteria->offset   = (int)$offset;
        return CustomerEmailBlacklist::model()->findAll($criteria);
    }

    /**
     * Import existing suppressed emails
     */
    public function actionImport()
    {
        set_time_limit(0);

        $request  = Yii::app()->request;
        $notify   = Yii::app()->notify;
        $redirect = array('email_blacklist/index');

        if (!$request->isPostRequest) {
            $this->redirect($redirect);
        }

        ini_set('auto_detect_line_endings', true);

        $import = new CustomerEmailBlacklist('import');
        $import->file = CUploadedFile::getInstance($import, 'file');

        if (!$import->validate()) {
            $notify->addError(Yii::t('app', 'Your form has a few errors, please fix them and try again!'));
            $notify->addError($import->shortErrors->getAllAsString());
            $this->redirect($redirect);
        }

        $delimiter = StringHelper::detectCsvDelimiter($import->file->tempName);
        $file = new SplFileObject($import->file->tempName);
        $file->setCsvControl($delimiter);
        $file->setFlags(SplFileObject::READ_CSV | SplFileObject::SKIP_EMPTY | SplFileObject::DROP_NEW_LINE | SplFileObject::READ_AHEAD);
        $columns = $file->current(); // the header

        if (!empty($columns)) {
            $columns = array_map('strtolower', $columns);
            if (array_search('email', $columns) === false) {
                $columns = null;
            }
        }

        if (empty($columns)) {
            $notify->addError(Yii::t('app', 'Your form has a few errors, please fix them and try again!'));
            $notify->addError(Yii::t('email_blacklist', 'Your file does not contain the header with the fields title!'));
            $this->redirect($redirect);
        }

        $ioFilter     = Yii::app()->ioFilter;
        $columnCount  = count($columns);
        $totalRecords = 0;
        $totalImport  = 0;

        while (!$file->eof()) {

            ++$totalRecords;

            $row = $file->fgetcsv();
            if (empty($row)) {
                continue;
            }

            $row = $ioFilter->stripPurify($row);
            $rowCount = count($row);

            if ($rowCount == 0) {
                continue;
            }

            $isEmpty = true;
            foreach ($row as $value) {
                if (!empty($value)) {
                    $isEmpty = false;
                    break;
                }
            }

            if ($isEmpty) {
                continue;
            }

            if ($columnCount > $rowCount) {
                $fill = array_fill($rowCount, $columnCount - $rowCount, '');
                $row = array_merge($row, $fill);
            } elseif ($rowCount > $columnCount) {
                $row = array_slice($row, 0, $columnCount);
            }

            $model = new CustomerEmailBlacklist();
            $data  = new CMap(array_combine($columns, $row));
            $model->customer_id = (int)Yii::app()->customer->getId();
            $model->email       = $data->itemAt('email');
            $model->reason      = $data->itemAt('reason');
            if ($model->save()) {
                $totalImport++;
            }
            unset($model, $data);
        }

        $notify->addSuccess(Yii::t('email_blacklist', 'Your file has been successfuly imported, from {count} records, {total} were imported!', array(
            '{count}'   => $totalRecords,
            '{total}'   => $totalImport,
        )));

        $this->redirect($redirect);
    }

}
