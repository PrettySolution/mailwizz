<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * Campaign_groupsController
 *
 * Handles the actions for campaign groups related tasks
 *
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.3.4.3
 */

class Campaign_groupsController extends Controller
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
     * List available campaign groups
     */
    public function actionIndex()
    {
        $request = Yii::app()->request;
        $group   = new CampaignGroup('search');

        $group->unsetAttributes();
        $group->attributes  = (array)$request->getQuery($group->modelName, array());
        $group->customer_id = (int)Yii::app()->customer->getId();

        $this->setData(array(
            'pageMetaTitle'     => $this->data->pageMetaTitle . ' | '. Yii::t('campaigns', 'View groups'),
            'pageHeading'       => Yii::t('campaigns', 'View groups'),
            'pageBreadcrumbs'   => array(
                Yii::t('campaigns', 'Campaigns') => $this->createUrl('campaigns/index'),
                Yii::t('campaigns', 'Groups') => $this->createUrl('campaign_groups/index'),
                Yii::t('app', 'View all')
            )
        ));

        $this->render('list', compact('group'));
    }

    /**
     * Create a new campaign group
     */
    public function actionCreate()
    {
        $request = Yii::app()->request;
        $notify  = Yii::app()->notify;
        $group   = new CampaignGroup();

        $group->customer_id = (int)Yii::app()->customer->getId();

        if ($request->isPostRequest && ($attributes = (array)$request->getPost($group->modelName, array()))) {
            $group->attributes  = $attributes;
            $group->customer_id = Yii::app()->customer->getId();
            if (!$group->save()) {
                $notify->addError(Yii::t('app', 'Your form has a few errors, please fix them and try again!'));
            } else {
                $notify->addSuccess(Yii::t('app', 'Your form has been successfully saved!'));
            }

            Yii::app()->hooks->doAction('controller_action_save_data', $collection = new CAttributeCollection(array(
                'controller'=> $this,
                'success'   => $notify->hasSuccess,
                'group'     => $group,
            )));

            if ($collection->success) {
                $this->redirect(array('campaign_groups/update', 'group_uid' => $group->group_uid));
            }
        }

        $this->setData(array(
            'pageMetaTitle'     => $this->data->pageMetaTitle . ' | '. Yii::t('campaigns', 'Create new group'),
            'pageHeading'       => Yii::t('campaigns', 'Create new campaign group'),
            'pageBreadcrumbs'   => array(
                Yii::t('campaigns', 'Campaigns') => $this->createUrl('campaigns/index'),
                Yii::t('campaigns', 'Groups') => $this->createUrl('campaign_groups/index'),
                Yii::t('app', 'Create new'),
            )
        ));

        $this->render('form', compact('group'));
    }

    /**
     * Update existing campaign group
     */
    public function actionUpdate($group_uid)
    {
        $group = CampaignGroup::model()->findByAttributes(array(
            'group_uid'    => $group_uid,
            'customer_id'  => (int)Yii::app()->customer->getId(),
        ));

        if (empty($group)) {
            throw new CHttpException(404, Yii::t('app', 'The requested page does not exist.'));
        }

        $request = Yii::app()->request;
        $notify  = Yii::app()->notify;

        if ($request->isPostRequest && ($attributes = (array)$request->getPost($group->modelName, array()))) {
            $group->attributes = $attributes;
            $group->customer_id= Yii::app()->customer->getId();
            if (!$group->save()) {
                $notify->addError(Yii::t('app', 'Your form has a few errors, please fix them and try again!'));
            } else {
                $notify->addSuccess(Yii::t('app', 'Your form has been successfully saved!'));
            }

            Yii::app()->hooks->doAction('controller_action_save_data', $collection = new CAttributeCollection(array(
                'controller'=> $this,
                'success'   => $notify->hasSuccess,
                'group'     => $group,
            )));
        }

        $this->setData(array(
            'pageMetaTitle'     => $this->data->pageMetaTitle . ' | '. Yii::t('campaigns', 'Update group'),
            'pageHeading'       => Yii::t('campaigns', 'Update campaign group'),
            'pageBreadcrumbs'   => array(
                Yii::t('campaigns', 'Campaigns') => $this->createUrl('campaigns/index'),
                Yii::t('campaigns', 'Groups') => $this->createUrl('campaign_groups/index'),
                Yii::t('app', 'Update'),
            )
        ));

        $this->render('form', compact('group'));
    }

    /**
     * Delete existing campaign group
     */
    public function actionDelete($group_uid)
    {
        $group = CampaignGroup::model()->findByAttributes(array(
            'group_uid'   => $group_uid,
            'customer_id' => (int)Yii::app()->customer->getId(),
        ));

        if (empty($group)) {
            throw new CHttpException(404, Yii::t('app', 'The requested page does not exist.'));
        }

        $group->delete();

        $request = Yii::app()->request;
        $notify  = Yii::app()->notify;

        $redirect = null;
        if (!$request->getQuery('ajax')) {
            $notify->addSuccess(Yii::t('app', 'The item has been successfully deleted!'));
            $redirect = $request->getPost('returnUrl', array('campaign_groups/index'));
        }

        // since 1.3.5.9
        Yii::app()->hooks->doAction('controller_action_delete_data', $collection = new CAttributeCollection(array(
            'controller' => $this,
            'model'      => $group,
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

        $models = CampaignGroup::model()->findAllByAttributes(array(
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
        HeaderHelper::setDownloadHeaders('campaign-groups.csv');

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
}
