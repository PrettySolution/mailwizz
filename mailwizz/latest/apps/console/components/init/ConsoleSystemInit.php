<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * ConsoleSystemInit
 * 
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com> 
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.1
 */
 
class ConsoleSystemInit extends CApplicationComponent 
{
    // flag to mark the begin request event handler has been called
    protected $_hasRanOnBeginRequest = false;
    
    // flag to mark the end request event handler has been called
    protected $_hasRanOnEndRequest = false;
    
    /**
     * ConsoleSystemInit::init()
     * 
     * Init the console system and attach the event handlers
     */
    public function init()
    {
        parent::init();
        
        // attach the event handler to the onBeginRequest event
        Yii::app()->attachEventHandler('onBeginRequest', array($this, 'runOnBeginRequest'));
        
        // attach the event handler to the onEndRequest event
        Yii::app()->attachEventHandler('onEndRequest', array($this, 'runOnEndRequest'));
    }
    
    /**
     * ConsoleSystemInit::runOnBeginRequest()
     * 
     * This will run on begin of request
     * It's important since when updating the app, if the app is online the console commands will fail
     * and the campaigns will remain stuck
     */
    public function runOnBeginRequest(CEvent $event)
    {
        if ($this->_hasRanOnBeginRequest) {
            return;
        }
        
        // if the site offline, stop.
        if (Yii::app()->options->get('system.common.site_status', 'online') != 'online') {
            // since 1.3.4.8
            // if it's the update command then just go ahead.
            if (!empty($_SERVER['argv']) && !empty($_SERVER['argv'][1]) && in_array($_SERVER['argv'][1], array('update', 'auto-update'))) {
                // mark the event as completed
                $this->_hasRanOnBeginRequest = true;
                // and continue execution by returing from this method
                return;
            }
            
            // otherwise stop execution
            Yii::app()->end();
        }

        // and mark the event as completed.
        $this->_hasRanOnBeginRequest = true;
    }
    
    /**
     * ConsoleSystemInit::runOnEndRequest()
     * 
     * This is kept as reference for future additions
     */
    public function runOnEndRequest(CEvent $event)
    {
        if ($this->_hasRanOnEndRequest) {
            return;
        }

        // and mark the event as completed.
        $this->_hasRanOnEndRequest = true;
    }
}