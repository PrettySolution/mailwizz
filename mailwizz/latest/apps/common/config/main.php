<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * Common application main configuration file
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
    'basePath'          => Yii::getPathOfAlias('common'),
    'runtimePath'       => Yii::getPathOfAlias('common.runtime'),
    'name'              => 'MailWizz', // never change this
    'id'                => 'MailWizz', // never change this
    'sourceLanguage'    => 'en',
    'language'          => 'en',
    'defaultController' => '',
    'charset'           => 'utf-8',
    'timeZone'          => 'UTC', // make sure we stay UTC

    // preloading components
    'preload' => array(
        'log', 'systemInit'
    ),

    // autoloading model and component classes
    'import' => array(
        'common.components.*',
        'common.components.db.*',
        'common.components.db.ar.*',
        'common.components.db.behaviors.*',
        'common.components.helpers.*',
        'common.components.init.*',
        'common.components.managers.*',
        'common.components.mutex.*',
        'common.components.mailer.*',
        'common.components.utils.*',
        'common.components.web.*',
        'common.components.web.auth.*',
        'common.components.web.response.*',
        'common.components.web.widgets.*',
        'common.models.*',
        'common.models.option.*',

        'common.vendors.Urlify.*',
    ),

    // application components
    'components' => array(

        // will be merged with the custom one to get connection string/username/password and table prefix
        'db' => array(
            'connectionString'      => '{DB_CONNECTION_STRING}',
            'username'              => '{DB_USER}',
            'password'              => '{DB_PASS}',
            'tablePrefix'           => '{DB_PREFIX}',
            'emulatePrepare'        => true,
            'charset'               => 'utf8',
            'schemaCachingDuration' => MW_CACHE_TTL,
            'enableParamLogging'    => MW_DEBUG,
            'enableProfiling'       => MW_DEBUG,
            'schemaCacheID'         => 'cache',
            'queryCacheID'          => 'cache',
            'initSQLs'              => array(
                'SET time_zone="+00:00"',
                'SET NAMES utf8',
                'SET SQL_MODE=""',
            ), // make sure we stay UTC and utf-8,
            'autoConnect'           => true,
        ),

        'request'=>array(
            'class'             => 'common.components.web.BaseHttpRequest',
            'csrfCookie'        => array(
                'httpOnly'      => true,
            ),
            'csrfTokenName'           => 'csrf_token',
            'enableCsrfValidation'    => true,
            'enableCookieValidation'  => true,
        ),

        'cache' => array(
            'class'     => 'system.caching.CFileCache',
	        'keyPrefix' => md5(MW_ROOT_PATH),
        ),

        'dbCache' => array(
	        'class'                 => 'system.caching.CDbCache',
	        'keyPrefix'             => sha1(MW_ROOT_PATH),
	        'connectionID'          => 'db',
	        'cacheTableName'        => '{{cache}}',
	        'autoCreateCacheTable'  => false,
        ),
        
        'urlManager' => array(
            'class'          => 'CUrlManager',
            'urlFormat'      => 'path',
            'showScriptName' => true,
            'caseSensitive'  => false,
            'urlSuffix'      => null,
            'rules'          => array(),
        ),

        'messages' => array(
            'class'     => 'CPhpMessageSource',
            'basePath'  => Yii::getPathOfAlias('common.messages'),
        ),

        'log' => array(
            'class' => 'CLogRouter',
            'routes' => array(
                array(
                    'class'   => 'common.components.logging.FileLogRoute',
                    'levels'  => 'error',
                    // 'categories' => '!exception.CHttpException.404',
                    'enabled' => true,
                ),
                array(
                    'class'   => 'CWebLogRoute',
                    'filter'  => 'CLogFilter',
                    'enabled' => MW_DEBUG,
                ),
                array(
                    'class'   => 'CProfileLogRoute',
                    'report'  => 'summary',
                    'enabled' => MW_DEBUG,
                ),
            ),
        ),

        'errorHandler' => array(
            'errorAction' => 'site/error',
        ),

        'format' => array(
            'class' => 'system.utils.CLocalizedFormatter',
        ),

        'passwordHasher' => array(
            'class' =>  'common.components.utils.PasswordHasher',
        ),

        'ioFilter' => array(
            'class' => 'common.components.utils.IOFilter',
        ),

        'hooks' => array(
            'class' => 'common.components.managers.HooksManager',
        ),

        'options' => array(
            'class'     => 'common.components.managers.OptionsManager',
            'cacheTtl'  => MW_CACHE_TTL,
        ),

        'notify' => array(
            'class' => 'common.components.managers.NotifyManager',
        ),

        'mailer' => array(
            'class' => 'common.components.mailer.Mailer',
        ),

        'mutex' => array(
            'class'     => 'common.components.mutex.FileMutex',
            'keyPrefix' => md5(MW_ROOT_PATH),
        ),
        
        'extensionMimes' => array(
            'class' => 'common.components.utils.FileExtensionMimes',
        ),

        'extensionsManager' => array(
            'class'    => 'common.components.managers.ExtensionsManager',
            'paths'    => array(
                array(
                    'alias'    => 'common.extensions',
                    'priority' => -1000
                ),
                array(
                    'alias'    => 'extensions',
                    'priority' => -999
                ),
            ),
            'coreExtensionsList' => array(),
        ),

        'systemInit' => array(
            'class' => 'common.components.init.SystemInit',
        ),
    ),

    'modules' => array(

    	/*
    	'gii' => array(
            'class'     => 'system.gii.GiiModule',
            'password'  => 'mailwizz',
            'ipFilters' => array('*'),
        ),
    	*/

    ),

    // application-level parameters that can be accessed
    // using Yii::app()->params['paramName']
    'params' => array(

        // https://kb.mailwizz.com/articles/enable-use-temporary-queue-tables-sending-campaigns/
        'send.campaigns.command.useTempQueueTables'         => false,
        'send.campaigns.command.tempQueueTables.copyAtOnce' => 500,

        // if you change this param, you know what you are doing and the implications!
        'email.custom.header.prefix' => 'X-Mw-',

        // dkim custom selectors for sending domains
        'email.custom.dkim.selector'      => 'mailer',
        'email.custom.dkim.full_selector' => 'mailer._domainkey',
        'email.custom.dkim.key.size'      => 2048,
        
        // since 1.7.3 
	    'email.custom.returnPath.enabled' => true,

        // since 1.6.3 - default email template stub
        'email.templates.stub' => '<!DOCTYPE html><html><head><meta charset="utf-8"/><title></title></head><body></body></html>',
        
        // use tidy for templates parsing
        'email.templates.tidy.enabled' => true,
        
        // tidy default options
        'email.templates.tidy.options' => array(
            'indent'            => true,
            'output-xhtml'      => true,
            'wrap'              => 200,
            'fix-bad-comments'  => true,
        ),

        // custom campaign tags prefix
        'customer.campaigns.custom_tags.prefix'  => 'CCT_',
        'customer.campaigns.extra_tags.prefix'   => 'CET_',
        
        // since 1.3.6.6 - cache directories
        'cache.directory.aliases' => array(
            'root.backend.assets.cache',
            'root.customer.assets.cache',
            'root.frontend.assets.cache',
            'common.runtime.cache',
        ),
        
        // since 1.3.7.2
        'store.enabled'     => true,
        'store.cache.count' => 0,
        
        // since 1.4.4
        'campaign.delivery.giveup.retries'                        => 3,
        'campaign.delivery.logs.delete.days_back'                 => 5,
        'campaign.delivery.logs.delete.process_campaigns_at_once' => 50,
        'campaign.delivery.logs.delete.process_logs_at_once'      => 5000,

	    // since 1.7.9
        'campaign.bounce.logs.delete.days_back'                 => 5,
        'campaign.bounce.logs.delete.process_campaigns_at_once' => 50,
        'campaign.bounce.logs.delete.process_logs_at_once'      => 5000,

	    // since 1.7.9
        'campaign.open.logs.delete.days_back'                 => 5,
        'campaign.open.logs.delete.process_campaigns_at_once' => 50,
        'campaign.open.logs.delete.process_logs_at_once'      => 5000,

	    // since 1.7.9
        'campaign.click.logs.delete.days_back'                 => 5,
        'campaign.click.logs.delete.process_campaigns_at_once' => 50,
        'campaign.click.logs.delete.process_logs_at_once'      => 5000,
        
        // since 1.5.2
        'campaign.stats.processor.enable_cache' => true,
        
        // since 1.4.5
        'campaign.delivery.sending.check_paused_realtime' => true,
        
        // since 1.4.5
        'ip.location.maxmind.db.path' => MW_ROOT_PATH . '/apps/common/data/maxmind/GeoLite2-City.mmdb',
        'ip.location.maxmind.db.url'  => 'http://geolite.maxmind.com/download/geoip/database/GeoLite2-City.tar.gz',
        
        // since 1.5.1
        'customer.pulsate_info.enabled' => true,
        'backend.pulsate_info.enabled'  => true,
        
        // since 1.5.2
        'delivery_servers.show_provider_url' => true,
	    
	    // since 1.6.4
	    'console.save_command_history' => true,

        // since 1.8.5
        'servers.imap.search.mailboxes' => array('INBOX', 'JUNK', 'SPAM'),
	    
	    // since 1.9.3
	    'lists.counters.cache.adapter' => 'dbCache'
    ),
);
