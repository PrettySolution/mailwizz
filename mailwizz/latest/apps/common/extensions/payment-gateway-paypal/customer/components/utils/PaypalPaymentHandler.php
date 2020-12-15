<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * PaypalPaymentHandler
 * 
 * @package MailWizz EMA
 * @subpackage Payment Gateway Paypal
 * @author Serban George Cristian <cristian.serban@mailwizz.com> 
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.0
 */
 
class PaypalPaymentHandler extends PaymentHandlerAbstract
{
    // render the payment form
    public function renderPaymentView()
    {
        $order   = $this->controller->getData('order');
        $model   = $this->extension->getExtModel();
        $company = !empty($order->customer->company) ? $order->customer->company : null;
        
        $cancelUrl = Yii::app()->createAbsoluteUrl('price_plans/index');
        $returnUrl = Yii::app()->createAbsoluteUrl('price_plans/index');
        $notifyUrl = Yii::app()->createAbsoluteUrl('payment_gateway_ext_paypal/ipn');
        
        $assetsUrl = Yii::app()->assetManager->publish(Yii::getPathOfAlias($this->extension->getPathAlias()) . '/assets/customer', false, -1, MW_DEBUG);
        Yii::app()->clientScript->registerScriptFile($assetsUrl . '/js/payment-form.js');
        
        $customVars = sha1(StringHelper::uniqid());
        $view       = $this->extension->getPathAlias() . '.customer.views.payment-form';
        
        $this->controller->renderPartial($view, compact('model', 'order', 'company', 'cancelUrl', 'returnUrl', 'notifyUrl', 'customVars'));
    }
    
    // mark the order as pending retry
    public function processOrder()
    {
        $request = Yii::app()->request;
        
        if (strlen($request->getPost('custom')) != 40) {
            return false;
        }
        
        $transaction = $this->controller->getData('transaction');
        $order       = $this->controller->getData('order');
        
        $order->status = PricePlanOrder::STATUS_PENDING;
        $order->save(false);
        
        $transaction->payment_gateway_name = 'Paypal - www.paypal.com';
        $transaction->payment_gateway_transaction_id = $request->getPost('custom');
        $transaction->status = PricePlanOrderTransaction::STATUS_PENDING_RETRY;
        $transaction->save(false);
  
        $message = Yii::t('payment_gateway_ext_paypal', 'Your order is in "{status}" status, it usually takes a few minutes to be processed and if everything is fine, your pricing plan will become active!', array(
            '{status}' => Yii::t('orders', $order->status),
        ));
        
        if ($request->isAjaxRequest) {
            return $this->controller->renderJson(array(
                'result'  => 'success', 
                'message' => $message,
            ));
        }
        
        Yii::app()->notify->addInfo($message);
        $this->controller->redirect(array('price_plans/index'));
    }
}
