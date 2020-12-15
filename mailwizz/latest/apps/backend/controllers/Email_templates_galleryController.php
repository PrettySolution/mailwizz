<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * Email_templates_galleryController
 *
 * Handles the actions for templates related tasks
 *
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.3.4.7
 */

class Email_templates_galleryController extends Controller
{
    public function init()
    {
        $this->getData('pageScripts')->add(array('src' => AssetsUrl::js('email-templates-gallery.js')));
        parent::init();
    }

    /**
     * Define the filters for various controller actions
     * Merge the filters with the ones from parent implementation
     */
    public function filters()
    {
        return CMap::mergeArray(array(
            'postOnly + delete',
        ), parent::filters());
    }

    /**
     * List available templates
     */
    public function actionIndex()
    {
        $request  = Yii::app()->request;
        $template = new CustomerEmailTemplate('search');
        $template->unsetAttributes();

        // for filters.
        $template->attributes  = (array)$request->getQuery($template->modelName, array());
        $template->customer_id = null;
        
        $this->setData(array(
            'pageMetaTitle'     => $this->data->pageMetaTitle.' | '.Yii::t('email_templates',  'Email templates'),
            'pageHeading'       => Yii::t('email_templates',  'View templates'),
            'pageBreadcrumbs'   => array(
                Yii::t('email_templates', 'Email templates') => $this->createUrl('email_templates_gallery/index'),
                Yii::t('email_templates', 'Gallery') => $this->createUrl('email_templates_gallery/index'),
                Yii::t('app', 'View all')
            )
        ));

        $templateUp = new CustomerEmailTemplate('upload');
        
        $this->render('list', compact('template', 'templateUp'));
    }

    /**
     * Create a new template
     */
    public function actionCreate()
    {
        $request = Yii::app()->request;
        $notify  = Yii::app()->notify;

        $template         = new CustomerEmailTemplate();
        $campaignTemplate = new CampaignTemplate();
        
        if ($request->isPostRequest && ($attributes = $request->getPost($template->modelName, array()))) {
            $template->attributes = $attributes;
            $template->content    = Yii::app()->params['POST'][$template->modelName]['content'];

            if ($template->save()) {
                $notify->addSuccess(Yii::t('email_templates',  'You successfully created a new email template!'));
            }

            Yii::app()->hooks->doAction('controller_action_save_data', $collection = new CAttributeCollection(array(
                'controller'    => $this,
                'success'       => $notify->hasSuccess,
                'template'      => $template,
            )));

            if ($collection->success) {
                $this->redirect(array('email_templates_gallery/update', 'template_uid' => $template->template_uid));
            }
        }

        $template->fieldDecorator->onHtmlOptionsSetup = array($this, '_setDefaultEditorForContent');

        $this->setData(array(
            'pageMetaTitle'     => $this->data->pageMetaTitle.' | '.Yii::t('email_templates',  'Create email template'),
            'pageHeading'       => Yii::t('email_templates',  'Create email template'),
            'pageBreadcrumbs'   => array(
                Yii::t('email_templates', 'Email templates') => $this->createUrl('email_templates_gallery/index'),
                Yii::t('email_templates', 'Gallery') => $this->createUrl('email_templates_gallery/index'),
                Yii::t('app', 'Create new'),
            )
        ));

        $this->render('form', compact('template', 'campaignTemplate'));
    }

    /**
     * Update existing template
     */
    public function actionUpdate($template_uid)
    {
        $campaignTemplate = new CampaignTemplate();
        $template         = $this->loadModel($template_uid);
        $request          = Yii::app()->request;
        $notify           = Yii::app()->notify;

        if ($request->isPostRequest && $attributes = $request->getPost($template->modelName, array())) {
            $template->attributes = $attributes;
            $template->content    = Yii::app()->params['POST'][$template->modelName]['content'];

            if ($template->save()) {
                $notify->addSuccess(Yii::t('email_templates',  'You successfully updated your email template!'));
            }

            Yii::app()->hooks->doAction('controller_action_save_data', $collection = new CAttributeCollection(array(
                'controller'    => $this,
                'success'       => $notify->hasSuccess,
                'template'      => $template,
            )));

            if ($collection->success) {
                $this->redirect(array('email_templates_gallery/update', 'template_uid' => $template->template_uid));
            }
        }

        $template->fieldDecorator->onHtmlOptionsSetup = array($this, '_setDefaultEditorForContent');
        $this->data->previewUrl = $this->createUrl('email_templates_gallery/preview', array('template_uid' => $template_uid));

        $this->setData(array(
            'pageMetaTitle'     => $this->data->pageMetaTitle.' | '.Yii::t('email_templates',  'Update email template'),
            'pageHeading'       => Yii::t('email_templates',  'Update email template'),
            'pageBreadcrumbs'   => array(
                Yii::t('email_templates', 'Email templates') => $this->createUrl('email_templates_gallery/index'),
                Yii::t('email_templates', 'Gallery') => $this->createUrl('email_templates_gallery/index'),
                Yii::t('app', 'Update')
            )
        ));

        $this->render('form', compact('template', 'campaignTemplate'));
    }

    /**
     * Copy a template
     */
    public function actionCopy($template_uid)
    {
        $template = $this->loadModel($template_uid);

        if (!($newTemplate = $template->copy())) {
            Yii::app()->notify->addError(Yii::t('email_templates', 'Unable to copy the template!'));
            $this->redirect(array('email_templates_gallery/index'));
        }

        Yii::app()->notify->addSuccess(Yii::t('email_templates', 'The template has been successfully copied!'));
        $this->redirect(array('email_templates_gallery/index'));
    }

    /**
     * Preview template
     */
    public function actionPreview($template_uid)
    {
        $template   = $this->loadModel($template_uid);
        $request    = Yii::app()->request;

        $cs = Yii::app()->clientScript;
        $cs->reset();
        $cs->registerCoreScript('jquery');

        if ($template->create_screenshot === CustomerEmailTemplate::TEXT_YES) {

            if (Yii::app()->request->enableCsrfValidation) {
                $cs->registerMetaTag($request->csrfTokenName, 'csrf-token-name');
                $cs->registerMetaTag($request->csrfToken, 'csrf-token-value');
            }

            $cs->registerMetaTag($this->createUrl('email_templates_gallery/save_screenshot', array('template_uid' => $template_uid)), 'save-screenshot-url');
            $cs->registerMetaTag(Yii::t('email_templates',  'Please wait while saving your template screenshot...'), 'wait-message');
            $cs->registerScriptFile(AssetsUrl::js('html2canvas/html2canvas.min.js'));
        }

        $cs->registerScriptFile(AssetsUrl::js('email-templates-gallery-preview.js'));

        $this->renderPartial('preview', compact('template'), false, true);
    }

    /**
     * Save template screenshot
     */
    public function actionSave_screenshot($template_uid)
    {
        $request = Yii::app()->request;
        if (!$request->isPostRequest || MW_DEBUG) {
           Yii::app()->end();
        }

        $template = $this->loadModel($template_uid);

        if ($template->create_screenshot !== CustomerEmailTemplate::TEXT_YES) {
           Yii::app()->end();
        }

        $data = null;

        // in case it takes to much.
        set_time_limit(0);

        // in case the user closes the popup!
        ignore_user_abort(true);

        if (isset(Yii::app()->params['POST']['data'])) {
            $data = Yii::app()->ioFilter->purify(Yii::app()->params['POST']['data']);
        }

        if (empty($data) || strpos($data, 'data:image/png;base64,') !== 0) {
           Yii::app()->end();
        }

        $base64img = str_replace('data:image/png;base64,', '', $data);
        if (!($image = base64_decode($base64img))) {
           Yii::app()->end();
        }

        $baseDir = Yii::getPathOfAlias('root.frontend.assets.gallery.'.$template_uid);
        if((!file_exists($baseDir) && !@mkdir($baseDir, 0777, true)) || (!@is_writable($baseDir) && !@chmod($baseDir, 0777))){
           Yii::app()->end();
        }

        $destination = $baseDir.'/'.$template_uid.'.png';
        file_put_contents($destination, $image);

        if (!($info = @getimagesize($destination))) {
            @unlink($destination);
        }

        $template->screenshot = '/frontend/assets/gallery/' . $template_uid . '/' . $template_uid . '.png';
        $template->create_screenshot = CustomerEmailTemplate::TEXT_NO;
        $template->save(false);

       Yii::app()->end();
    }

    /**
     * Upload a template zip archive
     */
    public function actionUpload()
    {
        $model = new CustomerEmailTemplate('upload');

        $request = Yii::app()->request;
        $redirect = array('email_templates_gallery/index');

        if ($request->isPostRequest && ($attributes = (array)$request->getPost($model->modelName, array()))) {
            $model->attributes = $attributes;
            $model->archive = CUploadedFile::getInstance($model, 'archive');
            if (!$model->validate() || !$model->uploader->handleUpload()) {
                Yii::app()->notify->addError($model->shortErrors->getAllAsString());
            } else {
                Yii::app()->notify->addSuccess(Yii::t('app', 'Your file has been successfully uploaded!'));
                $redirect = array('email_templates_gallery/update', 'template_uid' => $model->template_uid);
            }
            $this->redirect($redirect);
          }

         Yii::app()->notify->addError(Yii::t('app', 'Please select a file for upload!'));
         $this->redirect($redirect);
    }

    /**
     * Delete existing template
     */
    public function actionDelete($template_uid)
    {
        $template = $this->loadModel($template_uid);

        $template->delete();

        $request = Yii::app()->request;
        $notify  = Yii::app()->notify;

        $redirect = null;
        if (!$request->isAjaxRequest) {
            $notify->addSuccess(Yii::t('email_templates',  'Your template was successfully deleted!'));
            $redirect = $request->getPost('returnUrl', array('email_templates_gallery/index'));
        }

        // since 1.3.5.9
        Yii::app()->hooks->doAction('controller_action_delete_data', $collection = new CAttributeCollection(array(
            'controller' => $this,
            'model'      => $template,
            'redirect'   => $redirect,
        )));

        if ($collection->redirect) {
            $this->redirect($collection->redirect);
        }
    }

    /**
     * Helper method to load the email template AR model
     */
    public function loadModel($template_uid)
    {
        $model = CustomerEmailTemplate::model()->find(array(
            'condition' => 'template_uid = :uid AND customer_id IS NULL',
            'params'    => array(':uid' => $template_uid),
        ));

        if($model === null) {
            throw new CHttpException(404, Yii::t('app', 'The requested page does not exist.'));
        }

        return $model;
    }

    /**
     * Callback to setup the editor for creating/updating the template
     */
    public function _setDefaultEditorForContent(CEvent $event)
    {
        if ($event->params['attribute'] == 'content') {
            $options = array();
            if ($event->params['htmlOptions']->contains('wysiwyg_editor_options')) {
                $options = (array)$event->params['htmlOptions']->itemAt('wysiwyg_editor_options');
            }

            $options['id'] = CHtml::activeId($event->sender->owner, 'content');
            $options['fullPage'] = true;
            $options['allowedContent'] = true;
            $options['contentsCss'] = array();
            $options['height'] = 800;

            $event->params['htmlOptions']->add('wysiwyg_editor_options', $options);
        }
    }
}
