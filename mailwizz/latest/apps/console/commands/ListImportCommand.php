<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * ListImportCommand
 *
 * Handles the actions for list import related tasks.
 * Most of the logic is borrowed from the web interface importer.
 *
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.3.5.9
 */

class ListImportCommand extends ConsoleCommand
{
	/**
	 * The folder path from where we should load files
	 * 
	 * @var string
	 */
	public $folder_path = '';

	/**
	 * Max amount of files to process from the folder
	 * 
	 * @var int 
	 */
	public $folder_process_files = 10;

	/**
	 * The list where we want to import into
	 * 
	 * @var string 
	 */
	public $list_uid = '';
	
	/**
	 * The path where the import file is located
	 * 
	 * @var string 
	 */
	public $file_path = '';
	
	/**
	 * @var int maximum number of records allowed per file.
	 * Above this number, files will be split into smaller files
	 */
	public $max_records_per_file_split = 10000;

	/**
	 * Is verbose
	 * 
	 * @var int 
	 */
	public $verbose = 0;

	/**
	 * For external access maybe?
	 * 
	 * @var array 
	 */
	public $lastMessage = array();

	/**
	 * @return int
	 * @throws CException
	 */
	public function actionFolder()
	{
		if (empty($this->folder_path)) {
			$this->folder_path = Yii::getPathOfAlias('common.runtime.list-import-queue');
		}

		if ((!is_dir($this->folder_path) && @mkdir($this->folder_path, 0777, true)) || !is_readable($this->folder_path)) {
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
			$files = array_slice($files, 0, (int)$this->folder_process_files);
		}

		$this->renderMessage(array(
			'result'  => 'info',
			'message' => 'Found '. count($files) . ' files (some of them might be already processing)',
		));

		foreach ($files as $file) {

			for ($i = 0; $i < 5; $i++) {
				$this->stdout('.', false, '');
				sleep(1);
			}

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
			Yii::app()->mutex->release($lockName);
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

			Yii::app()->hooks->doAction('console_command_list_import_before_process', new CAttributeCollection(array(
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
			} elseif ($extension == 'txt') {
				$this->processText(array(
					'list_uid'    => $listName,
					'file_path'   => $file,
				));
			}

			Yii::app()->hooks->doAction('console_command_list_import_after_process', new CAttributeCollection(array(
				'command'    => $this,
				'importType' => $extension,
				'listUid'    => $listName,
				'filePath'   => $file,
			)));

			if (in_array($extension, array('csv', 'txt')) && is_file($file)) {

				// 1.4.4
				$list = Lists::model()->findByAttributes(array(
					'list_uid' => $listName,
				));

				// 1.7.6
				$canSendEmail = false;
				if (!empty($list)) {
					$accessKey = sha1(__METHOD__ . ':access_key:' . $list->list_uid);
					if (Yii::app()->mutex->acquire($accessKey, 30)) {

						// remove the file
						@unlink($file);

						// [LIST_UID]-1.(csv|txt) | [LIST_UID]-part-1.(csv|txt)
						if (!glob(dirname($file) . '/' . $list->list_uid . '-*.' . $extension)) {
							$canSendEmail = true;
						}

						Yii::app()->mutex->release($accessKey);
					}
				}
				//

				// 1.7.7 - remove the file
				if (is_file($file)) {
					@unlink($file);
				}

				if ($canSendEmail && ($server = DeliveryServer::pickServer())) {
					$options         = Yii::app()->options;
					$listOverviewUrl = $options->get('system.urls.customer_absolute_url') . 'lists/' . $list->list_uid . '/overview';

					$emailParams = CommonEmailTemplate::getAsParamsArrayBySlug('list-import-finished',
						array(
							'to'      => array($list->customer->email => $list->customer->email),
							'subject' => Yii::t('list_import', 'List import has finished!'),
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
		Yii::app()->hooks->doAction('console_command_list_import_before_process', new CAttributeCollection(array(
			'command'    => $this,
			'importType' => 'csv',
			'listUid'    => $this->list_uid,
			'filePath'   => $this->file_path,
		)));

		$result = $this->processCsv(array(
			'list_uid'    => $this->list_uid,
			'file_path'   => $this->file_path,
		));

		Yii::app()->hooks->doAction('console_command_list_import_after_process', new CAttributeCollection(array(
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
	 * @throws CException
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

		$list = Lists::model()->findByUid($params['list_uid']);
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

		$delimiter = StringHelper::detectCsvDelimiter($params['file_path']);
		$file      = new SplFileObject($params['file_path']);
		$file->setCsvControl($delimiter);
		$file->setFlags(SplFileObject::READ_CSV | SplFileObject::SKIP_EMPTY | SplFileObject::DROP_NEW_LINE | SplFileObject::READ_AHEAD);
		$columns = $file->current(); // the header

		if (empty($columns)) {
			return $this->renderMessage(array(
				'result'  => 'error',
				'message' => Yii::t('list_import', 'Your file does not contain the header with the fields title!'),
				'return'  => 1,
			));
		}

		$file->seek(0);
		$linesCount         = iterator_count($file);
		$totalFileRecords   = $linesCount - 1; // minus the header
		$file->seek(0);

		// set a default
		if ((int)$this->max_records_per_file_split < 10000) {
			$this->max_records_per_file_split = 10000;
		}

		// this is max number of rows per file
		$maxRecordsPerFile = (int)$this->max_records_per_file_split;

		// we need to split in multiple smaller files
		if ($totalFileRecords > $maxRecordsPerFile) {

			$this->stdout('The file is too large, we are splitting it into multiple smaller files');

			$fileCounter  = 0;
			$fp           = null;
			$lockName     = null;
			$lockNames    = array();
			$smallerFiles = array();
			$totalRecords = 0;

			try {

				while (!$file->eof()) {

					if ($totalRecords == 0 || $totalRecords % $maxRecordsPerFile === 0) {
						$smallerFile = $this->folder_path . '/' . basename($params['file_path'], '.csv') . '-part-' . $fileCounter . '.csv';

						if ($lockName) {
							Yii::app()->mutex->release($lockName);
							if (isset($lockNames[$lockName])) {
								unset($lockNames[$lockName]);
							}
						}

						$lockName = sha1($smallerFile);
						if (!Yii::app()->mutex->acquire($lockName, 5)) {
							throw new Exception('Unable to acquire lock for smaller file!');
						}
						$lockNames[$lockName] = $lockName;

						$fileCounter++;

						if ($fp) {
							fclose($fp);
						}

						if (!($fp = fopen($smallerFile, 'w'))) {
							throw new Exception('Unable to create temporary smaller file!');
						}

						$smallerFiles[] = $smallerFile;
						$this->stdout('Adding into file: ' . $smallerFile);

						fputcsv($fp, $columns);
					}

					if ($row = $file->fgetcsv()) {
						fputcsv($fp, $row);
					}

					$totalRecords++;
				}

			} catch (Exception $e) {

				foreach ($smallerFiles as $smallerFile) {
					if (is_file($smallerFile)) {
						unlink($smallerFile);
					}
				}
			}

			if ($fp) {
				fclose($fp);
			}

			unset($file);

			// this prevents email sending
			@unlink($params['file_path']);

			// release any lock
			$lockNames = array_values($lockNames);
			foreach ($lockNames as $lockName) {
				Yii::app()->mutex->release($lockName);
			}

			return $this->renderMessage(array(
				'result'  => 'error',
				'message' => Yii::t('list_import', 'The file is too large, and it has been splitted into multiple smaller files!'),
				'return'  => 1,
			));
		}

		$customer              = $list->customer;
		$totalSubscribersCount = 0;
		$listSubscribersCount  = 0;
		$maxSubscribersPerList = (int)$customer->getGroupOption('lists.max_subscribers_per_list', -1);
		$maxSubscribers        = (int)$customer->getGroupOption('lists.max_subscribers', -1);

		if ($maxSubscribers > -1 || $maxSubscribersPerList > -1) {
			$criteria = new CDbCriteria();
			$criteria->select = 'COUNT(DISTINCT(t.email)) as counter';

			if ($maxSubscribers > -1 && ($listsIds = $customer->getAllListsIdsNotMerged())) {
				$criteria->addInCondition('t.list_id', $listsIds);
				$totalSubscribersCount = ListSubscriber::model()->count($criteria);
				if ($totalSubscribersCount >= $maxSubscribers) {
					return $this->renderMessage(array(
						'result'  => 'error',
						'message' => Yii::t('list_import', 'You have reached the maximum number of allowed subscribers.'),
						'return'  => 1,
					));
				}
			}

			if ($maxSubscribersPerList > -1) {
				$criteria->compare('t.list_id', (int)$list->list_id);
				$listSubscribersCount = ListSubscriber::model()->count($criteria);
				if ($listSubscribersCount >= $maxSubscribersPerList) {
					return $this->renderMessage(array(
						'result'  => 'error',
						'message' => Yii::t('list_import', 'You have reached the maximum number of allowed subscribers into this list.'),
						'return'  => 1,
					));
				}
			}
		}

		$criteria = new CDbCriteria();
		$criteria->select = 'field_id, label, tag';
		$criteria->compare('list_id', $list->list_id);
		$fields = ListField::model()->findAll($criteria);
		
		$searchReplaceTags = array(
			'E_MAIL'        => 'EMAIL',
			'EMAIL_ADDRESS' => 'EMAIL',
			'EMAILADDRESS'  => 'EMAIL',
		);
		foreach ($fields as $field) {
			if ($field->tag == 'FNAME') {
				$searchReplaceTags['F_NAME']     = 'FNAME';
				$searchReplaceTags['FIRST_NAME'] = 'FNAME';
				$searchReplaceTags['FIRSTNAME']  = 'FNAME';
				continue;
			}
			if ($field->tag == 'LNAME') {
				$searchReplaceTags['L_NAME']    = 'LNAME';
				$searchReplaceTags['LAST_NAME'] = 'LNAME';
				$searchReplaceTags['LASTNAME']  = 'LNAME';
				continue;
			}
		}

		$ioFilter = Yii::app()->ioFilter;
		$columns  = (array)$ioFilter->stripPurify($columns);
		$columns  = array_map('trim', $columns);

		$foundTags = array();
		foreach ($columns as $value) {
			$tagName     = StringHelper::getTagFromString($value);
			$tagName     = str_replace(array_keys($searchReplaceTags), array_values($searchReplaceTags), $tagName);
			$foundTags[] = $tagName;
		}

		// empty tags, not allowed
		if (count($foundTags) !== count(array_filter($foundTags))) {
			return $this->renderMessage(array(
				'result'  => 'error',
				'message' => Yii::t('list_import', 'Empty column names are not allowed!'),
				'return'  => 1,
			));
		}
		
		$foundEmailTag = false;
		foreach ($foundTags as $tagName) {
			if ($tagName === 'EMAIL') {
				$foundEmailTag = true;
				break;
			}
		}

		if (!$foundEmailTag) {
			return $this->renderMessage(array(
				'result'  => 'error',
				'message' => Yii::t('list_import', 'Cannot find the "email" column in your file!'),
				'return'  => 1,
			));
		}

		$foundReservedColumns = array();
		foreach ($columns as $columnName) {
			$columnName     = StringHelper::getTagFromString($columnName);
			$columnName     = str_replace(array_keys($searchReplaceTags), array_values($searchReplaceTags), $columnName);
			$tagIsReserved  = TagRegistry::model()->findByAttributes(array('tag' => '['.$columnName.']'));
			if (!empty($tagIsReserved)) {
				$foundReservedColumns[] = $columnName;
			}
		}

		if (!empty($foundReservedColumns)) {
			return $this->renderMessage(array(
				'result'  => 'error',
				'message' => Yii::t('list_import', 'Your list contains the columns: "{columns}" which are system reserved. Please update your file and change the column names!', array(
					'{columns}' => implode(', ', $foundReservedColumns)
				)),
				'return'  => 1,
			));
		}

		$rounds      = $totalFileRecords > $importAtOnce ? round($totalFileRecords / $importAtOnce) : 1;
		$mainCounter = 0;
		for ($rCount = 1; $rCount <= $rounds; $rCount++) {
			if ($rCount == 1) {
				$this->renderMessage(array(
					'message' => Yii::t('list_import', 'Found the following column names: {columns}', array(
						'{columns}' => implode(', ', $columns)
					)),
				));
			}

			$offset = $importAtOnce * ($rCount - 1);
			if ($offset >= $totalFileRecords) {
				return $this->renderMessage(array(
					'result'  => 'success',
					'message' => Yii::t('list_import', 'The import process has finished!'),
					'return'  => 0,
				));
			}
			$file->seek($offset);

			$csvData     = array();
			$columnCount = count($columns);
			$i           = 0;

			while (!$file->eof()) {

				$row = $file->fgetcsv();
				if (empty($row)) {
					continue;
				}

				$row = (array)$ioFilter->stripPurify($row);
				$row = array_map('trim', $row);
				$rowCount = count($row);

				if ($rowCount == 0) {
					continue;
				}

				$isEmpty = true;
				foreach ($row as $value) {
					if (!empty($value)) {
						$isEmpty = false;
						break;
					}
				}

				if ($isEmpty) {
					continue;
				}

				if ($columnCount > $rowCount) {
					$fill = array_fill($rowCount, $columnCount - $rowCount, '');
					$row  = array_merge($row, $fill);
				} elseif ($rowCount > $columnCount) {
					$row  = array_slice($row, 0, $columnCount);
				}

				$csvData[] = array_combine($columns, $row);

				++$i;

				if ($i >= $importAtOnce) {
					break;
				}
			}

			$fieldType = ListFieldType::model()->findByAttributes(array(
				'identifier' => 'text',
			));

			$data = array();
			foreach ($csvData as $row) {
				$rowData = array();
				foreach ($row as $name => $value) {
					$tagName = StringHelper::getTagFromString($name);
					$tagName = str_replace(array_keys($searchReplaceTags), array_values($searchReplaceTags), $tagName);

					$rowData[] = array(
						'name'      => ucwords(str_replace('_', ' ', $name)),
						'tagName'   => trim($tagName),
						'tagValue'  => trim($value),
					);
				}
				$data[] = $rowData;
			}

			if (empty($data) || count($data) < 1) {
				if ($rCount == 1) {
					return $this->renderMessage(array(
						'result'  => 'error',
						'message' => Yii::t('list_import', 'Your file does not contain enough data to be imported!'),
						'return'  => 1,
					));
				} else {

					return $this->renderMessage(array(
						'result'  => 'success',
						'message' => Yii::t('list_import', 'The import process has finished!'),
						'return'  => 0,
					));
				}
			}

			$tagToModel = array();
			foreach ($data[0] as $sample) {
				if ($rCount == 1) {
					$this->renderMessage(array(
						'type'    => 'info',
						'message' => Yii::t('list_import', 'Checking to see if the tag "{tag}" is defined in your list fields...', array(
							'{tag}' => CHtml::encode($sample['tagName'])
						)),
						'counter' => false,
					));
				}

				$model = ListField::model()->findByAttributes(array(
					'list_id' => $list->list_id,
					'tag'     => $sample['tagName']
				));

				if (!empty($model)) {

					if ($rCount == 1) {
						$this->renderMessage(array(
							'type'    => 'info',
							'message' => Yii::t('list_import', 'The tag "{tag}" is already defined in your list fields.', array(
								'{tag}' => CHtml::encode($sample['tagName'])
							)),
							'counter' => false,
						));
					}

					$tagToModel[$sample['tagName']] = $model;
					continue;
				}

				if ($rCount == 1) {
					$this->renderMessage(array(
						'type'    => 'info',
						'message' => Yii::t('list_import', 'The tag "{tag}" is not defined in your list fields, we will try to create it.', array(
							'{tag}' => CHtml::encode($sample['tagName'])
						)),
						'counter' => false,
					));
				}

				$model = new ListField();
				$model->type_id = $fieldType->type_id;
				$model->list_id = $list->list_id;
				$model->label   = $sample['name'];
				$model->tag     = $sample['tagName'];

				if ($model->save()) {

					if ($rCount == 1) {
						$this->renderMessage(array(
							'type'    => 'success',
							'message' => Yii::t('list_import', 'The tag "{tag}" has been successfully created.', array(
								'{tag}' => CHtml::encode($sample['tagName'])
							)),
							'counter' => false,
						));
					}

					$tagToModel[$sample['tagName']] = $model;

				} else {

					if ($rCount == 1) {
						$this->renderMessage(array(
							'type'    => 'error',
							'message' => Yii::t('list_import', 'The tag "{tag}" cannot be saved, reason: {reason}', array(
								'{tag}'    => CHtml::encode($sample['tagName']),
								'{reason}' => '<br />'.$model->shortErrors->getAllAsString()
							)),
							'counter' => false,
						));
					}
				}
			}

			// since 1.3.5.9
			$bulkEmails = array();
			foreach ($data as $index => $fields) {
				foreach ($fields as $detail) {
					if ($detail['tagName'] == 'EMAIL' && !empty($detail['tagValue'])) {
						$email = $detail['tagValue'];
						if (!EmailBlacklist::getFromStore($email)) {
							$bulkEmails[$email] = false;
						}
						break;
					}
				}
			}
			$failures = (array)Yii::app()->hooks->applyFilters('list_import_data_bulk_check_failures', array(), (array)$bulkEmails);
			foreach ($failures as $email => $message) {
				EmailBlacklist::addToBlacklist($email, $message);
			}
			// end 1.3.5.9

			$finished    = false;
			$importCount = 0;
			$importLog   = array();

			// since 1.3.5.9
			Yii::app()->hooks->doAction('list_import_before_processing_data', $collection = new CAttributeCollection(array(
				'data'        => $data,
				'list'        => $list,
				'importLog'   => $importLog,
				'finished'    => $finished,
				'importCount' => $importCount,
				'failures'    => $failures,
				'importType'  => 'csv'
			)));

			$data        = $collection->data;
			$importLog   = $collection->importLog;
			$importCount = $collection->importCount;
			$finished    = $collection->finished;
			$failures    = $collection->failures;
			//

			foreach ($data as $index => $fields) {

				$email = null;
				foreach ($fields as $detail) {
					if ($detail['tagName'] == 'EMAIL' && !empty($detail['tagValue'])) {
						$email = $detail['tagValue'];
						break;
					}
				}

				if (empty($email)) {
					continue;
				}

				$mainCounter++;
				$percent = round(($mainCounter / $totalFileRecords) * 100);

				$this->renderMessage(array(
					'type'    => 'info',
					'message' => '['.$percent.'%] - ' . Yii::t('list_import', 'Checking the list for the email: "{email}"', array(
							'{email}' => CHtml::encode($email),
						)),
					'counter' => false,
				));

				if (!empty($failures[$email])) {
					$this->renderMessage(array(
						'type'    => 'error',
						'message' => '['.$percent.'%] - ' . Yii::t('list_import', 'Failed to save the email "{email}", reason: {reason}', array(
								'{email}'  => CHtml::encode($email),
								'{reason}' => '<br />'.$failures[$email],
							)),
						'counter' => true,
					));
					continue;
				}

				$subscriber = null;
				if (!empty($email)) {
					$subscriber = ListSubscriber::model()->findByAttributes(array(
						'list_id' => $list->list_id,
						'email'   => $email,
					));
				}
				
				try {

					if (empty($subscriber)) {

						$this->renderMessage(array(
							'type'    => 'info',
							'message' => '['.$percent.'%] - ' . Yii::t('list_import', 'The email "{email}" was not found, we will try to create it...', array(
									'{email}' => CHtml::encode($email),
								)),
							'counter' => false,
						));

						$subscriber = new ListSubscriber();
						$subscriber->list_id = $list->list_id;
						$subscriber->email   = $email;
						$subscriber->source  = ListSubscriber::SOURCE_IMPORT;
						$subscriber->status  = ListSubscriber::STATUS_CONFIRMED;

						$validator = new CEmailValidator();
						$validator->allowEmpty  = false;
						$validator->validateIDN = true;
						if (Yii::app()->options->get('system.common.dns_email_check', false)) {
							$validator->checkMX   = CommonHelper::functionExists('checkdnsrr');
							$validator->checkPort = CommonHelper::functionExists('dns_get_record') && CommonHelper::functionExists('fsockopen');
						}
						$validEmail = !empty($email) && $validator->validateValue($email);

						if (!$validEmail) {
							$subscriber->addError('email', Yii::t('list_import', 'Invalid email address!'));
						} else {
							$blacklisted = $subscriber->getIsBlacklisted(array('checkZone' => EmailBlacklist::CHECK_ZONE_LIST_IMPORT));
							if (!empty($blacklisted)) {
								$subscriber->addError('email', Yii::t('list_import', 'This email address is blacklisted!'));
							}
						}

						if (!$validEmail || $subscriber->hasErrors() || !$subscriber->save()) {
							$this->renderMessage(array(
								'type'    => 'error',
								'message' => '['.$percent.'%] - ' . Yii::t('list_import', 'Failed to save the email "{email}", reason: {reason}', array(
										'{email}'  => CHtml::encode($email),
										'{reason}' => '<br />'.$subscriber->shortErrors->getAllAsString()
									)),
								'counter' => true,
							));
							
							continue;
						}

						$listSubscribersCount++;
						$totalSubscribersCount++;

						if ($maxSubscribersPerList > -1 && $listSubscribersCount >= $maxSubscribersPerList) {
							$finished = Yii::t('lists', 'You have reached the maximum number of allowed subscribers into this list.');
							break;
						}

						if ($maxSubscribers > -1 && $totalSubscribersCount >= $maxSubscribers) {
							$finished = Yii::t('lists', 'You have reached the maximum number of allowed subscribers.');
							break;
						}

						// 1.5.2
						$subscriber->takeListSubscriberAction(ListSubscriberAction::ACTION_SUBSCRIBE);

						$this->renderMessage(array(
							'type'    => 'success',
							'message' => '['.$percent.'%] - ' . Yii::t('list_import', 'The email "{email}" has been successfully saved.', array(
									'{email}' => CHtml::encode($email),
								)),
							'counter' => true,
						));

					} else {

						$this->renderMessage(array(
							'type'    => 'info',
							'message' => '['.$percent.'%] - ' . Yii::t('list_import', 'The email "{email}" has been found, we will update it.', array(
									'{email}' => CHtml::encode($email),
								)),
							'counter' => true,
						));
					}

					foreach ($fields as $detail) {
						if (!isset($tagToModel[$detail['tagName']])) {
							continue;
						}
						$fieldModel = $tagToModel[$detail['tagName']];
						$valueModel = ListFieldValue::model()->findByAttributes(array(
							'field_id'      => $fieldModel->field_id,
							'subscriber_id' => $subscriber->subscriber_id,
						));
						if (empty($valueModel)) {
							$valueModel = new ListFieldValue();
							$valueModel->field_id      = $fieldModel->field_id;
							$valueModel->subscriber_id = $subscriber->subscriber_id;
						}
						$valueModel->value = $detail['tagValue'];
						$valueModel->save();
					}

					++$importCount;
					
					if ($finished) {
						break;
					}
					
				} catch (Exception $e) {

					Yii::log($e->getMessage(), CLogger::LEVEL_ERROR);
				}
			}
			
			if ($finished) {
				return $this->renderMessage(array(
					'result'  => 'error',
					'message' => $finished,
					'return'  => 0,
				));
			}
		}

		return $this->renderMessage(array(
			'result'  => 'success',
			'message' => Yii::t('list_import', 'The import process has finished!'),
			'return'  => 0,
		));
	}

	/**
	 * @return int
	 * @throws CException
	 */
	public function actionText()
	{
		Yii::app()->hooks->doAction('console_command_list_import_before_process', new CAttributeCollection(array(
			'command'    => $this,
			'importType' => 'text',
			'listUid'    => $this->list_uid,
			'filePath'   => $this->file_path,
		)));

		$result = $this->processText(array(
			'list_uid'    => $this->list_uid,
			'file_path'   => $this->file_path,
		));

		Yii::app()->hooks->doAction('console_command_list_import_after_process', new CAttributeCollection(array(
			'command'    => $this,
			'importType' => 'text',
			'listUid'    => $this->list_uid,
			'filePath'   => $this->file_path,
		)));

		return $result;
	}

	/**
	 * @param array $params
	 * @return int
	 * @throws CException
	 */
	protected function processText(array $params)
	{
		if (empty($params['list_uid'])) {
			return $this->renderMessage(array(
				'result'  => 'error',
				'message' => Yii::t('list_import', 'Call this command with the --list_uid=XYZ param where XYZ is the 13 chars unique list id.'),
				'return'  => 1,
			));
		}

		$list = Lists::model()->findByUid($params['list_uid']);
		if (empty($list)) {
			return $this->renderMessage(array(
				'result'  => 'error',
				'message' => Yii::t('list_import', 'The list with the uid {uid} was not found in database.', array(
					'{uid}' => $params['list_uid'],
				)),
				'return' => 1,
			));
		}

		if (empty($params['file_path'])) {
			return $this->renderMessage(array(
				'result'  => 'error',
				'message' => Yii::t('list_import', 'Call this command with the --file_path=/some/file.txt param where /some/file.txt is the full path to the csv file to be imported.'),
				'return'  => 1,
			));
		}

		$options      = Yii::app()->options;
		$importAtOnce = (int)$options->get('system.importer.import_at_once', 50);
		$pause        = (int)$options->get('system.importer.pause', 1);

		$file = new SplFileObject($params['file_path']);
		// $file->setFlags(SplFileObject::SKIP_EMPTY | SplFileObject::DROP_NEW_LINE | SplFileObject::READ_AHEAD);

		$file->seek($file->getSize());
		$totalFileRecords = $file->key() + 1;
		$file->seek(0);

		$customer              = $list->customer;
		$totalSubscribersCount = 0;
		$listSubscribersCount  = 0;
		$maxSubscribersPerList = (int)$customer->getGroupOption('lists.max_subscribers_per_list', -1);
		$maxSubscribers        = (int)$customer->getGroupOption('lists.max_subscribers', -1);

		if ($maxSubscribers > -1 || $maxSubscribersPerList > -1) {
			$criteria = new CDbCriteria();
			$criteria->select = 'COUNT(DISTINCT(t.email)) as counter';

			if ($maxSubscribers > -1 && ($listsIds = $customer->getAllListsIdsNotMerged())) {
				$criteria->addInCondition('t.list_id', $listsIds);
				$totalSubscribersCount = ListSubscriber::model()->count($criteria);
				if ($totalSubscribersCount >= $maxSubscribers) {
					return $this->renderMessage(array(
						'result'  => 'error',
						'message' => Yii::t('list_import', 'You have reached the maximum number of allowed subscribers.'),
						'return'  => 1,
					));
				}
			}

			if ($maxSubscribersPerList > -1) {
				$criteria->compare('t.list_id', (int)$list->list_id);
				$listSubscribersCount = ListSubscriber::model()->count($criteria);
				if ($listSubscribersCount >= $maxSubscribersPerList) {
					return $this->renderMessage(array(
						'result'  => 'error',
						'message' => Yii::t('list_import', 'You have reached the maximum number of allowed subscribers into this list.'),
						'return'  => 1,
					));
				}
			}
		}

		$rounds = round($totalFileRecords / $importAtOnce);
		for ($rCount = 1; $rCount <= $rounds; $rCount++) {

			$offset = $importAtOnce * ($rCount - 1);
			if ($offset >= $totalFileRecords) {
				return $this->renderMessage(array(
					'result'  => 'success',
					'message' => Yii::t('list_import', 'The import process has finished!'),
					'return'  => 0,
				));
			}
			$file->seek($offset > 0 ? $offset - 1 : 0);

			$ioFilter = Yii::app()->ioFilter;
			$emails   = array();
			$i        = 0;

			while (!$file->eof()) {
				$emails[] = $ioFilter->stripPurify(trim($file->fgets()));
				++$i;
				if ($i >= $importAtOnce) {
					break;
				}
			}

			if (empty($emails)) {
				if ($rCount == 1) {
					return $this->renderMessage(array(
						'result'  => 'error',
						'message' => Yii::t('list_import', 'Your file does not contain enough data to be imported!'),
						'return'  => 1,
					));
				} else {
					return $this->renderMessage(array(
						'result'  => 'success',
						'message' => Yii::t('list_import', 'The import process has finished!'),
						'return'  => 0,
					));
				}
			}

			// trim them
			$emails = array_map('trim', $emails);

			// since 1.3.5.9
			$bulkEmails = array();
			foreach ($emails as $email) {
				if (!EmailBlacklist::getFromStore($email)) {
					$bulkEmails[$email] = false;
				}
			}
			$failures = (array)Yii::app()->hooks->applyFilters('list_import_data_bulk_check_failures', array(), (array)$bulkEmails);
			foreach ($failures as $email => $message) {
				EmailBlacklist::addToBlacklist($email, $message);
			}
			// end 1.3.5.9

			$fieldModel = ListField::model()->findByAttributes(array(
				'list_id' => $list->list_id,
				'tag'     => 'EMAIL',
			));

			$finished    = false;
			$importCount = 0;
			$importLog   = array();

			// since 1.3.5.9
			Yii::app()->hooks->doAction('list_import_before_processing_data', $collection = new CAttributeCollection(array(
				'data'        => $emails,
				'list'        => $list,
				'importLog'   => $importLog,
				'finished'    => $finished,
				'importCount' => $importCount,
				'failures'    => $failures,
				'importType'  => 'text'
			)));

			$emails      = $collection->data;
			$importLog   = $collection->importLog;
			$importCount = $collection->importCount;
			$finished    = $collection->finished;
			$failures    = $collection->failures;
			//

			foreach ($emails as $email) {

				$this->renderMessage(array(
					'type'    => 'info',
					'message' => Yii::t('list_import', 'Checking the list for the email: "{email}"', array(
						'{email}' => CHtml::encode($email),
					)),
					'counter' => false,
				));

				if (!empty($failures[$email])) {
					$this->renderMessage(array(
						'type'    => 'error',
						'message' => Yii::t('list_import', 'Failed to save the email "{email}", reason: {reason}', array(
							'{email}'  => CHtml::encode($email),
							'{reason}' => '<br />'.$failures[$email],
						)),
						'counter' => true,
					));
					continue;
				}

				$subscriber = null;
				if (!empty($email)) {
					$subscriber = ListSubscriber::model()->findByAttributes(array(
						'list_id' => $list->list_id,
						'email'   => $email,
					));
				}
				
				try {

					if (empty($subscriber)) {

						$this->renderMessage(array(
							'type'    => 'info',
							'message' => Yii::t('list_import', 'The email "{email}" was not found, we will try to create it...', array(
								'{email}' => CHtml::encode($email),
							)),
							'counter' => false,
						));

						$subscriber = new ListSubscriber();
						$subscriber->list_id = $list->list_id;
						$subscriber->email   = $email;
						$subscriber->source  = ListSubscriber::SOURCE_IMPORT;
						$subscriber->status  = ListSubscriber::STATUS_CONFIRMED;

						$validator = new CEmailValidator();
						$validator->allowEmpty  = false;
						$validator->validateIDN = true;
						if (Yii::app()->options->get('system.common.dns_email_check', false)) {
							$validator->checkMX   = CommonHelper::functionExists('checkdnsrr');
							$validator->checkPort = CommonHelper::functionExists('dns_get_record') && CommonHelper::functionExists('fsockopen');
						}
						$validEmail = !empty($email) && $validator->validateValue($email);

						if (!$validEmail) {
							$subscriber->addError('email', Yii::t('list_import', 'Invalid email address!'));
						} else {
							$blacklisted = $subscriber->getIsBlacklisted(array('checkZone' => EmailBlacklist::CHECK_ZONE_LIST_IMPORT));
							if (!empty($blacklisted)) {
								$subscriber->addError('email', Yii::t('list_import', 'This email address is blacklisted!'));
							}
						}

						if (!$validEmail || $subscriber->hasErrors() || !$subscriber->save()) {
							$this->renderMessage(array(
								'type'    => 'error',
								'message' => Yii::t('list_import', 'Failed to save the email "{email}", reason: {reason}', array(
									'{email}'  => CHtml::encode($email),
									'{reason}' => '<br />'.$subscriber->shortErrors->getAllAsString()
								)),
								'counter' => true,
							));
							
							continue;
						}

						$listSubscribersCount++;
						$totalSubscribersCount++;

						if ($maxSubscribersPerList > -1 && $listSubscribersCount >= $maxSubscribersPerList) {
							$finished = Yii::t('lists', 'You have reached the maximum number of allowed subscribers into this list.');
							break;
						}

						if ($maxSubscribers > -1 && $totalSubscribersCount >= $maxSubscribers) {
							$finished = Yii::t('lists', 'You have reached the maximum number of allowed subscribers.');
							break;
						}

						// 1.5.2
						$subscriber->takeListSubscriberAction(ListSubscriberAction::ACTION_SUBSCRIBE);

						$this->renderMessage(array(
							'type'    => 'success',
							'message' => Yii::t('list_import', 'The email "{email}" has been successfully saved.', array(
								'{email}' => CHtml::encode($email),
							)),
							'counter' => true,
						));

					} else {

						$this->renderMessage(array(
							'type'    => 'info',
							'message' => Yii::t('list_import', 'The email "{email}" has been found, we will update it.', array(
								'{email}' => CHtml::encode($email),
							)),
							'counter' => true,
						));
					}

					$valueModel = ListFieldValue::model()->findByAttributes(array(
						'field_id'      => $fieldModel->field_id,
						'subscriber_id' => $subscriber->subscriber_id,
					));
					if (empty($valueModel)) {
						$valueModel = new ListFieldValue();
						$valueModel->field_id      = $fieldModel->field_id;
						$valueModel->subscriber_id = $subscriber->subscriber_id;
					}
					$valueModel->value = $email;
					$valueModel->save();

					++$importCount;
					
					if ($finished) {
						break;
					}
					
				} catch (Exception $e) {
					
					Yii::log($e->getMessage(), CLogger::LEVEL_ERROR);
				}
			}

			if ($finished) {
				return $this->renderMessage(array(
					'result'  => 'error',
					'message' => $finished,
					'return'  => 1,
				));
			}
		}

		return $this->renderMessage(array(
			'result'  => 'success',
			'message' => Yii::t('list_import', 'The import process has finished!'),
			'return'  => 0,
		));
	}

	/**
	 * @return int
	 */
	public function actionUrl()
	{
		$this->stdout('Starting to fetch data from remote urls...');

		$mutexKey = sha1(__METHOD__);
		if (!Yii::app()->mutex->acquire($mutexKey)) {
			$this->stdout('Seems this process is already running... aborting for now!');
			return 1;
		}

		try {

			$storagePath = Yii::getPathOfAlias('common.runtime.list-import-url');
			if (!file_exists($storagePath) || !@is_dir($storagePath)) {
				if (!@mkdir($storagePath)) {
					throw new Exception('Unable to create: ' . $storagePath);
				}
			}

			$storagePath = Yii::getPathOfAlias('common.runtime.list-import-queue');
			if (!file_exists($storagePath) || !@is_dir($storagePath)) {
				if (!@mkdir($storagePath)) {
					throw new Exception('Unable to create: ' . $storagePath);
				}
			}

			$models = ListUrlImport::model()->findAllByAttributes(array(
				'status' => ListUrlImport::STATUS_ACTIVE,
			));

			foreach ($models as $model) {

				$this->stdout('Processing the url: ' . $model->url);

				if (!in_array($model->getExtension(), array('.csv', '.txt'))) {
					$model->failures = PHP_INT_MAX; // force inactive
					$model->save(false);
					$this->stdout('The url processing failed!');
					if (is_file($model->getDownloadPath())) {
						@unlink($model->getDownloadPath());
					}
					continue;
				}

				if (!$model->getIsUrlValid() || !$model->download()) {
					$model->failures++;
					$model->save(false);
					$this->stdout('The url processing failed!');
					if (is_file($model->getDownloadPath())) {
						@unlink($model->getDownloadPath());
					}
					continue;
				}

				if (!is_file($model->getDownloadPath())) {
					$this->stdout('The url processing failed!');
					continue;
				}

				$fileSize = @filesize($model->getDownloadPath());
				if ((int)$fileSize == 0) {
					@unlink($model->getDownloadPath());
					$this->stdout('The contents of the url returned 0 bytes!');
					continue;
				}

				$fileNumber      = 1;
				$basePath        = Yii::getPathOfAlias('common.runtime.list-import-queue') . '/' . $model->list->list_uid . '-';
				$destinationPath = $basePath . $fileNumber . $model->getExtension();
				while (is_file($destinationPath)) {
					$fileNumber++;
					$destinationPath = $basePath . $fileNumber . $model->getExtension();
				}

				$this->stdout('Copy ' . $model->getDownloadPath() . ' to ' . $destinationPath);
				@copy($model->getDownloadPath(), $destinationPath);

				$this->stdout('Deleting ' . $model->getDownloadPath());
				@unlink($model->getDownloadPath());

				$this->stdout('Done processing ' . $model->url);
			}

			$this->stdout('Done fetching data from remote urls...');

		} catch (Exception $e) {

			$this->stdout(__LINE__ . ': ' .  $e->getMessage());
			Yii::log($e->getMessage(), CLogger::LEVEL_ERROR);
		}

		Yii::app()->mutex->release($mutexKey);
		return 0;
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