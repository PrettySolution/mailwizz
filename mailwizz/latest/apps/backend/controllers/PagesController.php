<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * PagesController
 *
 * Handles the actions for pages related tasks
 *
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.5.5
 */

class PagesController extends Controller
{
    /**
     * @inheritdoc
     * 
     * @return BaseController|void
     */
    public function init()
    {
        $this->getData('pageScripts')->add(array('src' => AssetsUrl::js('pages.js')));
        parent::init();
    }

    /**
     * Define the filters for various controller actions
     * Merge the filters with the ones from parent implementation
     */
    public function filters()
    {
        $filters = array(
            'postOnly + delete, slug',
        );

        return CMap::mergeArray($filters, parent::filters());
    }

    /**
     * List all available pages
     */
    public function actionIndex()
    {
        $request = Yii::app()->request;
        $page    = new Page('search');
        $page->unsetAttributes();

        // for filters.
        $page->attributes = (array)$request->getQuery($page->modelName, array());

        $this->setData(array(
            'pageMetaTitle'     => $this->data->pageMetaTitle . ' | '. Yii::t('pages', 'View pages'),
            'pageHeading'       => Yii::t('pages', 'View pages'),
            'pageBreadcrumbs'   => array(
                Yii::t('pages', 'Pages') => $this->createUrl('pages/index'),
                Yii::t('app', 'View all')
            )
        ));

        $this->render('list', compact('page'));
    }

    /**
     * Create a new page
     * 
     * @throws CException
     */
    public function actionCreate()
    {
        $request = Yii::app()->request;
        $notify  = Yii::app()->notify;
        $page    = new Page();

        if ($request->isPostRequest && ($attributes = (array)$request->getPost($page->modelName, array()))) {
            
            $page->attributes = $attributes;
            
            if (isset(Yii::app()->params['POST'][$page->modelName]['content'])) {
                $page->content = Yii::app()->ioFilter->purify(Yii::app()->params['POST'][$page->modelName]['content']);
            }
            
            if (!$page->save()) {
                $notify->addError(Yii::t('app', 'Your form has a few errors, please fix them and try again!'));
            } else {
                $notify->addSuccess(Yii::t('app', 'Your form has been successfully saved!'));
            }

            Yii::app()->hooks->doAction('controller_action_save_data', $collection = new CAttributeCollection(array(
                'controller'=> $this,
                'success'   => $notify->hasSuccess,
                'page'   => $page,
            )));

            if ($collection->success) {
                $this->redirect(array('pages/index'));
            }
        }

        $page->fieldDecorator->onHtmlOptionsSetup = array($this, '_setupEditorOptions');
        
        $this->setData(array(
            'pageMetaTitle'   => $this->data->pageMetaTitle . ' | '. Yii::t('pages', 'Create new page'),
            'pageHeading'     => Yii::t('pages', 'Create new page'),
            'pageBreadcrumbs' => array(
                Yii::t('pages', 'Pages') => $this->createUrl('pages/index'),
                Yii::t('app', 'Create new'),
            )
        ));

        $this->render('form', compact('page'));
    }

    /**
     * Update existing page
     * 
     * @param $id
     * @throws CException
     * @throws CHttpException
     */
    public function actionUpdate($id)
    {
        $page = Page::model()->findByPk((int)$id);

        if (empty($page)) {
            throw new CHttpException(404, Yii::t('app', 'The requested page does not exist.'));
        }

        $request = Yii::app()->request;
        $notify  = Yii::app()->notify;

        if ($request->isPostRequest && ($attributes = (array)$request->getPost($page->modelName, array()))) {
            
            $page->attributes = $attributes;
            
            if (isset(Yii::app()->params['POST'][$page->modelName]['content'])) {
                $page->content = Yii::app()->ioFilter->purify(Yii::app()->params['POST'][$page->modelName]['content']);
            }
            
            if (!$page->save()) {
                $notify->addError(Yii::t('app', 'Your form has a few errors, please fix them and try again!'));
            } else {
                $notify->addSuccess(Yii::t('app', 'Your form has been successfully saved!'));
            }

            Yii::app()->hooks->doAction('controller_action_save_data', $collection = new CAttributeCollection(array(
                'controller'=> $this,
                'success'   => $notify->hasSuccess,
                'page'      => $page,
            )));

            if ($collection->success) {
                $this->redirect(array('pages/index'));
            }
        }

        $page->fieldDecorator->onHtmlOptionsSetup = array($this, '_setupEditorOptions');
        
        $this->setData(array(
            'pageMetaTitle'   => $this->data->pageMetaTitle . ' | '. Yii::t('pages', 'Update page'),
            'pageHeading'     => Yii::t('pages', 'Update page'),
            'pageBreadcrumbs' => array(
                Yii::t('pages', 'Pages') => $this->createUrl('pages/index'),
                Yii::t('app', 'Update'),
            )
        ));

        $this->render('form', compact('page'));
    }

    /**
     * Delete an existing page
     * 
     * @param $id
     * @throws CDbException
     * @throws CException
     * @throws CHttpException
     */
    public function actionDelete($id)
    {
        $page = Page::model()->findByPk((int)$id);

        if (empty($page)) {
            throw new CHttpException(404, Yii::t('app', 'The requested page does not exist.'));
        }

        $page->delete();

        $request = Yii::app()->request;
        $notify  = Yii::app()->notify;

        $redirect = null;
        if (!$request->getQuery('ajax')) {
            $notify->addSuccess(Yii::t('app', 'The item has been successfully deleted!'));
            $redirect = $request->getPost('returnUrl', array('pages/index'));
        }

        // since 1.3.5.9
        Yii::app()->hooks->doAction('controller_action_delete_data', $collection = new CAttributeCollection(array(
            'controller' => $this,
            'model'      => $page,
            'redirect'   => $redirect,
        )));

        if ($collection->redirect) {
            $this->redirect($collection->redirect);
        }
    }

    /**
     * Generate the slug for an page based on the page title
     */
    public function actionSlug()
    {
        $request = Yii::app()->request;

        if (!$request->isAjaxRequest) {
            $this->redirect(array('pages/index'));
        }

        $page = new Page();
        $page->page_id = (int)$request->getPost('page_id');
        $page->slug    = $request->getPost('string');
        $page->slug    = $page->generateSlug();

        return $this->renderJson(array(
            'result' => 'success', 
            'slug'   => $page->slug
        ));
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

        $options['id']     = CHtml::activeId($event->sender->owner, $event->params['attribute']);
        $options['height'] = 500;

        $event->params['htmlOptions']->add('wysiwyg_editor_options', $options);
    }
}
