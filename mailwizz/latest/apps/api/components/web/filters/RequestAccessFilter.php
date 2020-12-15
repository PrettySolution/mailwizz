<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * RequestAccessFilter
 *
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.0
 */

class RequestAccessFilter extends CFilter
{
    protected function preFilter($filterChain)
    {
        $request            = Yii::app()->request;
        $options            = Yii::app()->options;
        $controller         = $filterChain->controller;
        $action             = $filterChain->action;
        $currentTimestamp   = time();

        // since 1.3.5.9
        $allowedIPs = CommonHelper::getArrayFromString($options->get('system.api.ip_access.allowed_ips'));
        $deniedIPs  = CommonHelper::getArrayFromString($options->get('system.api.ip_access.denied_ips'));
        $currentIP  = Yii::app()->request->getUserHostAddress();
        if (!empty($deniedIPs) && in_array($currentIP, $deniedIPs)) {
            $controller->renderJson(array(
                'status'    => 'error',
                'error'     => Yii::t('api', 'Your IP address is not allowed to access this server.')
            ), 400);
            return false;
        }
        if (!empty($allowedIPs) && !in_array($currentIP, $allowedIPs)) {
            $controller->renderJson(array(
                'status'    => 'error',
                'error'     => Yii::t('api', 'Your IP address is not allowed to access this server.')
            ), 400);
            return false;
        }
        //

        $unprotectedControllers = (array)Yii::app()->params->itemAt('unprotectedControllers');
        if (in_array($controller->id, $unprotectedControllers)) {
            return true;
        }
        
        // unfiltered _SERVER
        $server     = isset(Yii::app()->params['SERVER']) ? Yii::app()->params['SERVER']->toArray() : array();
        $publicKey  = isset($server['HTTP_X_MW_PUBLIC_KEY'])  ? $server['HTTP_X_MW_PUBLIC_KEY'] : '';
        $timestamp  = isset($server['HTTP_X_MW_TIMESTAMP'])   ? $server['HTTP_X_MW_TIMESTAMP'] : '';
        $signature  = isset($server['HTTP_X_MW_SIGNATURE'])   ? $server['HTTP_X_MW_SIGNATURE'] : '';
        $ipAddress  = isset($server['HTTP_X_MW_REMOTE_ADDR']) ? $server['HTTP_X_MW_REMOTE_ADDR'] : '';
        
        // 1.3.8.8
        $disableSignatureCheck = $options->get('system.api.disable_signature_check', 'no') == 'yes';
        if ($disableSignatureCheck && empty($signature)) {
            $signature = '---';
        }
        
        // verify required params.
        if (empty($publicKey) || empty($timestamp) || empty($signature)) {
            $controller->renderJson(array(
                'status'    => 'error',
                'error'     => Yii::t('api', 'Invalid API request params. Please refer to the documentation.')
            ), 400);
            return false;
        }

        $key = CustomerApiKey::model()->findByAttributes(array(
            'public' => $publicKey
        ));

        if (empty($key)) {
            $controller->renderJson(array(
                'status'    => 'error',
                'error'     => Yii::t('app', 'Invalid API key. Please refer to the documentation.')
            ), 400);
            return false;
        }
        
        // since 1.3.6.2
        $deniedIPs  = !empty($key->ip_blacklist) ? CommonHelper::getArrayFromString($key->ip_blacklist) : array();
        $allowedIPs = !empty($key->ip_whitelist) ? CommonHelper::getArrayFromString($key->ip_whitelist) : array();
        if (!empty($deniedIPs) && in_array($currentIP, $deniedIPs)) {
            $controller->renderJson(array(
                'status'    => 'error',
                'error'     => Yii::t('api', 'Your IP address is not allowed to access this server.')
            ), 400);
            return false;
        }
        if (!empty($allowedIPs) && !in_array($currentIP, $allowedIPs)) {
            $controller->renderJson(array(
                'status'    => 'error',
                'error'     => Yii::t('api', 'Your IP address is not allowed to access this server.')
            ), 400);
            return false;
        }
        //
        
        $customer = Customer::model()->findByPk((int)$key->customer_id);

        // since 1.3.4.8
        if ($customer->status != Customer::STATUS_ACTIVE) {
            $controller->renderJson(array(
                'status'    => 'error',
                'error'     => Yii::t('app', 'Your account must be active in order to use the API.')
            ), 400);
        }
        
        // since 1.5.3
        if ($customer->getGroupOption('api.enabled', 'yes') != 'yes') {
            $controller->renderJson(array(
                'status'    => 'error',
                'error'     => Yii::t('app', 'Your account is not allowed to use the API.')
            ), 400);
        }

        // set language
        if (!empty($customer->language_id)) {
            $language = Language::model()->findByPk((int)$customer->language_id);
            Yii::app()->setLanguage($language->getLanguageAndLocaleCode());
        }

        $requestTimeFrame = (int)$options->get('system.api.request_timeframe', 900);

        if (((int)$timestamp + $requestTimeFrame) < $currentTimestamp) {
            $controller->renderJson(array(
                'status'    => 'error',
                'error'     => Yii::t('app', 'Your request expired. Please refer to the documentation.')
            ), 400);
            return false;
        }

        Yii::app()->user->setModel($customer);
        Yii::app()->user->setId($customer->customer_id);

        if (Yii::app()->options->get('system.customer.action_logging_enabled', true)) {
            Yii::app()->user->getModel()->attachBehavior('logAction', array(
                'class' => 'customer.components.behaviors.CustomerActionLogBehavior',
            ));
        }
        
        // 1.3.8.8
        if ($disableSignatureCheck) {
            return true;
        }

        // reindex the GET array!
        Yii::app()->params['GET']->mergeWith($_GET);
        $_GET = Yii::app()->ioFilter->stripClean($_GET);
        
        // unfiltered _GET
        $getParams = isset(Yii::app()->params['GET']) ? (array)Yii::app()->params['GET']->toArray() : array();
        if (!empty($getParams)) {
            ksort($getParams, SORT_STRING);
        }

        $requestUrl = Yii::app()->createAbsoluteUrl($controller->route, $getParams);
        
        // prepare the params for creating and validating the signature.
        $specialHeaderParams = array(
            'X-MW-PUBLIC-KEY'  => $publicKey,
            'X-MW-TIMESTAMP'   => $timestamp,
            'X-MW-REMOTE-ADDR' => $ipAddress,
        );

        // unfiltered _POST / Rest data
        $postData = $request->getOriginalPost(null);
        $restData = $request->getRestParams();
        $params = new CMap($specialHeaderParams);
        $params->mergeWith($postData);
        $params->mergeWith($restData);
        
        $params = $params->toArray();
        ksort($params, SORT_STRING);

        $separator          = count($getParams) > 0 && strpos($requestUrl, '?') !== false ? '&' : '?';
        $signatureString    = strtoupper($request->getRequestType()) . ' ' . $requestUrl . $separator . http_build_query($params, '', '&');
        $signatureHash      = hash_hmac('sha1', $signatureString, $key->private, false);
        
        if ($signatureHash !== $signature) {
            $controller->renderJson(array(
                'status'    => 'error',
                'error'     => Yii::t('app', 'Invalid API request signature. Please refer to the documentation.')
            ), 400);
            return false;
        }

        return true;
    }

    protected function postFilter($filterChain)
    {
    }
}
