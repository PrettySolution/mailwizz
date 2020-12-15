<?php defined('MW_PATH') || exit('No direct script access allowed');

/** 
 * Controller file for recaptcha settings.
 * 
 * @package MailWizz EMA
 * @subpackage Recaptcha
 * @author Serban George Cristian <cristian.serban@mailwizz.com> 
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 */
 
class Ext_recaptcha_settingsController extends Controller
{
    // the extension instance
    public $extension;
    
    // move the view path
    public function getViewPath()
    {
        return Yii::getPathOfAlias('ext-recaptcha.backend.views.settings');
    }
    
    /**
     * Common settings for Amazon S3
     */
    public function actionIndex()
    {
        $request = Yii::app()->request;
        $notify  = Yii::app()->notify;

        $model = new RecaptchaExtCommon();
        $model->populate();
        
        if ($request->isPostRequest) {
            $model->attributes = (array)$request->getPost($model->modelName, array());
            if ($model->validate()) {
                $notify->addSuccess(Yii::t('app', 'Your form has been successfully saved!'));
                $model->save();
            } else {
                $notify->addError(Yii::t('app', 'Your form has a few errors, please fix them and try again!'));
            }
        }
        
        $this->setData(array(
            'pageMetaTitle'    => $this->data->pageMetaTitle . ' | '. Yii::t('ext_recaptcha', 'Recaptcha'),
            'pageHeading'      => Yii::t('ext_recaptcha', 'Recaptcha'),
            'pageBreadcrumbs'  => array(
                Yii::t('app', 'Extensions') => $this->createUrl('extensions/index'),
                Yii::t('ext_recaptcha', 'Recaptcha') => $this->createUrl('ext_recaptcha_settings/index'),
            )
        ));

        $this->render('index', compact('model'));
    }
}