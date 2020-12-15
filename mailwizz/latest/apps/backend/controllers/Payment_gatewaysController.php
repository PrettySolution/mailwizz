<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * Payment_gatewaysController
 * 
 * Handles the actions for payment gateways related tasks
 * 
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com> 
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.3.4.4
 */
 
class Payment_gatewaysController extends Controller
{
    /**
     * Display available gateways
     */
    public function actionIndex()
    {
        $request = Yii::app()->request;
        $model = new PaymentGatewaysList();
        
        $this->setData(array(
            'pageMetaTitle'     => $this->data->pageMetaTitle . ' | ' . Yii::t('payment_gateways', 'Payment gateways'), 
            'pageHeading'       => Yii::t('payment_gateways', 'Payment gateways'),
            'pageBreadcrumbs'   => array(
                Yii::t('payment_gateways', 'Payment gateways'),
            ),
        ));
        
        $this->render('index', compact('model'));
    }

}