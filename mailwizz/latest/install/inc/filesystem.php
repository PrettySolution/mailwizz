<?php defined('MW_INSTALLER_PATH') || exit('No direct script access allowed');

/**
 * Filesystem requirements file
 * 
 * List of requirements (name, required or not, result, used by, memo)
 * Based on Yii Framework requirements checker.
 * 
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com> 
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.0
 */
 
return array(

    array(
        'Main configuration directory',
        true,
        $dir = MW_APPS_PATH . '/common/config',
        file_exists($dir) && is_dir($dir) && (chmod($dir, 0777) || is_writable($dir)),
        'The directory must be writable by the web server (chmod 0777).'
    ),
    
    array(
        'Runtime directory',
        true,
        $dir = MW_APPS_PATH . '/common/runtime',
        file_exists($dir) && is_dir($dir) && (chmod($dir, 0777) || is_writable($dir)),
        'The directory must be writable by the web server (chmod -R 0777).'
    ),
    
    array(
        'Backend assets cache',
        true,
        $dir = MW_ROOT_PATH . '/backend/assets/cache',
        file_exists($dir) && is_dir($dir) && (chmod($dir, 0777) || is_writable($dir)),
        'The directory must be writable by the web server (chmod -R 0777).'
    ),
    
    array(
        'Customer assets cache',
        true,
        $dir = MW_ROOT_PATH . '/customer/assets/cache',
        file_exists($dir) && is_dir($dir) && (chmod($dir, 0777) || is_writable($dir)),
        'The directory must be writable by the web server (chmod -R 0777).'
    ),
    
    array(
        'Frontend assets cache',
        true,
        $dir = MW_ROOT_PATH . '/frontend/assets/cache',
        file_exists($dir) && is_dir($dir) && (chmod($dir, 0777) || is_writable($dir)),
        'The directory must be writable by the web server (chmod -R 0777).'
    ),
    
    array(
        'Frontend files',
        true,
        $dir = MW_ROOT_PATH . '/frontend/assets/files',
        file_exists($dir) && is_dir($dir) && (chmod($dir, 0777) || is_writable($dir)),
        'The directory must be writable by the web server (chmod -R 0777).'
    ),
    
    array(
        'Frontend gallery',
        true,
        $dir = MW_ROOT_PATH . '/frontend/assets/gallery',
        file_exists($dir) && is_dir($dir) && (chmod($dir, 0777) || is_writable($dir)),
        'The directory must be writable by the web server (chmod -R 0777).'
    ),
    
    array(
        'Extensions',
        true,
        $dir = MW_APPS_PATH . '/extensions',
        file_exists($dir) && is_dir($dir) && (chmod($dir, 0777) || is_writable($dir)),
        'The directory must be writable by the web server (chmod -R 0777).'
    ),
);