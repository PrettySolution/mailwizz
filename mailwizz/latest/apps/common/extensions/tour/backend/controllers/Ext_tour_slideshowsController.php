<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 *
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.0
 */

class Ext_tour_slideshowsController extends Controller
{
    // the extension instance
    public $extension;

    // move the view path
    public function getViewPath()
    {
        return Yii::getPathOfAlias('ext-tour.backend.views.slideshows');
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
     * List all available articles
     */
    public function actionIndex()
    {
        $request   = Yii::app()->request;
        $slideshow = new TourSlideshow('search');
        $slideshow->unsetAttributes();

        // for filters.
        $slideshow->attributes = (array)$request->getQuery($slideshow->modelName, array());

        $this->setData(array(
            'pageMetaTitle'   => $this->data->pageMetaTitle . ' | '. $this->extension->t('View slideshows'),
            'pageHeading'     => $this->extension->t('View slideshows'),
            'pageBreadcrumbs' => array(
                Yii::t('app', 'Extensions')       => $this->createUrl('extensions/index'),
                $this->extension->t('Tour')       => $this->createUrl('ext_tour_settings/index'),
                $this->extension->t('Slideshows') => $this->createUrl('ext_tour_slideshows/index'),
                Yii::t('app', 'View all')
            )
        ));

        $this->render('list', compact('slideshow'));
    }

    /**
     * Create a new slideshow
     */
    public function actionCreate()
    {
        $request    = Yii::app()->request;
        $notify     = Yii::app()->notify;
        $slideshow  = new TourSlideshow();

        if ($request->isPostRequest && ($attributes = (array)$request->getPost($slideshow->modelName, array()))) {

            $slideshow->attributes = $attributes;
            
            if (!$slideshow->save()) {
                $notify->addError(Yii::t('app', 'Your form has a few errors, please fix them and try again!'));
            } else {
                $notify->addSuccess(Yii::t('app', 'Your form has been successfully saved!'));
            }

            Yii::app()->hooks->doAction('controller_action_save_data', $collection = new CAttributeCollection(array(
                'controller'=> $this,
                'success'   => $notify->hasSuccess,
                'model'     => $slideshow,
            )));

            if ($collection->success) {
                $this->redirect(array('ext_tour_slideshows/index'));
            }
        }
        
        $this->setData(array(
            'pageMetaTitle'     => $this->data->pageMetaTitle . ' | '. $this->extension->t('View slideshows'),
            'pageHeading'       => $this->extension->t('View slideshows'),
            'pageBreadcrumbs'   => array(
                Yii::t('app', 'Extensions')       => $this->createUrl('extensions/index'),
                $this->extension->t('Tour')       => $this->createUrl('ext_tour_settings/index'),
                $this->extension->t('Slideshows') => $this->createUrl('ext_tour_slideshows/index'),
                IconHelper::make('create') . Yii::t('app', 'Create new')
            )
        ));

        $this->render('form', compact('slideshow'));
    }

    /**
     * Update existing article
     */
    public function actionUpdate($id)
    {
        $slideshow = TourSlideshow::model()->findByPk((int)$id);

        if (empty($slideshow)) {
            throw new CHttpException(404, Yii::t('app', 'The requested page does not exist.'));
        }

        $request = Yii::app()->request;
        $notify  = Yii::app()->notify;

        if ($request->isPostRequest && ($attributes = (array)$request->getPost($slideshow->modelName, array()))) {

            $slideshow->attributes = $attributes;
            
            if (!$slideshow->save()) {
                $notify->addError(Yii::t('app', 'Your form has a few errors, please fix them and try again!'));
            } else {
                $notify->addSuccess(Yii::t('app', 'Your form has been successfully saved!'));
            }

            Yii::app()->hooks->doAction('controller_action_save_data', $collection = new CAttributeCollection(array(
                'controller'=> $this,
                'success'   => $notify->hasSuccess,
                'model'     => $slideshow,
            )));

            if ($collection->success) {
                $this->redirect(array('ext_tour_slideshows/index'));
            }
        }
        
        $this->setData(array(
            'pageMetaTitle'     => $this->data->pageMetaTitle . ' | '. $this->extension->t('View slideshows'),
            'pageHeading'       => $this->extension->t('View slideshows'),
            'pageBreadcrumbs'   => array(
                Yii::t('app', 'Extensions')       => $this->createUrl('extensions/index'),
                $this->extension->t('Tour')       => $this->createUrl('ext_tour_settings/index'),
                $this->extension->t('Slideshows') => $this->createUrl('ext_tour_slideshows/index'),
                Yii::t('app', 'Update')
            )
        ));

        $this->render('form', compact('slideshow'));
    }

    /**
     * Delete an existing article
     */
    public function actionDelete($id)
    {
        $slideshow = TourSlideshow::model()->findByPk((int)$id);

        if (empty($slideshow)) {
            throw new CHttpException(404, Yii::t('app', 'The requested page does not exist.'));
        }

        $slideshow->delete();

        $request = Yii::app()->request;
        $notify  = Yii::app()->notify;

        $redirect = null;
        if (!$request->getQuery('ajax')) {
            $notify->addSuccess(Yii::t('app', 'The item has been successfully deleted!'));
            $redirect = $request->getPost('returnUrl', array('ext_tour_slideshows/index'));
        }

        // since 1.3.5.9
        Yii::app()->hooks->doAction('controller_action_delete_data', $collection = new CAttributeCollection(array(
            'controller' => $this,
            'model'      => $slideshow,
            'redirect'   => $redirect,
        )));

        if ($collection->redirect) {
            $this->redirect($collection->redirect);
        }
    }
}
