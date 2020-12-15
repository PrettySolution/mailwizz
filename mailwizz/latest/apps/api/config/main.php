<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * Api application main configuration file
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
    'basePath'          => Yii::getPathOfAlias('api'),
    'defaultController' => 'site', 
    
    'preload' => array(
        'apiSystemInit'
    ),
    
    // autoloading model and component classes
    'import' => array(
        'api.components.*',
        'api.components.db.*',
        'api.components.db.ar.*',
        'api.components.db.behaviors.*',
        'api.components.utils.*',
        'api.components.web.*',
        'api.components.web.auth.*',
        'api.models.*',  
    ),
    
    'components' => array(
    
        'request' => array( 
            'enableCsrfValidation'      => false,
            'enableCookieValidation'    => false,
        ),

        'urlManager' => array(
            'rules' => array(
                array('lists/index', 'pattern' => 'lists', 'verb' => 'GET'),
                array('lists/create', 'pattern' => 'lists', 'verb' => 'POST'),
                array('lists/view', 'pattern' => 'lists/<list_uid:([a-z0-9]+)>', 'verb' => 'GET'),
                array('lists/update', 'pattern' => 'lists/<list_uid:([a-z0-9]+)>', 'verb' => 'PUT'),
                array('lists/copy', 'pattern' => 'lists/<list_uid:([a-z0-9]+)>/copy', 'verb' => 'POST'),
                array('lists/delete', 'pattern' => 'lists/<list_uid:([a-z0-9]+)>', 'verb' => 'DELETE'),
                
                array('templates/index', 'pattern' => 'templates', 'verb' => 'GET'),
                array('templates/create', 'pattern' => 'templates', 'verb' => 'POST'),
                array('templates/view', 'pattern' => 'templates/<template_uid:([a-z0-9]+)>', 'verb' => 'GET'),
                array('templates/update', 'pattern' => 'templates/<template_uid:([a-z0-9]+)>', 'verb' => 'PUT'),
                array('templates/delete', 'pattern' => 'templates/<template_uid:([a-z0-9]+)>', 'verb' => 'DELETE'),

                // since 1.3.7.3
                array('campaigns_tracking/track_opening', 'pattern' => 'campaigns/<campaign_uid:([a-z0-9]+)>/track-opening/<subscriber_uid:([a-z0-9]+)>', 'verb' => 'GET'),
                array('campaigns_tracking/track_url', 'pattern' => 'campaigns/<campaign_uid:([a-z0-9]+)>/track-url/<subscriber_uid:([a-z0-9]+)>/<hash:([a-z0-9]+)>', 'verb' => 'GET'),
                array('campaigns_tracking/track_unsubscribe', 'pattern' => 'campaigns/<campaign_uid:([a-z0-9]+)>/track-unsubscribe/<subscriber_uid:([a-z0-9]+)>', 'verb' => 'POST'),
                
                // since 1.4.4
                array('campaign_bounces/index', 'pattern' => 'campaigns/<campaign_uid:([a-z0-9]+)>/bounces', 'verb' => 'GET'),
                array('campaign_bounces/create', 'pattern' => 'campaigns/<campaign_uid:([a-z0-9]+)>/bounces', 'verb' => 'POST'),

                array('campaigns/index', 'pattern' => 'campaigns', 'verb' => 'GET'),
                array('campaigns/create', 'pattern' => 'campaigns', 'verb' => 'POST'),
                array('campaigns/view', 'pattern' => 'campaigns/<campaign_uid:([a-z0-9]+)>', 'verb' => 'GET'),
                array('campaigns/update', 'pattern' => 'campaigns/<campaign_uid:([a-z0-9]+)>', 'verb' => 'PUT'),
                array('campaigns/copy', 'pattern' => 'campaigns/<campaign_uid:([a-z0-9]+)>/copy', 'verb' => 'POST'),
                array('campaigns/delete', 'pattern' => 'campaigns/<campaign_uid:([a-z0-9]+)>', 'verb' => 'DELETE'),
                array('campaigns/pause_unpause', 'pattern' => 'campaigns/<campaign_uid:([a-z0-9]+)>/pause-unpause', 'verb' => 'PUT'),
                array('campaigns/mark_sent', 'pattern' => 'campaigns/<campaign_uid:([a-z0-9]+)>/mark-sent', 'verb' => 'PUT'),
                
                array('list_fields/index', 'pattern' => 'lists/<list_uid:([a-z0-9]+)>/fields', 'verb' => 'GET'),
                array('list_segments/index', 'pattern' => 'lists/<list_uid:([a-z0-9]+)>/segments', 'verb' => 'GET'),
                
                array('list_subscribers/index', 'pattern' => 'lists/<list_uid:([a-z0-9]+)>/subscribers', 'verb' => 'GET'),
                array('list_subscribers/create', 'pattern' => 'lists/<list_uid:([a-z0-9]+)>/subscribers', 'verb' => 'POST'),
	            array('list_subscribers/create_bulk', 'pattern' => 'lists/<list_uid:([a-z0-9]+)>/subscribers/bulk', 'verb' => 'POST'),
                array('list_subscribers/unsubscribe', 'pattern' => 'lists/<list_uid:([a-z0-9]+)>/subscribers/<subscriber_uid:([a-z0-9]+)>/unsubscribe', 'verb' => 'PUT'),
                array('list_subscribers/update', 'pattern' => 'lists/<list_uid:([a-z0-9]+)>/subscribers/<subscriber_uid:([a-z0-9]+)>', 'verb' => 'PUT'),
                array('list_subscribers/delete', 'pattern' => 'lists/<list_uid:([a-z0-9]+)>/subscribers/<subscriber_uid:([a-z0-9]+)>', 'verb' => 'DELETE'),
                array('list_subscribers/view', 'pattern' => 'lists/<list_uid:([a-z0-9]+)>/subscribers/<subscriber_uid:([a-z0-9]+)>', 'verb' => 'GET'),
                array('list_subscribers/search_by_email', 'pattern' => 'lists/<list_uid:([a-z0-9]+)>/subscribers/search-by-email', 'verb' => 'GET'),
	            array('list_subscribers/search_by_custom_fields', 'pattern' => 'lists/<list_uid:([a-z0-9]+)>/subscribers/search-by-custom-fields', 'verb' => 'GET'),
	            array('list_subscribers/search_by_email_in_all_lists', 'pattern' => 'lists/subscribers/search-by-email-in-all-lists', 'verb' => 'GET'),
                array('list_subscribers/unsubscribe_by_email_from_all_lists', 'pattern' => 'lists/subscribers/unsubscribe-by-email-from-all-lists', 'verb' => 'PUT'),
                
                array('countries/index', 'pattern' => 'countries', 'verb' => 'GET'),
                array('countries/zones', 'pattern' => 'countries/<country_id:(\d+)>/zones', 'verb' => 'GET'),
                
                array('transactional_emails/index', 'pattern' => 'transactional-emails', 'verb' => 'GET'),
                array('transactional_emails/create', 'pattern' => 'transactional-emails', 'verb' => 'POST'),
                array('transactional_emails/view', 'pattern' => 'transactional-emails/<email_uid:([a-z0-9]+)>', 'verb' => 'GET'),
                array('transactional_emails/delete', 'pattern' => 'transactional-emails/<email_uid:([a-z0-9]+)>', 'verb' => 'DELETE'),
                
                array('customers/create', 'pattern' => 'customers', 'verb' => 'POST'),
            ),
        ),
        
        'user' => array(
            'class'     => 'api.components.web.auth.WebUser',
            'loginUrl'  => null,
        ),
        
        'apiSystemInit' => array(
            'class' => 'api.components.init.ApiSystemInit',
        ),
    ),
    
    'modules' => array(),
    
    // application-level parameters that can be accessed
    // using Yii::app()->params['paramName']
    'params' => array(
        'unprotectedControllers' => array(
            'site', 'customers',
        )
    ),
);