<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * User_groupsController
 *
 * Handles the actions for user groups related tasks
 *
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.0
 */

class User_groupsController extends Controller
{
    public function init()
    {
        $this->getData('pageScripts')->add(array('src' => AssetsUrl::js('user-groups.js')));
        parent::init();
    }

    /**
     * Define the filters for various controller actions
     * Merge the filters with the ones from parent implementation
     */
    public function filters()
    {
        $filters = array(
            'postOnly + delete', // we only allow deletion via POST request
        );

        return CMap::mergeArray($filters, parent::filters());
    }

    /**
     * List all available groups
     */
    public function actionIndex()
    {
        $request = Yii::app()->request;
        $group = new UserGroup('search');
        $group->unsetAttributes();

        // for filters.
        $group->attributes = (array)$request->getQuery($group->modelName, array());

        $this->setData(array(
            'pageMetaTitle'     => $this->data->pageMetaTitle . ' | '. Yii::t('user_groups', 'View user groups'),
            'pageHeading'       => Yii::t('user_groups', 'View user groups'),
            'pageBreadcrumbs'   => array(
                Yii::t('users', 'Users') => $this->createUrl('users/index'),
                Yii::t('user_groups', 'User groups') => $this->createUrl('user_groups/index'),
                Yii::t('app', 'View all')
            )
        ));

        $this->render('list', compact('group'));
    }

    /**
     * Create a new group
     */
    public function actionCreate()
    {
        $request = Yii::app()->request;
        $notify  = Yii::app()->notify;
        $group   = new UserGroup('search');

        $routesAccess = $group->getAllRoutesAccess();

        if ($request->isPostRequest && ($attributes = (array)$request->getPost($group->modelName, array()))) {
            $group->attributes = $attributes;
            if (!$group->save()) {
                $notify->addError(Yii::t('app', 'Your form has a few errors, please fix them and try again!'));
            } else {
                if ($routes = (array)$request->getPost('UserGroupRouteAccess', array())) {
                    foreach ($routesAccess as $index => $data) {
                        foreach ($data['routes'] as $route) {
                            $route->group_id = $group->group_id;
                            $route->access   = UserGroupRouteAccess::ALLOW;
                            if (isset($routes[$index]['routes'][$route->route])) {
                                $route->access = $routes[$index]['routes'][$route->route];
                            }
                            $route->save();
                        }
                    }
                }
                $notify->addSuccess(Yii::t('app', 'Your form has been successfully saved!'));
            }

            Yii::app()->hooks->doAction('controller_action_save_data', $collection = new CAttributeCollection(array(
                'controller' => $this,
                'success'    => $notify->hasSuccess,
                'group'      => $group,
            )));

            if ($request->isAjaxRequest) {
                Yii::app()->end();
            }

            if ($collection->success) {
                $this->redirect(array('user_groups/index'));
            }
        }

        $this->setData(array(
            'pageMetaTitle'     => $this->data->pageMetaTitle . ' | '. Yii::t('user_groups', 'Create new user group'),
            'pageHeading'       => Yii::t('user_groups', 'Create new user group'),
            'pageBreadcrumbs'   => array(
                Yii::t('users', 'Users') => $this->createUrl('users/index'),
                Yii::t('user_groups', 'User groups') => $this->createUrl('user_groups/index'),
                Yii::t('app', 'Create new'),
            )
        ));

        $this->render('form', compact('group', 'routesAccess'));
    }

    /**
     * Update existing group
     */
    public function actionUpdate($id)
    {
        $group = UserGroup::model()->findByPk((int)$id);

        if (empty($group)) {
            throw new CHttpException(404, Yii::t('app', 'The requested page does not exist.'));
        }

        $request = Yii::app()->request;
        $notify  = Yii::app()->notify;

        $routesAccess = $group->getAllRoutesAccess();

        if ($request->isPostRequest && ($attributes = (array)$request->getPost($group->modelName, array()))) {
            $group->attributes = $attributes;
            if (!$group->save()) {
                $notify->addError(Yii::t('app', 'Your form has a few errors, please fix them and try again!'));
            } else {
                if ($routes = (array)$request->getPost('UserGroupRouteAccess', array())) {
                    foreach ($routesAccess as $index => $data) {
                        foreach ($data['routes'] as $route) {
                            $route->group_id = $group->group_id;
                            $route->access   = UserGroupRouteAccess::ALLOW;
                            if (isset($routes[$index]['routes'][$route->route])) {
                                $route->access = $routes[$index]['routes'][$route->route];
                            }
                            $route->save();
                        }
                    }
                }
                $notify->addSuccess(Yii::t('app', 'Your form has been successfully saved!'));
            }

            Yii::app()->hooks->doAction('controller_action_save_data', $collection = new CAttributeCollection(array(
                'controller' => $this,
                'success'    => $notify->hasSuccess,
                'group'      => $group,
            )));

            if ($request->isAjaxRequest) {
                Yii::app()->end();
            }

            if ($collection->success) {
                $this->redirect(array('user_groups/index'));
            }
        }

        $this->setData(array(
            'pageMetaTitle'     => $this->data->pageMetaTitle . ' | '. Yii::t('users', 'Update user group'),
            'pageHeading'       => Yii::t('users', 'Update user group'),
            'pageBreadcrumbs'   => array(
                Yii::t('users', 'Users') => $this->createUrl('users/index'),
                Yii::t('user_groups', 'User groups') => $this->createUrl('user_groups/index'),
                Yii::t('app', 'Update'),
            )
        ));

        $this->render('form', compact('group', 'routesAccess'));
    }

    /**
     * Delete existing group
     */
    public function actionDelete($id)
    {
        $group = UserGroup::model()->findByPk((int)$id);

        if (empty($group)) {
            throw new CHttpException(404, Yii::t('app', 'The requested page does not exist.'));
        }

        $group->delete();

        $request = Yii::app()->request;
        $notify  = Yii::app()->notify;

        $redirect = null;
        if (!$request->getQuery('ajax')) {
            $notify->addSuccess(Yii::t('app', 'The item has been successfully deleted!'));
            $redirect = $request->getPost('returnUrl', array('user_groups/index'));
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

}
