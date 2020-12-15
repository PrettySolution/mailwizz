<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * Company_typesController
 *
 * Handles the actions for company types related tasks
 *
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.3.4.7
 */

class Company_typesController extends Controller
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
     * List all the available company types
     */
    public function actionIndex()
    {
        $request = Yii::app()->request;
        $type    = new CompanyType('search');
        $type->unsetAttributes();

        // for filters.
        $type->attributes = (array)$request->getQuery($type->modelName, array());

        $this->setData(array(
            'pageMetaTitle'     => $this->data->pageMetaTitle . ' | '. Yii::t('company_types', 'Company types'),
            'pageHeading'       => Yii::t('company_types', 'Company types'),
            'pageBreadcrumbs'   => array(
                Yii::t('company_types', 'Company types')    => $this->createUrl('company_types/index'),
                Yii::t('app', 'View all'),
            )
        ));

        $this->render('list', compact('type'));
    }

    /**
     * Create a new company type
     */
    public function actionCreate()
    {
        $request = Yii::app()->request;
        $notify  = Yii::app()->notify;
        $type    = new CompanyType();

        if ($request->isPostRequest && ($attributes = (array)$request->getPost($type->modelName, array()))) {
            $type->attributes = $attributes;
            if (!$type->save()) {
                $notify->addError(Yii::t('app', 'Your form has a few errors, please fix them and try again!'));
            } else {
                $notify->addSuccess(Yii::t('app', 'Your form has been successfully saved!'));
            }

            Yii::app()->hooks->doAction('controller_action_save_data', $collection = new CAttributeCollection(array(
                'controller'=> $this,
                'success'   => $notify->hasSuccess,
                'type'      => $type,
            )));

            if ($collection->success) {
                $this->redirect(array('company_types/index'));
            }
        }

        $this->setData(array(
            'pageMetaTitle'     => $this->data->pageMetaTitle . ' | '. Yii::t('company_types', 'Create new company type'),
            'pageHeading'       => Yii::t('company_types', 'Create new company type'),
            'pageBreadcrumbs'   => array(
                Yii::t('company_types', 'Company types') => $this->createUrl('company_types/index'),
                Yii::t('app', 'Create new'),
            )
        ));

        $this->render('form', compact('type'));
    }

    /**
     * Update existing company type
     */
    public function actionUpdate($id)
    {
        $type = CompanyType::model()->findByPk((int)$id);

        if (empty($type)) {
            throw new CHttpException(404, Yii::t('app', 'The requested page does not exist.'));
        }

        $request = Yii::app()->request;
        $notify  = Yii::app()->notify;

        if ($request->isPostRequest && ($attributes = (array)$request->getPost($type->modelName, array()))) {
            $type->attributes = $attributes;
            if (!$type->save()) {
                $notify->addError(Yii::t('app', 'Your form has a few errors, please fix them and try again!'));
            } else {
                $notify->addSuccess(Yii::t('app', 'Your form has been successfully saved!'));
            }

            Yii::app()->hooks->doAction('controller_action_save_data', $collection = new CAttributeCollection(array(
                'controller'=> $this,
                'success'   => $notify->hasSuccess,
                'type'      => $type,
            )));

            if ($collection->success) {
                $this->redirect(array('company_types/index'));
            }
        }

        $this->setData(array(
            'pageMetaTitle'     => $this->data->pageMetaTitle . ' | '. Yii::t('company_types', 'Update company type'),
            'pageHeading'       => Yii::t('company_types', 'Update company type'),
            'pageBreadcrumbs'   => array(
                Yii::t('company_types', 'Company types') => $this->createUrl('company_types/index'),
                Yii::t('app', 'Update'),
            )
        ));

        $this->render('form', compact('type'));
    }

    /**
     * Delete exiting company type
     */
    public function actionDelete($id)
    {
        $type = CompanyType::model()->findByPk((int)$id);

        if (empty($type)) {
            throw new CHttpException(404, Yii::t('app', 'The requested page does not exist.'));
        }

        $type->delete();

        $request = Yii::app()->request;
        $notify  = Yii::app()->notify;

        $redirect = null;
        if (!$request->getQuery('ajax')) {
            $notify->addSuccess(Yii::t('app', 'The item has been successfully deleted!'));
            $redirect = $request->getPost('returnUrl', array('company_types/index'));
        }

        // since 1.3.5.9
        Yii::app()->hooks->doAction('controller_action_delete_data', $collection = new CAttributeCollection(array(
            'controller' => $this,
            'model'      => $type,
            'redirect'   => $redirect,
        )));

        if ($collection->redirect) {
            $this->redirect($collection->redirect);
        }
    }
}
