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

class Ext_tour_slideshow_slidesController extends Controller
{
    // the extension instance
    public $extension;
    
    // init
    public function init()
    {
        $this->getData('pageScripts')->add(array('src' => $this->extension->getAssetsUrl() . '/js/tour.js'));
        parent::init();
    }
    
    // move the view path
    public function getViewPath()
    {
        return Yii::getPathOfAlias('ext-tour.backend.views.slideshow-slides');
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
     * List all available slides for this slideshow
     */
    public function actionIndex($slideshow_id)
    {
        $slideshow = TourSlideshow::model()->findByPk((int)$slideshow_id);

        if (empty($slideshow)) {
            throw new CHttpException(404, Yii::t('app', 'The requested page does not exist.'));
        }
        
        $request = Yii::app()->request;
        $slide   = new TourSlideshowSlide('search');
        $slide->unsetAttributes();
        
        // for filters.
        $slide->attributes   = (array)$request->getQuery($slide->modelName, array());
        $slide->slideshow_id = $slideshow->slideshow_id;
        
        $this->setData(array(
            'pageMetaTitle'   => $this->data->pageMetaTitle . ' | '. $this->extension->t('View all slides'),
            'pageHeading'     => $this->extension->t('{name} slides', array('{name}' => $slideshow->name)),
            'pageBreadcrumbs' => array(
                Yii::t('app', 'Extensions')       => $this->createUrl('extensions/index'),
                $this->extension->t('Tour')       => $this->createUrl('ext_tour_settings/index'),
                $this->extension->t('Slideshows') => $this->createUrl('ext_tour_slideshows/index'),
                $slideshow->name . ' '            => $this->createUrl('ext_tour_slideshows/update', array('id' => $slideshow->slideshow_id)),
                $this->extension->t('View all slides')
            )
        ));

        $this->render('list', compact('slideshow', 'slide'));
    }

    /**
     * Create a new slideshow slide
     */
    public function actionCreate($slideshow_id)
    {
        $slideshow = TourSlideshow::model()->findByPk((int)$slideshow_id);

        if (empty($slideshow)) {
            throw new CHttpException(404, Yii::t('app', 'The requested page does not exist.'));
        }

        $request = Yii::app()->request;
        $notify  = Yii::app()->notify;
        $slide   = new TourSlideshowSlide();

        if ($request->isPostRequest && ($attributes = (array)$request->getPost($slide->modelName, array()))) {

            $slide->attributes   = $attributes;
            $slide->slideshow_id = $slideshow->slideshow_id;
            
            if (isset(Yii::app()->params['POST'][$slide->modelName]['content'])) {
                $slide->content = Yii::app()->ioFilter->purify(Yii::app()->params['POST'][$slide->modelName]['content']);
            }
            
            if (!$slide->save()) {
                $notify->addError(Yii::t('app', 'Your form has a few errors, please fix them and try again!'));
            } else {
                $notify->addSuccess(Yii::t('app', 'Your form has been successfully saved!'));
            }

            Yii::app()->hooks->doAction('controller_action_save_data', $collection = new CAttributeCollection(array(
                'controller'=> $this,
                'success'   => $notify->hasSuccess,
                'model'     => $slide,
            )));

            if ($collection->success) {
                $this->redirect(array('ext_tour_slideshow_slides/index', 'slideshow_id' => $slideshow->slideshow_id));
            }
        }

        $this->setData(array(
            'pageMetaTitle'     => $this->data->pageMetaTitle . ' | '. $this->extension->t('Create slide'),
            'pageHeading'       => $this->extension->t('Create new slide'),
            'pageBreadcrumbs'   => array(
                Yii::t('app', 'Extensions')       => $this->createUrl('extensions/index'),
                $this->extension->t('Tour')       => $this->createUrl('ext_tour_settings/index'),
                $this->extension->t('Slideshows') => $this->createUrl('ext_tour_slideshows/index'),
                $slideshow->name . ' '            => $this->createUrl('ext_tour_slideshows/update', array('id' => $slideshow->slideshow_id)),
                $this->extension->t('Create slide')
            )
        ));

        $slide->fieldDecorator->onHtmlOptionsSetup = array($this, '_setEditorOptions');

        $this->render('form', compact('slideshow', 'slide'));
    }

    /**
     * Update existing slideshow slide
     */
    public function actionUpdate($slideshow_id, $id)
    {
        $slideshow = TourSlideshow::model()->findByPk((int)$slideshow_id);

        if (empty($slideshow)) {
            throw new CHttpException(404, Yii::t('app', 'The requested page does not exist.'));
        }
        
        $slide = TourSlideshowSlide::model()->findByPk((int)$id);

        if (empty($slide)) {
            throw new CHttpException(404, Yii::t('app', 'The requested page does not exist.'));
        }

        $request = Yii::app()->request;
        $notify  = Yii::app()->notify;

        if ($request->isPostRequest && ($attributes = (array)$request->getPost($slide->modelName, array()))) {

            $slide->attributes   = $attributes;
            $slide->slideshow_id = $slideshow->slideshow_id;

            if (isset(Yii::app()->params['POST'][$slide->modelName]['content'])) {
                $slide->content = Yii::app()->ioFilter->purify(Yii::app()->params['POST'][$slide->modelName]['content']);
            }
            
            if (!$slide->save()) {
                $notify->addError(Yii::t('app', 'Your form has a few errors, please fix them and try again!'));
            } else {
                $notify->addSuccess(Yii::t('app', 'Your form has been successfully saved!'));
            }

            Yii::app()->hooks->doAction('controller_action_save_data', $collection = new CAttributeCollection(array(
                'controller'=> $this,
                'success'   => $notify->hasSuccess,
                'model'     => $slide,
            )));

            if ($collection->success) {
                $this->redirect(array('ext_tour_slideshow_slides/index', 'slideshow_id' => $slideshow->slideshow_id));
            }
        }

        $this->setData(array(
            'pageMetaTitle'     => $this->data->pageMetaTitle . ' | '. $this->extension->t('Update slide'),
            'pageHeading'       => $this->extension->t('Update slide'),
            'pageBreadcrumbs'   => array(
                Yii::t('app', 'Extensions')       => $this->createUrl('extensions/index'),
                $this->extension->t('Tour')       => $this->createUrl('ext_tour_settings/index'),
                $this->extension->t('Slideshows') => $this->createUrl('ext_tour_slideshows/index'),
                $slideshow->name . ' '            => $this->createUrl('ext_tour_slideshows/update', array('id' => $slideshow->slideshow_id)),
                $this->extension->t('Update slide')
            )
        ));

        $slide->fieldDecorator->onHtmlOptionsSetup = array($this, '_setEditorOptions');
        
        $this->render('form', compact('slideshow', 'slide'));
    }

    /**
     * Delete an existing article
     */
    public function actionDelete($id)
    {
        $model = TourSlideshowSlide::model()->findByPk((int)$id);

        if (empty($model)) {
            throw new CHttpException(404, Yii::t('app', 'The requested page does not exist.'));
        }

        $model->delete();

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
            'model'      => $model,
            'redirect'   => $redirect,
        )));

        if ($collection->redirect) {
            $this->redirect($collection->redirect);
        }
    }

    /**
     * Callback method to set the editor options
     */
    public function _setEditorOptions(CEvent $event)
    {
        if (!in_array($event->params['attribute'], array('content'))) {
            return;
        }

        $options = array();
        if ($event->params['htmlOptions']->contains('wysiwyg_editor_options')) {
            $options = (array)$event->params['htmlOptions']->itemAt('wysiwyg_editor_options');
        }
        $options['id']          = CHtml::activeId($event->sender->owner, $event->params['attribute']);
        $options['contentsCss'] = array(
            'https://fonts.googleapis.com/css?family=Raleway',
            Yii::app()->extensionsManager->getExtensionInstance('tour')->getAssetsUrl() . '/css/editor.css',
        );
        $event->params['htmlOptions']->add('wysiwyg_editor_options', $options);
    }
}
