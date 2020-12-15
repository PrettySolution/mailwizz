<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * ConsoleCommand
 *
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.3.6.6
 *
 */

class ConsoleCommand extends CConsoleCommand
{
    /**
     * Whether this should be verbose and output to console
     * 
     * @var int
     */
    public $verbose = 0;

    /**
     * @var int
     */
    protected $__startTime = 0;

    /**
     * @var int
     */
    protected $__startMemory = 0;

    /**
     * @var array 
     */
    protected $stdoutLogs = array();
    
    /**
     * @inheritdoc
     */
    protected function beforeAction($action, $params) 
    {
        $this->__startTime   = microtime(true);
        $this->__startMemory = memory_get_peak_usage(true);
        
        return parent::beforeAction($action, $params);
    }

    /**
     * @inheritdoc
     */
    protected function afterAction($action, $params, $exitCode = 0)
    {
        parent::afterAction($action, $params, $exitCode);
        $this->saveCommandHistory($action, $params, $exitCode);
    }
    
    /**
     * @param $message
     * @param bool $timer
     * @param string $separator
     * @param bool $store
     * @return int
     */
    public function stdout($message, $timer = true, $separator = "\n", $store = false)
    {
        if ($store) {
            $this->stdoutLogs = array_slice($this->stdoutLogs, -500);
            $this->stdoutLogs[] = ($timer ? '[' . date('Y-m-d H:i:s') . '] - ' : '') . $message . ($separator ? $separator : '');
        }
        
        if (!$this->verbose) {
            return 0;
        }
        
        if (!is_array($message)) {
            $message = array($message);
        }

        $out = '';
        
        foreach ($message as $msg) {
            
            if ($timer) {
                $out .= '[' . date('Y-m-d H:i:s') . '] - ';
            }
            
            $out .= $msg;
            
            if ($separator) {
                $out .= $separator;
            }
        }

        echo $out;
        return 0;
    }

    /**
     * @param array $params
     * @return string
     */
    protected function stringifyParams(array $params = array())
    {
        if (empty($params)) {
            return '';
        }
        
        $out = array();
        foreach ($params as $key => $value) {
            $out[] = '--' . $key . '=' . $value;
        }
        
        return implode(' ', $out);
    }

    /**
     * @param $action
     * @param array $params
     * @param int $exitCode
     */
    protected function saveCommandHistory($action, $params = array(), $exitCode = 0)
    {
    	if (!Yii::app()->params['console.save_command_history']) {
    		return;
	    }
    	
        try {

            $command = ConsoleCommandList::model()->findByAttributes(array(
                'command' => $this->getName(),
            ));

            if (empty($command)) {
                $command = new ConsoleCommandList();
                $command->command = $this->getName();
                $command->save();
            }

            $commandHistory = new ConsoleCommandListHistory();
            $commandHistory->command_id   = $command->command_id;
            $commandHistory->action       = $action;
            $commandHistory->params       = $this->stringifyParams($params);
            $commandHistory->start_time   = $this->__startTime;
            $commandHistory->end_time     = microtime(true);
            $commandHistory->start_memory = $this->__startMemory;
            $commandHistory->end_memory   = memory_get_peak_usage(true);
            $commandHistory->status       = $exitCode !== 0 ? ConsoleCommandListHistory::STATUS_ERROR : ConsoleCommandListHistory::STATUS_SUCCESS;
            $commandHistory->save();

        } catch (Exception $e) {
            Yii::log($e->getMessage(), CLogger::LEVEL_ERROR);
        }
    }

    /**
     * @param bool $active
     * @return $this
     * @throws CException
     */
    protected function setExternalConnectionsActive($active = true)
    {
        Yii::app()->db->setActive($active);
        Yii::app()->mutex->setConnectionActive($active);
        
        if (method_exists(Yii::app()->cache, 'setConnectionActive')) {
            Yii::app()->cache->setConnectionActive($active);
        }
        
        return $this;
    }
}
