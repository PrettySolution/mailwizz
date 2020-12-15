<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * Controller file for service settings.
 * 
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com> 
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 */
 
class Ip_location_services_ext_maxmindController extends Controller
{
    /**
     * @var $extension - The extension instance
     */
    public $extension;
    
    // init the controller
    public function init()
    {
        parent::init();
        Yii::import('ext-ip-location-maxmind.backend.models.*');
    }
    
    // move the view path
    public function getViewPath()
    {
        return Yii::getPathOfAlias('ext-ip-location-maxmind.backend.views');
    }
    
    /**
     * Default action.
     */
    public function actionIndex()
    {
        $request = Yii::app()->request;
        $notify  = Yii::app()->notify;
        
        $model = new IpLocationMaxmindExtModel();
        $model->populate($this->extension);

        if ($request->isPostRequest && ($attributes = (array)$request->getPost($model->modelName, array()))) {
            $model->attributes = $attributes;
            if ($model->validate()) {
                $notify->addSuccess(Yii::t('app', 'Your form has been successfully saved!'));
                $model->save($this->extension);
            } else {
                $notify->addError(Yii::t('app', 'Your form has a few errors, please fix them and try again!'));
            }
        }
        
        // 1.4.5
        MaxmindDatabase::addNotifyErrorIfMissingDbFile();
        
        $this->setData(array(
            'pageMetaTitle'     => $this->data->pageMetaTitle . ' | '. $this->extension->t('Ip location service from MaxMind.com'),
            'pageHeading'       => $this->extension->t('Ip location service from MaxMind.com'),
            'pageBreadcrumbs'   => array(
                Yii::t('ip_location', 'Ip location services') => $this->createUrl('ip_location_services/index'),
                $this->extension->t('Ip location service from MaxMind.com'),
            )
        ));

        $this->render('settings', compact('model'));
    }
}