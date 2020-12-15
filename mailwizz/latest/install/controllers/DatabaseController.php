<?php defined('MW_INSTALLER_PATH') || exit('No direct script access allowed');

/**
 * DatabaseController
 * 
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com> 
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.0
 */
 
class DatabaseController extends Controller
{
    public function actionIndex()
    {
        if (!getSession('filesystem') || !getSession('license_data')) {
            redirect('index.php?route=filesystem');
        }
        
        $this->validateRequest();
        
        if (getSession('database')) {
            redirect('index.php?route=admin');
        }
        
        $this->data['pageHeading'] = 'Database import';
        $this->data['breadcrumbs'] = array(
            'Database import' => 'index.php?route=database',
        );
        
        $this->render('database');
    }
    
    protected function validateRequest()
    {
        if (!getPost('next')) {
            return;
        }

        $dbHost     = trim(getPost('hostname'));
        $dbPort     = trim(getPost('port'));
        $dbName     = trim(getPost('dbname'));
        $dbUser     = trim(getPost('username'));
        $dbPass     = isset($_POST['password']) ? addcslashes($_POST['password'], "'") : null; // keep original
        $dbPrefix   = trim(getPost('prefix'));
        
        if (empty($dbHost)) {
            $this->addError('hostname', 'Please provide your database hostname!');
        } elseif (!preg_match('/^([a-z0-9\_\-\.=\/\\\]+)$/i', $dbHost)) {
            $this->addError('hostname', 'The hostname contains invalid characters!');
        }
        
        if (!empty($dbPort) && !is_numeric($dbPort)) {
            $this->addError('port', 'The port value must be a number, usualy 3306!');
        }
        
        if (empty($dbName)) {
            $this->addError('dbname', 'Please provide your database name!');
        } elseif (!preg_match('/^([a-z0-9\_\-]+)$/i', $dbName)) {
            $this->addError('dbname', 'Database name must contain only letters, numbers and underscores!');
        }
        
        if (!empty($dbPrefix) && !preg_match('/^([a-z0-9\_]+)$/', $dbPrefix)) {
            $this->addError('prefix', 'Tables prefix must contain only lowercase letters, numbers and underscores!');
        }
        
        if ($this->hasErrors()) {
            return $this->addError('general', 'Your form has a few errors, please fix them and try again!');
        }
        
        try {
            if (strpos($dbHost, 'unix_socket=') === 0) {
                $dbConnectionString = sprintf('mysql:%s;dbname=%s;', $dbHost, $dbName); 
            } else {
                $dbConnectionString = sprintf('mysql:host=%s;dbname=%s', $dbHost, $dbName);
                if (!empty($dbPort)) {
                    $dbConnectionString = sprintf('mysql:host=%s;port=%d;dbname=%s', $dbHost, (int)$dbPort, $dbName);
                }
            }
            $dbh = new PDO($dbConnectionString, $dbUser, $dbPass);
            $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (Exception $e) {
            return $this->addError('general', $e->getMessage());
        }

        $searchReplace = array(
            '{DB_CONNECTION_STRING}'        => $dbConnectionString,
            '{DB_USER}'                     => $dbUser,
            '{DB_PASS}'                     => $dbPass,
            '{DB_PREFIX}'                   => $dbPrefix,
            '{EMAILS_CUSTOM_HEADER_PREFIX}' => sprintf('X-%s-', ucfirst(StringHelper::random(4, true, true))),
        );
        
        $contents = @file_get_contents(MW_MAIN_CONFIG_FILE_DEFINITION);
        if (empty($contents)) {
            return $this->addError('general', 'Unable to open the definition configuration file!');
        } 
        
        $contents = str_replace(array_keys($searchReplace), array_values($searchReplace), $contents);
        if (!@file_put_contents(MW_MAIN_CONFIG_FILE, $contents)) {
            return $this->addError('general', 'Unable to write the configuration file!');
        }
        
        // try to force the mode of the file/dir
        @chmod(dirname(MW_MAIN_CONFIG_FILE), 0755);
        @chmod(MW_MAIN_CONFIG_FILE, 0555);
        
        setSession('config_file_created', 1);
        
        $dbh->exec('SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0');
        $dbh->exec('SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0');
        $dbh->exec('SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE=""');
        
        $dbh->exec('SET time_zone="+00:00"');
        $dbh->exec('SET NAMES utf8');
        
        $error = false;
        
        try {
            // Always make sure each FK constraint has the fk_ prefix to avoid some nasty bugs i have found across multiple shared hosts!!!
            $sqlFiles = array(
                MW_APPS_PATH . '/common/data/install-sql/schema.sql',
                MW_APPS_PATH . '/common/data/install-sql/insert.sql',
                MW_APPS_PATH . '/common/data/install-sql/country-zone.sql',
            );
        
            foreach ($sqlFiles as $sqlFile) {
                $dbh->beginTransaction(); 
                $queries = (array)CommonHelper::getQueriesFromSqlFile($sqlFile, $dbPrefix);
                foreach ($queries as $query) {
                    $dbh->exec($query);
                } 
                $dbh->commit();  
            }

        } catch (Exception $e) {
            $dbh->rollBack();
            $this->addError('general', $e->getMessage());
            $error = true;
        }
        
        $dbh->exec('SET SQL_MODE=@OLD_SQL_MODE');
        $dbh->exec('SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS');
        $dbh->exec('SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS');
        
        if ($error) {
            return;
        }
        
        // insert the license info.
        $licenseData = (array)getSession('license_data', array());
        foreach ($licenseData as $key => $value) {
            $sql = '
            INSERT INTO `'.$dbPrefix.'option` SET 
                `category`      = "system.license",
                `key`           = :key,
                `value`         = :value,
                `is_serialized` = 0, 
                `date_added`    = NOW(), 
                `last_updated`  = NOW()
            ';
            $sth = $dbh->prepare($sql);
            $sth->execute(array(
                ':key'      => $key,
                ':value'    => $value,
            ));
        }
        
        // insert the version
        $sth = $dbh->prepare('
            INSERT INTO `'.$dbPrefix.'option` SET 
                `category`     = "system.common", 
                `key`          = "version", 
                `value`        = :v, 
                `date_added`   = NOW(), 
                `last_updated` = NOW()
        ');
        $sth->execute(array(':v' => MW_VERSION));
        
        setSession('databaseData', array(
            'DB_CONNECTION_STRING'   => $dbConnectionString,
            'DB_USER'                => $dbUser,
            'DB_PASS'                => $dbPass,
            'DB_PREFIX'              => $dbPrefix,
        ));
        setSession('database', 1);
    }
}