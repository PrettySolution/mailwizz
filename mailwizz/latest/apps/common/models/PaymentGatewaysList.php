<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * PaymentGatewaysList
 * 
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com> 
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.3.4.4
 */
 
class PaymentGatewaysList extends FormModel
{

    public function getDataProvider()
    {
        $hooks = Yii::app()->hooks;
        $registeredGateways = (array)$hooks->applyFilters('backend_payment_gateways_display_list', array());
        if (empty($registeredGateways)) {
            return new CArrayDataProvider(array());
        }
        
        $validRegisteredGateways = $sortOrder = array();
        foreach ($registeredGateways as $gateway) {
            if (!isset($gateway['id'], $gateway['name'], $gateway['description'], $gateway['status'], $gateway['sort_order'])) {
                continue;
            }  
            $sortOrder[] = (int)$gateway['sort_order'];
            $validRegisteredGateways[] = $gateway;
        }
        
        if (empty($validRegisteredGateways)) {
            return new CArrayDataProvider(array());
        }
        
        array_multisort($sortOrder, SORT_NUMERIC, $validRegisteredGateways);
        
        foreach ($validRegisteredGateways as $index => $gateway) {
            $gateway['name'] = CHtml::encode($gateway['name']);
            if (!empty($gateway['page_url'])) {
                $gateway['name'] = CHtml::link($gateway['name'], $gateway['page_url']);
            }
            $validRegisteredGateways[$index] = array(
                'id'            => $gateway['id'],
                'name'          => $gateway['name'],
                'description'   => $gateway['description'],
                'status'        => ucfirst(Yii::t('app', $gateway['status'])),
                'sort_order'    => (int)$gateway['sort_order'],
                'page_url'      => isset($gateway['page_url']) ? $gateway['page_url'] : null,
            );
        }
        
        return new CArrayDataProvider($validRegisteredGateways);
    }
}