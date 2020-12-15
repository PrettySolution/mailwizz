<?php defined('MW_PATH') || exit('No direct script access allowed');

return array(
	array(
		'name'      => 'Delivery server validation',
		'slug'      => 'delivery-server-validation',
		'subject'   => 'Please validate this server.',
		'content'   => '',
		'tags'      => array(
			array(
				'tag'           => '[HOSTNAME]',
				'description'   => 'The delivery server hostname',
			),
			array(
				'tag'           => '[CONFIRMATION_URL]',
				'description'   => 'The confirmation url for this server',
			),
			array(
				'tag'           => '[CONFIRMATION_KEY]',
				'description'   => 'The confirmation key for this server',
			)
		),
	),
	array(
		'name'      => 'Campaign pending approval',
		'slug'      => 'campaign-pending-approval',
		'subject'   => 'A campaign requires approval before sending!',
		'content'   => '',
		'tags'      => array(
			array(
				'tag'           => '[CAMPAIGN_OVERVIEW_URL]',
				'description'   => 'The campaign overview url',
			),
		),
	),
	array(
		'name'      => 'Campaign has been blocked',
		'slug'      => 'campaign-has-been-blocked',
		'subject'   => 'A campaign has been blocked!',
		'content'   => '',
		'tags'      => array(
			array(
				'tag'           => '[CAMPAIGN_OVERVIEW_URL]',
				'description'   => 'The campaign overview url',
			),
		),
	),
	array(
		'name'      => 'Campaign stats',
		'slug'      => 'campaign-stats',
		'subject'   => 'The campaign [CAMPAIGN_NAME] has finished sending, here are the stats',
		'content'   => '',
		'tags'      => array(
			array(
				'tag'           => '[CAMPAIGN_NAME]',
				'description'   => 'The campaign name',
			),
			array(
				'tag'           => '[CAMPAIGN_OVERVIEW_URL]',
				'description'   => 'The campaign overview url',
			),
			array(
				'tag'           => '[STATS_TABLE]',
				'description'   => 'The html table containing the stats',
			),
		),
	),
	array(
		'name'      => 'Campaign share reports access',
		'slug'      => 'campaign-share-reports-access',
		'subject'   => 'Campaign share reports access!',
		'content'   => '',
		'tags'      => array(
			array(
				'tag'           => '[CAMPAIGN_NAME]',
				'description'   => 'The campaign name',
			),
			array(
				'tag'           => '[CAMPAIGN_REPORTS_URL]',
				'description'   => 'The campaign reports url',
			),
			array(
				'tag'           => '[CAMPAIGN_REPORTS_PASSWORD]',
				'description'   => 'The password to view the campaign reports',
			),
		),
	),
	array(
		'name'      => 'Customer confirm registration',
		'slug'      => 'customer-confirm-registration',
		'subject'   => 'Please confirm your account!',
		'content'   => '',
		'tags'      => array(
			array(
				'tag'           => '[CONFIRMATION_URL]',
				'description'   => 'The confirmation url',
			),
		),
	),
	array(
		'name'      => 'New customer registration',
		'slug'      => 'new-customer-registration',
		'subject'   => 'New customer registration!',
		'content'   => '',
		'tags'      => array(
			array(
				'tag'           => '[CUSTOMER_INFO]',
				'description'   => 'The information related to this customer',
			),
			array(
				'tag'           => '[CUSTOMER_URL]',
				'description'   => 'The url to view the customer',
			),
		),
	),
	array(
		'name'      => 'New list subscriber',
		'slug'      => 'new-list-subscriber',
		'subject'   => 'New list subscriber!',
		'content'   => '',
		'tags'      => array(
			array(
				'tag'           => '[CUSTOMER_INFO]',
				'description'   => 'The information related to this customer',
			),
			array(
				'tag'           => '[CUSTOMER_URL]',
				'description'   => 'The url to view the customer',
			),
		),
	),
	array(
		'name'      => 'Email blacklist import finished',
		'slug'      => 'email-blacklist-import-finished',
		'subject'   => 'Email blacklist import has finished!',
		'content'   => '',
		'tags'      => array(
			array(
				'tag'           => '[USER_NAME]',
				'description'   => 'The name of the user receiving the email',
			),
			array(
				'tag'           => '[FILE_NAME]',
				'description'   => 'The file name which finished importing',
			),
			array(
				'tag'           => '[OVERVIEW_URL]',
				'description'   => 'The overview url',
			),
		),
	),
	array(
		'name'      => 'Auto update notification',
		'slug'      => 'auto-update-notification',
		'subject'   => 'Automatic update notification!',
		'content'   => '',
		'tags'      => array(
			array(
				'tag'           => '[LOGS]',
				'description'   => 'The auto-update logs',
			),
		),
	),
	array(
		'name'      => 'Email blacklist monitor results',
		'slug'      => 'email-blacklist-monitor-results',
		'subject'   => 'Blacklist monitor results for: [MONITOR_NAME]',
		'content'   => '',
		'tags'      => array(
			array(
				'tag'           => '[MONITOR_NAME]',
				'description'   => 'The name of the monitor',
			),
			array(
				'tag'           => '[COUNT]',
				'description'   => 'The number of records processed',
			),
			array(
				'tag'           => '[SUCCESS_COUNT]',
				'description'   => 'The number of records deleted with success',
			),
			array(
				'tag'           => '[ERROR_COUNT]',
				'description'   => 'The number of records which shown errors while removing',
			)
		),
	),
	array(
		'name'      => 'Suppression list import finished',
		'slug'      => 'suppression-list-import-finished',
		'subject'   => 'Suppression list import has finished!',
		'content'   => '',
		'tags'      => array(
			array(
				'tag'           => '[CUSTOMER_NAME]',
				'description'   => 'The name of the custyomer receiving the email',
			),
			array(
				'tag'           => '[LIST_NAME]',
				'description'   => 'The list name which finished importing',
			),
			array(
				'tag'           => '[OVERVIEW_URL]',
				'description'   => 'The overview url',
			),
		),
	),
	array(
		'name'      => 'Order invoice',
		'slug'      => 'order-invoice',
		'subject'   => 'Your requested invoice - [REF]!',
		'content'   => '',
		'tags'      => array(
			array(
				'tag'           => '[REF]',
				'description'   => 'Invoice reference',
			),
			array(
				'tag'           => '[CUSTOMER_NAME]',
				'description'   => 'The name of the custyomer receiving the email',
			),
		),
	),
	array(
		'name'      => 'List import finished',
		'slug'      => 'list-import-finished',
		'subject'   => 'List import has finished!',
		'content'   => '',
		'tags'      => array(
			array(
				'tag'           => '[CUSTOMER_NAME]',
				'description'   => 'The name of the custyomer receiving the email',
			),
			array(
				'tag'           => '[LIST_NAME]',
				'description'   => 'The list name which finished importing',
			),
			array(
				'tag'           => '[OVERVIEW_URL]',
				'description'   => 'The overview url',
			),
		),
	),
	array(
		'name'      => 'Password reset request',
		'slug'      => 'password-reset-request',
		'subject'   => 'Password reset request!',
		'content'   => '',
		'tags'      => array(
			array(
				'tag'           => '[CONFIRMATION_URL]',
				'description'   => 'The url where to confirm the reset of the password',
			),
		),
	),
	array(
		'name'      => 'New login info',
		'slug'      => 'new-login-info',
		'subject'   => 'Your new login info!',
		'content'   => '',
		'tags'      => array(
			array(
				'tag'           => '[LOGIN_EMAIL]',
				'description'   => 'The login email',
			),
			array(
				'tag'           => '[LOGIN_PASSWORD]',
				'description'   => 'The login password',
			),
			array(
				'tag'           => '[LOGIN_URL]',
				'description'   => 'The login url',
			),
		),
	),
	array(
		'name'      => 'New order placed - user',
		'slug'      => 'new-order-placed-user',
		'subject'   => 'A new order has been placed!',
		'content'   => '',
		'tags'      => array(
			array(
				'tag'           => '[USER_NAME]',
				'description'   => 'The name of the user which will receive the notification',
			),
			array(
				'tag'           => '[CUSTOMER_NAME]',
				'description'   => 'The name of the customer who made the order',
			),
			array(
				'tag'           => '[PLAN_NAME]',
				'description'   => 'The plan that has been bought',
			),
			array(
				'tag'           => '[ORDER_SUBTOTAL]',
				'description'   => 'The order subtotal amount, formatted',
			),
			array(
				'tag'           => '[ORDER_TAX]',
				'description'   => 'The order tax amount, formatted',
			),
			array(
				'tag'           => '[ORDER_DISCOUNT]',
				'description'   => 'The order discount, formatted',
			),
			array(
				'tag'           => '[ORDER_TOTAL]',
				'description'   => 'The order total, formatted',
			),
			array(
				'tag'           => '[ORDER_STATUS]',
				'description'   => 'The status of the order',
			),
			array(
				'tag'           => '[ORDER_OVERVIEW_URL]',
				'description'   => 'The url where this order can be seen',
			),
		),
	),
	array(
		'name'      => 'New order placed - customer',
		'slug'      => 'new-order-placed-customer',
		'subject'   => 'Your order details!',
		'content'   => '',
		'tags'      => array(
			array(
				'tag'           => '[CUSTOMER_NAME]',
				'description'   => 'The name of the customer who made the order',
			),
			array(
				'tag'           => '[PLAN_NAME]',
				'description'   => 'The plan that has been bought',
			),
			array(
				'tag'           => '[ORDER_SUBTOTAL]',
				'description'   => 'The order subtotal amount, formatted',
			),
			array(
				'tag'           => '[ORDER_TAX]',
				'description'   => 'The order tax amount, formatted',
			),
			array(
				'tag'           => '[ORDER_DISCOUNT]',
				'description'   => 'The order discount, formatted',
			),
			array(
				'tag'           => '[ORDER_TOTAL]',
				'description'   => 'The order total, formatted',
			),
			array(
				'tag'           => '[ORDER_STATUS]',
				'description'   => 'The status of the order',
			),
			array(
				'tag'           => '[ORDER_OVERVIEW_URL]',
				'description'   => 'The url where this order can be seen',
			),
		),
	),
	array(
		'name'      => 'Account approved',
		'slug'      => 'account-approved',
		'subject'   => 'Your account has been approved!',
		'content'   => '',
		'tags'      => array(
			array(
				'tag'           => '[LOGIN_URL]',
				'description'   => 'The url to login page',
			),
		),
	),
	array(
		'name'      => 'Account details',
		'slug'      => 'account-details',
		'subject'   => 'Your account details!',
		'content'   => '',
		'tags'      => array(
			array(
				'tag'           => '[LOGIN_URL]',
				'description'   => 'The url to login page',
			),
			array(
				'tag'           => '[LOGIN_EMAIL]',
				'description'   => 'The email used for login',
			),
			array(
				'tag'           => '[LOGIN_PASSWORD]',
				'description'   => 'The password for login',
			),
		),
	),
	array(
		'name'      => 'List subscriber unsubscribed',
		'slug'      => 'list-subscriber-unsubscribed',
		'subject'   => 'List subscriber unsubscribed!',
		'content'   => '',
		'tags'      => array(
			array(
				'tag'           => '[LIST_NAME]',
				'description'   => 'The name of the e mail list',
			),
			array(
				'tag'           => '[SUBSCRIBER_EMAIL]',
				'description'   => 'The email address of the subscriber',
			),
		),
	),
	array(
		'name'      => 'Confirm block email request',
		'slug'      => 'confirm-block-email-request',
		'subject'   => 'Confirm the block email request!',
		'content'   => '',
		'tags'      => array(
			array(
				'tag'           => '[CONFIRMATION_URL]',
				'description'   => 'The url where to confirm the request',
			),
		),
	),
	array(
		'name'      => 'Forward campaign to a friend',
		'slug'      => 'forward-campaign-friend',
		'subject'   => 'Your friend [FROM_NAME] thought you might like this!',
		'content'   => '',
		'tags'      => array(
			array(
				'tag'           => '[TO_NAME]',
				'description'   => 'The name to which this email is addresssed to',
			),
			array(
				'tag'           => '[FROM_NAME]',
				'description'   => 'The name where this email originates from',
			),
			array(
				'tag'           => '[MESSAGE]',
				'description'   => 'Additional message set by [FROM_NAME] for [TO_NAME]',
			),
			array(
				'tag'           => '[CAMPAIGN_URL]',
				'description'   => 'The url to the forwarded campaign',
			),
		),
	),
	array(
		'name'      => 'New abuse report',
		'slug'      => 'new-abuse-report',
		'subject'   => 'New abuse report!',
		'content'   => '',
		'tags'      => array(
			array(
				'tag'           => '[CUSTOMER_NAME]',
				'description'   => 'The name of the customer this email is addressed to',
			),
			array(
				'tag'           => '[CAMPAIGN_NAME]',
				'description'   => 'The name of the campaign for which the abuse report is made',
			),
			array(
				'tag'           => '[ABUSE_REPORTS_URL]',
				'description'   => 'The url to view the abuse reports',
			),
		),
	),
    array(
        'name'      => 'Scheduled inactive customers',
        'slug'      => 'scheduled-inactive-customers',
        'subject'   => 'Scheduled inactive customers',
        'content'   => '',
        'tags'      => array(
            array(
                'tag'           => '[CUSTOMERS_LIST]',
                'description'   => 'The customers that were marked as inactive as per your settings',
            ),
        ),
    ),
);