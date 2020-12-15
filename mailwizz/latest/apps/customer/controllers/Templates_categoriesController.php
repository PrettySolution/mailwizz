<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * Templates_categoriesController
 *
 * Handles the actions for template categories related tasks
 *
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.3.9.5
 */

class Templates_categoriesController extends Controller
{

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
     * List all available categories
     */
    public function actionIndex()
    {
        $request = Yii::app()->request;
        $category = new CustomerEmailTemplateCategory('search');
        $category->unsetAttributes();

        // for filters.
        $category->attributes  = (array)$request->getQuery($category->modelName, array());
        $category->customer_id = (int)Yii::app()->customer->getId();
        
        $this->setData(array(
            'pageMetaTitle'     => $this->data->pageMetaTitle . ' | '. Yii::t('email_templates', 'View categories'),
            'pageHeading'       => Yii::t('email_templates', 'View categories'),
            'pageBreadcrumbs'   => array(
                Yii::t('email_templates', 'Email templates') => $this->createUrl('templates/index'),
                Yii::t('email_templates', 'Categories') => $this->createUrl('templates_categories/index'),
                Yii::t('app', 'View all')
            )
        ));

        $this->render('list', compact('category'));
    }

    /**
     * Create a new category
     */
    public function actionCreate()
    {
        $request  = Yii::app()->request;
        $notify   = Yii::app()->notify;
        $category = new CustomerEmailTemplateCategory();
        $category->customer_id = (int)Yii::app()->customer->getId();
        
        if ($request->isPostRequest && ($attributes = (array)$request->getPost($category->modelName, array()))) {
            $category->attributes  = $attributes;
            $category->customer_id = (int)Yii::app()->customer->getId();
            
            if (!$category->save()) {
                $notify->addError(Yii::t('app', 'Your form has a few errors, please fix them and try again!'));
            } else {
                $notify->addSuccess(Yii::t('app', 'Your form has been successfully saved!'));
            }

            Yii::app()->hooks->doAction('controller_action_save_data', $collection = new CAttributeCollection(array(
                'controller'=> $this,
                'success'   => $notify->hasSuccess,
                'category'   => $category,
            )));

            if ($collection->success) {
                $this->redirect(array('templates_categories/index'));
            }
        }

        $this->setData(array(
            'pageMetaTitle'     => $this->data->pageMetaTitle . ' | '. Yii::t('email_templates', 'Create new category'),
            'pageHeading'       => Yii::t('email_templates', 'Create new category'),
            'pageBreadcrumbs'   => array(
                Yii::t('email_templates', 'Email templates') => $this->createUrl('templates/index'),
                Yii::t('email_templates', 'Categories') => $this->createUrl('templates_categories/index'),
                Yii::t('app', 'Create new'),
            )
        ));

        $this->render('form', compact('category'));
    }

    /**
     * Update existing category
     */
    public function actionUpdate($id)
    {
        $category = CustomerEmailTemplateCategory::model()->findByAttributes(array(
            'category_id' => $id,
            'customer_id' => (int)Yii::app()->customer->getId(),
        ));

        if (empty($category)) {
            throw new CHttpException(404, Yii::t('app', 'The requested page does not exist.'));
        }

        $request = Yii::app()->request;
        $notify  = Yii::app()->notify;

        if ($request->isPostRequest && ($attributes = (array)$request->getPost($category->modelName, array()))) {
            $category->attributes  = $attributes;
            $category->customer_id = (int)Yii::app()->customer->getId();
            
            if (!$category->save()) {
                $notify->addError(Yii::t('app', 'Your form has a few errors, please fix them and try again!'));
            } else {
                $notify->addSuccess(Yii::t('app', 'Your form has been successfully saved!'));
            }

            Yii::app()->hooks->doAction('controller_action_save_data', $collection = new CAttributeCollection(array(
                'controller' => $this,
                'success'    => $notify->hasSuccess,
                'category'   => $category,
            )));

            if ($collection->success) {
                $this->redirect(array('templates_categories/index'));
            }
        }

        $this->setData(array(
            'pageMetaTitle'     => $this->data->pageMetaTitle . ' | '. Yii::t('email_templates', 'Update category'),
            'pageHeading'       => Yii::t('email_templates', 'Update category'),
            'pageBreadcrumbs'   => array(
                Yii::t('email_templates', 'Email templates') => $this->createUrl('templates/index'),
                Yii::t('email_templates', 'Categories') => $this->createUrl('templates_categories/index'),
                Yii::t('app', 'Update'),
            )
        ));

        $this->render('form', compact('category'));
    }

    /**
     * Delete an existing category
     */
    public function actionDelete($id)
    {
        $category = CustomerEmailTemplateCategory::model()->findByAttributes(array(
            'category_id' => $id,
            'customer_id' => (int)Yii::app()->customer->getId(),
        ));

        if (empty($category)) {
            throw new CHttpException(404, Yii::t('app', 'The requested page does not exist.'));
        }

        $category->delete();

        $request = Yii::app()->request;
        $notify  = Yii::app()->notify;

        $redirect = null;
        if (!$request->getQuery('ajax')) {
            $notify->addSuccess(Yii::t('app', 'The item has been successfully deleted!'));
            $redirect = $request->getPost('returnUrl', array('templates_categories/index'));
        }

        // since 1.3.5.9
        Yii::app()->hooks->doAction('controller_action_delete_data', $collection = new CAttributeCollection(array(
            'controller' => $this,
            'model'      => $category,
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

        $models = CustomerEmailTemplateCategory::model()->findAllByAttributes(array(
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
        HeaderHelper::setDownloadHeaders('email-templates-categories.csv');

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
