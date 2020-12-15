<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * UpdateWorkerFor_1_2
 * 
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com> 
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.2
 */
 
class UpdateWorkerFor_1_2 extends UpdateWorkerAbstract
{
    public function run()
    {
        // run the sql from file
        $this->runQueriesFromSqlFile('1.2');

        // alter users and add a unique uid
        $command = $this->db->createCommand('SELECT user_id, user_uid FROM {{user}} WHERE user_uid = ""');
        $results = $command->queryAll();
        
        foreach ($results as $result) {
            $command = $this->db->createCommand('UPDATE {{user}} SET user_uid = :uid WHERE user_id = :id');
            $command->execute(array(
                ':uid'  => $this->generateUserUid(),
                ':id'   => (int)$result['user_id'],
            ));            
        }
        
        // alter customers and add a unique uid
        $command = $this->db->createCommand('SELECT customer_id, customer_uid FROM {{customer}} WHERE customer_uid = ""');
        $results = $command->queryAll();
        
        foreach ($results as $result) {
            $command = $this->db->createCommand('UPDATE {{customer}} SET customer_uid = :uid WHERE customer_id = :id');
            $command->execute(array(
                ':uid'  => $this->generateCustomerUid(),
                ':id'   => (int)$result['customer_id'],
            ));            
        }
        
        // add unique keys here to avoid duplicate errors.
        $command = $this->db->createCommand('ALTER TABLE `{{customer}}` ADD UNIQUE KEY `customer_uid_UNIQUE` (`customer_uid`)');
        $command->execute();
        
        $command = $this->db->createCommand('ALTER TABLE `{{user}}` ADD UNIQUE KEY `user_uid_UNIQUE` (`user_uid`)');
        $command->execute();
        
        // add a note about the new cron job
        $phpCli = CommonHelper::findPhpCliPath();
        $notify = Yii::app()->notify;
        $notify->addInfo(Yii::t('update', 'Version {version} brings a new cron job that you have to add to run once a day. After addition, it must look like: {cron}', array(
            '{version}' => '1.2',
            '{cron}'    => sprintf('<br /><strong>0 0 * * * %s -q ' . MW_ROOT_PATH . '/apps/console/console.php process-subscribers > /dev/null 2>&1</strong>', $phpCli),
        )));
    }
    
    protected function generateUserUid()
    {
        $unique  = StringHelper::uniqid();
        $command = $this->db->createCommand('SELECT user_uid FROM {{user}} WHERE user_uid = :uid');
        $row     = $command->queryRow(true, array(
            ':uid' => $unique,
        ));

        if (!empty($row['user_uid'])) {
            return $this->generateUserUid();
        }
        
        return $unique;
    }
    
    protected function generateCustomerUid()
    {
        $unique  = StringHelper::uniqid();
        $command = $this->db->createCommand('SELECT customer_uid FROM {{customer}} WHERE customer_uid = :uid');
        $row     = $command->queryRow(true, array(
            ':uid' => $unique,
        ));

        if (!empty($row['customer_uid'])) {
            return $this->generateCustomerUid();
        }
        
        return $unique;
    }
} 