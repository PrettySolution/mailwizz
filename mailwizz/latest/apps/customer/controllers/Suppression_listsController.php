<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * Suppression_listsController
 *
 * Handles the actions for customer suppression lists related tasks
 *
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.4.4
 */

class Suppression_listsController extends Controller
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
     * List all suppressions lists
     */
    public function actionIndex()
    {
        $request = Yii::app()->request;
        $list    = new CustomerSuppressionList('search');
        $list->unsetAttributes();

        // for filters.
        $list->attributes  = (array)$request->getQuery($list->modelName, array());
        $list->customer_id = (int)Yii::app()->customer->getId(); 
        
        $this->setData(array(
            'pageMetaTitle'     => $this->data->pageMetaTitle . ' | '. Yii::t('suppression_lists', 'Suppression lists'),
            'pageHeading'       => Yii::t('suppression_lists', 'Suppression lists'),
            'pageBreadcrumbs'   => array(
                Yii::t('suppression_lists', 'Suppression lists') => $this->createUrl('suppression_lists/index'),
                Yii::t('app', 'View all')
            )
        ));

        $this->render('list', compact('list'));
    }

    /**
     * Create a new suppression list
     */
    public function actionCreate()
    {
        $request = Yii::app()->request;
        $notify  = Yii::app()->notify;
        $list    = new CustomerSuppressionList();

        if ($request->isPostRequest && ($attributes = (array)$request->getPost($list->modelName, array()))) {
            $list->attributes  = $attributes;
            $list->customer_id = (int)Yii::app()->customer->getId();
            
            if (!$list->save()) {
                $notify->addError(Yii::t('app', 'Your form has a few errors, please fix them and try again!'));
            } else {
                $notify->addSuccess(Yii::t('app', 'Your form has been successfully saved!'));
            }

            Yii::app()->hooks->doAction('controller_action_save_data', $collection = new CAttributeCollection(array(
                'controller' => $this,
                'success'    => $notify->hasSuccess,
                'email'      => $list,
            )));

            if ($collection->success) {
                $this->redirect(array('suppression_lists/index'));
            }
        }

        $this->setData(array(
            'pageMetaTitle'   => $this->data->pageMetaTitle . ' | '. Yii::t('suppression_lists', 'Suppression lists'),
            'pageHeading'     => Yii::t('suppression_lists', 'Create new'),
            'pageBreadcrumbs' => array(
                Yii::t('suppression_lists', 'Suppression lists') => $this->createUrl('suppression_lists/index'),
                Yii::t('app', 'Create new'),
            )
        ));

        $this->render('form', compact('list'));
    }

    /**
     * Update an existing suppression list
     */
    public function actionUpdate($list_uid)
    {
        $list = CustomerSuppressionList::model()->findByAttributes(array(
            'list_uid'    => $list_uid,
            'customer_id' => (int)Yii::app()->customer->getId(),
        ));

        if (empty($list)) {
            throw new CHttpException(404, Yii::t('app', 'The requested page does not exist.'));
        }

        $request = Yii::app()->request;
        $notify  = Yii::app()->notify;

        if ($request->isPostRequest && ($attributes = (array)$request->getPost($list->modelName, array()))) {
            $list->attributes  = $attributes;
            $list->customer_id = (int)Yii::app()->customer->getId();
            if (!$list->save()) {
                $notify->addError(Yii::t('app', 'Your form has a few errors, please fix them and try again!'));
            } else {
                $notify->addSuccess(Yii::t('app', 'Your form has been successfully saved!'));
            }

            Yii::app()->hooks->doAction('controller_action_save_data', $collection = new CAttributeCollection(array(
                'controller'=> $this,
                'success'   => $notify->hasSuccess,
                'list'      => $list,
            )));

            if ($collection->success) {
                $this->redirect(array('suppression_lists/index'));
            }
        }

        $this->setData(array(
            'pageMetaTitle'     => $this->data->pageMetaTitle . ' | '. Yii::t('suppression_lists', 'Suppression lists'),
            'pageHeading'       => Yii::t('suppression_lists', 'Update'),
            'pageBreadcrumbs'   => array(
                Yii::t('suppression_lists', 'Suppression lists') => $this->createUrl('suppression_lists/index'),
                Yii::t('app', 'Update'),
            )
        ));

        $this->render('form', compact('list'));
    }

    /**
     * Delete a suppression list
     */
    public function actionDelete($list_uid)
    {
        $list = CustomerSuppressionList::model()->findByAttributes(array(
            'list_uid'    => $list_uid,
            'customer_id' => (int)Yii::app()->customer->getId(),
        ));

        if (empty($list)) {
            throw new CHttpException(404, Yii::t('app', 'The requested page does not exist.'));
        }

        $list->delete();

        $request = Yii::app()->request;
        $notify  = Yii::app()->notify;

        $redirect = null;
        if (!$request->getQuery('ajax')) {
            $notify->addSuccess(Yii::t('app', 'The item has been successfully deleted!'));
            $redirect = $request->getPost('returnUrl', array('suppression_lists/index'));
        }
        
        Yii::app()->hooks->doAction('controller_action_delete_data', $collection = new CAttributeCollection(array(
            'controller' => $this,
            'list'       => $list,
            'redirect'   => $redirect,
        )));

        if ($collection->redirect) {
            $this->redirect($collection->redirect);
        }
    }

    /**
     * Export
     */
    public function actionExport()
    {
        $notify = Yii::app()->notify;

        $models = CustomerSuppressionList::model()->findAllByAttributes(array(
            'customer_id' => (int)Yii::app()->customer->getId(),
        ));

        if (empty($models)) {
            $notify->addError(Yii::t('app', 'There is no item available for export!'));
            $this->redirect(array('index'));
        }

        if (!($fp = @fopen('php://output', 'w'))) {
            $notify->addError(Yii::t('app', 'Unable to access the output for writing the data!'));
            $this->redirect(array('index'));
        }

        /* Set the download headers */
        HeaderHelper::setDownloadHeaders('suppression-lists.csv');

        $attributes = AttributeHelper::removeSpecialAttributes($models[0]->getAttributes());
        $columns    = array_map(array($models[0], 'getAttributeLabel'), array_keys($attributes));
        @fputcsv($fp, $columns, ',', '"');

        foreach ($models as $model) {
            $attributes = AttributeHelper::removeSpecialAttributes($model->getAttributes());
            @fputcsv($fp, array_values($attributes), ',', '"');
        }

        @fclose($fp);
        Yii::app()->end();
    }
}
