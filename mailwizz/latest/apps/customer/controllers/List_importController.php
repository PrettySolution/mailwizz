<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * List_importController
 *
 * Handles the actions for list import related tasks
 *
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.0
 */

class List_importController extends Controller
{
    public function init()
    {
        parent::init();

        if (Yii::app()->options->get('system.importer.enabled', 'yes') != 'yes') {
            $this->redirect(array('lists/index'));
        }

        $customer = Yii::app()->customer->getModel();
        if ($customer->getGroupOption('lists.can_import_subscribers', 'yes') != 'yes') {
            $this->redirect(array('lists/index'));
        }

        $this->getData('pageScripts')->add(array('src' => AssetsUrl::js('list-import.js')));
    }

    /**
     * Define the filters for various controller actions
     * Merge the filters with the ones from parent implementation
     */
    public function filters()
    {
        return CMap::mergeArray(parent::filters(), array(
            'postOnly + csv, database, url',
        ));
    }

    /**
     * List available import options
     */
    public function actionIndex($list_uid)
    {
        $list       = $this->loadListModel($list_uid);
        $options    = Yii::app()->options;
        $request    = Yii::app()->request;
        $importCsv  = new ListCsvImport('upload');
        $importText = new ListTextImport('upload');
        $importDb   = new ListDatabaseImport();

        $importUrl = ListUrlImport::model()->findByAttributes(array(
            'list_id' => $list->list_id,
        ));
        if (empty($importUrl)) {
            $importUrl = new ListUrlImport();
        }
        
        $cliEnabled = $options->get('system.importer.cli_enabled', 'no') == 'yes';
        $webEnabled = $options->get('system.importer.web_enabled', 'yes') == 'yes';
        $urlEnabled = $options->get('system.importer.url_enabled', 'no') == 'yes';

        $this->setData(array(
            'pageMetaTitle'     => $this->data->pageMetaTitle . ' | '. Yii::t('list_import', 'Import subscribers into your list'),
            'pageHeading'       => Yii::t('list_import', 'Import subscribers'),
            'pageBreadcrumbs'   => array(
                Yii::t('lists', 'Lists') => $this->createUrl('lists/index'),
                $list->name . ' ' => $this->createUrl('lists/overview', array('list_uid' => $list->list_uid)),
                Yii::t('list_import', 'Import subscribers')
            )
        ));

        $maxUploadSize = (int)$options->get('system.importer.file_size_limit', 1024 * 1024 * 1) / 1024 / 1024;
        $this->render('list', compact('list', 'importCsv', 'importText', 'importDb', 'importUrl', 'maxUploadSize', 'cliEnabled', 'webEnabled', 'urlEnabled'));
    }

    /**
     * Handle the CSV import and place it in queue
     */
    public function actionCsv_queue($list_uid)
    {
        return $this->handleQueuePlacement($list_uid, 'ListCsvImport');
    }

    /**
     * Handle the CSV import
     */
    public function actionCsv($list_uid)
    {
        $list     = $this->loadListModel($list_uid);
        $options  = Yii::app()->options;
        $request  = Yii::app()->request;
        $notify   = Yii::app()->notify;

        $importLog = array();
        $filePath  = Yii::getPathOfAlias('common.runtime.list-import').'/';

        $importAtOnce = (int)$options->get('system.importer.import_at_once', 50);
        $pause        = (int)$options->get('system.importer.pause', 1);

        set_time_limit(0);
        if ($memoryLimit = $options->get('system.importer.memory_limit')) {
            ini_set('memory_limit', $memoryLimit);
        }
        ini_set('auto_detect_line_endings', true);

        $import = new ListCsvImport('upload');
        $import->file_size_limit = (int)$options->get('system.importer.file_size_limit', 1024 * 1024 * 1); // 1 mb
        $import->attributes      = (array)$request->getPost($import->modelName, array());
        $import->file            = CUploadedFile::getInstance($import, 'file');
		
        if (!empty($import->file)) {
            if (!$import->upload()) {
                $notify->addError(Yii::t('app', 'Your form has a few errors, please fix them and try again!'));
                $notify->addError($import->shortErrors->getAllAsString());
                $this->redirect(array('list_import/index', 'list_uid' => $list->list_uid));
            }

            $this->setData(array(
                'pageMetaTitle'     => $this->data->pageMetaTitle.' | '.Yii::t('list_import', 'Import subscribers'),
                'pageHeading'       => Yii::t('list_import', 'Import subscribers'),
                'pageBreadcrumbs'   => array(
                    Yii::t('lists', 'Lists') => $this->createUrl('lists/index'),
                    $list->name . ' ' => $this->createUrl('lists/overview', array('list_uid' => $list->list_uid)),
                    Yii::t('list_import', 'CSV Import')
                )
            ));

            return $this->render('csv', compact('list', 'import', 'importAtOnce', 'pause'));
        }

        // only ajax from now on.
        if (!$request->isAjaxRequest) {
            $this->redirect(array('list_import/index', 'list_uid' => $list->list_uid));
        }

        try {

            if (!is_file($filePath.$import->file_name)) {
                return $this->renderJson(array(
                    'result'  => 'error',
                    'message' => Yii::t('list_import', 'The import file does not exist anymore!')
                ));
            }
            
            $delimiter = StringHelper::detectCsvDelimiter($filePath.$import->file_name);
            $file      = new SplFileObject($filePath.$import->file_name);
            $file->setCsvControl($delimiter);
            $file->setFlags(SplFileObject::READ_CSV | SplFileObject::SKIP_EMPTY | SplFileObject::DROP_NEW_LINE | SplFileObject::READ_AHEAD);
            $columns = $file->current(); // the header

            if (empty($columns)) {
                unset($file);
                @unlink($filePath.$import->file_name);
                return $this->renderJson(array(
                    'result'  => 'error',
                    'message' => Yii::t('list_import', 'Your file does not contain the header with the fields title!')
                ));
            }

            if ($import->is_first_batch) {
                $linesCount         = iterator_count($file);
                $totalFileRecords   = $linesCount - 1; // minus the header
                $import->rows_count = $totalFileRecords;
            } else {
                $totalFileRecords = $import->rows_count;
            }

            $file->seek(1);

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
                        return $this->renderJson(array(
                            'result'  => 'error',
                            'message' => Yii::t('lists', 'You have reached the maximum number of allowed subscribers.'),
                        ));
                    }
                }

                if ($maxSubscribersPerList > -1) {
                    $criteria->compare('t.list_id', (int)$list->list_id);
                    $listSubscribersCount = ListSubscriber::model()->count($criteria);
                    if ($listSubscribersCount >= $maxSubscribersPerList) {
                        return $this->renderJson(array(
                            'result'  => 'error',
                            'message' => Yii::t('lists', 'You have reached the maximum number of allowed subscribers into this list.'),
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
		        unset($file);
		        @unlink($filePath.$import->file_name);
		        return $this->renderJson(array(
			        'result'  => 'error',
			        'message' => Yii::t('list_import', 'Empty column names are not allowed!')
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
                unset($file);
                @unlink($filePath.$import->file_name);
                return $this->renderJson(array(
                    'result'  => 'error',
                    'message' => Yii::t('list_import', 'Cannot find the "email" column in your file!')
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
                unset($file);
                @unlink($filePath.$import->file_name);
                return $this->renderJson(array(
                    'result'  => 'error',
                    'message' => Yii::t('list_import', 'Your list contains the columns: "{columns}" which are system reserved. Please update your file and change the column names!', array(
                        '{columns}' => implode(', ', $foundReservedColumns)
                    ))
                ));
            }

            if ($import->is_first_batch) {
                if ($logAction = Yii::app()->customer->getModel()->asa('logAction')) {
                    $logAction->listImportStart($list, $import);
                }

                $importLog[] = array(
                    'type'    => 'info',
                    'message' => Yii::t('list_import', 'Found the following column names: {columns}', array(
                        '{columns}' => implode(', ', $columns)
                    )),
                    'counter' => false,
                );
            }

            $offset = $importAtOnce * ($import->current_page - 1);
            if ($offset >= $totalFileRecords) {
	            if (is_file($filePath.$import->file_name)) {
		            @unlink($filePath.$import->file_name);
	            }
                return $this->renderJson(array(
                    'result'  => 'success',
                    'message' => Yii::t('list_import', 'The import process has finished!')
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
            unset($file);

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
            unset($csvData);

            if (empty($data) || count($data) < 1) {
                @unlink($filePath.$import->file_name);

                if ($logAction = Yii::app()->customer->getModel()->asa('logAction')) {
                    $logAction->listImportEnd($list, $import);
                }

                if ($import->is_first_batch) {
                    return $this->renderJson(array(
                        'result'  => 'error',
                        'message' => Yii::t('list_import', 'Your file does not contain enough data to be imported!')
                    ));
                } else {
                    return $this->renderJson(array(
                        'result'  => 'success',
                        'message' => Yii::t('list_import', 'The import process has finished!')
                    ));
                }
            }

            $tagToModel = array();
            foreach ($data[0] as $sample) {

                if ($import->is_first_batch) {
                    $importLog[] = array(
                        'type'    => 'info',
                        'message' => Yii::t('list_import', 'Checking to see if the tag "{tag}" is defined in your list fields...', array(
                            '{tag}' => CHtml::encode($sample['tagName'])
                        )),
                        'counter' => false,
                    );
                }

                $model = ListField::model()->findByAttributes(array(
                    'list_id' => $list->list_id,
                    'tag'     => $sample['tagName']
                ));

                if (!empty($model)) {

                    if ($import->is_first_batch) {
                        $importLog[] = array(
                            'type'    => 'info',
                            'message' => Yii::t('list_import', 'The tag "{tag}" is already defined in your list fields.', array(
                                '{tag}' => CHtml::encode($sample['tagName'])
                            )),
                            'counter' => false,
                        );
                    }

                    $tagToModel[$sample['tagName']] = $model;
                    continue;
                }

                if ($import->is_first_batch) {
                    $importLog[] = array(
                        'type'    => 'info',
                        'message' => Yii::t('list_import', 'The tag "{tag}" is not defined in your list fields, we will try to create it.', array(
                            '{tag}' => CHtml::encode($sample['tagName'])
                        )),
                        'counter' => false,
                    );
                }

                $model = new ListField();
                $model->type_id = $fieldType->type_id;
                $model->list_id = $list->list_id;
                $model->label   = $sample['name'];
                $model->tag     = $sample['tagName'];

                if ($model->save()) {

                    if ($import->is_first_batch) {
                        $importLog[] = array(
                            'type'    => 'success',
                            'message' => Yii::t('list_import', 'The tag "{tag}" has been successfully created.', array(
                                '{tag}' => CHtml::encode($sample['tagName'])
                            )),
                            'counter' => false,
                        );
                    }

                    $tagToModel[$sample['tagName']] = $model;

                } else {

                    if ($import->is_first_batch) {
                        $importLog[] = array(
                            'type'    => 'error',
                            'message' => Yii::t('list_import', 'The tag "{tag}" cannot be saved, reason: {reason}', array(
                                '{tag}'    => CHtml::encode($sample['tagName']),
                                '{reason}' => '<br />'.$model->shortErrors->getAllAsString()
                            )),
                            'counter' => false,
                        );
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
			        unset($data[$index]);
			        continue;
		        }

		        $importLog[] = array(
			        'type'    => 'info',
			        'message' => Yii::t('list_import', 'Checking the list for the email: "{email}"', array(
				        '{email}' => CHtml::encode($email),
			        )),
			        'counter' => false,
		        );

		        if (!empty($failures[$email])) {
			        $importLog[] = array(
				        'type'    => 'error',
				        'message' => Yii::t('list_import', 'Failed to save the email "{email}", reason: {reason}', array(
					        '{email}'  => CHtml::encode($email),
					        '{reason}' => '<br />'.$failures[$email],
				        )),
				        'counter' => true,
			        );
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

				        $importLog[] = array(
					        'type'    => 'info',
					        'message' => Yii::t('list_import', 'The email "{email}" was not found, we will try to create it...', array(
						        '{email}' => CHtml::encode($email),
					        )),
					        'counter' => false,
				        );

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
					        $importLog[] = array(
						        'type'    => 'error',
						        'message' => Yii::t('list_import', 'Failed to save the email "{email}", reason: {reason}', array(
							        '{email}'  => CHtml::encode($email),
							        '{reason}' => '<br />'.$subscriber->shortErrors->getAllAsString()
						        )),
						        'counter' => true,
					        );
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
				        
				        $importLog[] = array(
					        'type'    => 'success',
					        'message' => Yii::t('list_import', 'The email "{email}" has been successfully saved.', array(
						        '{email}' => CHtml::encode($email),
					        )),
					        'counter' => true,
				        );

			        } else {

				        $importLog[] = array(
					        'type'    => 'info',
					        'message' => Yii::t('list_import', 'The email "{email}" has been found, we will update it.', array(
						        '{email}' => CHtml::encode($email),
					        )),
					        'counter' => true,
				        );
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

			        unset($data[$index]);
			        ++$importCount;
			        
			        if ($finished) {
				        break;
			        }
			        
		        } catch (Exception $e) {
			        Yii::log($e->getMessage(), CLogger::LEVEL_ERROR);
		        }
	        }

            if ($finished) {
                return $this->renderJson(array(
                    'result'  => 'error',
                    'message' => $finished,
                ));
            }

            $import->is_first_batch = 0;
            $import->current_page++;

            return $this->renderJson(array(
                'result'    => 'success',
                'message'   => Yii::t('list_import', 'Imported {count} subscribers starting from row {rowStart} and ending with row {rowEnd}! Going further, please wait...', array(
                    '{count}'    => $importCount,
                    '{rowStart}' => $offset,
                    '{rowEnd}'   => $offset + $importAtOnce,
                )),
                'attributes'   => $import->attributes,
                'import_log'   => $importLog,
                'recordsCount' => $totalFileRecords,
            ));

        } catch(Exception $e) {

            if (isset($file)) {
                unset($file);
            }

            if (is_file($filePath.$import->file_name)) {
                @unlink($filePath.$import->file_name);
            }

            return $this->renderJson(array(
                'result'  => 'error',
                'message' => Yii::t('list_import', 'Your file cannot be imported, a general error has been encountered: {message}!', array(
                    '{message}' => $e->getMessage()
                ))
            ));

        }
    }

    /**
     * Handle the Database import
     */
    public function actionDatabase($list_uid)
    {
        $list      = $this->loadListModel($list_uid);
        $options   = Yii::app()->options;
        $request   = Yii::app()->request;
        $notify    = Yii::app()->notify;
		
        if (!$request->isPostRequest) {
            $this->redirect(array('list_import/index', 'list_uid' => $list->list_uid));
        }

        $importAtOnce = (int)$options->get('system.importer.import_at_once', 50);
        $pause        = (int)$options->get('system.importer.pause', 1);

        set_time_limit(0);
        if ($memoryLimit = $options->get('system.importer.memory_limit')) {
            ini_set('memory_limit', $memoryLimit);
        }

        $import = new ListDatabaseImport();
        $import->attributes = (array)$request->getPost($import->modelName, array());
        $import->validateAndConnect();

        if ($import->hasErrors()) {
            $message = Yii::t('app', 'Your form has a few errors, please fix them and try again!') . '<br />' . $import->shortErrors->getAllAsString();
            if ($request->isAjaxRequest) {
               return $this->renderJson(array(
                    'result'  => 'error',
                    'message' => $message
                ));
            }
            $notify->addError($message);
            $this->redirect(array('list_import/index', 'list_uid' => $list->list_uid));
        }

        if (!$request->isAjaxRequest) {

            $this->setData(array(
                'pageMetaTitle'     => $this->data->pageMetaTitle.' | '.Yii::t('list_import', 'Import subscribers'),
                'pageHeading'       => Yii::t('list_import', 'Import subscribers'),
                'pageBreadcrumbs'   => array(
                    Yii::t('lists', 'Lists') => $this->createUrl('lists/index'),
                    $list->name . ' ' => $this->createUrl('lists/overview', array('list_uid' => $list->list_uid)),
                    Yii::t('list_import', 'Database Import')
                )
            ));

            return $this->render('database', compact('list', 'import', 'importAtOnce', 'pause'));
        }

	    $importLog = array();
        
        try {

            $columns = $import->getColumns();
            if (empty($columns)) {
                return $this->renderJson(array(
                    'result'  => 'error',
                    'message' => Yii::t('list_import', 'Cannot find your database columns!')
                ));
            }

            if ($import->is_first_batch) {
                $totalRecords = $import->rows_count = $import->countResults();
            } else {
                $totalRecords = $import->rows_count;
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
                        return $this->renderJson(array(
                            'result'  => 'error',
                            'message' => Yii::t('lists', 'You have reached the maximum number of allowed subscribers.'),
                        ));
                    }
                }

                if ($maxSubscribersPerList > -1) {
                    $criteria->compare('t.list_id', (int)$list->list_id);
                    $listSubscribersCount = ListSubscriber::model()->count($criteria);
                    if ($listSubscribersCount >= $maxSubscribersPerList) {
                        return $this->renderJson(array(
                            'result'  => 'error',
                            'message' => Yii::t('lists', 'You have reached the maximum number of allowed subscribers into this list.'),
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
            
	        $foundTags = array();
            foreach ($columns as $value) {
                $tagName     = StringHelper::getTagFromString($value);
                $tagName     = str_replace(array_keys($searchReplaceTags), array_values($searchReplaceTags), $tagName);
                $foundTags[] = $tagName;
            }

	        // empty tags, not allowed
	        if (count($foundTags) !== count(array_filter($foundTags))) {
		        return $this->renderJson(array(
			        'result'  => 'error',
			        'message' => Yii::t('list_import', 'Empty column names are not allowed!')
		        ));
	        }

            $foundEmailTag = false;
            foreach ($foundTags as $tagName) {
                if ($tagName === 'EMAIL' || $tagName == strtoupper($import->email_column)) {
                    $foundEmailTag = true;
                    break;
                }
            }

            if (!$foundEmailTag) {
                return $this->renderJson(array(
                    'result'  => 'error',
                    'message' => Yii::t('list_import', 'Cannot find the "email" column in your database!')
                ));
            }

            $foundReservedColumns = array();
            foreach ($columns as $columnName) {
                $columnName    = StringHelper::getTagFromString($columnName);
                $columnName    = str_replace(array_keys($searchReplaceTags), array_values($searchReplaceTags), $columnName);
                $tagIsReserved = TagRegistry::model()->findByAttributes(array('tag' => '['.$columnName.']'));
                if (!empty($tagIsReserved)) {
                    $foundReservedColumns[] = $columnName;
                }
            }

            if (!empty($foundReservedColumns)) {
                return $this->renderJson(array(
                    'result'  => 'error',
                    'message' => Yii::t('list_import', 'Your database contains the columns: "{columns}" which are system reserved. Please update your database and change the column names or ignore them!', array(
                        '{columns}' => implode(', ', $foundReservedColumns)
                    ))
                ));
            }

            if ($import->is_first_batch) {
                if ($logAction = Yii::app()->customer->getModel()->asa('logAction')) {
                    $logAction->listImportStart($list, $import);
                }

                $importLog[] = array(
                    'type'    => 'info',
                    'message' => Yii::t('list_import', 'Found the following column names: {columns}', array(
                        '{columns}' => implode(', ', $columns)
                    )),
                    'counter' => false,
                );
            }

            $offset = $importAtOnce * ($import->current_page - 1);
            if ($offset >= $totalRecords) {
                if ($logAction = Yii::app()->customer->getModel()->asa('logAction')) {
                    $logAction->listImportEnd($list, $import);
                }
                return $this->renderJson(array(
                    'result'  => 'success',
                    'message' => Yii::t('list_import', 'The import process has finished!')
                ));
            }

            $results = $import->getResults($offset, $importAtOnce);
            if (empty($results)) {
                if ($logAction = Yii::app()->customer->getModel()->asa('logAction')) {
                    $logAction->listImportEnd($list, $import);
                }
                return $this->renderJson(array(
                    'result'  => 'success',
                    'message' => Yii::t('list_import', 'The import process has finished!')
                ));
            }

            $fieldType = ListFieldType::model()->findByAttributes(array(
                'identifier' => 'text',
            ));

            $data = array();
            foreach ($results as $result) {
                $rowData = array();
                foreach ($result as $name => $value) {
                    $tagName = StringHelper::getTagFromString($name);
                    $tagName = str_replace(array_keys($searchReplaceTags), array_values($searchReplaceTags), $tagName);

                    $rowData[] = array(
                        'name'     => ucwords(str_replace('_', ' ', $name)),
                        'tagName'  => trim($tagName),
                        'tagValue' => trim($value),
                    );
                }
                $data[] = $rowData;
            }

            if (empty($data) || count($data) < 1) {

                if ($logAction = Yii::app()->customer->getModel()->asa('logAction')) {
                    $logAction->listImportEnd($list, $import);
                }

                if ($import->is_first_batch) {
                    return $this->renderJson(array(
                        'result'  => 'error',
                        'message' => Yii::t('list_import', 'Your database does not contain enough data to be imported!')
                    ));
                } else {
                    return $this->renderJson(array(
                        'result'  => 'success',
                        'message' => Yii::t('list_import', 'The import process has finished!')
                    ));
                }
            }

            $tagToModel = array();
            foreach ($data[0] as $sample) {

                if ($import->is_first_batch) {
                    $importLog[] = array(
                        'type'     => 'info',
                        'message'  => Yii::t('list_import', 'Checking to see if the tag "{tag}" is defined in your list fields...', array(
                            '{tag}'=> CHtml::encode($sample['tagName'])
                        )),
                        'counter'  => false,
                    );
                }

                $model = ListField::model()->findByAttributes(array(
                    'list_id' => $list->list_id,
                    'tag'     => $sample['tagName']
                ));

                if (!empty($model)) {

                    if ($import->is_first_batch) {
                        $importLog[] = array(
                            'type'    => 'info',
                            'message' => Yii::t('list_import', 'The tag "{tag}" is already defined in your list fields.', array(
                                '{tag}' => CHtml::encode($sample['tagName'])
                            )),
                            'counter' => false,
                        );
                    }

                    $tagToModel[$sample['tagName']] = $model;
                    continue;
                }

                if ($import->is_first_batch) {
                    $importLog[] = array(
                        'type'    => 'info',
                        'message' => Yii::t('list_import', 'The tag "{tag}" is not defined in your list fields, we will try to create it.', array(
                            '{tag}' => CHtml::encode($sample['tagName'])
                        )),
                        'counter' => false,
                    );
                }

                $model = new ListField();
                $model->type_id = $fieldType->type_id;
                $model->list_id = $list->list_id;
                $model->label   = $sample['name'];
                $model->tag     = $sample['tagName'];

                if ($model->save()) {

                    if ($import->is_first_batch) {
                        $importLog[] = array(
                            'type'    => 'success',
                            'message' => Yii::t('list_import', 'The tag "{tag}" has been successfully created.', array(
                                '{tag}' => CHtml::encode($sample['tagName'])
                            )),
                            'counter' => false,
                        );
                    }

                    $tagToModel[$sample['tagName']] = $model;

                } else {

                    if ($import->is_first_batch) {
                        $importLog[] = array(
                            'type'    => 'error',
                            'message' => Yii::t('list_import', 'The tag "{tag}" cannot be saved, reason: {reason}', array(
                                '{tag}'    => CHtml::encode($sample['tagName']),
                                '{reason}' => '<br />'.$model->shortErrors->getAllAsString()
                            )),
                            'counter' => false,
                        );
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

            // since 1.3.5.9
            Yii::app()->hooks->doAction('list_import_before_processing_data', $collection = new CAttributeCollection(array(
                'data'        => $data,
                'list'        => $list,
                'importLog'   => $importLog,
                'finished'    => $finished,
                'importCount' => $importCount,
                'failures'    => $failures,
                'importType'  => 'database'
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
			        unset($data[$index]);
			        continue;
		        }

		        $importLog[] = array(
			        'type'    => 'info',
			        'message' => Yii::t('list_import', 'Checking the list for the email: "{email}"', array(
				        '{email}' => CHtml::encode($email),
			        )),
			        'counter' => false,
		        );

		        if (!empty($failures[$email])) {
			        $importLog[] = array(
				        'type'    => 'error',
				        'message' => Yii::t('list_import', 'Failed to save the email "{email}", reason: {reason}', array(
					        '{email}'  => CHtml::encode($email),
					        '{reason}' => '<br />'.$failures[$email],
				        )),
				        'counter' => true,
			        );
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

				        $importLog[] = array(
					        'type'    => 'info',
					        'message' => Yii::t('list_import', 'The email "{email}" was not found, we will try to create it...', array(
						        '{email}' => CHtml::encode($email),
					        )),
					        'counter' => false,
				        );

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
					        $importLog[] = array(
						        'type'    => 'error',
						        'message' => Yii::t('list_import', 'Failed to save the email "{email}", reason: {reason}', array(
							        '{email}'  => CHtml::encode($email),
							        '{reason}' => '<br />'.$subscriber->shortErrors->getAllAsString()
						        )),
						        'counter' => true,
					        );
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

				        $importLog[] = array(
					        'type'    => 'success',
					        'message' => Yii::t('list_import', 'The email "{email}" has been successfully saved.', array(
						        '{email}' => CHtml::encode($email),
					        )),
					        'counter' => true,
				        );

			        } else {

				        $importLog[] = array(
					        'type'    => 'info',
					        'message' => Yii::t('list_import', 'The email "{email}" has been found, we will update it.', array(
						        '{email}' => CHtml::encode($email),
					        )),
					        'counter' => true,
				        );
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
					        $valueModel->field_id = $fieldModel->field_id;
					        $valueModel->subscriber_id = $subscriber->subscriber_id;
				        }
				        $valueModel->value = $detail['tagValue'];
				        $valueModel->save();
			        }

			        unset($data[$index]);
			        ++$importCount;
			        
			        if ($finished) {
				        break;
			        }
			        
		        } catch (Exception $e) {
			        Yii::log($e->getMessage(), CLogger::LEVEL_ERROR);
		        }
	        }
	        
            if ($finished) {
                return $this->renderJson(array(
                    'result'  => 'error',
                    'message' => $finished,
                ));
            }

            $import->is_first_batch = 0;
            $import->current_page++;

            return $this->renderJson(array(
                'result'    => 'success',
                'message'   => Yii::t('list_import', 'Imported {count} subscribers starting from row {rowStart} and ending with row {rowEnd}! Going further, please wait...', array(
                    '{count}'     => $importCount,
                    '{rowStart}'  => $offset,
                    '{rowEnd}'    => $offset + $importAtOnce,
                )),
                'attributes'    => $import->attributes,
                'import_log'    => $importLog,
                'recordsCount'  => $totalRecords,
            ));

        } catch (Exception $e) {

            return $this->renderJson(array(
                'result'    => 'error',
                'message'   => Yii::t('list_import', 'Your database cannot be imported, a general error has been encountered: {message}!', array(
                    '{message}' => $e->getMessage()
                ))
            ));

        }
    }

    /**
     * Handle the Text import
     */
    public function actionText($list_uid)
    {
        $list     = $this->loadListModel($list_uid);
        $options  = Yii::app()->options;
        $request  = Yii::app()->request;
        $notify   = Yii::app()->notify;

        $importLog  = array();
        $filePath   = Yii::getPathOfAlias('common.runtime.list-import').'/';

        $importAtOnce = (int)$options->get('system.importer.import_at_once', 50);
        $pause        = (int)$options->get('system.importer.pause', 1);

        set_time_limit(0);
        if ($memoryLimit = $options->get('system.importer.memory_limit')) {
            ini_set('memory_limit', $memoryLimit);
        }
        ini_set('auto_detect_line_endings', true);

        $import = new ListTextImport('upload');
        $import->file_size_limit = (int)$options->get('system.importer.file_size_limit', 1024 * 1024 * 1); // 1 mb
        $import->attributes      = (array)$request->getPost($import->modelName, array());
        $import->file            = CUploadedFile::getInstance($import, 'file');

        if (!empty($import->file)) {
            if (!$import->upload()) {
                $notify->addError(Yii::t('app', 'Your form has a few errors, please fix them and try again!'));
                $notify->addError($import->shortErrors->getAllAsString());
                $this->redirect(array('list_import/index', 'list_uid' => $list->list_uid));
            }

            $this->setData(array(
                'pageMetaTitle'   => $this->data->pageMetaTitle.' | '.Yii::t('list_import', 'Import subscribers'),
                'pageHeading'     => Yii::t('list_import', 'Import subscribers'),
                'pageBreadcrumbs' => array(
                    Yii::t('lists', 'Lists') => $this->createUrl('lists/index'),
                    $list->name . ' ' => $this->createUrl('lists/overview', array('list_uid' => $list->list_uid)),
                    Yii::t('list_import', 'Text Import')
                )
            ));

            return $this->render('text', compact('list', 'import', 'importAtOnce', 'pause'));
        }

        // only ajax from now on.
        if (!$request->isAjaxRequest) {
            $this->redirect(array('list_import/index', 'list_uid' => $list->list_uid));
        }

        try {

            if (!is_file($filePath.$import->file_name)) {
                return $this->renderJson(array(
                    'result'  => 'error',
                    'message' => Yii::t('list_import', 'The import file does not exist anymore!')
                ));
            }

            $file = new SplFileObject($filePath.$import->file_name);
            // $file->setFlags(SplFileObject::SKIP_EMPTY | SplFileObject::DROP_NEW_LINE | SplFileObject::READ_AHEAD);

            if ($import->is_first_batch) {
                $file->seek($file->getSize());
                $totalFileRecords   = $file->key() + 1;
                $import->rows_count = $totalFileRecords;
                $file->seek(0);
            } else {
                $totalFileRecords = $import->rows_count;
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
                        return $this->renderJson(array(
                            'result'  => 'error',
                            'message' => Yii::t('lists', 'You have reached the maximum number of allowed subscribers.'),
                        ));
                    }
                }

                if ($maxSubscribersPerList > -1) {
                    $criteria->compare('t.list_id', (int)$list->list_id);
                    $listSubscribersCount = ListSubscriber::model()->count($criteria);
                    if ($listSubscribersCount >= $maxSubscribersPerList) {
                        return $this->renderJson(array(
                            'result'  => 'error',
                            'message' => Yii::t('lists', 'You have reached the maximum number of allowed subscribers into this list.'),
                        ));
                    }
                }
            }

            $offset = $importAtOnce * ($import->current_page - 1);
            if ($offset >= $totalFileRecords) {
	            if (is_file($filePath.$import->file_name)) {
		            @unlink($filePath.$import->file_name);
	            }
                return $this->renderJson(array(
                    'result'  => 'success',
                    'message' => Yii::t('list_import', 'The import process has finished!')
                ));
            }

            // $file->seek($offset);
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
            unset($file);

            if (empty($emails)) {
                @unlink($filePath.$import->file_name);
                if ($logAction = Yii::app()->customer->getModel()->asa('logAction')) {
                    $logAction->listImportEnd($list, $import);
                }
                if ($import->is_first_batch) {
                    return $this->renderJson(array(
                        'result'  => 'error',
                        'message' => Yii::t('list_import', 'Your file does not contain enough data to be imported!')
                    ));
                } else {
                    return $this->renderJson(array(
                        'result'  => 'success',
                        'message' => Yii::t('list_import', 'The import process has finished!')
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
            $failures = (array)Yii::app()->hooks->applyFilters('customer_list_import_data_bulk_check_failures', array(), (array)$bulkEmails);
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

		        $importLog[] = array(
			        'type'    => 'info',
			        'message' => Yii::t('list_import', 'Checking the list for the email: "{email}"', array(
				        '{email}' => CHtml::encode($email),
			        )),
			        'counter' => false,
		        );

		        if (!empty($failures[$email])) {
			        $importLog[] = array(
				        'type'    => 'error',
				        'message' => Yii::t('list_import', 'Failed to save the email "{email}", reason: {reason}', array(
					        '{email}'  => CHtml::encode($email),
					        '{reason}' => '<br />'.$failures[$email],
				        )),
				        'counter' => true,
			        );
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

				        $importLog[] = array(
					        'type'    => 'info',
					        'message' => Yii::t('list_import', 'The email "{email}" was not found, we will try to create it...', array(
						        '{email}' => CHtml::encode($email),
					        )),
					        'counter' => false,
				        );

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
					        $importLog[] = array(
						        'type'    => 'error',
						        'message' => Yii::t('list_import', 'Failed to save the email "{email}", reason: {reason}', array(
							        '{email}'  => CHtml::encode($email),
							        '{reason}' => '<br />'.$subscriber->shortErrors->getAllAsString()
						        )),
						        'counter' => true,
					        );
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
				        
				        $importLog[] = array(
					        'type'    => 'success',
					        'message' => Yii::t('list_import', 'The email "{email}" has been successfully saved.', array(
						        '{email}' => CHtml::encode($email),
					        )),
					        'counter' => true,
				        );

			        } else {

				        $importLog[] = array(
					        'type'    => 'info',
					        'message' => Yii::t('list_import', 'The email "{email}" has been found, we will update it.', array(
						        '{email}' => CHtml::encode($email),
					        )),
					        'counter' => true,
				        );
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
                return $this->renderJson(array(
                    'result'  => 'error',
                    'message' => $finished,
                ));
            }

            $import->is_first_batch = 0;
            $import->current_page++;

            return $this->renderJson(array(
                'result'    => 'success',
                'message'   => Yii::t('list_import', 'Imported {count} subscribers starting from row {rowStart} and ending with row {rowEnd}! Going further, please wait...', array(
                    '{count}'     => $importCount,
                    '{rowStart}'  => $offset,
                    '{rowEnd}'    => $offset + $importAtOnce,
                )),
                'attributes'    => $import->attributes,
                'import_log'    => $importLog,
                'recordsCount'  => $totalFileRecords,
            ));

        } catch(Exception $e) {

            if (isset($file)) {
                unset($file);
            }

            if (is_file($filePath.$import->file_name)) {
                @unlink($filePath.$import->file_name);
            }

            return $this->renderJson(array(
                'result'  => 'error',
                'message' => Yii::t('list_import', 'Your file cannot be imported, a general error has been encountered: {message}!', array(
                    '{message}' => $e->getMessage()
                ))
            ));

        }
    }

    /**
     * Handle the Text import and place it in queue
     */
    public function actionText_queue($list_uid)
    {
        return $this->handleQueuePlacement($list_uid, 'ListTextImport');
    }

    /**
     * Save the url for recurring import
     */
    public function actionUrl($list_uid)
    {
        $list      = $this->loadListModel($list_uid);
        $importUrl = ListUrlImport::model()->findByAttributes(array(
            'list_id' => $list->list_id,
        ));
        
        if (empty($importUrl)) {
            $importUrl = new ListUrlImport();
        }

        $request = Yii::app()->request;
        $notify  = Yii::app()->notify;
        
        $importUrl->attributes = $request->getPost($importUrl->modelName, array());
        $importUrl->list_id    = $list->list_id;
        if (!$importUrl->save()) {
            $notify->addError($importUrl->shortErrors->getAllAsString());
        } else {
            $notify->addSuccess(Yii::t('lists', 'The url has been added successfully!'));
        }
        
        $this->redirect(array('list_import/index', 'list_uid' => $list->list_uid));
    }

    /**
     * Will prevent the CSRF token expiration if the import takes too much time.
     */
    public function actionPing()
    {
        $this->render('ping');
    }

    /**
     * Helper method to load the list AR model
     */
    public function loadListModel($list_uid)
    {
        $model = Lists::model()->findByAttributes(array(
            'list_uid'    => $list_uid,
            'customer_id' => (int)Yii::app()->customer->getId(),
        ));

        if ($model === null) {
            throw new CHttpException(404, Yii::t('app', 'The requested page does not exist.'));
        }

        return $model;
    }

    /**
     * Helper method
     */
    protected function handleQueuePlacement($list_uid, $importClass = 'ListCsvImport')
    {
        $list    = $this->loadListModel($list_uid);
        $options = Yii::app()->options;
        $request = Yii::app()->request;
        $notify  = Yii::app()->notify;

        set_time_limit(0);
        if ($memoryLimit = $options->get('system.importer.memory_limit')) {
            ini_set('memory_limit', $memoryLimit);
        }
        ini_set('auto_detect_line_endings', true);

        $import = new $importClass('upload');
        $import->file_size_limit = (int)$options->get('system.importer.file_size_limit', 1024 * 1024 * 1); // 1 mb
        $import->attributes      = (array)$request->getPost($import->modelName, array());
        $import->file            = CUploadedFile::getInstance($import, 'file');

        if (empty($import->file)) {
            $notify->addError(Yii::t('list_import', 'Please select a file for import!'));
            $this->redirect(array('list_import/index', 'list_uid' => $list->list_uid));
        }

        $finalPath = Yii::getPathOfAlias('common.runtime.list-import-queue');
        if (!file_exists($finalPath) && !@mkdir($finalPath, 0777, true)) {
            $notify->addError(Yii::t('list_import', 'Unable to create target directory!'));
            $this->redirect(array('list_import/index', 'list_uid' => $list->list_uid));
        }

        $extension  = ($importClass == 'ListTextImport' ? '.txt' : '.csv');
        $suffix     = '';
	    $count      = 0;
	    
	    while (is_file($finalPath . '/' . $list->list_uid . $suffix . $extension)) {
		    $count++;
		    $suffix = '-' . $count;
		    clearstatcache();
	    }

        $fileName = $list->list_uid . $suffix . $extension;
        $filePath = $finalPath . '/' . $fileName;

        if (!$import->upload()) {
            $notify->addError($import->shortErrors->getAllAsString());
            $this->redirect(array('list_import/index', 'list_uid' => $list->list_uid));
        }

        $tmpFile = rtrim($import->getUploadPath(), '/') . '/' . $import->file_name;
        if (!copy($tmpFile, $filePath)) {
            $notify->addError(Yii::t('list_import', 'Unable to queue your import file!'));
            $this->redirect(array('list_import/index', 'list_uid' => $list->list_uid));
        }
        @unlink($tmpFile);

        $notify->addSuccess(Yii::t('list_import', 'Your file has been queued successfully!'));
        $this->redirect(array('list_import/index', 'list_uid' => $list->list_uid));
    }
}
