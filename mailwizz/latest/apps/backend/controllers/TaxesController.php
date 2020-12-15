<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * TaxesController
 *
 * Handles the actions for taxes related tasks
 *
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.3.4.5
 */

class TaxesController extends Controller
{
    public function init()
    {
        $this->getData('pageScripts')->add(array('src' => AssetsUrl::js('taxes.js')));
        parent::init();
    }

    /**
     * Define the filters for various controller actions
     * Merge the filters with the ones from parent implementation
     */
    public function filters()
    {
        $filters = array(
            'postOnly + delete, reset_sending_quota',
        );

        return CMap::mergeArray($filters, parent::filters());
    }

    /**
     * List all available taxes
     */
    public function actionIndex()
    {
        $request = Yii::app()->request;
        $tax     = new Tax('search');
        $tax->unsetAttributes();

        $tax->attributes = (array)$request->getQuery($tax->modelName, array());

        $this->setData(array(
            'pageMetaTitle'     => $this->data->pageMetaTitle . ' | '. Yii::t('taxes', 'View taxes'),
            'pageHeading'       => Yii::t('taxes', 'View taxes'),
            'pageBreadcrumbs'   => array(
                Yii::t('taxes', 'Taxes') => $this->createUrl('taxes/index'),
                Yii::t('app', 'View all')
            )
        ));

        $this->render('list', compact('tax'));
    }

    /**
     * Create a new tax
     */
    public function actionCreate()
    {
        $tax      = new Tax();
        $request  = Yii::app()->request;
        $notify   = Yii::app()->notify;

        if ($request->isPostRequest && ($attributes = (array)$request->getPost($tax->modelName, array()))) {
            $tax->attributes = $attributes;
            if (!$tax->save()) {
                $notify->addError(Yii::t('app', 'Your form has a few errors, please fix them and try again!'));
            } else {
                $notify->addSuccess(Yii::t('app', 'Your form has been successfully saved!'));
            }

            Yii::app()->hooks->doAction('controller_action_save_data', $collection = new CAttributeCollection(array(
                'controller'=> $this,
                'success'   => $notify->hasSuccess,
                'tax'  => $tax,
            )));

            if ($collection->success) {
                $this->redirect(array('taxes/index'));
            }
        }

        $this->setData(array(
            'pageMetaTitle'     => $this->data->pageMetaTitle . ' | '. Yii::t('taxes', 'Create new tax'),
            'pageHeading'       => Yii::t('taxes', 'Create new tax'),
            'pageBreadcrumbs'   => array(
                Yii::t('taxes', 'Taxes') => $this->createUrl('taxes/index'),
                Yii::t('app', 'Create new'),
            )
        ));

        $this->render('form', compact('tax'));
    }

    /**
     * Update existing tax
     */
    public function actionUpdate($id)
    {
        $tax = Tax::model()->findByPk((int)$id);

        if (empty($tax)) {
            throw new CHttpException(404, Yii::t('app', 'The requested page does not exist.'));
        }

        $request = Yii::app()->request;
        $notify  = Yii::app()->notify;

        if ($request->isPostRequest && ($attributes = (array)$request->getPost($tax->modelName, array()))) {
            $tax->attributes = $attributes;
            if (!$tax->save()) {
                $notify->addError(Yii::t('app', 'Your form has a few errors, please fix them and try again!'));
            } else {
                $notify->addSuccess(Yii::t('app', 'Your form has been successfully saved!'));
            }

            Yii::app()->hooks->doAction('controller_action_save_data', $collection = new CAttributeCollection(array(
                'controller'=> $this,
                'success'   => $notify->hasSuccess,
                'tax'  => $tax,
            )));

            if ($collection->success) {
                $this->redirect(array('taxes/index'));
            }
        }

        $this->setData(array(
            'pageMetaTitle'     => $this->data->pageMetaTitle . ' | '. Yii::t('taxes', 'Update tax'),
            'pageHeading'       => Yii::t('taxes', 'Update tax'),
            'pageBreadcrumbs'   => array(
                Yii::t('taxes', 'Taxes') => $this->createUrl('taxes/index'),
                Yii::t('app', 'Update'),
            )
        ));

        $this->render('form', compact('tax'));
    }

    /**
     * Delete existing tax
     */
    public function actionDelete($id)
    {
        $tax = Tax::model()->findByPk((int)$id);

        if (empty($tax)) {
            throw new CHttpException(404, Yii::t('app', 'The requested page does not exist.'));
        }

        $tax->delete();

        $request = Yii::app()->request;
        $notify  = Yii::app()->notify;

        $redirect = null;
        if (!$request->getQuery('ajax')) {
            $notify->addSuccess(Yii::t('app', 'The item has been successfully deleted!'));
            $redirect = $request->getPost('returnUrl', array('taxes/index'));
        }

        // since 1.3.5.9
        Yii::app()->hooks->doAction('controller_action_delete_data', $collection = new CAttributeCollection(array(
            'controller' => $this,
            'model'      => $tax,
            'redirect'   => $redirect,
        )));

        if ($collection->redirect) {
            $this->redirect($collection->redirect);
        }
    }

    /**
     * Display country zones
     */
    public function actionZones_by_country()
    {
        $criteria = new CDbCriteria();
        $criteria->select = 'zone_id, name';
        $criteria->compare('country_id', (int) Yii::app()->request->getQuery('country_id'));
        $models = Zone::model()->findAll($criteria);

        $zones = array();
        foreach ($models as $model) {
            $zones[] = array(
                'zone_id'    => $model->zone_id,
                'name'        => $model->name
            );
        }
        return $this->renderJson(array('zones' => $zones));
    }

}
