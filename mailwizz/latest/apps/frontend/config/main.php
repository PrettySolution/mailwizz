<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * Frontend application main configuration file
 * 
 * This file should not be altered in any way!
 * 
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com> 
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.0
 */

return array(
    'basePath'          => Yii::getPathOfAlias('frontend'),
    'defaultController' => 'site', 
    
    'preload' => array(
        'frontendSystemInit'
    ),
    
    // autoloading model and component classes
    'import' => array(
        'frontend.components.*',
        'frontend.components.db.*',
        'frontend.components.db.ar.*',
        'frontend.components.db.behaviors.*',
        'frontend.components.utils.*',
        'frontend.components.web.*',
        'frontend.components.web.auth.*',
        'frontend.models.*',   
    ),
    
    'components' => array(
        
        'request' => array( 
            'class'                   => 'frontend.components.web.FrontendHttpRequest',
            'noCsrfValidationRoutes'  => array('lists/*', 'dswh/*'),
        ),
        
        'urlManager' => array(
            'rules' => array(
                array('site/index', 'pattern' => ''),
                
                array('lists/subscribe_confirm', 'pattern' => 'lists/<list_uid:([a-z0-9]+)>/confirm-subscribe/<subscriber_uid:([a-z0-9]+)>/<do:([a-z0-9\_\-]+)>'),
                array('lists/subscribe_confirm', 'pattern' => 'lists/<list_uid:([a-z0-9]+)>/confirm-subscribe/<subscriber_uid:([a-z0-9]+)>'),
                
                array('lists/unsubscribe_confirm', 'pattern' => 'lists/<list_uid:([a-z0-9]+)>/confirm-unsubscribe/<subscriber_uid:([a-z0-9]+)>/<campaign_uid:([a-z0-9]+)>'),
                array('lists/unsubscribe_confirm', 'pattern' => 'lists/<list_uid:([a-z0-9]+)>/confirm-unsubscribe/<subscriber_uid:([a-z0-9]+)>'),
                
                array('lists/update_profile', 'pattern' => 'lists/<list_uid:([a-z0-9]+)>/update-profile/<subscriber_uid:([a-z0-9]+)>'),
                array('lists/subscribe_pending', 'pattern' => 'lists/<list_uid:([a-z0-9]+)>/pending-subscribe'),
                
                array('lists/unsubscribe', 'pattern' => 'lists/<list_uid:([a-z0-9]+)>/unsubscribe/<subscriber_uid:([a-z0-9]+)>/<campaign_uid:([a-z0-9]+)>/<type:(unsubscribe\-([a-z]+))>'),
                array('lists/unsubscribe', 'pattern' => 'lists/<list_uid:([a-z0-9]+)>/unsubscribe/<subscriber_uid:([a-z0-9]+)>/<campaign_uid:([a-z0-9]+)>'),
                array('lists/unsubscribe', 'pattern' => 'lists/<list_uid:([a-z0-9]+)>/unsubscribe/<subscriber_uid:([a-z0-9]+)>'),
                array('lists/unsubscribe', 'pattern' => 'lists/<list_uid:([a-z0-9]+)>/unsubscribe/<subscriber_uid:([a-z0-9]+)>/<type:(unsubscribe\-([a-z]+))>'),
                
                array('lists/subscribe', 'pattern' => 'lists/<list_uid:([a-z0-9]+)>/subscribe/<subscriber_uid:([a-z0-9]+)>'),
                array('lists/block_address_confirmation', 'pattern' => 'lists/block-address-confirmation/<key:([a-z0-9]{40})>'),
                array('lists/block_address', 'pattern' => 'lists/block-address'),
	            array('lists/unsubscribe_from_customer', 'pattern' => 'lists/unsubscribe-from-customer/<customer_uid:([a-z0-9]+)>/<subscriber_uid:([a-z0-9]+)>/<campaign_uid:([a-z0-9]+)>'),
	            array('lists/unsubscribe_from_customer', 'pattern' => 'lists/unsubscribe-from-customer/<customer_uid:([a-z0-9]+)>/<subscriber_uid:([a-z0-9]+)>'),
	            array('lists/unsubscribe_from_customer', 'pattern' => 'lists/unsubscribe-from-customer/<customer_uid:([a-z0-9]+)>'),
	            array('lists/vcard', 'pattern' => 'lists/<list_uid:([a-z0-9]+)>/vcard'),
	            array('lists/<action>', 'pattern' => 'lists/<list_uid:([a-z0-9]+)>/<action>'),

                array('campaigns_reports/open_by_subscriber', 'pattern' => 'campaigns/<campaign_uid:([a-z0-9]+)>/reports/open-by-subscriber/<subscriber_uid:([a-z0-9]+)>'),
                array('campaigns_reports/click_by_subscriber_unique', 'pattern' => 'campaigns/<campaign_uid:([a-z0-9]+)>/reports/click-by-subscriber-unique/<subscriber_uid:([a-z0-9]+)>'),
                array('campaigns_reports/click_by_subscriber', 'pattern' => 'campaigns/<campaign_uid:([a-z0-9]+)>/reports/click-by-subscriber/<subscriber_uid:([a-z0-9]+)>'),
                array('campaigns_reports/open_unique', 'pattern' => 'campaigns/<campaign_uid:([a-z0-9]+)>/reports/open-unique'),
                array('campaigns_reports/click_url', 'pattern' => 'campaigns/<campaign_uid:([a-z0-9]+)>/reports/click-url'),
                array('campaigns_reports/forward_friend', 'pattern' => 'campaigns/<campaign_uid:([a-z0-9]+)>/reports/forward-friend'),
                array('campaigns_reports/abuse_reports', 'pattern' => 'campaigns/<campaign_uid:([a-z0-9]+)>/reports/abuse-reports'),
                array('campaigns_reports/<action>', 'pattern' => 'campaigns/<campaign_uid:([a-z0-9]+)>/reports/<action:(\w+)>/*'),
                array('campaigns_reports/<action>', 'pattern' => 'campaigns/<campaign_uid:([a-z0-9]+)>/reports/<action:(\w+)>'),
                
                array('campaigns/web_version', 'pattern' => 'campaigns/<campaign_uid:([a-z0-9]+)>/web-version/<subscriber_uid:([a-z0-9]+)>'),
                array('campaigns/track_opening', 'pattern' => 'campaigns/<campaign_uid:([a-z0-9]+)>/track-opening/<subscriber_uid:([a-z0-9]+)>'),
                array('campaigns/track_url', 'pattern' => 'campaigns/<campaign_uid:([a-z0-9]+)>/track-url/<subscriber_uid:([a-z0-9]+)>/<hash:([a-z0-9\.\s\-\_=]+)>'),
                array('campaigns/web_version', 'pattern' => 'campaigns/<campaign_uid:([a-z0-9]+)>'),
                array('campaigns/forward_friend', 'pattern' => 'campaigns/<campaign_uid:([a-z0-9]+)>/forward-friend/<subscriber_uid:([a-z0-9]+)>'),
                array('campaigns/forward_friend', 'pattern' => 'campaigns/<campaign_uid:([a-z0-9]+)>/forward-friend'),
                array('campaigns/report_abuse', 'pattern' => 'campaigns/<campaign_uid:([a-z0-9]+)>/report-abuse/<list_uid:([a-z0-9]+)>/<subscriber_uid:([a-z0-9]+)>'),
	            array('campaigns/vcard', 'pattern' => 'campaigns/<campaign_uid:([a-z0-9]+)>/vcard'),
	            array('campaigns/<action>', 'pattern' => 'campaigns/<campaign_uid:([a-z0-9]+)>/<action:(\w+)>'),

                array('campaigns_reports_export/basic', 'pattern' => 'campaigns/<campaign_uid:([a-z0-9]+)>/reports-export/basic'),
                array('campaigns_reports_export/click_url', 'pattern' => 'campaigns/<campaign_uid:([a-z0-9]+)>/reports-export/click-url'),
                array('campaigns_reports_export/click_by_subscriber', 'pattern' => 'campaigns/<campaign_uid:([a-z0-9]+)>/reports-export/click-by-subscriber/<subscriber_uid:([a-z0-9]+)>'),
                array('campaigns_reports_export/click_by_subscriber_unique', 'pattern' => 'campaigns/<campaign_uid:([a-z0-9]+)>/reports-export/click-by-subscriber-unique/<subscriber_uid:([a-z0-9]+)>'),
                array('campaigns_reports_export/<action>', 'pattern' => 'campaigns/<campaign_uid:([a-z0-9]+)>/reports-export/<action:(\w+)>'),

                array('articles/index', 'pattern' => 'articles/page/<page:(\d+)>'),
                array('articles/index', 'pattern' => 'articles'),
                array('articles/category', 'pattern' => 'articles/<slug:(.*)>'),
                array('articles/view', 'pattern' => 'article/<slug:(.*)>'),

                array('pages/view', 'pattern' => 'page/<slug:(.*)>'),
                
                array('dswh/index', 'pattern' => 'dswh/<id:([0-9]+)>'),

	            array('surveys/index', 'pattern' => 'surveys/<survey_uid:([a-z0-9]+)>/<subscriber_uid:([a-z0-9]+)>/<campaign_uid:([a-z0-9]+)>'),
	            array('surveys/index', 'pattern' => 'surveys/<survey_uid:([a-z0-9]+)>/<subscriber_uid:([a-z0-9]+)>'),
                array('surveys/index', 'pattern' => 'surveys/<survey_uid:([a-z0-9]+)>'),
                array('surveys/<action>', 'pattern' => 'surveys/<survey_uid:([a-z0-9]+)>/<action>'),

            ),
        ),
        
        'assetManager' => array(
            'basePath'  => Yii::getPathOfAlias('root.frontend.assets.cache'),
            'baseUrl'   => AppInitHelper::getBaseUrl('frontend/assets/cache')
        ),
        
        'themeManager' => array(
            'class'     => 'common.components.managers.ThemeManager',
            'basePath'  => Yii::getPathOfAlias('root.frontend.themes'),
            'baseUrl'   => AppInitHelper::getBaseUrl('frontend/themes'),
        ),
        
        'errorHandler' => array(
            'errorAction'   => 'site/error',
        ),
		
        'session' => array(
            'class'                  => 'system.web.CDbHttpSession',
            'connectionID'           => 'db',
            'sessionName'            => 'mwsid',
            'timeout'                => 7200,
            'sessionTableName'       => '{{session}}',
            'autoCreateSessionTable' => false,
            'cookieParams'           => array(
                'httponly' => true,
            ),
        ),
        
        'user' => array(
            'class'             => 'backend.components.web.auth.WebUser',
            'allowAutoLogin'    => true,
            'authTimeout'       => 7200,
            'identityCookie'    => array(
                'httpOnly'      => true, 
            )
        ),
        
        'customer' => array(
            'class'             => 'customer.components.web.auth.WebCustomer',
            'allowAutoLogin'    => true,
            'authTimeout'       => 7200,
            'identityCookie'    => array(
                'httpOnly'      => true, 
            )
        ),

        'frontendSystemInit' => array(
            'class' => 'frontend.components.init.FrontendSystemInit',
        ),
    ),
    
    'modules' => array(),

    // application-level parameters that can be accessed
    // using Yii::app()->params['paramName']
    'params' => array(),
);