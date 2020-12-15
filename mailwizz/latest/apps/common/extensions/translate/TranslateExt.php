<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * Translate Extension
 * 
 * Will create and update translation files for the current language if other than english.
 * 
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com> 
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 */
 
class TranslateExt extends ExtensionInit 
{
    // name of the extension as shown in the backend panel
    public $name = 'Translate';
    
    // description of the extension as shown in backend panel
    public $description = 'Will create and update translation files for your language.';
    
    // current version of this extension
    public $version = '1.3';
    
    // the author name
    public $author = 'Cristian Serban';
    
    // author website
    public $website = 'https://www.mailwizz.com/';
    
    // contact email address
    public $email = 'cristian.serban@mailwizz.com';
    
    // mark it as cli enabled to collect from console too
    public $cliEnabled = true;
    
    // in which apps this extension is not allowed to run
    public $allowedApps = array('*');

    // can this extension be deleted? this only applies to core extensions.
    protected $_canBeDeleted = false;
    
    // can this extension be disabled? this only applies to core extensions.
    protected $_canBeDisabled = true;
    
    // make sure this is the first extension that executes in order to catch missing translations from everywhere.
    public $priority = -1000;
    
    // run the extension
    public function run()
    {
        // register the extension page route and controller only if backend
        if ($this->isAppName('backend')) {
            
            // register the url rule to resolve the extension page.
            Yii::app()->urlManager->addRules(array(
                array('ext_translate/index', 'pattern' => 'extensions/translate'),
                array('ext_translate/<action>', 'pattern' => 'extensions/translate/*'),
            ));
            
            // add the backend controller
            Yii::app()->controllerMap['ext_translate'] = array(
                'class' => 'ext-translate.backend.controllers.Ext_translateController',
            );
        }
        
        // run the worker only if the user specifically enabled it.
        if ($this->getOption('enabled', 0)) {
            $this->checkAndEnableTranslations();
        }
    }
    
    /**
     * Add the landing page for this extension (settings/general info/etc)
     */
    public function getPageUrl()
    {
        return Yii::app()->createUrl('ext_translate/index');
    }
    
    protected function checkAndEnableTranslations()
    {
        if (!Yii::app()->hasComponent('messages') || !Yii::app()->getComponent('messages')) {
            return;
        }
        
        $messages = Yii::app()->messages;
        if (!($messages instanceof CPhpMessageSource)) {
            return;
        }
        
        if (!Yii::app()->getLocale()) {
            return;
        }
        
        $messages->attachEventHandler('onMissingTranslation', array($this, '_handleMissingTranslations'));
    }
    
    public function _handleMissingTranslations($event)
    {
        $sender = $event->sender;
        if (!($sender instanceof CPhpMessageSource)) {
            return;
        }
        
        // do not translate extensions.
        if (!$this->getOption('translate_extensions', 0) && strpos($event->category, 'ext') === 0) {
            return;
        }
        
        static $checkedFiles = array();
        
        $languageDir  = $sender->basePath . '/' . $event->language;
        $languageFile = $languageDir . '/' . $event->category . '.php';
        
        if (isset($checkedFiles[$languageFile]) && !is_array($checkedFiles[$languageFile])) {
            return;
        }
        
        if (!isset($checkedFiles[$languageFile])) {
            if (!file_exists($languageDir) || !is_dir($languageDir)) {
                if (!is_writable($sender->basePath) && !@chmod($sender->basePath, 0777)) {
                    return $checkedFiles[$languageFile] = false;
                }
                if (!@mkdir($languageDir, 0777, true)) {
                    return $checkedFiles[$languageFile] = false;
                }
            }
            
            if (!is_file($languageFile) && (!@touch($languageFile) || !@chmod($languageFile, 0666))) {
                return $checkedFiles[$languageFile] = false;
            }
             
            $checkedFiles[$languageFile] = array();   
        }
        
        if (empty($checkedFiles[$languageFile])) {
            $checkedFiles[$languageFile] = require $languageFile;
        }
        
        if (!is_array($checkedFiles[$languageFile])) {
            $checkedFiles[$languageFile] = array();
        }
        
        $checkedFiles[$languageFile][$event->message] = $event->message;
        
        static $stub;
        if (empty($stub)) {
            $stub = file_get_contents(dirname(__FILE__) . '/stub.php');
        }
        
        $newStub = str_replace('[[category]]', $event->category, $stub);
        $newStub .= 'return ' . var_export($checkedFiles[$languageFile], true) . ';' . "\n";
        @file_put_contents($languageFile, $newStub);
    }
    
}