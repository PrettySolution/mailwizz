<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * Start_pagesController
 *
 * Handles the actions for list page types related tasks
 *
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.3.9.2
 */

class Start_pagesController extends Controller
{
    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->getData('pageStyles')->add(array('src' => Yii::app()->apps->getAppUrl('frontend', 'assets/js/colorpicker/css/bootstrap-colorpicker.css', false, true)));
        $this->getData('pageScripts')->add(array('src' => Yii::app()->apps->getAppUrl('frontend', 'assets/js/colorpicker/js/bootstrap-colorpicker.min.js', false, true)));
        $this->getData('pageScripts')->add(array('src' => AssetsUrl::js('start-pages.js')));
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
     * List all the available page indexes
     */
    public function actionIndex()
    {
        $request = Yii::app()->request;
        $model   = new StartPage('search');
        $model->unsetAttributes();
        
        // for filters.
        $model->attributes = (array)$request->getQuery($model->modelName, array());

        $this->setData(array(
            'pageMetaTitle'     => $this->data->pageMetaTitle . ' | '. Yii::t('start_pages', 'Start pages'),
            'pageHeading'       => Yii::t('start_pages', 'Start pages'),
            'pageBreadcrumbs'   => array(
                Yii::t('start_pages', 'Start pages') => $this->createUrl('start_pages/index'),
                Yii::t('app', 'View all')
            )
        ));

        $this->render('list', compact('model'));
    }

    /**
     * Create page index
     */
    public function actionCreate()
    {
        $model   = new StartPage();
        $request = Yii::app()->request;
        $notify  = Yii::app()->notify;

        if ($request->isPostRequest && ($attributes = (array)$request->getPost($model->modelName, array()))) {
            $model->attributes = $attributes;
            if (isset(Yii::app()->params['POST'][$model->modelName]['content'])) {
                $rawContent = Yii::app()->params['POST'][$model->modelName]['content'];
                $model->content = Yii::app()->ioFilter->purify($rawContent);
            }
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

            if ($collection->success) {
                $this->redirect(array('start_pages/update', 'id' => $model->page_id));
            }
        }

        // append the wysiwyg editor
        $model->fieldDecorator->onHtmlOptionsSetup = array($this, '_setupEditorOptions');

        $this->setData(array(
            'pageMetaTitle'     => $this->data->pageMetaTitle . ' | '. Yii::t('start_pages', 'Create new page'),
            'pageHeading'       => Yii::t('start_pages', 'Create new page'),
            'pageBreadcrumbs'   => array(
                Yii::t('start_pages', 'Start pages') => $this->createUrl('start_pages/index'),
                Yii::t('app', 'Create new')
            )
        ));

        $this->render('form', compact('model'));
    }

    /**
     * Update page index
     */
    public function actionUpdate($id)
    {
        $model = StartPage::model()->findByPk((int)$id);

        if (empty($model)) {
            throw new CHttpException(404, Yii::t('app', 'The requested page does not exist.'));
        }

        $request = Yii::app()->request;
        $notify  = Yii::app()->notify;

        if ($request->isPostRequest && ($attributes = (array)$request->getPost($model->modelName, array()))) {
            $model->attributes = $attributes;
            if (isset(Yii::app()->params['POST'][$model->modelName]['content'])) {
                $rawContent = Yii::app()->params['POST'][$model->modelName]['content'];
                $model->content = Yii::app()->ioFilter->purify($rawContent);
            }
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

            if ($collection->success) {
                $this->redirect(array('start_pages/update', 'id' => $model->page_id));
            }
        }

        // append the wysiwyg editor
        $model->fieldDecorator->onHtmlOptionsSetup = array($this, '_setupEditorOptions');

        $this->setData(array(
            'pageMetaTitle'     => $this->data->pageMetaTitle . ' | '. Yii::t('start_pages', 'Update page'),
            'pageHeading'       => Yii::t('start_pages', 'Update page'),
            'pageBreadcrumbs'   => array(
                Yii::t('start_pages', 'Start pages') => $this->createUrl('start_pages/index'),
                Yii::t('app', 'Update')
            )
        ));

        $this->render('form', compact('model'));
    }

    /**
     * Delete existing page index
     */
    public function actionDelete($id)
    {
        $model = StartPage::model()->findByPk((int)$id);

        if (empty($model)) {
            throw new CHttpException(404, Yii::t('app', 'The requested page does not exist.'));
        }

        $model->delete();

        $request = Yii::app()->request;
        $notify  = Yii::app()->notify;

        $redirect = null;
        if (!$request->getQuery('ajax')) {
            $notify->addSuccess(Yii::t('app', 'The item has been successfully deleted!'));
            $redirect = $request->getPost('returnUrl', array('start_pages/index'));
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
    public function _setupEditorOptions(CEvent $event)
    {
        if (!in_array($event->params['attribute'], array('content'))) {
            return;
        }

        $options = array();
        if ($event->params['htmlOptions']->contains('wysiwyg_editor_options')) {
            $options = (array)$event->params['htmlOptions']->itemAt('wysiwyg_editor_options');
        }
        $options['id'] = CHtml::activeId($event->sender->owner, $event->params['attribute']);

        if ($event->params['attribute'] == 'content') {
            $options['height'] = 300;
        }
        
        $event->params['htmlOptions']->add('wysiwyg_editor_options', $options);
    }
}
