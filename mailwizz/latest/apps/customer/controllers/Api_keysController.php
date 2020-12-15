<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * Api_keysController
 *
 * Handles the actions for api keys related tasks
 *
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @version 1.0
 * @since 1.0
 */

class Api_keysController extends Controller
{
    /**
     * Init
     */
    public function init()
    {
        parent::init();
        if (Yii::app()->options->get('system.common.api_status') != 'online') {
            $this->redirect(array('dashboard/index'));
        } elseif (Yii::app()->customer->getModel()->getGroupOption('api.enabled', 'yes') != 'yes') {
            $this->redirect(array('dashboard/index'));
        }
    }

    /**
     * Define the filters for various controller actions.
     * Merge the filters with the ones from parent implementation
     */
    public function filters()
    {
        return CMap::mergeArray(array(
            'postOnly + delete',
        ), parent::filters());
    }

    /**
     * List available api keys
     */
    public function actionIndex()
    {
        $request = Yii::app()->request;
        $model = new CustomerApiKey('search');
        $model->attributes = (array)$request->getQuery($model->modelName, array());
        $model->customer_id = Yii::app()->customer->getId();

        $this->setData(array(
            'pageMetaTitle'     => $this->data->pageMetaTitle . ' | '. Yii::t('api_keys', 'Api keys'),
            'pageHeading'       => Yii::t('api_keys', 'Api keys'),
            'pageBreadcrumbs'   => array(
                Yii::t('api_keys', 'Api keys') => $this->createUrl('api_keys/index'),
                Yii::t('app', 'View all')
            )
        ));

        $this->render('list', compact('model'));
    }

    /**
     * Generate a new api key
     */
    public function actionGenerate()
    {
        $model = new CustomerApiKey();
        $model->customer_id = Yii::app()->customer->getId();
        $model->save();

        Yii::app()->notify->addInfo(Yii::t('api_keys', 'A new API access has been added:<br />Public key: {public} <br />Private key: {private}', array(
            '{public}'  => $model->public,
            '{private}' => $model->private,
        )));

        $this->redirect(array('api_keys/update', 'id' => $model->key_id));
    }

    /**
     * Update existing keys
     */
    public function actionUpdate($id)
    {
        $model = CustomerApiKey::model()->findByAttributes(array(
            'key_id'        => (int)$id,
            'customer_id'   => (int)Yii::app()->customer->getId(),
        ));

        if (empty($model)) {
            throw new CHttpException(404, Yii::t('app', 'The requested page does not exist.'));
        }

        $request = Yii::app()->request;
        $notify  = Yii::app()->notify;

        if ($request->isPostRequest && ($attributes = (array)$request->getPost($model->modelName, array()))) {
            $model->attributes  = $attributes;
            $model->customer_id = Yii::app()->customer->getId();
            if (!$model->save()) {
                $notify->addError(Yii::t('app', 'Your form has a few errors, please fix them and try again!'));
            } else {
                $notify->addSuccess(Yii::t('app', 'Your form has been successfully saved!'));
            }

            Yii::app()->hooks->doAction('controller_action_save_data', $collection = new CAttributeCollection(array(
                'controller'=> $this,
                'success'   => $notify->hasSuccess,
                'model'     => $model,
            )));
        }

        $this->setData(array(
            'pageMetaTitle'     => $this->data->pageMetaTitle . ' | '. Yii::t('api_keys', 'Update api keys'),
            'pageHeading'       => Yii::t('api_keys', 'Update api keys'),
            'pageBreadcrumbs'   => array(
                Yii::t('api_keys', 'Api keys') => $this->createUrl('api_keys/index'),
                Yii::t('app', 'Update')
            )
        ));

        $this->render('form', compact('model'));
    }

    /**
     * Delete existing api key
     */
    public function actionDelete($id)
    {
        $model = CustomerApiKey::model()->findByAttributes(array(
            'key_id'        => (int)$id,
            'customer_id'   => (int)Yii::app()->customer->getId(),
        ));

        if (empty($model)) {
            throw new CHttpException(404, Yii::t('app', 'The requested page does not exist.'));
        }

        $model->delete();

        $request = Yii::app()->request;
        $notify = Yii::app()->notify;

        $redirect = null;
        if (!$request->getQuery('ajax')) {
            $notify->addSuccess(Yii::t('api_keys', 'Requested API access has been successfully removed!'));
        }

        // since 1.3.5.9
        Yii::app()->hooks->doAction('controller_action_delete_data', $collection = new CAttributeCollection(array(
            'controller' => $this,
            'model'      => $model,
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
        
        $models = CustomerApiKey::model()->findAllByAttributes(array(
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
        HeaderHelper::setDownloadHeaders('api-keys.csv');

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
