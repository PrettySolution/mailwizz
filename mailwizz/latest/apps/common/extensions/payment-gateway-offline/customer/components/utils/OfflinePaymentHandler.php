<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * OfflinePaymentHandler
 * 
 * @package MailWizz EMA
 * @subpackage Payment Gateway Stripe
 * @author Serban George Cristian <cristian.serban@mailwizz.com> 
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.0
 */
 
class OfflinePaymentHandler extends PaymentHandlerAbstract
{
    // render the payment form
    public function renderPaymentView()
    {
        $model = $this->extension->getExtModel();
        $view  = $this->extension->getPathAlias() . '.customer.views.payment-form';
        $this->controller->renderPartial($view, compact('model'));
    }
    
    // validate the data and process the order
    public function processOrder()
    {
        $request     = Yii::app()->request;
        $transaction = $this->controller->getData('transaction');
        $order       = $this->controller->getData('order');
        
        $order->status = PricePlanOrder::STATUS_DUE;
        $order->save(false);
        
        $transaction->payment_gateway_name = Yii::t('payment_gateway_ext_offline', 'Offline payment');
        $transaction->payment_gateway_transaction_id = StringHelper::random(40);
        $transaction->status = PricePlanOrderTransaction::STATUS_SUCCESS;
        $transaction->save(false);

        $message = Yii::t('payment_gateway_ext_offline', 'Your order is in "{status}" status, once it gets approved, your pricing plan will become active!', array(
            '{status}' => Yii::t('orders', $order->status),
        ));
        Yii::app()->notify->addInfo($message);
        
        // the order is not complete, so return false
        $this->controller->redirect(array('price_plans/index'));
    }
}
