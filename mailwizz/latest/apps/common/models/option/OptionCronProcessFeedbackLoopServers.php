<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * OptionCronProcessFeedbackLoopServers
 * 
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com> 
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.3.3.1
 */
 
class OptionCronProcessFeedbackLoopServers extends OptionBase
{
    // action flag
    const ACTION_DELETE_SUBSCRIBER = 'delete';
    
    // action flag
    const ACTION_UNSUBSCRIBE_SUBSCRIBER = 'unsubscribe';
    
    // settings category
    protected $_categoryName = 'system.cron.process_feedback_loop_servers';
    
    // maximum amount of memory allowed
    public $memory_limit;
    
    // how many servers to process at once
    public $servers_at_once = 10;
    
    // how many emails should we load at once for each loaded server
    public $emails_at_once = 500;

    // how many seconds should we pause between the batches
    public $pause = 5;
    
    // what action to take against subscriber
    public $subscriber_action = 'unsubscribe';

    // select emails that are newer that x days
    public $days_back = 3;

    // whether to use pcntl
    public $use_pcntl = 'yes';

    // how many pcntl processes
    public $pcntl_processes = 10;
    
    public function rules()
    {
        $rules = array(
            array('servers_at_once, emails_at_once, pause, subscriber_action, days_back', 'required'),
            array('memory_limit', 'in', 'range' => array_keys($this->getMemoryLimitOptions())),
            array('servers_at_once, emails_at_once, pause', 'numerical', 'integerOnly' => true),
            array('servers_at_once', 'numerical', 'min' => 1, 'max' => 100),
            array('emails_at_once', 'numerical', 'min' => 100, 'max' => 1000),
            array('pause, days_back', 'numerical', 'min' => 0, 'max' => 60),
            array('subscriber_action', 'in', 'range' => array_keys($this->getSubscriberActionOptions())),
            array('use_pcntl', 'in', 'range' => array_keys($this->getYesNoOptions())),
            array('pcntl_processes', 'numerical', 'min' => 1, 'max' => 100),
        );
        
        return CMap::mergeArray($rules, parent::rules());    
    }
    
    public function attributeLabels()
    {
        $labels = array(
            'memory_limit'     => Yii::t('settings', 'Memory limit'),
            'servers_at_once'  => Yii::t('settings', 'Servers at once'),
            'emails_at_once'   => Yii::t('settings', 'Emails at once'),
            'pause'            => Yii::t('settings', 'Pause'),
            'subscriber_action'=> Yii::t('settings', 'Action against subscriber'),
            'days_back'        => Yii::t('settings', 'Days back'),
            'use_pcntl'        => Yii::t('settings', 'Parallel processing via PCNTL'),
            'pcntl_processes'  => Yii::t('settings', 'Parallel processes count')
        );
        
        return CMap::mergeArray($labels, parent::attributeLabels());    
    }
    
    public function attributePlaceholders()
    {
        $placeholders = array(
            'memory_limit'     => null,
            'servers_at_once'  => null,
            'emails_at_once'   => null,
            'pause'            => null,
            'subscriber_action'=> null,
            'days_back'        => 3,
            'pcntl_processes'  => 10,
        );
        
        return CMap::mergeArray($placeholders, parent::attributePlaceholders());
    }
    
    public function attributeHelpTexts()
    {
        $texts = array(
            'memory_limit'     => Yii::t('settings', 'The maximum memory amount the process is allowed to use while processing one batch of servers.'),
            'servers_at_once'  => Yii::t('settings', 'How many servers to process at once.'),
            'emails_at_once'   => Yii::t('settings', 'How many emails for each server to process at once.'),
            'pause'            => Yii::t('settings', 'How many seconds to sleep after processing the emails from a server.'),
            'subscriber_action'=> Yii::t('settings', 'Whether to unsubscribe or to delete the subscriber.'),
            'days_back'        => Yii::t('settings', 'Process emails that are newer than this amount of days. Increasing the number of days increases the amount of emails to be processed.'),
            'use_pcntl'        => Yii::t('settings', 'Whether to process using PCNTL, that is multiple processes in parallel.'),
            'pcntl_processes'  => Yii::t('settings', 'The number of processes to run in parallel.'),
        );
        
        return CMap::mergeArray($texts, parent::attributeHelpTexts());
    }
    
    public function getSubscriberActionOptions()
    {
        return array(
            self::ACTION_DELETE_SUBSCRIBER          => ucwords(Yii::t('settings', self::ACTION_DELETE_SUBSCRIBER)),
            self::ACTION_UNSUBSCRIBE_SUBSCRIBER     => ucwords(Yii::t('settings', self::ACTION_UNSUBSCRIBE_SUBSCRIBER)),
        );
    }
}
