<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * Campaign_tagsController
 *
 * Handles the actions for customer campaign tags related tasks
 *
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.3.5.9
 */

class Campaign_tagsController extends Controller
{
    
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
     * List available campaign tags
     */
    public function actionIndex()
    {
        $request = Yii::app()->request;
        $model   = new CustomerCampaignTag('search');

        $model->unsetAttributes();
        $model->attributes  = (array)$request->getQuery($model->modelName, array());
        $model->customer_id = (int)Yii::app()->customer->getId();

        $this->setData(array(
            'pageMetaTitle'     => $this->data->pageMetaTitle . ' | '. Yii::t('campaigns', 'View campaign tags'),
            'pageHeading'       => Yii::t('campaigns', 'View campaign tags'),
            'pageBreadcrumbs'   => array(
                Yii::t('campaigns', 'Campaigns') => $this->createUrl('campaigns/index'),
                Yii::t('campaigns', 'Tags') => $this->createUrl('campaign_tags/index'),
                Yii::t('app', 'View all')
            )
        ));

        $this->render('list', compact('model'));
    }

    /**
     * Create a new campaign tag
     */
    public function actionCreate()
    {
        $request = Yii::app()->request;
        $notify  = Yii::app()->notify;
        $model   = new CustomerCampaignTag();

        $model->customer_id = (int)Yii::app()->customer->getId();

        if ($request->isPostRequest && ($attributes = (array)$request->getPost($model->modelName, array()))) {
            $model->attributes  = $attributes;
            $model->customer_id = Yii::app()->customer->getId();
            $model->content     = Yii::app()->ioFilter->purify(Yii::app()->params['POST'][$model->modelName]['content']);
            
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
                $this->redirect(array('campaign_tags/update', 'tag_uid' => $model->tag_uid));
            }
        }

        $this->setData(array(
            'pageMetaTitle'     => $this->data->pageMetaTitle . ' | '. Yii::t('campaigns', 'Create new tag'),
            'pageHeading'       => Yii::t('campaigns', 'Create new campaign tag'),
            'pageBreadcrumbs'   => array(
                Yii::t('campaigns', 'Campaigns') => $this->createUrl('campaigns/index'),
                Yii::t('campaigns', 'Tags') => $this->createUrl('campaign_tags/index'),
                Yii::t('app', 'Create new'),
            )
        ));

        $model->fieldDecorator->onHtmlOptionsSetup = array($this, '_addEditorOptions');

        $this->render('form', compact('model'));
    }

    /**
     * Update existing campaign tag
     */
    public function actionUpdate($tag_uid)
    {
        $model = CustomerCampaignTag::model()->findByAttributes(array(
            'tag_uid'     => $tag_uid,
            'customer_id' => (int)Yii::app()->customer->getId(),
        ));

        if (empty($model)) {
            throw new CHttpException(404, Yii::t('app', 'The requested page does not exist.'));
        }

        $request = Yii::app()->request;
        $notify  = Yii::app()->notify;

        if ($request->isPostRequest && ($attributes = (array)$request->getPost($model->modelName, array()))) {
            $model->attributes  = $attributes;
            $model->customer_id = Yii::app()->customer->getId();
            $model->content     = Yii::app()->ioFilter->purify(Yii::app()->params['POST'][$model->modelName]['content']);
            
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
        }

        $this->setData(array(
            'pageMetaTitle'     => $this->data->pageMetaTitle . ' | '. Yii::t('campaigns', 'Update tag'),
            'pageHeading'       => Yii::t('campaigns', 'Update campaign tag'),
            'pageBreadcrumbs'   => array(
                Yii::t('campaigns', 'Campaigns') => $this->createUrl('campaigns/index'),
                Yii::t('campaigns', 'Tags') => $this->createUrl('campaign_tags/index'),
                Yii::t('app', 'Update'),
            )
        ));

        $model->fieldDecorator->onHtmlOptionsSetup = array($this, '_addEditorOptions');

        $this->render('form', compact('model'));
    }

    /**
     * Delete existing campaign tag
     */
    public function actionDelete($tag_uid)
    {
        $model = CustomerCampaignTag::model()->findByAttributes(array(
            'tag_uid'     => $tag_uid,
            'customer_id' => (int)Yii::app()->customer->getId(),
        ));

        if (empty($model)) {
            throw new CHttpException(404, Yii::t('app', 'The requested page does not exist.'));
        }

        $model->delete();

        $request = Yii::app()->request;
        $notify  = Yii::app()->notify;

        $redirect = null;
        if (!$request->getQuery('ajax')) {
            $notify->addSuccess(Yii::t('app', 'The item has been successfully deleted!'));
            $redirect = $request->getPost('returnUrl', array('campaign_tags/index'));
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
     * Export
     */
    public function actionExport()
    {
        $notify = Yii::app()->notify;

        $models = CustomerCampaignTag::model()->findAllByAttributes(array(
            'customer_id' => (int)Yii::app()->customer->getId(),
        ));

        if (empty($models)) {
            $notify->addError(Yii::t('app', 'There is no item available for export!'));
            $this->redirect(array('index'));
        }

        if (!($fp = @fopen('php://output', 'w'))) {
            $notify->addError(Yii::t('app', 'Unable to access the output for writing the data!'));
            $this->redirect(array('index'));
        }
        
        /* Set the download headers */
        HeaderHelper::setDownloadHeaders('campaigns-custom-tags.csv');

        $attributes = AttributeHelper::removeSpecialAttributes($models[0]->getAttributes());
        $columns    = array_map(array($models[0], 'getAttributeLabel'), array_keys($attributes));
        @fputcsv($fp, $columns, ',', '"');

        foreach ($models as $model) {
            $attributes = AttributeHelper::removeSpecialAttributes($model->getAttributes());
            @fputcsv($fp, array_values($attributes), ',', '"');
        }

        @fclose($fp);
        Yii::app()->end();
    }
    
    /**
     * Callback method to setup the editor
     */
    public function _addEditorOptions(CEvent $event)
    {
        if (!in_array($event->params['attribute'], array('content'))) {
            return;
        }

        $options = array();
        if ($event->params['htmlOptions']->contains('wysiwyg_editor_options')) {
            $options = (array)$event->params['htmlOptions']->itemAt('wysiwyg_editor_options');
        }
        $options['id'] = CHtml::activeId($event->sender->owner, $event->params['attribute']);
        $event->params['htmlOptions']->add('wysiwyg_editor_options', $options);
    }
}
