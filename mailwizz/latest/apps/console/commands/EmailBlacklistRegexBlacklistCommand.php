<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * EmailBlacklistRegexBlacklistCommand
 *
 * Handles the actions for email blacklist import related tasks.
 * Most of the logic is borrowed from the web interface importer.
 *
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.6.4
 */

class EmailBlacklistRegexBlacklistCommand extends ConsoleCommand
{
    /**
     * @return int
     */
    public function actionIndex()
    {
        $regexes = Yii::app()->options->get('system.email_blacklist.regular_expressions', '');
        $regexes = explode(PHP_EOL, $regexes);
        if (empty($regexes)) {
            $this->stdout("There is no regex defined!");
            return 0;
        }

        $limit  = 1000;
        $offset = 0;

        $subscribers = $this->getSubscribers($limit, $offset);
        if (!empty($subscribers)) {
            $this->stdout(sprintf("Started a batch from %d to %d and found %d results to process...", $offset, $offset + $limit, count($subscribers)));
        } else {
            $this->stdout("Done, nothing else to process!");
        }

        while (!empty($subscribers)) {

            foreach ($subscribers as $subscriber) {

                $blacklistRegex = '';

                foreach ($regexes as $regex) {
                    if (!preg_match($regex, $subscriber->email)) {
                        continue;
                    }
                    $blacklistRegex = $regex;
                    break;
                }

                if ($blacklistRegex) {
                    $subscriber->saveStatus(ListSubscriber::STATUS_BLACKLISTED);
                    $this->stdout(sprintf('"%s" matched "%s" regex and was blacklisted!', $subscriber->email, $blacklistRegex));
                }
            }

            $offset = $offset + $limit;
            $subscribers = $this->getSubscribers($limit, $offset);

            if (!empty($subscribers)) {
                $this->stdout(sprintf("Started a batch from %d to %d and found %d results to process...", $offset, $offset + $limit, count($subscribers)));
            } else {
                $this->stdout("Done, nothing else to process!");
            }
        }

        return 0;
    }

    /**
     * @param $limit
     * @param $offset
     * @return array|mixed|null
     */
    protected function getSubscribers($limit, $offset)
    {
        $criteria = new CDbCriteria();
        $criteria->compare('status', ListSubscriber::STATUS_CONFIRMED);
        $criteria->limit  = $limit;
        $criteria->offset = $offset;

        return ListSubscriber::model()->findAll($criteria);
    }
}
