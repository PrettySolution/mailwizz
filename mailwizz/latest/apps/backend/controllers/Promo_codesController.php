<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * Promo_codesController
 *
 * Handles the actions for promo codes related tasks
 *
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.3.4.3
 */

class Promo_codesController extends Controller
{
    // init method
    public function init()
    {
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
            'postOnly + delete',
        );

        return CMap::mergeArray($filters, parent::filters());
    }

    /**
     * List all available promo codes
     */
    public function actionIndex()
    {
        $request    = Yii::app()->request;
        $ioFilter   = Yii::app()->ioFilter;
        $promoCode  = new PricePlanPromoCode('search');
        $promoCode->unsetAttributes();

        $promoCode->attributes = $ioFilter->xssClean((array)$request->getOriginalQuery($promoCode->modelName, array()));

        $this->setData(array(
            'pageMetaTitle'   => $this->data->pageMetaTitle . ' | '. Yii::t('promo_codes', 'View promo codes'),
            'pageHeading'     => Yii::t('promo_codes', 'Promo codes'),
            'pageBreadcrumbs' => array(
                Yii::t('promo_codes', 'Promo codes') => $this->createUrl('promo_codes/index'),
                Yii::t('app', 'View all')
            )
        ));

        $this->render('list', compact('promoCode'));
    }

    /**
     * Create a new promo code
     */
    public function actionCreate()
    {
        $promoCode  = new PricePlanPromoCode();
        $request    = Yii::app()->request;
        $notify     = Yii::app()->notify;

        if ($request->isPostRequest && ($attributes = (array)$request->getPost($promoCode->modelName, array()))) {
            $promoCode->attributes = $attributes;
            if (!$promoCode->save()) {
                $notify->addError(Yii::t('app', 'Your form has a few errors, please fix them and try again!'));
            } else {
                $notify->addSuccess(Yii::t('app', 'Your form has been successfully saved!'));
            }

            Yii::app()->hooks->doAction('controller_save_data', $collection = new CAttributeCollection(array(
                'controller'=> $this,
                'success'   => $notify->hasSuccess,
                'promoCode' => $promoCode,
            )));

            if ($collection->success) {
                $this->redirect(array('promo_codes/index'));
            }
        }

        $this->setData(array(
            'pageMetaTitle'     => $this->data->pageMetaTitle . ' | '. Yii::t('promo_codes', 'Create new promo code'),
            'pageHeading'       => Yii::t('promo_codes', 'Create new promo code'),
            'pageBreadcrumbs'   => array(
                Yii::t('promo_codes', 'Promo codes') => $this->createUrl('promo_codes/index'),
                Yii::t('app', 'Create new'),
            )
        ));

        $this->render('form', compact('promoCode'));
    }

    /**
     * Update existing promo code
     */
    public function actionUpdate($id)
    {
        $promoCode = PricePlanPromoCode::model()->findByPk((int)$id);

        if (empty($promoCode)) {
            throw new CHttpException(404, Yii::t('app', 'The requested page does not exist.'));
        }

        $request = Yii::app()->request;
        $notify  = Yii::app()->notify;

        if ($request->isPostRequest && ($attributes = (array)$request->getPost($promoCode->modelName, array()))) {
            $promoCode->attributes = $attributes;
            if (!$promoCode->save()) {
                $notify->addError(Yii::t('app', 'Your form has a few errors, please fix them and try again!'));
            } else {
                $notify->addSuccess(Yii::t('app', 'Your form has been successfully saved!'));
            }

            Yii::app()->hooks->doAction('controller_save_data', $collection = new CAttributeCollection(array(
                'controller'=> $this,
                'success'   => $notify->hasSuccess,
                'promoCode' => $promoCode,
            )));

            if ($collection->success) {
                $this->redirect(array('promo_codes/index'));
            }
        }

        $this->setData(array(
            'pageMetaTitle'     => $this->data->pageMetaTitle . ' | '. Yii::t('promo_codes', 'Update promo code'),
            'pageHeading'       => Yii::t('promo_codes', 'Update promo code'),
            'pageBreadcrumbs'   => array(
                Yii::t('promo_codes', 'Promo codes') => $this->createUrl('promo_codes/index'),
                Yii::t('app', 'Update'),
            )
        ));

        $this->render('form', compact('promoCode'));
    }

    /**
     * Delete existing promo code
     */
    public function actionDelete($id)
    {
        $promoCode = PricePlanPromoCode::model()->findByPk((int)$id);

        if (empty($promoCode)) {
            throw new CHttpException(404, Yii::t('app', 'The requested page does not exist.'));
        }

        $promoCode->delete();

        $request = Yii::app()->request;
        $notify  = Yii::app()->notify;

        $redirect = null;
        if (!$request->getQuery('ajax')) {
            $notify->addSuccess(Yii::t('app', 'The item has been successfully deleted!'));
            $redirect = $request->getPost('returnUrl', array('promo_codes/index'));
        }

        // since 1.3.5.9
        Yii::app()->hooks->doAction('controller_action_delete_data', $collection = new CAttributeCollection(array(
            'controller' => $this,
            'model'      => $promoCode,
            'redirect'   => $redirect,
        )));

        if ($collection->redirect) {
            $this->redirect($collection->redirect);
        }
    }

    /**
     * Autocomplete for promo codes
     */
    public function actionAutocomplete($term)
    {
        $request = Yii::app()->request;
        if (!$request->isAjaxRequest) {
            $this->redirect(array('customers/index'));
        }

        $criteria = new CDbCriteria();
        $criteria->select = 'promo_code_id, code';
        $criteria->compare('code', $term, true);
        $criteria->limit = 10;

        $models = PricePlanPromoCode::model()->findAll($criteria);
        $results = array();

        foreach ($models as $model) {
            $results[] = array(
                'promo_code_id' => $model->promo_code_id,
                'value'         => $model->code,
            );
        }

        return $this->renderJson($results);
    }

    /**
     * Callback to register Jquery ui bootstrap only for certain actions
     */
    public function _registerJuiBs($event)
    {
        if (in_array($event->params['action']->id, array('index', 'create', 'update'))) {
            $this->getData('pageStyles')->mergeWith(array(
                array('src' => Yii::app()->apps->getBaseUrl('assets/css/jui-bs/jquery-ui-1.10.3.custom.css'), 'priority' => -1001),
            ));
        }
    }
}
