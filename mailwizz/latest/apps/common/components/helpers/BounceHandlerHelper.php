<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * BounceHandlerHelper
 *
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.3.9.7
 */

class BounceHandlerHelper
{
    /**
     * @return array|mixed
     */
    public static function getRules()
    {
        $cacheTtl = 3600 * 24;
        $cacheKey = sha1(__METHOD__);
        if (($rules = Yii::app()->cache->get($cacheKey)) !== false) {
            return $rules;
        }
        
        $licenseKey = Yii::app()->options->get('system.license.purchase_code', '');
        if (empty($licenseKey)) {
            Yii::app()->cache->set($cacheKey, array(), $cacheTtl);
            return array();
        }
        
        $response = AppInitHelper::makeRemoteRequest('https://www.mailwizz.com/api/bounces/rules', array(
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_TIMEOUT        => 10,
            CURLOPT_HTTPHEADER => array(
                'X-LICENSEKEY: ' . $licenseKey,
            )
        ));
        
        if (empty($response) || empty($response['message']) || empty($response['status']) || $response['status'] != 'success') {
            Yii::app()->cache->set($cacheKey, array(), $cacheTtl);
            return array();
        }

        $_rules = @json_decode($response['message'], true);
        if (empty($_rules) || empty($_rules['rules'])) {
            Yii::app()->cache->set($cacheKey, array(), $cacheTtl);
            return array();
        }

        Yii::app()->cache->set($cacheKey, $_rules['rules'], $cacheTtl);
        return $_rules['rules'];
    }
}
