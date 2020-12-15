<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * SiteController
 * 
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com> 
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.0
 */
 
class SiteController extends Controller
{
    /**
     * The landing page
     */
    public function actionIndex()
    {
        if (Yii::app()->options->get('system.common.frontend_homepage', 'yes') != 'yes') {
            $this->redirect(Yii::app()->apps->getAppUrl('customer'));
        }
        
        $this->setData(array(
            'pageMetaTitle' => $this->data->pageMetaTitle . ' | '. Yii::t('app', 'Welcome'),
        ));
        
        $view = 'index';
        if ($this->getViewFile($view . '-custom') !== false) {
            $view .= '-custom';
        }
        
        $this->render($view, array(
            'siteName' => Yii::app()->options->get('system.common.site_name', ''),
        ));
    }

    /**
     * @throws CHttpException
     */
    public function actionOffline()
    {
        if (Yii::app()->options->get('system.common.site_status') !== 'offline') {
            $this->redirect(array('site/index'));
        }
        
        throw new CHttpException(503, Yii::app()->options->get('system.common.site_offline_message'));
    }

    /**
     * Error handler
     */
    public function actionError()
    {
        if ($error = Yii::app()->errorHandler->error) {
            if (Yii::app()->request->isAjaxRequest) {
                echo CHtml::encode($error['message']);
            } else {
                $this->setData(array(
                    'pageMetaTitle'         => Yii::t('app', 'Error {code}!', array('{code}' => (int)$error['code'])), 
                    'pageMetaDescription'   => CHtml::encode($error['message']),
                ));
                $this->render('error', $error) ;
            }    
        }
    }

}