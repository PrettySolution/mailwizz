<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * SuppressionListImportCommand
 *
 * Handles the actions for suppression list import related tasks.
 * Most of the logic is borrowed from the web interface importer.
 *
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.4.4
 */

class SuppressionListImportCommand extends ConsoleCommand
{
    // the folder path from where we should load files
    public $folder_path;

    // max amount of files to process from the folder
    public $folder_process_files = 10;

    // the list where we want to import into
    public $list_uid;

    // the path where the import file is located
    public $file_path;

    // is verbose
    public $verbose = 0;

    // for external access maybe?
    public $lastMessage = array();
    
    /**
     * @return int
     * @throws CException
     */
    public function actionFolder()
    {
        if (empty($this->folder_path)) {
            $this->folder_path = Yii::getPathOfAlias('common.runtime.suppression-list-import-queue');
            if (!file_exists($this->folder_path) || !is_dir($this->folder_path)) {
            	@mkdir($this->folder_path, 0777, true);
            }
        }

        if (!is_dir($this->folder_path) || !is_readable($this->folder_path)) {
            return $this->renderMessage(array(
                'result'  => 'error',
                'message' => Yii::t('list_import', 'Call this command with the --folder_path=XYZ param where XYZ is the full path to the folder you want to monitor.'),
                'return'  => 1,
            ));
        }

        $this->renderMessage(array(
            'result'  => 'info',
            'message' => 'The folder path is: '. $this->folder_path,
        ));

        $files  = FileSystemHelper::readDirectoryContents($this->folder_path, true);
        $pcntl  = CommonHelper::functionExists('pcntl_fork') && CommonHelper::functionExists('pcntl_waitpid');
        $childs = array();

        if ($pcntl) {
            // close the external connections
            $this->setExternalConnectionsActive(false);
        }

        if (count($files) > (int)$this->folder_process_files) {
            $files = array_slice($files, (int)$this->folder_process_files);
        }

        $this->renderMessage(array(
            'result'  => 'info',
            'message' => 'Found '. count($files) . ' files (some of them might be already processing)',
        ));

        foreach ($files as $file) {
            if (!$pcntl) {
                $this->processFile($file);
                continue;
            }

            //
            $pid = pcntl_fork();
            if($pid == -1) {
                continue;
            }

            // Parent
            if ($pid) {
                $childs[] = $pid;
            }

            // Child
            if (!$pid) {
                $this->processFile($file);
                Yii::app()->end();
            }
        }

        if ($pcntl) {
            while (count($childs) > 0) {
                foreach ($childs as $key => $pid) {
                    $res = pcntl_waitpid($pid, $status, WNOHANG);
                    if($res == -1 || $res > 0) {
                        unset($childs[$key]);
                    }
                }
                sleep(1);
            }
        }

        return 0;
    }

    /**
     * @param $file
     * @return int
     */
    protected function processFile($file)
    {
        $this->renderMessage(array(
            'result'  => 'info',
            'message' => 'Processing: ' . $file,
        ));

        $lockName = sha1($file);
        if (!Yii::app()->mutex->acquire($lockName, 5)) {
            return $this->renderMessage(array(
                'result'  => 'info',
                'message' => 'Cannot acquire lock for processing: ' . $file,
                'return'  => 1,
            ));
        }

        if (!is_file($file)) {
            return $this->renderMessage(array(
                'result'  => 'info',
                'message' => 'The file: "' . $file . '" was removed by another process!',
                'return'  => 1,
            ));
        }

        try {

            $fileName  = basename($file);
            $extension = pathinfo($fileName, PATHINFO_EXTENSION);
            $listName  = substr(trim(basename($fileName, $extension), '.'), 0, 13); // maybe uid-1.csv uid-2.txt

            Yii::app()->hooks->doAction('console_command_suppression_list_import_before_process', new CAttributeCollection(array(
                'command'    => $this,
                'importType' => $extension,
                'listUid'    => $listName,
                'filePath'   => $file,
            )));

            if ($extension == 'csv') {
                $this->processCsv(array(
                    'list_uid'    => $listName,
                    'file_path'   => $file,
                ));
            }

            Yii::app()->hooks->doAction('console_command_suppression_list_import_after_process', new CAttributeCollection(array(
                'command'    => $this,
                'importType' => $extension,
                'listUid'    => $listName,
                'filePath'   => $file,
            )));

            if (in_array($extension, array('csv')) && is_file($file)) {

                // remove the file
                @unlink($file);

                // 1.4.4
                $list = CustomerSuppressionList::model()->findByAttributes(array(
                    'list_uid' => $listName,
                ));

	            // since 1.7.9
                if (!empty($list)) {
	                $list->touchLastUpdated();
                }

                if (!empty($list) && ($server = DeliveryServer::pickServer())) {
                    $options         = Yii::app()->options;
                    $listOverviewUrl = $options->get('system.urls.customer_absolute_url') . 'suppression-lists/' . $list->list_uid . '/emails/index';
                    
	                $emailParams = CommonEmailTemplate::getAsParamsArrayBySlug('suppression-list-import-finished',
		                array(
			                'to'      => array($list->customer->email => $list->customer->email),
			                'subject' => Yii::t('list_import', 'Suppression list import has finished!'),
		                ), array(
			                '[CUSTOMER_NAME]'   => $list->customer->getFullName(),
			                '[LIST_NAME]'       => $list->name,
			                '[OVERVIEW_URL]'    => $listOverviewUrl,
		                )
	                );

                    $server->sendEmail($emailParams);
                }
                //
            }
            
        } catch (Exception $e) {

            $this->stdout(__LINE__ . ': ' .  $e->getMessage());
            Yii::log($e->getMessage(), CLogger::LEVEL_ERROR);
        }

        Yii::app()->mutex->release($lockName);

        $this->renderMessage(array(
            'result'  => 'info',
            'message' => 'The file: "' . $file . '" was processed!',
        ));
    }

    /**
     * @return int
     * @throws CException
     */
    public function actionCsv()
    {
        Yii::app()->hooks->doAction('console_command_suppression_list_import_before_process', new CAttributeCollection(array(
            'command'    => $this,
            'importType' => 'csv',
            'listUid'    => $this->list_uid,
            'filePath'   => $this->file_path,
        )));

        $result = $this->processCsv(array(
            'list_uid'    => $this->list_uid,
            'file_path'   => $this->file_path,
        ));

        Yii::app()->hooks->doAction('console_command_suppression_list_import_after_process', new CAttributeCollection(array(
            'command'    => $this,
            'importType' => 'csv',
            'listUid'    => $this->list_uid,
            'filePath'   => $this->file_path,
        )));

        return $result;
    }

    /**
     * @param array $params
     * @return int
     * @throws CDbException
     */
    protected function processCsv(array $params)
    {
        if (empty($params['list_uid'])) {
            return $this->renderMessage(array(
                'result'  => 'error',
                'message' => Yii::t('list_import', 'Call this command with the --list_uid=XYZ param where XYZ is the 13 chars unique list id.'),
                'return'  => 1,
            ));
        }

        $list = CustomerSuppressionList::model()->findByUid($params['list_uid']);
        if (empty($list)) {
            return $this->renderMessage(array(
                'result'  => 'error',
                'message' => Yii::t('list_import', 'The list with the uid {uid} was not found in database.', array(
                    '{uid}' => $params['list_uid'],
                )),
                'return' => 1,
            ));
        }

        if (empty($params['file_path']) || !is_file($params['file_path'])) {
            return $this->renderMessage(array(
                'result'  => 'error',
                'message' => Yii::t('list_import', 'Call this command with the --file_path=/some/file.csv param where /some/file.csv is the full path to the csv file to be imported.'),
                'return'  => 1,
            ));
        }

        $options      = Yii::app()->options;
        $importAtOnce = (int)$options->get('system.importer.import_at_once', 50);

        ini_set('auto_detect_line_endings', true);

        $file = new SplFileObject($params['file_path']);
        $file->setCsvControl(StringHelper::detectCsvDelimiter($params['file_path']));
        $file->setFlags(SplFileObject::READ_CSV | SplFileObject::SKIP_EMPTY | SplFileObject::DROP_NEW_LINE | SplFileObject::READ_AHEAD);
        $columns = $file->current(); // the header

        if (empty($columns) || !is_array($columns)) {
            return $this->renderMessage(array(
                'result'  => 'error',
                'message' => Yii::t('list_import', 'Your file does not contain the header with the fields title!'),
                'return'  => 1,
            ));
        }

        $linesCount       = iterator_count($file);
        $totalFileRecords = $linesCount - 1; // minus the header

        $file->seek(1);
        
        $columns     = array_map('strtolower', array_map('trim', $columns));
        $columnCount = count($columns);
        
        if (array_search('email', $columns) === false) {
            return $this->renderMessage(array(
                'result'  => 'error',
                'message' => Yii::t('list_import', 'Cannot find the "email" column in your file!'),
                'return'  => 1,
            ));
        }
        
        $builder     = Yii::app()->getDb()->getSchema()->getCommandBuilder();
        $insert      = array();
        $mainCounter = 0;
        $count       = 0;
        
        while (!$file->eof()) {

            $row = $file->fgetcsv();
            if (empty($row) || !is_array($row)) {
                continue;
            }
            $rowCount = count($row);
            
            if ($columnCount > $rowCount) {
                $fill = array_fill($rowCount, $columnCount - $rowCount, '');
                $row  = array_merge($row, $fill);
            } elseif ($rowCount > $columnCount) {
                $row  = array_slice($row, 0, $columnCount);
            }

            $row = array_combine($columns, $row);
            if (empty($row['email']) || (!FilterVarHelper::email($row['email']) && !StringHelper::isMd5($row['email']))) {
                continue;
            }

            $mainCounter++;
            $percent = round(($mainCounter / $totalFileRecords) * 100);

            $this->renderMessage(array(
                'type'    => 'info',
                'message' => '['.$percent.'%] - ' . Yii::t('list_import', 'Processing the email: "{email}"', array(
                        '{email}' => $row['email'],
                    )),
                'counter' => false,
            ));

            $_insert = array(
                'list_id' => $list->list_id,
            );
            
            if (StringHelper::isMd5($row['email'])) {
                $_insert['email_md5'] = $row['email'];
            } else {
                $_insert['email']     = $row['email'];
                $_insert['email_md5'] = md5($row['email']);
            }
            
            $insert[] = $_insert;

            $count++;
            if ($count < $importAtOnce) {
                continue;
            }
            $count = 0;
            
            try {
                $builder->createMultipleInsertCommand('{{customer_suppression_list_email}}', $insert)->execute();
            } catch (Exception $e) {
                Yii::log($e->getMessage(), CLogger::LEVEL_ERROR);
            }
            
            $insert = array();
        }
        
        if (!empty($insert)) {
            try {
                $builder->createMultipleInsertCommand('{{customer_suppression_list_email}}', $insert)->execute();
            } catch (Exception $e) {
                Yii::log($e->getMessage(), CLogger::LEVEL_ERROR);
            }
        }

        $this->renderMessage(array(
            'type'    => 'info',
            'message' => Yii::t('list_import', 'Deleting duplicates, this might take a while...'),
            'counter' => false,
        ));

        try {
            $deleteSql = 'DELETE l1 FROM {{customer_suppression_list_email}}  l1
                        INNER JOIN {{customer_suppression_list_email}}  l2 
                        WHERE l1.email_id < l2.email_id AND l1.email = l2.email AND l1.list_id = :lid';

            while (true) {
	            $count = Yii::app()->db->createCommand($deleteSql)->execute(array(
		            ':lid' => $list->list_id,
	            ));
	            if (!$count) {
	            	break;
	            }
            }
        } catch(Exception $e) {
            Yii::log($e->getMessage(), CLogger::LEVEL_ERROR);
        }

        return $this->renderMessage(array(
            'result'  => 'success',
            'message' => Yii::t('list_import', 'The import process has finished!'),
            'return'  => 0,
        ));
    }
    
    /**
     * @param array $data
     * @return int
     */
    protected function renderMessage($data = array())
    {
        if (isset($data['type']) && in_array($data['type'], array('success', 'error'))) {
            $this->lastMessage = $data;
        }

        if (isset($data['message']) && $this->verbose) {
            $out = '['.date('Y-m-d H:i:s').'] - ';
            if (isset($data['type'])) {
                $out .= '[' . strtoupper($data['type']) . '] - ';
            }
            $out .= strip_tags(str_replace(array('<br />', '<br/>', '<br>'), PHP_EOL, $data['message'])) . PHP_EOL;
            echo $out;
        }

        if (isset($data['return']) || array_key_exists('return', $data)) {
            return (int)$data['return'];
        }
    }
}