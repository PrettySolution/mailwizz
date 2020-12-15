<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * DeleteCustomerSuppressionListsDuplicateEmailsCommand
 * 
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com> 
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.8.8
 */
 
class DeleteCustomerSuppressionListsDuplicateEmailsCommand extends ConsoleCommand
{
    /**
     * @var string
     */
    public $list_uid = '';

    /**
     * Will delete the customer suppression list emails that are duplicates per suppression list.
     *
     * @return int
     */
    public function actionIndex()
    {
        Yii::app()->hooks->doAction('console_command_delete_customer_suppression_lists_duplicate_emails_before_process', $this);
        
        $result = $this->process();
        
        Yii::app()->hooks->doAction('console_command_delete_customer_suppression_lists_duplicate_emails_after_process', $this);
        
        return $result;
    }

    /**
     * @return int
     */
    protected function process()
    {
        $lockName = sha1(__METHOD__ . $this->list_uid);
        if (!Yii::app()->mutex->acquire($lockName)) {
            $this->stdout('Could not acquire lock...');
            return 1;
        }

        $this->stdout(sprintf('Loading suppression lists to delete their email duplicates...'));

        $lists = $this->getLists();

        if (empty($lists)) {
            $this->stdout('No suppression list found for deleting its email duplicates!');
            return 0;
        }

        foreach ($lists as $list) {

            $this->stdout(sprintf('Processing list uid: %s', $list->list_uid));

            try {
                $deleteSql = 'DELETE l1 FROM {{customer_suppression_list_email}}  l1
                        INNER JOIN {{customer_suppression_list_email}}  l2 
                        WHERE l1.email_id < l2.email_id AND l1.email = l2.email AND l1.list_id = :lid';

                while (true) {
                    $count = Yii::app()->db->createCommand($deleteSql)->execute(array(
                        ':lid' => $list->list_id
                    ));
                    if (!$count) {
                        break;
                    }
                }
            } catch(Exception $e) {
                Yii::log($e->getMessage(), CLogger::LEVEL_ERROR);
            }
        }

        $this->stdout('Done!');

        Yii::app()->mutex->release($lockName);
        
        return 0;
    }

    /**
     * @return CustomerSuppressionList[]
     */
    protected function getLists()
    {
        $criteria = new CDbCriteria();
        if ($this->list_uid) {
            $criteria->compare('list_uid', (string)$this->list_uid);
        }
        return CustomerSuppressionList::model()->findAll($criteria);
    }
}
