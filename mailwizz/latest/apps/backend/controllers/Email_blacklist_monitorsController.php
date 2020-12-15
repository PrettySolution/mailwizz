<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * Email_blacklist_monitorsController
 *
 * Handles the actions for blacklist monitors related tasks
 *
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.3.6.9
 */

class Email_blacklist_monitorsController extends Controller
{
    /**
     * @inheritdoc
     */
    public function init()
    {
        set_time_limit(0);
        $this->getData('pageScripts')->add(array('src' => AssetsUrl::js('email-blacklist-monitors.js')));
        parent::init();
    }
    
    /**
     * Define the filters for various controller actions
     * Merge the filters with the ones from parent implementation
     */
    public function filters()
    {
        $filters = array(
            'postOnly + delete',
        );

        return CMap::mergeArray($filters, parent::filters());
    }

    /**
     * List all blacklist monitors.
     */
    public function actionIndex()
    {
        $request = Yii::app()->request;
        $monitor = new EmailBlacklistMonitor('search');
        $monitor->unsetAttributes();

        // for filters.
        $monitor->attributes = (array)$request->getQuery($monitor->modelName, array());

        $this->setData(array(
            'pageMetaTitle'   => $this->data->pageMetaTitle . ' | '. Yii::t('email_blacklist', 'Blacklist monitors'),
            'pageHeading'     => Yii::t('email_blacklist', 'Blacklist monitors'),
            'pageBreadcrumbs' => array(
                Yii::t('email_blacklist', 'Blacklist monitors') => $this->createUrl('email_blacklist_monitors/index'),
                Yii::t('app', 'View all')
            )
        ));

        $this->render('list', compact('monitor'));
    }

    /**
     * Add a new blacklist monitor
     */
    public function actionCreate()
    {
        $request = Yii::app()->request;
        $notify  = Yii::app()->notify;
        $monitor = new EmailBlacklistMonitor();

        if ($request->isPostRequest && ($attributes = (array)$request->getPost($monitor->modelName, array()))) {
            $monitor->attributes = $attributes;
            if (!$monitor->save()) {
                $notify->addError(Yii::t('app', 'Your form has a few errors, please fix them and try again!'));
            } else {
                $notify->addSuccess(Yii::t('app', 'Your form has been successfully saved!'));
            }

            Yii::app()->hooks->doAction('controller_action_save_data', $collection = new CAttributeCollection(array(
                'controller'=> $this,
                'success'   => $notify->hasSuccess,
                'monitor'   => $monitor,
            )));

            if ($collection->success) {
                $this->redirect(array('email_blacklist_monitors/index'));
            }
        }

        $this->setData(array(
            'pageMetaTitle'   => $this->data->pageMetaTitle . ' | '. Yii::t('email_blacklist', 'Blacklist monitors'),
            'pageHeading'     => Yii::t('email_blacklist', 'Create a new blacklist monitor.'),
            'pageBreadcrumbs' => array(
                Yii::t('email_blacklist', 'Blacklist monitors') => $this->createUrl('email_blacklist_monitors/index'),
                Yii::t('app', 'Create new'),
            )
        ));

        $this->render('form', compact('monitor'));
    }

    /**
     * Update an existing blacklist monitor
     */
    public function actionUpdate($id)
    {
        $monitor = EmailBlacklistMonitor::model()->findByPk((int)$id);

        if (empty($monitor)) {
            throw new CHttpException(404, Yii::t('app', 'The requested page does not exist.'));
        }

        $request = Yii::app()->request;
        $notify  = Yii::app()->notify;

        if ($request->isPostRequest && ($attributes = (array)$request->getPost($monitor->modelName, array()))) {
            $monitor->attributes = $attributes;
            if (!$monitor->save()) {
                $notify->addError(Yii::t('app', 'Your form has a few errors, please fix them and try again!'));
            } else {
                $notify->addSuccess(Yii::t('app', 'Your form has been successfully saved!'));
            }

            Yii::app()->hooks->doAction('controller_action_save_data', $collection = new CAttributeCollection(array(
                'controller'=> $this,
                'success'   => $notify->hasSuccess,
                'monitor'   => $monitor,
            )));

            if ($collection->success) {
                $this->redirect(array('email_blacklist_monitors/index'));
            }
        }

        $this->setData(array(
            'pageMetaTitle'     => $this->data->pageMetaTitle . ' | '. Yii::t('email_blacklist', 'Blacklist monitors'),
            'pageHeading'       => Yii::t('email_blacklist', 'Update blacklist monitor.'),
            'pageBreadcrumbs'   => array(
                Yii::t('email_blacklist', 'Blacklist monitors') => $this->createUrl('email_blacklist_monitors/index'),
                Yii::t('app', 'Update'),
            )
        ));

        $this->render('form', compact('monitor'));
    }

    /**
     * Delete a blacklist monitor.
     */
    public function actionDelete($id)
    {
        $monitor = EmailBlacklistMonitor::model()->findByPk((int)$id);

        if (empty($monitor)) {
            throw new CHttpException(404, Yii::t('app', 'The requested page does not exist.'));
        }

        $monitor->delete();

        $request = Yii::app()->request;
        $notify  = Yii::app()->notify;

        $redirect = null;
        if (!$request->getQuery('ajax')) {
            $notify->addSuccess(Yii::t('app', 'The item has been successfully deleted!'));
            $redirect = $request->getPost('returnUrl', array('email_blacklist_monitors/index'));
        }

        // since 1.3.5.9
        Yii::app()->hooks->doAction('controller_action_delete_data', $collection = new CAttributeCollection(array(
            'controller' => $this,
            'model'      => $monitor,
            'redirect'   => $redirect,
        )));

        if ($collection->redirect) {
            $this->redirect($collection->redirect);
        }
    }

    /**
     * Delete all the emails from the blacklist
     */
    public function actionDelete_all()
    {
        EmailBlacklistMonitor::model()->deleteAll();

        $request = Yii::app()->request;
        $notify  = Yii::app()->notify;

        if (!$request->getQuery('ajax')) {
            $notify->addSuccess(Yii::t('app', 'Your items have been successfully deleted!'));
            $this->redirect($request->getPost('returnUrl', array('email_blacklist_monitors/index')));
        }
    }


    /**
     * Export blacklist monitors
     */
    public function actionExport()
    {
        $notify     = Yii::app()->notify;
        $redirect   = array('email_blacklist_monitors/index');

        if (!($fp = @fopen('php://output', 'w'))) {
            $notify->addError(Yii::t('email_blacklist', 'Cannot open export temporary file!'));
            $this->redirect($redirect);
        }
        
        /* Set the download headers */
        HeaderHelper::setDownloadHeaders('email-blacklist-monitors-' . date('Y-m-d-h-i-s') . '.csv');
        
        // reference
        $model = new EmailBlacklistMonitor();
        
        // columns
        $attributes = $model->attributes;
        unset($attributes['monitor_id']);
        $columns = array_keys($attributes);
        fputcsv($fp, $columns, ',', '"');

        // rows
        $limit  = 500;
        $offset = 0;
        $models = $this->getBlacklistMonitors($limit, $offset);
        while (!empty($models)) {
            foreach ($models as $model) {
                $row = array();
                foreach ($columns as $column) {
                    $row[] = $model->$column;
                }
                fputcsv($fp, $row, ',', '"');
            }
            if (connection_status() != 0) {
                @fclose($fp);
                Yii::app()->end();
            }
            $offset = $offset + $limit;
            $models = $this->getBlacklistMonitors($limit, $offset);
        }

        @fclose($fp);
        Yii::app()->end();
    }

    /**
     * @param int $limit
     * @param int $offset
     * @return static[]
     */
    protected function getBlacklistMonitors($limit = 100, $offset = 0)
    {
        $criteria = new CDbCriteria;
        $criteria->limit  = (int)$limit;
        $criteria->offset = (int)$offset;
        return EmailBlacklistMonitor::model()->findAll($criteria);
    }

    /**
     * Import blacklist monitors
     */
    public function actionImport()
    {
        $request  = Yii::app()->request;
        $notify   = Yii::app()->notify;
        $redirect = array('email_blacklist_monitors/index');

        if (!$request->isPostRequest) {
            $this->redirect($redirect);
        }

        ini_set('auto_detect_line_endings', true);

        $import = new EmailBlacklistMonitor('import');
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

            $row      = $ioFilter->stripPurify($row);
            $rowCount = count($row);
            
            if ($columnCount > $rowCount) {
                $fill = array_fill($rowCount, $columnCount - $rowCount, '');
                $row = array_merge($row, $fill);
            } elseif ($rowCount > $columnCount) {
                $row = array_slice($row, 0, $columnCount);
            }

            $model = new EmailBlacklistMonitor();
            $model->setAttributes(array_combine($columns, $row));
            
            if ($model->save()) {
                $totalImport++;
            }
            unset($model, $data);
        }

        $notify->addSuccess(Yii::t('email_blacklist', 'Your file has been successfuly imported, from {count} records, {total} were imported!', array(
            '{count}'   => ($totalRecords-1),
            '{total}'   => $totalImport,
        )));

        $this->redirect($redirect);
    }
}
