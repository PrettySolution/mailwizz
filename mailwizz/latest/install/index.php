<?php

/**
 * Install bootstrap file
 *
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.0
 */

// start the session since we will hold various data in session.
session_start();

// since this is production, stay silent
ini_set('display_errors', 0);
error_reporting(0);

// make sure we have enough time and memory
set_time_limit(0);
ini_set('memory_limit', -1);

// stay utc
if (function_exists('date_default_timezone_set')) {
    date_default_timezone_set('UTC');
}

// a few constants that will be used later. Some of them for reference only
define('MW_NAME', 'MailWizz EMA');
define('MW_VERSION', '1.9.4');
define('MW_IS_CLI', php_sapi_name() == 'cli' || (!isset($_SERVER['SERVER_SOFTWARE']) && !empty($_SERVER['argv'])));
define('MW_ROOT_PATH', realpath(dirname(__FILE__) . '/..'));
define('MW_APPS_PATH', MW_ROOT_PATH . '/apps');
define('MW_INSTALLER_PATH', MW_ROOT_PATH . '/install');
define('MW_PATH', MW_ROOT_PATH);
define('MW_MAIN_CONFIG_FILE', MW_APPS_PATH . '/common/config/main-custom.php');
define('MW_MAIN_CONFIG_FILE_DEFINITION', MW_APPS_PATH . '/common/data/config/main-custom.php');

if (!isset($_SESSION['config_file_created']) && is_file(MW_MAIN_CONFIG_FILE)) {
    header('location: ../');
    exit;
}

// common helper methods
require_once MW_APPS_PATH . '/common/components/helpers/CommonHelper.php';

// string helper, to generate uids, etc
require_once MW_APPS_PATH . '/common/components/helpers/StringHelper.php';

// cron jobs display handler
require_once MW_APPS_PATH . '/common/components/helpers/CronJobDisplayHandler.php';

// icon helper methods
require_once MW_APPS_PATH . '/common/components/helpers/IconHelper.php';

// fix the client ip address and the global variables if magic quotes are enabled.
require_once MW_APPS_PATH . '/common/components/helpers/FilterVarHelper.php';
require_once MW_APPS_PATH . '/common/components/helpers/AppInitHelper.php';
if (!MW_IS_CLI) {
    AppInitHelper::fixRemoteAddress();
}

// require base functionality files.
require_once MW_INSTALLER_PATH . '/inc/functions.php';
require_once MW_INSTALLER_PATH . '/inc/Controller.php';

$route = getQuery('route');

if (empty($route)) {
    $route = 'welcome';
}

// just to make sure file inclusion won't work.
$route = str_replace(array('../', '..'), '', $route);

// find the controller and the action and apply any fallback for controller/action.
$controller = $action = null;

if (strpos($route, '/') !== false) {
    $routeParts = explode('/', $route);
    $routeParts = array_slice($routeParts, 0, 2);
    list($controller, $action) = $routeParts;
} else {
    $controller = $route;
}

$controller = formatController($controller);
if (!empty($action)) {
    $action = formatAction($action);
}

if (!is_file($controllerFile = MW_INSTALLER_PATH . '/controllers/' . $controller . '.php')) {
    $controller = formatController('welcome');
    $controllerFile = MW_INSTALLER_PATH . '/controllers/' . $controller . '.php';
}

require_once $controllerFile;
$controller = new $controller();

if (empty($action)) {
    $action = 'actionIndex';
} elseif (!method_exists($controller, $action)) {
    $action = 'actionNot_found';
}

// and finally run the controller action.
$controller->$action();
