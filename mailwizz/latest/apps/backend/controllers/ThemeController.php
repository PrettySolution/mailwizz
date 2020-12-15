<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * ThemeController
 *
 * Handles the actions for themes related tasks
 *
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.0
 */

class ThemeController extends Controller
{
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
     * List all available themes in selected application
     */
    public function actionIndex($app = 'backend')
    {
        $this->checkAppName($app);

        $model = new ThemeHandlerForm('upload');

        $this->setData(array(
            'pageMetaTitle'     => $this->data->pageMetaTitle . ' | '. Yii::t('themes', 'View themes'),
            'pageHeading'       => Yii::t('themes', 'View themes'),
            'pageBreadcrumbs'   => array(
                Yii::t('themes', 'Themes') => $this->createUrl('theme/index'),
                Yii::t('app', 'View all')
            )
        ));

        $apps = $this->getAllowedApps();
        $this->render('index', compact('model', 'apps', 'app'));
    }

    /**
     * Settings page for theme
     */
    public function actionSettings($app, $theme)
    {
        $this->checkAppName($app);

        if (!($themeInstance = Yii::app()->themeManager->getThemeInstance($theme, $app))) {
            throw new CHttpException(400, Yii::t('app', 'Invalid request. Please do not repeat this request again.'));
        }

        $this->setData(array(
            'pageMetaTitle'     => $this->data->pageMetaTitle . ' | '. Yii::t('themes', 'Theme settings'),
            'pageHeading'       => Yii::t('themes', 'Theme settings'),
            'pageBreadcrumbs'   => array(
                Yii::t('themes', 'Themes') => $this->createUrl('theme/index'),
                Yii::t('app', 'Settings')
            )
        ));

        $themeInstance->settingsPage();
    }

    /**
     * Upload a new themes
     */
    public function actionUpload($app)
    {
        $this->checkAppName($app);

        $request = Yii::app()->request;
        $notify  = Yii::app()->notify;
        $model   = new ThemeHandlerForm('upload');

        if ($request->isPostRequest && ($attributes = (array)$request->getPost($model->modelName, array()))) {
            $model->archive = CUploadedFile::getInstance($model, 'archive');
            if (!$model->upload($app)) {
                $notify->addError($model->shortErrors->getAllAsString());
            } else {
                $notify->addSuccess(Yii::t('themes', 'Your theme has been successfully uploaded!'));
            }
            $this->redirect(array('theme/index', 'app' => $app));
        }

        $notify->addError(Yii::t('themes', 'Please select a theme archive for upload!'));
        $this->redirect(array('theme/index'));
    }

    /**
     * Enable theme
     */
    public function actionEnable($app, $name)
    {
        $this->checkAppName($app);

        $notify  = Yii::app()->notify;
        $manager = Yii::app()->themeManager;

        if (!$manager->enableTheme($name, $app)) {
            $notify->clearAll()->addError($manager->getErrors());
        } else {
            $message = Yii::t('themes', 'The theme "{name}" has been successfully enabled!', array(
                '{name}' => CHtml::encode($manager->getThemeInstance($name, $app)->name),
            ));
            $notify->clearAll()->addSuccess($message);
        }

        $this->redirect(array('theme/index', 'app' => $app));
    }

    /**
     * Disable theme
     */
    public function actionDisable($app, $name)
    {
        $this->checkAppName($app);

        $notify  = Yii::app()->notify;
        $manager = Yii::app()->themeManager;

        if (!$manager->disableTheme($name, $app)) {
            $notify->clearAll()->addError($manager->getErrors());
        } else {
            $message = Yii::t('themes', 'The theme "{name}" has been successfully disabled!', array(
                '{name}' => CHtml::encode($manager->getThemeInstance($name, $app)->name),
            ));
            $notify->clearAll()->addSuccess($message);
        }

        $this->redirect(array('theme/index', 'app' => $app));
    }

    /**
     * Delete theme
     */
    public function actionDelete($app, $name)
    {
        $this->checkAppName($app);

        $notify  = Yii::app()->notify;
        $manager = Yii::app()->themeManager;
        $request = Yii::app()->request;

        if (!$manager->deleteTheme($name, $app)) {
            $notify->clearAll()->addError($manager->getErrors());
        } else {
            $message = Yii::t('themes', 'The theme "{name}" has been successfully deleted!', array(
                '{name}' => CHtml::encode($manager->getThemeInstance($name, $app)->name),
            ));
            $notify->clearAll()->addSuccess($message);
        }

        $redirect = null;
        if (!$request->isAjaxRequest) {
            $redirect = array('theme/index', 'app' => $app);
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

    public function getAllowedApps()
    {
        static $allowedApps;
        if ($allowedApps) {
            return $allowedApps;
        }

        $allowedApps = Yii::app()->apps->getWebApps();

        if (($index = array_search('api', $allowedApps)) !== false) {
            unset($allowedApps[$index]);
        }

        sort($allowedApps);

        return $allowedApps;
    }

    public function checkAppName($appName)
    {
        $allowedApps = $this->getAllowedApps();
        if (!in_array($appName, $allowedApps)) {
            throw new CHttpException(400, Yii::t('app', 'Invalid request. Please do not repeat this request again.'));
        }
    }


}
