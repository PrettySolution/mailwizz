<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * TableCleanerCommand
 *
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.3.6.2
 */

class TableCleanerCommand extends ConsoleCommand
{
    /**
     * @param $table
     * @param $time
     * @return int
     * @throws CDbException
     * @throws CException
     */
    public function actionIndex($table, $time)
    {
        if (empty($table)) {
            echo 'Please set the table name using the --table option!' . "\n";
            return 0;
        }

        if (empty($time)) {
            echo 'Please set the time option using the --time option!' . "\n";
            return 0;
        }
        
        $connection = Yii::app()->getDb();
        $schema     = $connection->getSchema();
        
        if (!($tbl = $schema->getTable($table))) {
            echo sprintf('Table "%s" does not exist!', $table) . "\n";
            return 0;
        }
        
        if (!($column = $tbl->getColumn('date_added'))) {
            echo sprintf('Table "%s" does not contain the "date_added" column!', $table) . "\n";
            return 0;
        }
        
        $timestamp = strtotime($time);
        $date      = date('Y-m-d H:i:s', $timestamp);
        $confirm   = sprintf('Are you sure you want to delete the records from the "%s" table that are older than "%s" date?', $table, $date);
        
        $input = $this->confirm($confirm);
        if (!$input) {
            echo "Okay, aborting!\n";
            return 0;
        }
        
        $sql   = sprintf('SELECT COUNT(*) as `c` FROM `%s` WHERE `date_added` < :dt', $table);
        $count = $connection->createCommand($sql)->queryRow(true, array(
            ':dt' => $date,
        ));
        
        if (empty($count['c'])) {
            echo "Nothing to delete, aborting!\n";
            return 0;
        }
        
        $input = $this->confirm(sprintf('This action will delete %d records from the "%s" table. Proceed?', $count['c'], $table));
        if (!$input) {
            echo "Okay, aborting!\n";
            return 0;
        }
        
        $start = microtime(true);
 
        $sql = sprintf('DELETE FROM `%s` WHERE `date_added` < :dt', $table);
        $connection->createCommand($sql)->execute(array(':dt' => $date));
        
        $timeTook  = round(microtime(true) - $start, 4);
        
        echo sprintf("DONE, took %s seconds!\n", $timeTook);
        return 0;
    }

    /**
     * @return int
     * @throws CDbException
     */
    public function actionTables()
    {
        $tables = Yii::app()->getDb()->getSchema()->getTableNames();
        foreach ($tables as $index => $table) {
            echo sprintf('%d. %s', $index+1, $table) . "\n";
        }
        return 0;
    }

    /**
     * @return string
     */
    public function getHelp()
    {
        $cmd = $this->getCommandRunner()->getScriptName() .' '. $this->getName();
        
        $help  = sprintf('command: %s tables', $cmd) . "\n";
        $help .= 'gives a list of all the tables from database that you can use.' . "\n\n";

        $help .= sprintf('command: %s --table=NAME --time=EXPRESSION', $cmd) . "\n";
        $help .= '--table=NAME where NAME can be any of the table listed with the tables command.' . "\n";
        $help .= '--time=EXPRESSION where EXPRESSION can be any expression parsable by php\'s strtotime function. ie: --time="-10 days".' . "\n";
        
        return $help;
    }
}