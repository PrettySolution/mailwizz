<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * Performance levels definition
 * 
 * DO NOT CHANGE THIS FILE IN ANY WAY, REALLY, DON'T!
 *  
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com> 
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.3.5.5
 */
    
$performanceLevels = array(
    'MW_PERF_LVL_DISABLE_DS_LOG_USAGE'                   => 2, // disable delivery server log usage
    'MW_PERF_LVL_DISABLE_CUSTOMER_QUOTA_CHECK'           => 4, // disable customer quota check
    'MW_PERF_LVL_DISABLE_DS_QUOTA_CHECK'                 => 8, // disable delivery server quota check
    'MW_PERF_LVL_DISABLE_DS_CAN_SEND_TO_DOMAIN_OF_CHECK' => 16, // disable checking if can send to domain of the email address
    'MW_PERF_LVL_DISABLE_SUBSCRIBER_BLACKLIST_CHECK'     => 32, // disable checking emails against blacklist,
    // since 1.3.6.2
    'MW_PERF_LVL_DISABLE_NEW_BLACKLIST_RECORDS'          => 64, // do not save new emails in the email blacklist,
    'MW_PERF_LVL_DISABLE_CUSTOMER_NEW_BLACKLIST_RECORDS' => 128, // do not save new email in the customer email blacklist,
    'MW_PERF_LVL_ENABLE_SUBSCRIBER_FIELD_CACHE'          => 256, // whether to force using the subscriber field cache feature.
);

foreach ($performanceLevels as $constName => $constValue) {
    defined($constName) or define($constName, $constValue);
}

unset($performanceLevels, $constName, $constValue);