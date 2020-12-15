<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * Custom application main configuration file
 * 
 * This file can be used to overload config/components/etc
 * 
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com> 
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.1
 */
    
return array(

    // application components
    'components' => array(
        'db' => array(
            'connectionString'  => '{DB_CONNECTION_STRING}',
            'username'          => '{DB_USER}',
            'password'          => '{DB_PASS}',
            'tablePrefix'       => '{DB_PREFIX}',
        ),
    ),
    // params
    'params' => array(
        'email.custom.header.prefix' => '{EMAILS_CUSTOM_HEADER_PREFIX}'
    ),
);