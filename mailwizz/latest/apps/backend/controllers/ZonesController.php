<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * ZonesController
 *
 * Handles the actions for zones related tasks
 *
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.3.4.5
 */

class ZonesController extends Controller
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
     * List all available zones
     */
    public function actionIndex()
    {
        $request = Yii::app()->request;

        $zone = new Zone('search');
        $zone->unsetAttributes();

        $zone->attributes = (array)$request->getQuery($zone->modelName, array());

        $this->setData(array(
            'pageMetaTitle'     => $this->data->pageMetaTitle . ' | '. Yii::t('zones', 'View zones'),
            'pageHeading'       => Yii::t('articles', 'View zones'),
            'pageBreadcrumbs'   => array(
                Yii::t('zones', 'Zones') => $this->createUrl('zones/index'),
                Yii::t('app', 'View all')
            )
        ));

        $this->render('list', compact('zone'));
    }

    /**
     * Create a new zone
     */
    public function actionCreate()
    {
        $zone    = new Zone();
        $request = Yii::app()->request;
        $notify  = Yii::app()->notify;

        if ($request->isPostRequest && ($attributes = (array)$request->getPost($zone->modelName, array()))) {
            $zone->attributes = $attributes;
            if (!$zone->save()) {
                $notify->addError(Yii::t('app', 'Your form has a few errors, please fix them and try again!'));
            } else {
                $notify->addSuccess(Yii::t('app', 'Your form has been successfully saved!'));
            }

            Yii::app()->hooks->doAction('controller_action_save_data', $collection = new CAttributeCollection(array(
                'controller'=> $this,
                'success'   => $notify->hasSuccess,
                'zone'      => $zone,
            )));

            if ($collection->success) {
                $this->redirect(array('zones/index'));
            }
        }

        $this->setData(array(
            'pageMetaTitle'     => $this->data->pageMetaTitle . ' | '. Yii::t('zones', 'Create new zone'),
            'pageHeading'       => Yii::t('zones', 'Create new zone'),
            'pageBreadcrumbs'   => array(
                Yii::t('zones', 'Zones') => $this->createUrl('zones/index'),
                Yii::t('app', 'Create new'),
            )
        ));

        $this->render('form', compact('zone'));
    }

    /**
     * Update existing zone
     */
    public function actionUpdate($id)
    {
        $zone = Zone::model()->findByPk((int)$id);

        if (empty($zone)) {
            throw new CHttpException(404, Yii::t('app', 'The requested page does not exist.'));
        }

        $request = Yii::app()->request;
        $notify  = Yii::app()->notify;

        if ($request->isPostRequest && ($attributes = (array)$request->getPost($zone->modelName, array()))) {
            $zone->attributes = $attributes;
            if (!$zone->save()) {
                $notify->addError(Yii::t('app', 'Your form has a few errors, please fix them and try again!'));
            } else {
                $notify->addSuccess(Yii::t('app', 'Your form has been successfully saved!'));
            }

            Yii::app()->hooks->doAction('controller_action_save_data', $collection = new CAttributeCollection(array(
                'controller'=> $this,
                'success'   => $notify->hasSuccess,
                'zone'      => $zone,
            )));

            if ($collection->success) {
                $this->redirect(array('zones/index'));
            }
        }

        $this->setData(array(
            'pageMetaTitle'     => $this->data->pageMetaTitle . ' | '. Yii::t('zones', 'Update zone'),
            'pageHeading'       => Yii::t('zones', 'Update zone'),
            'pageBreadcrumbs'   => array(
                Yii::t('zones', 'Zones') => $this->createUrl('zones/index'),
                Yii::t('app', 'Update'),
            )
        ));

        $this->render('form', compact('zone'));
    }

    /**
     * Delete existing zone
     */
    public function actionDelete($id)
    {
        $zone = Zone::model()->findByPk((int)$id);

        if (empty($zone)) {
            throw new CHttpException(404, Yii::t('app', 'The requested page does not exist.'));
        }

        $request = Yii::app()->request;
        $notify  = Yii::app()->notify;

        if ($request->isPostRequest) {

            set_time_limit(0);
            ignore_user_abort(true);

            $zone->delete();

            $notify->addSuccess(Yii::t('app', 'Your item has been successfully deleted!'));
            $redirect = $request->getPost('returnUrl', array('zones/index'));

            // since 1.3.5.9
            Yii::app()->hooks->doAction('controller_action_delete_data', $collection = new CAttributeCollection(array(
                'controller' => $this,
                'model'      => $zone,
                'redirect'   => $redirect,
            )));

            if ($collection->redirect) {
                $this->redirect($collection->redirect);
            }
        }

        $this->setData(array(
            'pageMetaTitle'     => $this->data->pageMetaTitle . ' | ' . Yii::t('zones', 'Confirm zone removal'),
            'pageHeading'       => Yii::t('zones', 'Confirm zone removal'),
            'pageBreadcrumbs'   => array(
                Yii::t('zones', 'Zones') => $this->createUrl('zones/index'),
                $zone->name . ' ' => $this->createUrl('zones/update', array('id' => $zone->zone_id)),
                Yii::t('zones', 'Confirm zone removal')
            )
        ));

        $this->render('delete', compact('zone'));
    }

    /**
     * Ajax search for zones
     */
    public function actionAjax_search()
    {
        $request  = Yii::app()->request;
        $zone     = new Zone('search');
        $zone->unsetAttributes();

        $zone->attributes = (array)$request->getQuery($zone->modelName, array());
        $zones = $zone->search()->getData();

        $data = array();
        foreach ($zones as $zone) {
            $data[] = $zone->getAttributes(array('zone_id', 'country_id', 'name', 'code'));
        }

        return $this->renderJson($data);
    }
}
