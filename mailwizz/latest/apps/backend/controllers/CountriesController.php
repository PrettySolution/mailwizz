<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * CountriesController
 *
 * Handles the actions for countries related tasks
 *
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.3.4.5
 */

class CountriesController extends Controller
{
    /**
     * Define the filters for various controller actions
     * Merge the filters with the ones from parent implementation
     */
    public function filters()
    {
        $filters = array();
        return CMap::mergeArray($filters, parent::filters());
    }

    /**
     * List all available countries
     */
    public function actionIndex()
    {
        $request  = Yii::app()->request;
        $country  = new Country('search');
        $country->unsetAttributes();

        $country->attributes = (array)$request->getQuery($country->modelName, array());

        $this->setData(array(
            'pageMetaTitle'   => $this->data->pageMetaTitle . ' | '. Yii::t('countries', 'View countries'),
            'pageHeading'     => Yii::t('articles', 'View countries'),
            'pageBreadcrumbs' => array(
                Yii::t('countries', 'Countries') => $this->createUrl('countries/index'),
                Yii::t('app', 'View all')
            )
        ));

        $this->render('list', compact('country'));
    }

    /**
     * Create a new country
     * @throws CException
     */
    public function actionCreate()
    {
        $country = new Country();
        $request = Yii::app()->request;
        $notify  = Yii::app()->notify;

        if ($request->isPostRequest && ($attributes = (array)$request->getPost($country->modelName, array()))) {
            $country->attributes = $attributes;
            if (!$country->save()) {
                $notify->addError(Yii::t('app', 'Your form has a few errors, please fix them and try again!'));
            } else {
                $notify->addSuccess(Yii::t('app', 'Your form has been successfully saved!'));
            }

            Yii::app()->hooks->doAction('controller_action_save_data', $collection = new CAttributeCollection(array(
                'controller'=> $this,
                'success'   => $notify->hasSuccess,
                'country'   => $country,
            )));

            if ($collection->success) {
                $this->redirect(array('countries/index'));
            }
        }

        $this->setData(array(
            'pageMetaTitle'     => $this->data->pageMetaTitle . ' | '. Yii::t('countries', 'Create new country'),
            'pageHeading'       => Yii::t('countries', 'Create new country'),
            'pageBreadcrumbs'   => array(
                Yii::t('countries', 'Countries') => $this->createUrl('countries/index'),
                Yii::t('app', 'Create new'),
            )
        ));

        $this->render('form', compact('country'));
    }

    /**
     * Update existing country
     */
    public function actionUpdate($id)
    {
        $country = Country::model()->findByPk((int)$id);

        if (empty($country)) {
            throw new CHttpException(404, Yii::t('app', 'The requested page does not exist.'));
        }

        $request = Yii::app()->request;
        $notify  = Yii::app()->notify;

        if ($request->isPostRequest && ($attributes = (array)$request->getPost($country->modelName, array()))) {
            $country->attributes = $attributes;
            if (!$country->save()) {
                $notify->addError(Yii::t('app', 'Your form has a few errors, please fix them and try again!'));
            } else {
                $notify->addSuccess(Yii::t('app', 'Your form has been successfully saved!'));
            }

            Yii::app()->hooks->doAction('controller_action_save_data', $collection = new CAttributeCollection(array(
                'controller'=> $this,
                'success'   => $notify->hasSuccess,
                'country'   => $country,
            )));

            if ($collection->success) {
                $this->redirect(array('countries/index'));
            }
        }

        $this->setData(array(
            'pageMetaTitle'     => $this->data->pageMetaTitle . ' | '. Yii::t('countries', 'Update country'),
            'pageHeading'       => Yii::t('countries', 'Update country'),
            'pageBreadcrumbs'   => array(
                Yii::t('countries', 'Countries') => $this->createUrl('countries/index'),
                Yii::t('app', 'Update'),
            )
        ));

        $this->render('form', compact('country'));
    }

    /**
     * Delete existing country
     */
    public function actionDelete($id)
    {
        $country = Country::model()->findByPk((int)$id);

        if (empty($country)) {
            throw new CHttpException(404, Yii::t('app', 'The requested page does not exist.'));
        }

        $request = Yii::app()->request;
        $notify  = Yii::app()->notify;

        if ($request->isPostRequest) {

            set_time_limit(0);
            ignore_user_abort(true);

            $country->delete();

            $notify->addSuccess(Yii::t('app', 'Your item has been successfully deleted!'));
            $redirect = $request->getPost('returnUrl', array('countries/index'));

            // since 1.3.5.9
            Yii::app()->hooks->doAction('controller_action_delete_data', $collection = new CAttributeCollection(array(
                'controller' => $this,
                'model'      => $country,
                'redirect'   => $redirect,
            )));

            if ($collection->redirect) {
                $this->redirect($collection->redirect);
            }
        }

        $this->setData(array(
            'pageMetaTitle'     => $this->data->pageMetaTitle . ' | ' . Yii::t('countries', 'Confirm country removal'),
            'pageHeading'       => Yii::t('countries', 'Confirm country removal'),
            'pageBreadcrumbs'   => array(
                Yii::t('countries', 'Countries') => $this->createUrl('countries/index'),
                $country->name . ' ' => $this->createUrl('countries/update', array('id' => $country->country_id)),
                Yii::t('countries', 'Confirm country removal')
            )
        ));

        $this->render('delete', compact('country'));
    }

    /**
     * Show existing country zones
     */
    public function actionZones($country_id)
    {
        return $this->renderJson(array('zones' => Zone::getAsDropdownOptionsByCountryId($country_id)));
    }
}
