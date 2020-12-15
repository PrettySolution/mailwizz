<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * List_page_typeController
 *
 * Handles the actions for list page types related tasks
 *
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.0
 */

class List_page_typeController extends Controller
{
    /**
     * List all the available page typs
     */
    public function actionIndex()
    {
        $request    = Yii::app()->request;
        $pageType   = new ListPageType('search');
        $pageType->unsetAttributes();

        // for filters.
        $pageType->attributes = (array)$request->getQuery($pageType->modelName, array());

        $this->setData(array(
            'pageMetaTitle'     => $this->data->pageMetaTitle . ' | '. Yii::t('list_page_types', 'List page types'),
            'pageHeading'       => Yii::t('list_page_types', 'List page types'),
            'pageBreadcrumbs'   => array(
                Yii::t('list_page_types', 'List page types') => $this->createUrl('list_page_type/index'),
                Yii::t('app', 'View all')
            )
        ));

        $this->render('list', compact('pageType'));
    }

    /**
     * Update certain page type
     */
    public function actionUpdate($id)
    {
        $pageType = ListPageType::model()->findByPk((int)$id);

        if (empty($pageType)) {
            throw new CHttpException(404, Yii::t('app', 'The requested page does not exist.'));
        }

        $request = Yii::app()->request;
        $notify = Yii::app()->notify;

        if ($request->isPostRequest && ($attributes = (array)$request->getPost($pageType->modelName, array()))) {
            $pageType->attributes = $attributes;
            if (isset(Yii::app()->params['POST'][$pageType->modelName]['content'])) {
                $rawContent = Yii::app()->params['POST'][$pageType->modelName]['content'];
                if ($pageType->full_html === ListPageType::TEXT_YES) {
                    $pageType->content = $rawContent;
                } else {
                    $pageType->content = Yii::app()->ioFilter->purify($rawContent);
                }
            }
            if (isset(Yii::app()->params['POST'][$pageType->modelName]['description'])) {
                $pageType->description = Yii::app()->ioFilter->purify(Yii::app()->params['POST'][$pageType->modelName]['description']);
            }
            if (!$pageType->save()) {
                $notify->addError(Yii::t('app', 'Your form has a few errors, please fix them and try again!'));
            } else {
                $notify->addSuccess(Yii::t('app', 'Your form has been successfully saved!'));
            }

            Yii::app()->hooks->doAction('controller_action_save_data', $collection = new CAttributeCollection(array(
                'controller'=> $this,
                'success'   => $notify->hasSuccess,
                'pageType'  => $pageType,
            )));

            if ($collection->success) {
                $this->redirect(array('list_page_type/update', 'id' => $pageType->type_id));
            }
        }

        // append the wysiwyg editor
        $pageType->fieldDecorator->onHtmlOptionsSetup = array($this, '_setupEditorOptions');
        $tags = $pageType->getAvailableTags();

        $this->setData(array(
            'pageMetaTitle'     => $this->data->pageMetaTitle . ' | '. Yii::t('list_page_types', 'Update page type'),
            'pageHeading'       => Yii::t('list_page_types', 'Update page type'),
            'pageBreadcrumbs'   => array(
                 Yii::t('list_page_types', 'List page types') => $this->createUrl('list_page_type/index'),
                Yii::t('app', 'Update'),
            )
        ));

        $this->render('form', compact('pageType', 'tags'));
    }

    /**
     * Callback method to set the editor options
     */
    public function _setupEditorOptions(CEvent $event)
    {
        if (!in_array($event->params['attribute'], array('content', 'description'))) {
            return;
        }

        $options = array();
        if ($event->params['htmlOptions']->contains('wysiwyg_editor_options')) {
            $options = (array)$event->params['htmlOptions']->itemAt('wysiwyg_editor_options');
        }
        $options['id'] = CHtml::activeId($event->sender->owner, $event->params['attribute']);

        if ($event->params['attribute'] == 'content' && $event->sender->owner->full_html === ListPageType::TEXT_YES) {
            $options['fullPage']        = true;
            $options['allowedContent']  = true;
            $options['height']          = 500;
        }

        if ($event->params['attribute'] == 'description') {
            $options['toolbar'] = 'Simple';
            $options['height']  = 50;
        }

        $event->params['htmlOptions']->add('wysiwyg_editor_options', $options);
    }
}
