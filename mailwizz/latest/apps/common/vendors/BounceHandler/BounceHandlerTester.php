<?php if ( ! defined('MW_PATH')) exit('No direct script access allowed');

/**
 * BounceHandlerTester
 *
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.6.6
 */

class BounceHandlerTester extends BounceHandler
{
	/**
	 * @var string 
	 */
	public $messagesLocation = 'common.runtime.bounce-handler-tester';

	/**
	 * @var array 
	 */
	public $messagesStore = array();
	
	/**
	 * @param $messageId
	 *
	 * @return string
	 */
    public function imapFetchHeaders($messageId)
    {
    	return isset($this->messagesStore[$messageId]) ? $this->messagesStore[$messageId] : '';
    }

	/**
	 * @param $messageId
	 *
	 * @return string
	 */
    public function imapBody($messageId)
    {
	    return isset($this->messagesStore[$messageId]) ? $this->messagesStore[$messageId] : '';
    }

	/**
	 * @param $messageId
	 * @param $section
	 *
	 * @return string
	 */
    public function imapFetchBody($messageId, $section)
    {
	    return isset($this->messagesStore[$messageId]) ? $this->messagesStore[$messageId] : '';
    }

	/**
	 * @param $messageId
	 *
	 * @return bool
	 */
    public function imapDelete($messageId)
    {
    	return true;
    }

	/**
	 * @param $messageId
	 * @param $section
	 *
	 * @return object
	 */
    public function imapBodyStruct($messageId, $section)
    {
	    return null;
    }

	/**
	 * @param $messageId
	 *
	 * @return object
	 */
    public function imapFetchStructure($messageId)
    {
	    return null;
    }

	/**
	 * @param $type
	 * @param $timeout
	 *
	 * @return mixed
	 */
    public function imapTimeout($type, $timeout)
    {
    	return 0;
    }

	/**
	 * @return resource
	 */
    public function imapOpen()
    {
    	return true;
    }

	/**
	 * @return array
	 */
    public function imapErrors()
    {
    	return array();
    }

	/**
	 * @return bool
	 */
    public function imapExpunge()
    {
    	return true;
    }

	/**
	 * @return bool
	 */
    public function imapClose()
    {
    	return true;
    }

	/**
	 * @return array
	 */
    public function imapSearch()
    {
    	$results = array();
	    $messagesLocation   = Yii::getPathOfAlias($this->messagesLocation);
    	$files              = FileSystemHelper::readDirectoryContents($messagesLocation, true);
	    $files              = !is_array($files) ? array() : $files;
    	
    	foreach ($files as $file) {
    		if (!is_file($file)) {
    			continue;
		    }
		    $content = file_get_contents($file);
    		$key     = basename($file);
		    $this->messagesStore[$key] = $content;
		    $results[] = $key;
	    }
	    
    	return $results;
    }
}
