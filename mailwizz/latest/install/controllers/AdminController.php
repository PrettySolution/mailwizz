<?php defined('MW_INSTALLER_PATH') || exit('No direct script access allowed');

/**
 * AdminController
 * 
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com> 
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.0
 */
 
class AdminController extends Controller
{
    public function actionIndex()
    {
        if (!getSession('database') || !getSession('databaseData') || !getSession('license_data')) {
            redirect('index.php?route=database');
        }
        
        $this->data['timezones'] = $this->getTimezones();
        
        $this->validateRequest();
        
        if (getSession('admin')) {
            redirect('index.php?route=cron');
        }
        
        $this->data['pageHeading'] = 'Admin account';
        $this->data['breadcrumbs'] = array(
            'Admin account' => 'index.php?route=admin',
        );
        
        $this->render('admin');
    }
    
    protected function validateRequest()
    {
        if (!getPost('next')) {
            return;
        }
        
        $firstName  = getPost('first_name');
        $lastName   = getPost('last_name');
        $email      = getPost('email');
        $password   = getPost('password');
        $timezone   = getPost('timezone');
        
        $createCustomer = getPost('create_customer') == 'yes';
        
        if (empty($firstName)) {
            $this->addError('first_name', 'Please supply your first name!');
        } elseif (strlen($firstName) < 2 || strlen($firstName) > 100) {
            $this->addError('first_name', 'First name length must be between 2 and 100 chars!');
        } elseif (!preg_match('/^([a-z\s\-]+)$/i', $firstName)) {
            $this->addError('first_name', 'First name must contain only letters, spaces and dashes!');
        }
        
        if (empty($lastName)) {
            $this->addError('last_name', 'Please supply your last name!');
        } elseif (strlen($lastName) < 2 || strlen($lastName) > 100) {
            $this->addError('last_name', 'Last name length must be between 2 and 100 chars!');
        } elseif (!preg_match('/^([a-z\s\-]+)$/i', $lastName)) {
            $this->addError('last_name', 'Last name must contain only letters, spaces and dashes!');
        }
        
        if (empty($email)) {
            $this->addError('email', 'Please supply your email address!');
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->addError('email', 'Please provide a valid email address!');
        }
        
        if (empty($password)) {
            $this->addError('password', 'Please supply your password!');
        } elseif (strlen($password) < 6 || strlen($lastName) > 100) {
            $this->addError('password', 'Password length must be between 6 and 100 chars!');
        }
        
        if (empty($timezone)) {
            $this->addError('timezone', 'Please supply your timezone!');
        } elseif (!in_array($timezone, array_keys($this->getTimezones()))) {
            $this->addError('timezone', 'Invalid timezone!');
        }
        
        if ($this->hasErrors()) {
            return $this->addError('general', 'Your form has a few errors, please fix them and try again!');
        }
        
        $dbConfig = getSession('databaseData');
        
        try {
            $dbh = new PDO($dbConfig['DB_CONNECTION_STRING'], $dbConfig['DB_USER'], $dbConfig['DB_PASS']);
            $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (Exception $e) {
            return $this->addError('general', $e->getMessage());
        }
        
        $dbh->exec('SET time_zone="+00:00"');
        $dbh->exec('SET NAMES utf8');
        $dbh->exec('SET SQL_MODE=""');
            
        require_once MW_APPS_PATH . '/common/vendors/Openwall/PasswordHash.php';
        $passwordHasher = new PasswordHash(13, true);
        
        $prefix = $dbConfig['DB_PREFIX'];
        $sql = '
        INSERT INTO `'.$prefix.'user` SET
            `user_uid`      = :uid, 
            `first_name`    = :fname, 
            `last_name`     = :lname, 
            `email`         = :email,
            `password`      = :password,
            `timezone`      = :tz,
            `removable`     = "no", 
            `status`        = "active", 
            `date_added`    = NOW(), 
            `last_updated`  = NOW()
        ';
        $sth = $dbh->prepare($sql);
        $sth->execute(array(
            ':uid'      => StringHelper::uniqid(),
            ':fname'    => $firstName,
            ':lname'    => $lastName,
            ':email'    => $email,
            ':password' => $passwordHasher->HashPassword($password),
            ':tz'       => $timezone,
        ));
        
        $userId = $dbh->lastInsertId();
        if (empty($userId)) {
            return $this->addError('general', 'Unable to create specified user!');
        }
        
        if ($createCustomer) {
            $sql = '
            INSERT INTO `'.$prefix.'customer` SET
                `customer_uid`  = :uid, 
                `first_name`    = :fname, 
                `last_name`     = :lname, 
                `email`         = :email,
                `password`      = :password,
                `timezone`      = :tz,
                `removable`     = "yes", 
                `status`        = "active", 
                `date_added`    = NOW(), 
                `last_updated`  = NOW()
            ';
            $sth = $dbh->prepare($sql);
            $sth->execute(array(
                ':uid'      => StringHelper::uniqid(),
                ':fname'    => $firstName,
                ':lname'    => $lastName,
                ':email'    => $email,
                ':password' => $passwordHasher->HashPassword($password),
                ':tz'       => $timezone,
            ));
        }

        setSession('admin', 1);
    }
    
    public function getTimezones()
    {
        static $_timezones;
        if ($_timezones !== null) {
            return $_timezones;
        }
        
        require_once MW_APPS_PATH . '/common/components/helpers/DateTimeHelper.php';
        return $_timezones = DateTimeHelper::getTimeZones();
    }
}