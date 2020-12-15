<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * Ext_ckeditorController
 *
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 */

class Ext_ckeditorController extends Controller
{
    // init the controller
    public function init()
    {
        parent::init();
        Yii::import('ext-ckeditor.models.*');
    }

    // move the view path
    public function getViewPath()
    {
        return Yii::getPathOfAlias('ext-ckeditor.views');
    }

    /**
     * Default action for settings, only admin users can access it.
     */
     public function actionIndex()
     {
        if (!Yii::app()->user->getId()) {
            throw new CHttpException(403, Yii::t('app', 'Invalid request. Please do not repeat this request again.'));
        }

        $extension  = Yii::app()->extensionsManager->getExtensionInstance('ckeditor');
        $request    = Yii::app()->request;
        $notify     = Yii::app()->notify;

        $model = new CkeditorExtModel();
        $model->populate($extension);

        if ($request->isPostRequest && ($attributes = (array)$request->getPost($model->modelName, array()))) {
            $model->attributes = $attributes;
            if ($model->validate()) {
                $notify->addSuccess(Yii::t('app', 'Your form has been successfully saved!'));
                $model->save($extension);
            } else {
                $notify->addError(Yii::t('app', 'Your form has a few errors, please fix them and try again!'));
            }
        }

        $this->setData(array(
            'pageMetaTitle'     => $this->data->pageMetaTitle . ' | '. Yii::t('ext_ckeditor', 'CKeditor options'),
            'pageHeading'       => Yii::t('ext_ckeditor', 'CKeditor options'),
            'pageBreadcrumbs'   => array(
                Yii::t('extensions', 'Extensions') => $this->createUrl('extensions/index'),
                Yii::t('ext_ckeditor', 'CKeditor options'),
            )
        ));

        $this->render('settings', compact('model'));
     }

    /**
     * Render the file manager
     * Customers and admin users are allowed to access it.
     */
    public function actionFilemanager()
    {
        $extension  = Yii::app()->extensionsManager->getExtensionInstance('ckeditor');
        $request    = Yii::app()->request;
        $canAccess  = false;

        if ($extension->isAppName('backend') && $extension->getOption('enable_filemanager_user') && Yii::app()->user->getId() > 0) {
            $canAccess = true;
        } elseif ($extension->isAppName('customer') && $extension->getOption('enable_filemanager_customer') && Yii::app()->customer->getId() > 0) {
            $canAccess = true;
        }

        if (!$canAccess) {
            throw new CHttpException(403, Yii::t('app', 'Invalid request. Please do not repeat this request again.'));
        }

        $assetsUrl      = $extension->getAssetsUrl();
        $language       = $this->getElFinderLanguage();
        $connectorUrl   = Yii::app()->createUrl('ext_ckeditor/filemanager_connector');
        $themeInfo      = $extension->getFilemanagerTheme($extension->getOption('filemanager_theme'));
        $theme          = !empty($themeInfo['url']) ? $themeInfo['url'] : null;

        $this->setData(array(
            'pageMetaTitle' => $this->data->pageMetaTitle . ' | '. Yii::t('ext_ckeditor', 'Filemanager'),
        ));

        $this->renderPartial('elfinder', compact('assetsUrl', 'language', 'connectorUrl', 'theme'));
    }

    /**
     * Connector action.
     * Customers and admin users are allowed to access it.
     */
    public function actionFilemanager_connector()
    {
        $extension      = Yii::app()->extensionsManager->getExtensionInstance('ckeditor');
        $request        = Yii::app()->request;
        $elfinderOpts   = array();
        $canAccess      = false;

        $filesPath   = $filesUrl = null;
        $uploadAllow = array('image');
        $uploadDeny  = array('all');
        $disabled    = array('archive', 'extract', 'mkfile', 'rename', 'paste', 'put', 'netmount', 'callback', 'chmod', 'download');

        if ($extension->isAppName('backend') && $extension->getOption('enable_filemanager_user') && Yii::app()->user->getId() > 0) {
            // this is a user requesting files.
            $canAccess  = true;
            $filesPath  = Yii::getPathOfAlias('root.frontend.assets.files');
            $filesUrl   = Yii::app()->apps->getAppUrl('frontend', 'frontend/assets/files', true, true);

        } elseif ($extension->isAppName('customer') && $extension->getOption('enable_filemanager_customer') && Yii::app()->customer->getId() > 0) {
            // this is a customer requesting files.
            $customerFolderName = Yii::app()->customer->getModel()->customer_uid;

            $canAccess  = true;
            $filesPath  = Yii::getPathOfAlias('root.frontend.assets.files');
            $filesUrl   = Yii::app()->apps->getAppUrl('frontend', 'frontend/assets/files/customer/' . $customerFolderName, true, true);

            $filesPath .= '/customer';
            if (!file_exists($filesPath) || !is_dir($filesPath)) {
                @mkdir($filesPath, 0777, true);
            }
            $filesPath .= '/' . $customerFolderName;
            if (!file_exists($filesPath) || !is_dir($filesPath)) {
                @mkdir($filesPath, 0777, true);
            }
        }

        // no user or customer? deny access!
        if (!$canAccess) {
            throw new CHttpException(403, Yii::t('app', 'Invalid request. Please do not repeat this request again.'));
        }

        $path = Yii::getPathOfAlias($extension->getPathAlias());
        
        $fileNameNoChars = array('\\','/',':','*','?','"','<','>','|', ' ');
        
        require_once $path . '/vendors/elfinder/elFinderConnector.class.php';
        require_once $path . '/vendors/elfinder/elFinder.class.php';
        require_once $path . '/vendors/elfinder/elFinderVolumeDriver.class.php';
        require_once $path . '/vendors/elfinder/elFinderVolumeLocalFileSystem.class.php';
        require_once $path . '/vendors/elfinder/elFinderPlugin.php';
        
        $elfinderOpts = array(
        	'debug' => false,
            'bind'  => array(
                'mkdir.pre mkfile.pre rename.pre' => array(
                    'Plugin.Sanitizer.cmdPreprocess'
                ),
                'upload.presave' => array(
                    'Plugin.Sanitizer.onUpLoadPreSave'
                )
            ),
            'plugin' => array(
                'Sanitizer' => array(
                    'enable' => true,
                    'targets'  => $fileNameNoChars,
                    'replace'  => '-'
                )
            ),
        	'roots' => array(
        		array(
        			'driver'            => 'LocalFileSystem',
        			'path'              => $filesPath . '/',
        			'URL'               => $filesUrl . '/',
                    'alias'             => Yii::t('app', 'Home'),
                    'uploadAllow'       => $uploadAllow,
                    'uploadDeny'        => $uploadDeny,
			        'uploadOverwrite'   => false, // 1.6.6
                    'disabled'          => $disabled,

                    'dateFormat'    => Yii::app()->locale->dateFormat,
                    'timeFormat'    => Yii::app()->locale->timeFormat,
                    'attributes'    => array(
                        // hide .tmb and .quarantine folders
                        array(
                            'pattern'   => '/.(tmb|quarantine)/i',
                            'read'      => false,
                            'write'     => false,
                            'hidden'    => true,
                            'locked'    => false
                        ),
                    ),

                    'plugin' => array(
                        'Sanitizer' => array(
                            'enable'   => true,
                            'targets'  => $fileNameNoChars,
                            'replace'  => '-'
                        )
                    )
        		)
        	)
        );

        // since 1.3.5.9
        $elfinderOpts = (array)Yii::app()->hooks->applyFilters('ext_ckeditor_el_finder_options', $elfinderOpts);

        // run elFinder
        $connector = new elFinderConnector(new elFinder($elfinderOpts));
        $connector->run();
    }

    protected function getElFinderLanguage()
    {
        $extension      = Yii::app()->extensionsManager->getExtensionInstance('ckeditor');
        $language       = Yii::app()->language;
        $languageFile   = null;
        $assetsPath     = Yii::getPathOfAlias($extension->getPathAlias()) . '/assets';

        if (strpos($language, '_') !== false) {
            $languageParts = explode('_', $language);
            $languageParts[1] = strtoupper($languageParts[1]);
            $language = implode('_', $languageParts);
        }

        if (is_file($assetsPath . '/elfinder/js/i18n/elfinder.'.$language.'.js')) {
            return $language;
        }

        if (strpos($language, '_') !== false) {
            $languageParts = explode('_', $language);
            $language = $languageParts[0];
            if (is_file($assetsPath . '/elfinder/js/i18n/elfinder.'.$language.'.js')) {
                return $language;
            }
        }

        return null;
    }
}
