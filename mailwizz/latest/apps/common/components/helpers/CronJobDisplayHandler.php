<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * CronJobDisplayHandler
 * 
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com> 
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.3.5
 */
 
class CronJobDisplayHandler
{
    public static function getCronJobsList()
    {
        static $cronJobs;
        if ($cronJobs !== null) {
            return $cronJobs;
        }
        
        $cronJobs = array(
            array(
                'frequency'     => '* * * * *',
                'phpBinary'     => CommonHelper::findPhpCliPath(),
                'consolePath'   => MW_APPS_PATH . '/console/console.php',
                'command'       => 'send-campaigns',
                'description'   => 'Campaigns sender, runs each minute.',
            ),
            array(
                'frequency'     => '*/2 * * * *',
                'phpBinary'     => CommonHelper::findPhpCliPath(),
                'consolePath'   => MW_APPS_PATH . '/console/console.php',
                'command'       => 'send-transactional-emails',
                'description'   => 'Transactional email sender, runs once at 2 minutes.',
            ),
            array(
                'frequency'     => '*/10 * * * *',
                'phpBinary'     => CommonHelper::findPhpCliPath(),
                'consolePath'   => MW_APPS_PATH . '/console/console.php',
                'command'       => 'bounce-handler',
                'description'   => 'Bounce handler, runs once at 10 minutes.',
            ),
            array(
                'frequency'     => '*/20 * * * *',
                'phpBinary'     => CommonHelper::findPhpCliPath(),
                'consolePath'   => MW_APPS_PATH . '/console/console.php',
                'command'       => 'feedback-loop-handler',
                'description'   => 'Feedback loop handler, runs once at 20 minutes.',
            ),
            array(
                'frequency'     => '*/3 * * * *',
                'phpBinary'     => CommonHelper::findPhpCliPath(),
                'consolePath'   => MW_APPS_PATH . '/console/console.php',
                'command'       => 'process-delivery-and-bounce-log',
                'description'   => 'Delivery/Bounce processor, runs once at 3 minutes.',
            ),
            array(
                'frequency'     => '0 * * * *',
                'phpBinary'     => CommonHelper::findPhpCliPath(),
                'consolePath'   => MW_APPS_PATH . '/console/console.php',
                'command'       => 'hourly',
                'description'   => 'Various tasks, runs each hour.',
            ),
            array(
                'frequency'     => '0 0 * * *',
                'phpBinary'     => CommonHelper::findPhpCliPath(),
                'consolePath'   => MW_APPS_PATH . '/console/console.php',
                'command'       => 'daily',
                'description'   => 'Daily cleaner, runs once a day.',
            )
        );
        
        if (class_exists('Yii', false)) {
            $cronJobs = (array)Yii::app()->hooks->applyFilters('cron_job_display_handler_cron_jobs_list', $cronJobs);  
        }
        
        foreach ($cronJobs as $index => $data) {
            if (!isset($data['frequency'], $data['phpBinary'], $data['consolePath'], $data['command'], $data['description'])) {
                unset($cronJobs[$index]);
                continue;
            }
            $cronJobs[$index]['cronjob'] = sprintf('%s %s -q %s %s >/dev/null 2>&1', $data['frequency'], $data['phpBinary'], $data['consolePath'], $data['command']);
        }

        return $cronJobs;
    }
    
    public static function getAsDataProvider()
    {
        $crons = array();
        foreach (self::getCronJobsList() as $data) {
            $crons[] = array(
                'id'          => $data['command'],
                'frequency'   => $data['frequency'],
                'phpBinary'   => $data['phpBinary'],
                'consolePath' => $data['consolePath'],
                'command'     => $data['command'],
                'description' => Yii::t('cron_jobs', $data['description']),
                'cronjob'     => $data['cronjob'], 
            );
        }
        
        return new CArrayDataProvider($crons, array(
            'pagination' => array(
                'pageSize' => 50,
            ),
        ));
    }
}