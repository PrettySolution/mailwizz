<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * ClearCacheCommand
 *
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.3.6.6
 *
 */

class ClearCacheCommand extends ConsoleCommand
{
    // enable verbose mode
    public $verbose = 1;
    
    /**
     * @return int
     */
    public function actionIndex()
    {
        $result = 0;
        
        try {

            Yii::app()->hooks->doAction('console_command_clear_cache_before_process', $this);

            $result = $this->process();

            Yii::app()->hooks->doAction('console_command_clear_cache_after_process', $this);
        
        } catch (Exception $e) {

            $this->stdout(__LINE__ . ': ' .  $e->getMessage());
            Yii::log($e->getMessage(), CLogger::LEVEL_ERROR);
        }

        return $result;
    }

    /**
     * @return int
     */
    protected function process()
    {
        $this->stdout(FileSystemHelper::clearCache());
        
        $this->stdout('Calling Cache::flush()...');
        Yii::app()->cache->flush();
        
        $this->stdout('Clearing the database schema cache...');
        Yii::app()->db->schema->getTables();
        Yii::app()->db->schema->refresh();
        
        $this->stdout('DONE.');
        
        return 0;
    }
}
