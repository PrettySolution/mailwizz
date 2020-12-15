<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * Customer application main configuration file
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
    'basePath'          => Yii::getPathOfAlias('customer'),
    'defaultController' => 'dashboard',

    'preload' => array(
        'customerSystemInit'
    ),

    // autoloading model and component classes
    'import' => array(
        'customer.components.*',
        'customer.components.db.*',
        'customer.components.db.ar.*',
        'customer.components.db.behaviors.*',
        'customer.components.utils.*',
        'customer.components.web.*',
        'customer.components.web.auth.*',
        'customer.models.*',
    ),

    'components' => array(

        'urlManager' => array(
            'rules' => array(
                array('guest/forgot_password', 'pattern' => 'guest/forgot-password'),
                array('guest/reset_password', 'pattern' => 'guest/reset-password/<reset_key:([a-zA-Z0-9]{40})>'),
                array('guest/confirm_registration', 'pattern' => 'guest/confirm-registration/<key:([a-zA-Z0-9]{40})>'),

                array('lists/index', 'pattern' => 'lists/index/*'),

                array('list_subscribers/index', 'pattern' => 'lists/<list_uid:([a-z0-9]+)>/subscribers'),
                array('list_subscribers/create', 'pattern' => 'lists/<list_uid:([a-z0-9]+)>/subscribers/create'),
                array('list_subscribers/bulk_action', 'pattern' => 'lists/<list_uid:([a-z0-9]+)>/subscribers/bulk-action'),
                array('list_subscribers/campaign_for_subscriber', 'pattern' => 'lists/<list_uid:([a-z0-9]+)>/subscribers/<subscriber_uid:([a-z0-9]+)>/campaign-for-subscriber'),
                array('list_subscribers/campaigns_export', 'pattern' => 'lists/<list_uid:([a-z0-9]+)>/subscribers/<subscriber_uid:([a-z0-9]+)>/campaigns/export'),
                array('list_subscribers/profile_export', 'pattern' => 'lists/<list_uid:([a-z0-9]+)>/subscribers/<subscriber_uid:([a-z0-9]+)>/profile/export'),
                array('list_subscribers/<action>', 'pattern' => 'lists/<list_uid:([a-z0-9]+)>/subscribers/<subscriber_uid:([a-z0-9]+)>/<action:(update|subscribe|unsubscribe|disable|delete|campaigns|profile)>'),
                array('list_segments/index', 'pattern' => 'lists/<list_uid:([a-z0-9]+)>/segments'),
                array('list_segments/create', 'pattern' => 'lists/<list_uid:([a-z0-9]+)>/segments/create'),
                array('list_segments/<action>', 'pattern' => 'lists/<list_uid:([a-z0-9]+)>/segments/<segment_uid:([a-z0-9]+)>/<action:(update|delete|copy|subscribers)>'),
                array('list_fields/index', 'pattern' => 'lists/<list_uid:([a-z0-9]+)>/fields'),
                array('list_page/index', 'pattern' => 'lists/<list_uid:([a-z0-9]+)>/page/<type:([a-zA-Z0-9_\-]+)>'),
                array('list_forms/index', 'pattern' => 'lists/<list_uid:([a-z0-9]+)>/forms'),

                array('list_import/index', 'pattern' => 'lists/<list_uid:([a-z0-9]+)>/import'),
                array('list_import/<action>', 'pattern' => 'lists/<list_uid:([a-z0-9]+)>/import/<action>'),
                array('list_export/index', 'pattern' => 'lists/<list_uid:([a-z0-9]+)>/export'),
                array('list_export/<action>', 'pattern' => 'lists/<list_uid:([a-z0-9]+)>/export/<action>'),
                array('list_segments_export/index', 'pattern' => 'lists/<list_uid:([a-z0-9]+)>/segments/<segment_uid:([a-z0-9]+)>/export'),
                array('list_segments_export/<action>', 'pattern' => 'lists/<list_uid:([a-z0-9]+)>/segments/<segment_uid:([a-z0-9]+)>/export/<action>'),

                array('lists_tools/<action>', 'pattern' => 'lists/tools/<action>'),

                array('list_tools/copy_subscribers', 'pattern' => 'lists/<list_uid:([a-z0-9]+)>/tools/copy-subscribers'),
                array('list_tools/<action>', 'pattern' => 'lists/<list_uid:([a-z0-9]+)>/tools/<action>'),
                
                array('lists/all_subscribers', 'pattern' => 'lists/all-subscribers/*'),
                array('lists/all_subscribers', 'pattern' => 'lists/all-subscribers'),
	            array('lists/toggle_archive', 'pattern' => 'lists/<list_uid:([a-z0-9]+)>/toggle-archive/*'),
	            array('lists/toggle_archive', 'pattern' => 'lists/<list_uid:([a-z0-9]+)>/toggle-archive'),
                array('lists/<action>', 'pattern' => 'lists/<list_uid:([a-z0-9]+)>/<action:([a-z0-9]+)>'),
                array('templates/gallery_import', 'pattern' => 'templates/gallery-import/<template_uid:([a-z0-9]+)>'),
                array('templates/update_sort_order', 'pattern' => 'templates/update-sort-order'),
                array('templates/<action>', 'pattern' => 'templates/<template_uid:([a-z0-9]+)>/<action:(update|test|delete|preview|copy)>'),

                array('campaign_reports/open_by_subscriber', 'pattern' => 'campaigns/<campaign_uid:([a-z0-9]+)>/reports/open-by-subscriber/<subscriber_uid:([a-z0-9]+)>'),
                array('campaign_reports/click_by_subscriber_unique', 'pattern' => 'campaigns/<campaign_uid:([a-z0-9]+)>/reports/click-by-subscriber-unique/<subscriber_uid:([a-z0-9]+)>'),
                array('campaign_reports/click_by_subscriber', 'pattern' => 'campaigns/<campaign_uid:([a-z0-9]+)>/reports/click-by-subscriber/<subscriber_uid:([a-z0-9]+)>'),
                array('campaign_reports/open_unique', 'pattern' => 'campaigns/<campaign_uid:([a-z0-9]+)>/reports/open-unique'),
                array('campaign_reports/click_url', 'pattern' => 'campaigns/<campaign_uid:([a-z0-9]+)>/reports/click-url'),
                array('campaign_reports/forward_friend', 'pattern' => 'campaigns/<campaign_uid:([a-z0-9]+)>/reports/forward-friend'),
                array('campaign_reports/abuse_reports', 'pattern' => 'campaigns/<campaign_uid:([a-z0-9]+)>/reports/abuse-reports'),
                array('campaign_reports/<action>', 'pattern' => 'campaigns/<campaign_uid:([a-z0-9]+)>/reports/<action:(\w+)>/*'),
                array('campaign_reports/<action>', 'pattern' => 'campaigns/<campaign_uid:([a-z0-9]+)>/reports/<action:(\w+)>'),

                array('campaigns_stats/<action>', 'pattern' => 'campaigns/stats/<action:(\w+)>/*'),
                array('campaigns_stats/<action>', 'pattern' => 'campaigns/stats/<action:(\w+)>'),

                array('campaigns_geo_opens/export_all', 'pattern' => 'campaigns/geo-opens/export/all'),
                array('campaigns_geo_opens/export_unique', 'pattern' => 'campaigns/geo-opens/export/unique'),
                array('campaigns_geo_opens/<action>', 'pattern' => 'campaigns/geo-opens/<action:(\w+)>/*'),
                array('campaigns_geo_opens/<action>', 'pattern' => 'campaigns/geo-opens/<action:(\w+)>'),
                array('campaigns_geo_opens/index', 'pattern' => 'campaigns/geo-opens'),
                
                array('campaign_groups/<action>', 'pattern' => 'campaigns/groups/<group_uid:([a-z0-9]+)>/<action:(\w+)>'),
                array('campaign_groups/<action>', 'pattern' => 'campaigns/groups/<action:(\w+)>'),
                array('campaign_groups/index', 'pattern' => 'campaigns/groups'),

                array('campaign_tags/<action>', 'pattern' => 'campaigns/tags/<tag_uid:([a-z0-9]+)>/<action:(\w+)>'),
                array('campaign_tags/<action>', 'pattern' => 'campaigns/tags/<action:(\w+)>'),
                array('campaign_tags/index', 'pattern' => 'campaigns/tags'),

                array('messages/view', 'pattern' => 'messages/<message_uid:([a-z0-9]+)>/view'),
                array('messages/delete', 'pattern' => 'messages/<message_uid:([a-z0-9]+)>/delete'),
                array('messages/mark_all_as_seen', 'pattern' => 'messages/mark-all-as-seen'),

	            array('campaigns/resend_giveups', 'pattern' => 'campaigns/<campaign_uid:([a-z0-9]+)>/resend-giveups'),
                array('campaigns/pause_unpause', 'pattern' => 'campaigns/<campaign_uid:([a-z0-9]+)>/pause-unpause'),
                array('campaigns/merge_lists', 'pattern' => 'campaigns/<campaign_uid:([a-z0-9]+)>/merge-lists'),
                array('campaigns/import_from_share_code', 'pattern' => 'campaigns/import-from-share-code'),

                array('campaigns/<action>', 'pattern' => 'campaigns/<campaign_uid:([a-z0-9]+)>/<action:(\w+)>'),

                array('api_keys/<action>', 'pattern' => 'api-keys/<action>/*'),
                array('api_keys/<action>', 'pattern' => 'api-keys/<action>'),

                array('survey_fields/index', 'pattern' => 'surveys/<survey_uid:([a-z0-9]+)>/fields'),
                array('survey_responders/index', 'pattern' => 'surveys/<survey_uid:([a-z0-9]+)>/responders'),
                array('survey_responders/create', 'pattern' => 'surveys/<survey_uid:([a-z0-9]+)>/responders/create'),
                array('survey_responders/<action>', 'pattern' => 'surveys/<survey_uid:([a-z0-9]+)>/responders/<responder_uid:([a-z0-9]+)>/<action:(update|delete)>'),
                array('survey_segments/index', 'pattern' => 'surveys/<survey_uid:([a-z0-9]+)>/segments'),
                array('survey_segments/create', 'pattern' => 'surveys/<survey_uid:([a-z0-9]+)>/segments/create'),
                array('survey_segments/<action>', 'pattern' => 'surveys/<survey_uid:([a-z0-9]+)>/segments/<segment_uid:([a-z0-9]+)>/<action:(update|delete|copy|responders)>'),

                array('survey_segments_export/index', 'pattern' => 'surveys/<survey_uid:([a-z0-9]+)>/segments/<segment_uid:([a-z0-9]+)>/export'),
                array('survey_segments_export/<action>', 'pattern' => 'surveys/<survey_uid:([a-z0-9]+)>/segments/<segment_uid:([a-z0-9]+)>/export/<action>'),

                array('surveys/index', 'pattern' => 'surveys/index/*'),
                array('surveys/<action>', 'pattern' => 'surveys/<survey_uid:([a-z0-9]+)>/<action:([a-z0-9]+)>'),

                array('dashboard/delete_log', 'pattern' => 'dashboard/delete-log/id/<id:(\d+)>'),
                array('dashboard/delete_logs', 'pattern' => 'dashboard/delete-logs'),
                array('dashboard/export_recent_activity', 'pattern' => 'dashboard/export-recent-activity'),

                array('campaign_reports_export/basic', 'pattern' => 'campaigns/<campaign_uid:([a-z0-9]+)>/reports-export/basic'),
                array('campaign_reports_export/click_url', 'pattern' => 'campaigns/<campaign_uid:([a-z0-9]+)>/reports-export/click-url'),
                array('campaign_reports_export/click_by_subscriber', 'pattern' => 'campaigns/<campaign_uid:([a-z0-9]+)>/reports-export/click-by-subscriber/<subscriber_uid:([a-z0-9]+)>'),
                array('campaign_reports_export/click_by_subscriber_unique', 'pattern' => 'campaigns/<campaign_uid:([a-z0-9]+)>/reports-export/click-by-subscriber-unique/<subscriber_uid:([a-z0-9]+)>'),
                array('campaign_reports_export/<action>', 'pattern' => 'campaigns/<campaign_uid:([a-z0-9]+)>/reports-export/<action:(\w+)>'),

                array('delivery_servers/<action>', 'pattern' => 'delivery-servers/<action:(\w+)>/*'),
                array('delivery_servers/<action>', 'pattern' => 'delivery-servers/<action:(\w+)>'),
                array('delivery_servers', 'pattern' => 'delivery-servers'),

                array('bounce_servers/<action>', 'pattern' => 'bounce-servers/<action:(\w+)>/*'),
                array('bounce_servers/<action>', 'pattern' => 'bounce-servers/<action:(\w+)>'),
                array('bounce_servers', 'pattern' => 'bounce-servers'),

                array('feedback_loop_servers/<action>', 'pattern' => 'feedback-loop-servers/<action:(\w+)>/*'),
                array('feedback_loop_servers/<action>', 'pattern' => 'feedback-loop-servers/<action:(\w+)>'),
                array('feedback_loop_servers', 'pattern' => 'feedback-loop-servers'),

                array('email_box_monitors/<action>', 'pattern' => 'email-box-monitors/<action:(\w+)>/*'),
                array('email_box_monitors/<action>', 'pattern' => 'email-box-monitors/<action:(\w+)>'),
                array('email_box_monitors', 'pattern' => 'email-box-monitors'),

                array('price_plans/orders_export', 'pattern' => 'price-plans/orders/export'),
                array('price_plans/order_detail', 'pattern' => 'price-plans/orders/<order_uid:([a-z0-9]+)>'),
                array('price_plans/order_pdf', 'pattern' => 'price-plans/orders/<order_uid:([a-z0-9]+)>/pdf'),
                array('price_plans/email_invoice', 'pattern' => 'price-plans/orders/<order_uid:([a-z0-9]+)>/email-invoice'),
                array('price_plans/<action>', 'pattern' => 'price-plans/<action:(\w+)>/*'),
                array('price_plans/<action>', 'pattern' => 'price-plans/<action>'),

                array('tracking_domains/<action>', 'pattern' => 'tracking-domains/<action:(\w+)>/*'),
                array('tracking_domains/<action>', 'pattern' => 'tracking-domains/<action:(\w+)>'),
                array('tracking_domains', 'pattern' => 'tracking-domains'),

                array('sending_domains/<action>', 'pattern' => 'sending-domains/<action:(\w+)>/*'),
                array('sending_domains/<action>', 'pattern' => 'sending-domains/<action:(\w+)>'),
                array('sending_domains', 'pattern' => 'sending-domains'),

                array('email_blacklist/delete_all', 'pattern' => 'email-blacklist/delete-all'),
	            array('email_blacklist/<action>', 'pattern' => 'email-blacklist/<action:(\w+)>/<email_uid:([a-z0-9]+)>'),
                array('email_blacklist/<action>', 'pattern' => 'email-blacklist/<action:(\w+)>/*'),
                array('email_blacklist/<action>', 'pattern' => 'email-blacklist/<action:(\w+)>'),
                
                array('templates_categories/<action>', 'pattern' => 'templates/categories/<action:(\w+)>/*'),
                array('templates_categories/<action>', 'pattern' => 'templates/categories/<action:(\w+)>'),

                array('suppression_list_emails/<action>', 'pattern' => 'suppression-lists/<list_uid:([a-z0-9]+)>/emails/<email_id:([0-9]+)>/<action:(\w+)>'),
                array('suppression_list_emails/<action>', 'pattern' => 'suppression-lists/<list_uid:([a-z0-9]+)>/emails/<action:(\w+)>/*'),
                array('suppression_list_emails/<action>', 'pattern' => 'suppression-lists/<list_uid:([a-z0-9]+)>/emails/<action:(\w+)>'),
                
                array('suppression_lists/<action>', 'pattern' => 'suppression-lists/<list_uid:([a-z0-9]+)>/<action:(\w+)>'),
                array('suppression_lists/<action>', 'pattern' => 'suppression-lists/<action:(\w+)>'),
            ),
        ),

        'assetManager' => array(
            'basePath'  => Yii::getPathOfAlias('root.customer.assets.cache'),
            'baseUrl'   => AppInitHelper::getBaseUrl('assets/cache')
        ),

        'themeManager' => array(
            'class'     => 'common.components.managers.ThemeManager',
            'basePath'  => Yii::getPathOfAlias('root.customer.themes'),
            'baseUrl'   => AppInitHelper::getBaseUrl('themes'),
        ),

        'errorHandler' => array(
            'errorAction'   => 'guest/error',
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
            'loginUrl'          => array('guest/index'),
            'returnUrl'         => array('dashboard/index'),
            'authTimeout'       => 7200,
            'identityCookie'    => array(
                'httpOnly'      => true,
            )
        ),

        'customerSystemInit' => array(
            'class' => 'customer.components.init.CustomerSystemInit',
        ),
    ),

    'modules' => array(),

    // application-level parameters that can be accessed
    // using Yii::app()->params['paramName']
    'params'=>array(
        // list of controllers where the user doesn't have to be logged in.
        'unprotectedControllers' => array('guest')
    ),
);
