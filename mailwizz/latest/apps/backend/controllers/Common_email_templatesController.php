<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * Common_email_templatesController
 *
 * Handles the actions for common email templates related tasks
 *
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.6.2
 */

class Common_email_templatesController extends Controller
{
	/**
	 * @return BaseController|void
	 * @throws CException
	 */
	public function init()
	{
		$this->getData('pageScripts')->add(array('src' => AssetsUrl::js('common-email-templates.js')));
		parent::init();
	}
	
	/**
	 * Define the filters for various controller actions
	 * Merge the filters with the ones from parent implementation
	 */
	public function filters()
	{
		$filters = array(
			'postOnly + delete, reinstall',
		);

		return CMap::mergeArray($filters, parent::filters());
	}

    /**
     * List all available email templates
     */
    public function actionIndex()
    {
        $request = Yii::app()->request;
        $model   = new CommonEmailTemplate('search');
        $model->unsetAttributes();

        $model->attributes = (array)$request->getQuery($model->modelName, array());
		$types = OptionEmailTemplate::getTypesList();
		
        $this->setData(array(
            'pageMetaTitle'   => $this->data->pageMetaTitle . ' | '. Yii::t('common_email_templates', 'View email templates'),
            'pageHeading'     => Yii::t('common_email_templates', 'View email templates'),
            'pageBreadcrumbs' => array(
                Yii::t('common_email_templates', 'Common email templates') => $this->createUrl('common_email_templates/index'),
                Yii::t('app', 'View all')
            )
        ));

        $this->render('list', compact('model', 'types'));
    }

    /**
     * Create a new template
     * @throws CException
     */
    public function actionCreate()
    {
        $model   = new CommonEmailTemplate();
        $request = Yii::app()->request;
        $notify  = Yii::app()->notify;

        if ($request->isPostRequest && ($attributes = (array)$request->getPost($model->modelName, array()))) {
            $model->attributes = $attributes;
            $model->removable  = CommonEmailTemplate::TEXT_YES;
	        $model->content    = Yii::app()->params['POST'][$model->modelName]['content'];
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
                $this->redirect(array('common_email_templates/index'));
            }
        }

	    $model->fieldDecorator->onHtmlOptionsSetup = array($this, '_setupEditorOptions');
        
        $this->setData(array(
            'pageMetaTitle'   => $this->data->pageMetaTitle . ' | '. Yii::t('common_email_templates', 'Create new template'),
            'pageHeading'     => Yii::t('common_email_templates', 'Create new template'),
            'pageBreadcrumbs' => array(
                Yii::t('common_email_templates', 'Common email templates') => $this->createUrl('common_email_templates/index'),
                Yii::t('app', 'Create new'),
            )
        ));

        $this->render('form', compact('model'));
    }

	/**
	 * Update existing template
	 * 
	 * @param $id
	 *
	 * @throws CException
	 * @throws CHttpException
	 */
    public function actionUpdate($id)
    {
        $model = CommonEmailTemplate::model()->findByPk((int)$id);

        if (empty($model)) {
            throw new CHttpException(404, Yii::t('app', 'The requested page does not exist.'));
        }

        $request = Yii::app()->request;
        $notify  = Yii::app()->notify;

        if ($request->isPostRequest && ($attributes = (array)$request->getPost($model->modelName, array()))) {
            $model->attributes = $attributes;
	        $model->content    = Yii::app()->params['POST'][$model->modelName]['content'];
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
                $this->redirect(array('common_email_templates/update', 'id' => $model->template_id));
            }
        }

	    $model->fieldDecorator->onHtmlOptionsSetup = array($this, '_setupEditorOptions');
        
        $this->setData(array(
            'pageMetaTitle'   => $this->data->pageMetaTitle . ' | '. Yii::t('common_email_templates', 'Update template'),
            'pageHeading'     => Yii::t('common_email_templates', 'Update template'),
            'pageBreadcrumbs' => array(
                Yii::t('common_email_templates', 'Common email templates') => $this->createUrl('common_email_templates/index'),
                Yii::t('app', 'Update'),
            )
        ));

        $this->render('form', compact('model'));
    }

	/**
	 * Reinstall core templates
	 */
	public function actionReinstall()
	{
		CommonEmailTemplate::reinstallCoreTemplates();
		
		$request = Yii::app()->request;
		$notify  = Yii::app()->notify;

		$redirect = null;
		if (!$request->getQuery('ajax')) {
			$notify->addSuccess(Yii::t('app', 'The action has been successfully completed!'));
			$redirect = $request->getPost('returnUrl', array('common_email_templates/index'));
		}

		// since 1.3.5.9
		Yii::app()->hooks->doAction('controller_action_delete_data', $collection = new CAttributeCollection(array(
			'controller' => $this,
			'redirect'   => $redirect,
		)));

		if ($collection->redirect) {
			$this->redirect($collection->redirect);
		}
	}
	
	/**
	 * Delete existing template
	 */
	public function actionDelete($id)
	{
		$model = CommonEmailTemplate::model()->findByPk((int)$id);

		if (empty($model)) {
			throw new CHttpException(404, Yii::t('app', 'The requested page does not exist.'));
		}

		if ($model->removable == CommonEmailTemplate::TEXT_YES) {
			$model->delete();
		}

		$request = Yii::app()->request;
		$notify  = Yii::app()->notify;

		$redirect = null;
		if (!$request->getQuery('ajax')) {
			$notify->addSuccess(Yii::t('app', 'The item has been successfully deleted!'));
			$redirect = $request->getPost('returnUrl', array('common_email_templates/index'));
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

		$options['id']              = CHtml::activeId($event->sender->owner, $event->params['attribute']);
		$options['height']          = 500;
		$options['fullPage']        = true;
		$options['allowedContent']  = true;

		$event->params['htmlOptions']->add('wysiwyg_editor_options', $options);
	}
}
