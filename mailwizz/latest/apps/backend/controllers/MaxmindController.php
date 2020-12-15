<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * MaxmindController
 * 
 * Handles the actions for maxmind related tasks
 * 
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com> 
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.4.5
 */
 
class MaxmindController extends Controller
{
    /**
     * Maxmind DB info
     */
    public function actionIndex()
    {
        $model = new MaxmindDatabase();
        
        $this->setData(array(
            'pageMetaTitle'     => $this->data->pageMetaTitle . ' | ' . Yii::t('ip_location', 'MaxMind.com database'), 
            'pageHeading'       => Yii::t('ip_location', 'MaxMind.com database'),
            'pageBreadcrumbs'   => array(
                Yii::t('ip_location', 'MaxMind.com database'),
            ),
        ));
        
        MaxmindDatabase::addNotifyErrorIfMissingDbFile();
        
        $this->render('index', compact('model'));
    }

}