<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * CommonHelper
 *
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.1
 */

class CommonHelper
{
    /**
     * CommonHelper::getQueriesFromSqlFile()
     *
     * @param string $sqlFile
     * @param string $dbPrefix
     * @return array
     */
    public static function getQueriesFromSqlFile($sqlFile, $dbPrefix = null)
    {
        if (!is_file($sqlFile) || !is_readable($sqlFile)) {
            return array();
        }

        if (!empty($dbPrefix)) {
            $searchReplace = array(
                'CREATE TABLE IF NOT EXISTS `'  => 'CREATE TABLE IF NOT EXISTS `' . $dbPrefix,
                'CREATE TABLE `'                => 'CREATE TABLE `' . $dbPrefix,
                'DROP TABLE IF EXISTS `'        => 'DROP TABLE IF EXISTS `' . $dbPrefix,
                'DROP TABLE `'                  => 'DROP TABLE `' . $dbPrefix,
                'INSERT INTO `'                 => 'INSERT INTO `' . $dbPrefix,
                'ALTER TABLE `'                 => 'ALTER TABLE `' . $dbPrefix,
                'RENAME TABLE `'                => 'RENAME TABLE `' . $dbPrefix,
                'ALTER IGNORE TABLE `'          => 'ALTER IGNORE TABLE `' . $dbPrefix,
                'REFERENCES `'                  => 'REFERENCES `' . $dbPrefix,
                'UPDATE `'                      => 'UPDATE `' . $dbPrefix,
                ' FROM `'                       => ' FROM `' . $dbPrefix,
                ' TO `'                         => ' TO `' . $dbPrefix,
            );
            $search  = array_keys($searchReplace);
            $replace = array_values($searchReplace);
        }

        $queries = array();
        $query   = '';
        $lines   = file($sqlFile);

        foreach ($lines as $line) {

            if (empty($line) || strpos($line, '--') === 0 || strpos($line, '#') === 0 || strpos($line, '/*!') === 0) {
                continue;
            }

            $query .= $line;

            if (!preg_match('/;\s*$/', $line)) {
                continue;
            }

            if (!empty($dbPrefix)) {
                $query = str_replace($search, $replace, $query);
            }

            if (!empty($query)) {
                $queries[] = $query;
            }

            $query = '';
        }

        return $queries;
    }

    /**
     * CommonHelper::functionExists()
     *
     * @param string $name
     * @return bool
     */
    public static function functionExists($name)
    {
        static $_exists     = array();
        static $_disabled   = null;
        static $_shDisabled = null;

        if (isset($_exists[$name]) || array_key_exists($name, $_exists)) {
            return $_exists[$name];
        }

        if (!function_exists($name)) {
            return $_exists[$name] = false;
        }

        if ($_disabled === null) {
            $_disabled = ini_get('disable_functions');
            $_disabled = explode(',', $_disabled);
            $_disabled = array_map('trim', $_disabled);
        }

        if (is_array($_disabled) && in_array($name, $_disabled)) {
            return $_exists[$name] = false;
        }

        if ($_shDisabled === null) {
            $_shDisabled = ini_get('suhosin.executor.func.blacklist');
            $_shDisabled = explode(',', $_shDisabled);
            $_shDisabled = array_map('trim', $_shDisabled);
        }

        if (is_array($_shDisabled) && in_array($name, $_shDisabled)) {
            return $_exists[$name] = false;
        }

        return $_exists[$name] = true;
    }

    /**
     * CommonHelper::findPhpCliPath()
     *
     * @since 1.3.3.1
     * @return string
     */
    public static function findPhpCliPath()
    {
        static $cliPath;

        if ($cliPath !== null) {
            return $cliPath;
        }
        
        // since 1.7.0
        if (defined('PHP_BINDIR') && PHP_BINDIR) {
        	$variants = array('php-cli', 'php');
        	foreach ($variants as $variant) {
        		if (@is_file($cliPath = PHP_BINDIR . DIRECTORY_SEPARATOR . $variant)) {
        			return $cliPath;
		        }
	        }
        }
        //

        $cliPath = '/usr/bin/php';

        if (!self::functionExists('exec')) {
            return $cliPath;
        }

        $variants = array('php-cli', 'php5-cli', 'php5', 'php');
        foreach ($variants as $variant) {
            $out = @exec(sprintf('command -v %s 2>&1', $variant), $lines, $status);
            if ($status != 0 || empty($out)) {
                continue;
            }
            $cliPath = $out;
            break;
        }

        return $cliPath;
    }

    // since 1.3.5.6
    public static function getIpAddressInfoUrl($ipAddress)
    {
        $url = "http://who.is/whois-ip/ip-address/" . $ipAddress;
        return Yii::app()->hooks->applyFilters('get_ip_address_info_url', $url, $ipAddress);
    }

    // since 1.3.5.6
    public static function getUserAgentInfoUrl($userAgent)
    {
        $url = 'javascript:;';
        return Yii::app()->hooks->applyFilters('get_user_agent_info_url', $url, $userAgent);
    }

    // since 1.3.5.9
    public static function getArrayFromString($string, $separator = ',')
    {
        $string = trim($string);
        if (empty($string)) {
            return array();
        }
        $array = explode($separator, $string);
        $array = array_map('trim', $array);
        $array = array_unique($array);
        return $array;
    }

    // since 1.3.5.9
    public static function getStringFromArray(array $array, $glue = ', ')
    {
        if (empty($array)) {
            return '';
        }
        return implode($glue, $array);
    }

    // since 1.3.5.9
    public static function getCurrentHostUrl($appendThis = null)
    {
        return Yii::app()->apps->getCurrentHostUrl($appendThis);
    }
    
    // since 1.3.7.9
    public static function formatBytes($bytes, $precision = 2) 
    {
        $units = array('B', 'KB', 'MB', 'GB', 'TB');

        $bytes = max($bytes, 0);
        $pow   = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow   = min($pow, count($units) - 1);

        // Uncomment one of the following alternatives
        // $bytes /= pow(1024, $pow);
        $bytes /= (1 << (10 * $pow)); 

        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}
