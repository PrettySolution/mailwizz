<?php

/**
 * Frontend application bootstrap file
 *
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.0
 */

// define the type of application we are creating.
define('MW_APP_NAME', 'frontend');

// and start an instance of it.
require_once(dirname(__FILE__) . '/apps/init.php');
