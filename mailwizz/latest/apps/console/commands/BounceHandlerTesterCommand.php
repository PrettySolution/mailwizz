<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * BounceHandlerTesterCommand
 *
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.6.6
 */

class BounceHandlerTesterCommand extends ConsoleCommand
{
    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        Yii::import('common.vendors.BounceHandler.*');
    }

    /**
     * @return int
     */
    public function actionIndex()
    {
        $this->stdout('Starting...');

	    //Yii::app()->params['email.custom.header.prefix'] = 'X-Wccc-';
	    $headerPrefix   = Yii::app()->params['email.custom.header.prefix'];
	    $headerPrefixUp = strtoupper($headerPrefix);

	    $bounceHandler = new BounceHandlerTester('__TEST__', '__TEST__', '__TEST__', array(
		    'deleteMessages'                => false,
		    'deleteAllMessages'             => false,
		    'processLimit'                  => 1000,
		    'searchCharset'                 => Yii::app()->charset,
		    'imapOpenParams'                => array(),
		    'processDaysBack'               => 10,
		    'processOnlyFeedbackReports'    => false,
		    'requiredHeaders'               => array(
			    $headerPrefix . 'Campaign-Uid',
			    $headerPrefix . 'Subscriber-Uid'
		    ),
		    'logger' => array($this, 'stdout'),
	    ));

	    $this->stdout('Fetching the results...');

	    // fetch the results
	    $results = $bounceHandler->getResults();

	    $this->stdout(sprintf('Found %d results.', count($results)));
	    
	    // done
	    if (empty($results)) {
		    $this->stdout('No results!');
		    return 0;
	    }

	    $hard = $soft = $internal = $fbl = 0;
	    
	    foreach ($results as $result) {

		    foreach ($result['originalEmailHeadersArray'] as $key => $value) {
			    unset($result['originalEmailHeadersArray'][$key]);
			    $result['originalEmailHeadersArray'][strtoupper($key)] = $value;
		    }

		    if (!isset($result['originalEmailHeadersArray'][$headerPrefixUp . 'CAMPAIGN-UID'], $result['originalEmailHeadersArray'][$headerPrefixUp . 'SUBSCRIBER-UID'])) {
			    continue;
		    }

		    $campaignUid   = trim($result['originalEmailHeadersArray'][$headerPrefixUp . 'CAMPAIGN-UID']);
		    $subscriberUid = trim($result['originalEmailHeadersArray'][$headerPrefixUp . 'SUBSCRIBER-UID']);

		    $this->stdout(sprintf('Processing campaign uid: %s and subscriber uid %s.', $campaignUid, $subscriberUid));
		    
		    if (in_array($result['bounceType'], array(BounceHandler::BOUNCE_SOFT, BounceHandler::BOUNCE_HARD))) {
		    	
		    	if ($result['bounceType'] == BounceHandler::BOUNCE_SOFT) {
		    		$soft++;
			    } else {
		    		$hard++;
			    }
			    
			    $this->stdout(sprintf('Subscriber uid: %s is %s bounced with the message: %s.', $subscriberUid, $result['bounceType'], $result['email']));

		    } elseif ($result['bounceType'] == BounceHandler::FEEDBACK_LOOP_REPORT) {

		    	$fbl++;
			    $_message = 'DELETED / UNSUB';
			    
			    $this->stdout(sprintf('Subscriber uid: %s is %s bounced with the message: %s.', $subscriberUid, (string)$result['bounceType'], (string)$_message));

		    } elseif ($result['bounceType'] == BounceHandler::BOUNCE_INTERNAL) {
		    	
		    	$internal++;
			    $this->stdout(sprintf('Subscriber uid: %s is %s bounced with the message: %s.', $subscriberUid, $result['bounceType'], $result['email']));
		    }
	    }
	    
	    $this->stdout(sprintf('Overall: %d hard / %d soft / %d internal / %d fbl', $hard, $soft, $internal, $fbl));
	    
        
        return 0;
    }
}
