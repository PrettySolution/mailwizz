<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * Ext_translateController
 * 
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com> 
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 */
 
class Ext_translateController extends Controller
{
    // init the controller
    public function init()
    {
        parent::init();
        Yii::import('ext-translate.backend.models.*');
    }
    
    // move the view path
    public function getViewPath()
    {
        return Yii::getPathOfAlias('ext-translate.backend.views.translate');
    }
    
    /**
     * Default action.
     */
    public function actionIndex()
    {
        $extensionInstance = Yii::app()->extensionsManager->getExtensionInstance('translate');
        
        $request = Yii::app()->request;
        $notify  = Yii::app()->notify;
        
        $model = new TranslateExtModel();
        $model->populate($extensionInstance);

        if ($request->isPostRequest && ($attributes = (array)$request->getPost($model->modelName, array()))) {
            $model->attributes = $attributes;
            if ($model->validate()) {
                $notify->addSuccess(Yii::t('app', 'Your form has been successfully saved!'));
                $model->save($extensionInstance);
            } else {
                $notify->addError(Yii::t('app', 'Your form has a few errors, please fix them and try again!'));
            }
        }
        
        $this->setData(array(
            'pageMetaTitle'     => $this->data->pageMetaTitle . ' | '. Yii::t('ext_translate', 'Translate extension'),
            'pageHeading'       => Yii::t('ext_translate', 'Translate extension'),
            'pageBreadcrumbs'   => array(
                Yii::t('extensions', 'Extensions') => $this->createUrl('extensions/index'),
                Yii::t('ext_translate', 'Translate extension'),
            )
        ));
        
        $messagesDir = null;
        if (Yii::app()->hasComponent('messages') && (Yii::app()->getComponent('messages') instanceof CPhpMessageSource)) {
            $messagesDir = Yii::app()->messages->basePath;
        }
        
        if (!empty($messagesDir) && (!file_exists($messagesDir) || !is_dir($messagesDir) || !is_writable($messagesDir))) {
            $notify->addWarning(Yii::t('ext_translate', 'The directory {dirName} must exist and be writable by the web server in order to write the translation files.', array(
                '{dirName}' => '<span class="badge">'.$messagesDir.'</span>',
            )));
        }

        $this->render('index', compact('messagesDir', 'extensionInstance', 'model'));
    }
}