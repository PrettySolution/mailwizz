<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * Tracking_domainsController
 *
 * Handles the actions for tracking domains related tasks
 *
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.3.4.6
 */

class Tracking_domainsController extends Controller
{
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
     * List all available tracking domains
     */
    public function actionIndex()
    {
        $request = Yii::app()->request;
        $domain  = new TrackingDomain('search');
        $domain->unsetAttributes();

        $domain->attributes = (array)$request->getQuery($domain->modelName, array());

        $this->setData(array(
            'pageMetaTitle'     => $this->data->pageMetaTitle . ' | '. Yii::t('tracking_domains', 'View tracking domains'),
            'pageHeading'       => Yii::t('tracking_domains', 'View tracking domains'),
            'pageBreadcrumbs'   => array(
                Yii::t('tracking_domains', 'Tracking domains') => $this->createUrl('tracking_domains/index'),
                Yii::t('app', 'View all')
            )
        ));

        $this->render('list', compact('domain'));
    }

    /**
     * Create a new tracking domain
     */
    public function actionCreate()
    {
        $request = Yii::app()->request;
        $notify  = Yii::app()->notify;
        $domain  = new TrackingDomain();
        $currentDomain = parse_url(Yii::app()->createAbsoluteUrl($this->route), PHP_URL_HOST);

        if ($request->isPostRequest && ($attributes = (array)$request->getPost($domain->modelName, array()))) {
            $domain->attributes = $attributes;
            if (!$domain->save()) {
                $notify->addError(Yii::t('app', 'Your form has a few errors, please fix them and try again!'));
            } else {
                $notify->addSuccess(Yii::t('app', 'Your form has been successfully saved!'));
            }

            Yii::app()->hooks->doAction('controller_action_save_data', $collection = new CAttributeCollection(array(
                'controller'=> $this,
                'success'   => $notify->hasSuccess,
                'domain'    => $domain,
            )));

            if ($collection->success) {
                $this->redirect(array('tracking_domains/update', 'id' => $domain->domain_id));
            }
        }

        $this->setData(array(
            'pageMetaTitle'     => $this->data->pageMetaTitle . ' | '. Yii::t('tracking_domains', 'Create new tracking domain'),
            'pageHeading'       => Yii::t('tracking_domains', 'Create new tracking domain'),
            'pageBreadcrumbs'   => array(
                Yii::t('tracking_domains', 'Tracking domains') => $this->createUrl('tracking_domains/index'),
                Yii::t('app', 'Create new'),
            )
        ));

        $this->render('form', compact('domain', 'currentDomain'));
    }

    /**
     * Update existing tracking domain
     */
    public function actionUpdate($id)
    {
        $domain = TrackingDomain::model()->findByPk((int)$id);

        if (empty($domain)) {
            throw new CHttpException(404, Yii::t('app', 'The requested page does not exist.'));
        }

        $request = Yii::app()->request;
        $notify  = Yii::app()->notify;
        $currentDomain = parse_url(Yii::app()->createAbsoluteUrl($this->route), PHP_URL_HOST);

        if ($request->isPostRequest && ($attributes = (array)$request->getPost($domain->modelName, array()))) {
            $domain->attributes = $attributes;
            if (!$domain->save()) {
                $notify->addError(Yii::t('app', 'Your form has a few errors, please fix them and try again!'));
            } else {
                $notify->addSuccess(Yii::t('app', 'Your form has been successfully saved!'));
            }

            Yii::app()->hooks->doAction('controller_action_save_data', $collection = new CAttributeCollection(array(
                'controller'=> $this,
                'success'   => $notify->hasSuccess,
                'domain'    => $domain,
            )));

            if ($collection->success) {
                $this->redirect(array('tracking_domains/update', 'id' => $domain->domain_id));
            }
        }

        $this->setData(array(
            'pageMetaTitle'     => $this->data->pageMetaTitle . ' | '. Yii::t('tracking_domains', 'Update tracking domain'),
            'pageHeading'       => Yii::t('tracking_domains', 'Update tracking domain'),
            'pageBreadcrumbs'   => array(
                Yii::t('tracking_domains', 'Tracking domains') => $this->createUrl('tracking_domains/index'),
                Yii::t('app', 'Update'),
            )
        ));

        $this->render('form', compact('domain', 'currentDomain'));
    }

    /**
     * Delete existing tracking domain
     */
    public function actionDelete($id)
    {
        $domain = TrackingDomain::model()->findByPk((int)$id);
        if (empty($domain)) {
            throw new CHttpException(404, Yii::t('app', 'The requested page does not exist.'));
        }

        $domain->delete();

        $request = Yii::app()->request;
        $notify  = Yii::app()->notify;

        $redirect = null;
        if (!$request->getQuery('ajax')) {
            $notify->addSuccess(Yii::t('app', 'The item has been successfully deleted!'));
            $redirect = $request->getPost('returnUrl', array('tracking_domains/index'));
        }

        // since 1.3.5.9
        Yii::app()->hooks->doAction('controller_action_delete_data', $collection = new CAttributeCollection(array(
            'controller' => $this,
            'model'      => $domain,
            'redirect'   => $redirect,
        )));

        if ($collection->redirect) {
            $this->redirect($collection->redirect);
        }
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
