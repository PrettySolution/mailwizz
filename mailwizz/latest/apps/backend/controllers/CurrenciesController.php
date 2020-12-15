<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * CurrenciesController
 *
 * Handles the actions for currencies related tasks
 *
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.3.4.5
 */

class CurrenciesController extends Controller
{
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
     * List all available currencies
     */
    public function actionIndex()
    {
        $request   = Yii::app()->request;
        $currency  = new Currency('search');
        $currency->unsetAttributes();

        $currency->attributes = (array)$request->getQuery($currency->modelName, array());

        $this->setData(array(
            'pageMetaTitle'     => $this->data->pageMetaTitle . ' | '. Yii::t('currencies', 'View currencies'),
            'pageHeading'       => Yii::t('currencies', 'View currencies'),
            'pageBreadcrumbs'   => array(
                Yii::t('currencies', 'Currencies') => $this->createUrl('currencies/index'),
                Yii::t('app', 'View all')
            )
        ));

        $this->render('list', compact('currency'));
    }

    /**
     * Create a new currency
     */
    public function actionCreate()
    {
        $currency = new Currency();
        $request  = Yii::app()->request;
        $notify   = Yii::app()->notify;

        if ($request->isPostRequest && ($attributes = (array)$request->getPost($currency->modelName, array()))) {
            $currency->attributes = $attributes;
            if (!$currency->save()) {
                $notify->addError(Yii::t('app', 'Your form has a few errors, please fix them and try again!'));
            } else {
                $notify->addSuccess(Yii::t('app', 'Your form has been successfully saved!'));
            }

            Yii::app()->hooks->doAction('controller_action_save_data', $collection = new CAttributeCollection(array(
                'controller'=> $this,
                'success'   => $notify->hasSuccess,
                'currency'  => $currency,
            )));

            if ($collection->success) {
                $this->redirect(array('currencies/index'));
            }
        }

        $this->setData(array(
            'pageMetaTitle'     => $this->data->pageMetaTitle . ' | '. Yii::t('currencies', 'Create new currency'),
            'pageHeading'       => Yii::t('currencies', 'Create new currency'),
            'pageBreadcrumbs'   => array(
                Yii::t('currencies', 'Currencies') => $this->createUrl('currencies/index'),
                Yii::t('app', 'Create new'),
            )
        ));

        $this->render('form', compact('currency'));
    }

    /**
     * Update existing currency
     */
    public function actionUpdate($id)
    {
        $currency = Currency::model()->findByPk((int)$id);

        if (empty($currency)) {
            throw new CHttpException(404, Yii::t('app', 'The requested page does not exist.'));
        }

        $request = Yii::app()->request;
        $notify  = Yii::app()->notify;

        if ($request->isPostRequest && ($attributes = (array)$request->getPost($currency->modelName, array()))) {
            $currency->attributes = $attributes;
            if (!$currency->save()) {
                $notify->addError(Yii::t('app', 'Your form has a few errors, please fix them and try again!'));
            } else {
                $notify->addSuccess(Yii::t('app', 'Your form has been successfully saved!'));
            }

            Yii::app()->hooks->doAction('controller_action_save_data', $collection = new CAttributeCollection(array(
                'controller'=> $this,
                'success'   => $notify->hasSuccess,
                'currency'  => $currency,
            )));

            if ($collection->success) {
                $this->redirect(array('currencies/index'));
            }
        }

        $this->setData(array(
            'pageMetaTitle'     => $this->data->pageMetaTitle . ' | '. Yii::t('currencies', 'Update currency'),
            'pageHeading'       => Yii::t('currencies', 'Update currency'),
            'pageBreadcrumbs'   => array(
                Yii::t('currencies', 'Currencies') => $this->createUrl('currencies/index'),
                Yii::t('app', 'Update'),
            )
        ));

        $this->render('form', compact('currency'));
    }

    /**
     * Delete existing currency
     */
    public function actionDelete($id)
    {
        $currency = Currency::model()->findByPk((int)$id);

        if (empty($currency)) {
            throw new CHttpException(404, Yii::t('app', 'The requested page does not exist.'));
        }

        if ($currency->isRemovable) {
            $currency->delete();
        }

        $request = Yii::app()->request;
        $notify  = Yii::app()->notify;

        $redirect = null;
        if (!$request->getQuery('ajax')) {
            $notify->addSuccess(Yii::t('app', 'The item has been successfully deleted!'));
            $redirect = $request->getPost('returnUrl', array('currencies/index'));
        }

        // since 1.3.5.9
        Yii::app()->hooks->doAction('controller_action_delete_data', $collection = new CAttributeCollection(array(
            'controller' => $this,
            'model'      => $currency,
            'redirect'   => $redirect,
        )));

        if ($collection->redirect) {
            $this->redirect($collection->redirect);
        }
    }

}
