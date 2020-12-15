<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * ValidateListMxRecordsCommand
 *
 * Handles the actions for list mx records validation.
 *
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.4.4
 */

class ValidateListMxRecordsCommand extends ConsoleCommand
{
    /**
     * @var int
     */
    public $limit = 500;

    /**
     * @var string
     */
    public $action = 'blacklist';

    /**
     * @param $list_uid
     * @return int
     */
    public function actionIndex($list_uid)
    {
        return $this->processList($list_uid);
    }

    /**
     * @param $listUid
     * @return int
     */
    protected function processList($listUid)
    {
        $this->stdout('Processing list uid: ' . $listUid);
        
        $list = Lists::model()->findByUid($listUid);
        if (empty($list)) {
            $this->stdout('List uid: ' . $listUid . ' does not exists anymore!');
            return 1;
        }

        $criteria = new CDbCriteria();
        $criteria->compare('list_id', $list->list_id);
        $criteria->compare('status', ListSubscriber::STATUS_CONFIRMED);
        $criteria->limit  = $this->limit;
        $criteria->offset = 0;

        $subscribers = ListSubscriber::model()->findAll($criteria);
        
        while (!empty($subscribers)) {

            foreach ($subscribers as $subscriber) {
                if ($subscriber->getEmailMxRecords()) {
                    continue;
                }
                
                $this->stdout('Domain ' . $subscriber->getEmailHostname() . ' does not have any mx records!');
                if ($this->action == 'blacklist') {
                    $subscriber->addToBlacklist('Domain ' . $subscriber->getEmailHostname() . ' does not have any mx records!');
                    continue;
                }
                if ($this->action == 'unsubscribe') {
                    $subscriber->saveStatus(ListSubscriber::STATUS_UNSUBSCRIBED);
                    continue;
                }
            }

            $criteria->offset = $criteria->offset + $criteria->limit;
            $subscribers = ListSubscriber::model()->findAll($criteria);
        }
        
        return 0;
    }
}
