<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * ListExportCommand
 *
 * Handles the actions for list export related tasks.
 * Most of the logic is borrowed from the web interface exporter.
 *
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.3.5.9
 */

class ListExportCommand extends ConsoleCommand
{
    /**
     * @var string the folder path where we should save the files
     */
    public $folder_path = '';

    /**
     * @var string the list from where we want to export
     */
    public $list_uid = '';

    /**
     * @var string the list segment from where we want to export
     */
    public $segment_uid = '';

    /**
     * @return int
     */
    public function actionIndex()
    {
        Yii::app()->hooks->doAction('console_command_list_export_before_process', $this);

        $result = $this->process(array(
            'list_uid'      => $this->list_uid,
            'segment_uid'   => $this->segment_uid,
            'folder_path'   => $this->folder_path,
        ));

        Yii::app()->hooks->doAction('console_command_list_export_after_process', $this);

        return $result;
    }

    /**
     * @param array $params
     * @return int
     */
    protected function process(array $params)
    {
        if (empty($params['list_uid'])) {
            return $this->renderMessage(array(
                'result'  => 'error',
                'message' => Yii::t('list_export', 'Call this command with the --list_uid=XYZ param where XYZ is the 13 chars unique list id.'),
                'return'  => 1,
            ));
        }

        $list = Lists::model()->findByUid($params['list_uid']);
        if (empty($list)) {
            return $this->renderMessage(array(
                'result'  => 'error',
                'message' => Yii::t('list_export', 'The list with the uid {uid} was not found in database.', array(
                    '{uid}' => $params['list_uid'],
                )),
                'return' => 1,
            ));
        }

        if (empty($params['folder_path']) || !is_dir($params['folder_path']) || !is_readable($params['folder_path'])) {
            return $this->renderMessage(array(
                'result'  => 'error',
                'message' => Yii::t('list_export', 'Call this command with the --folder_path=XYZ param where XYZ is the full path to the folder you want to save the exports to.'),
                'return'  => 1,
            ));
        }
        
        // 1.3.7
        $segment = null;
        if (!empty($params['segment_uid'])) {
            $segment = ListSegment::model()->findByAttributes(array(
                'list_id'     => $list->list_id,
                'segment_uid' => $params['segment_uid'],
            ));
        }
        //
        
        require_once Yii::getPathOfAlias('customer.components.web.FormModel') . '.php';
        $options = Yii::app()->options;
        $export  = new ListCsvExport();
        $export->list_id = $list->list_id; // should not be assigned in attributes
        
        // 1.3.7
        if ($segment) {
            $export->segment_id = $segment->segment_id; // should not be assigned in attributes
        }
        //
        
        $export->count = $export->countSubscribers();

        if ($export->count == 0) {
            return $this->renderMessage(array(
                'result'    => 'error',
                'message'   => Yii::t('list_export', 'Your list has no subscribers to export!'),
                'return'    => 1,
            ));
        }
        
        $processAtOnce  = (int)$options->get('system.exporter.process_at_once', 50);

        ini_set("auto_detect_line_endings", true);

        $storageDir = rtrim($params['folder_path'], '/');
        $csvFile    = $storageDir . '/' . $list->list_uid . '.csv';

        if (is_file($oldCsvFile = $storageDir . '/' . $csvFile)) {
             @unlink($oldCsvFile);
        }

        if (!is_file($csvFile) && !touch($csvFile)) {
            return $this->renderMessage(array(
                'result'    => 'error',
                'message'   => Yii::t('list_export', 'Cannot create the storage file for your export!'),
                'return'    => 1,
            ));
        }

        if (!($fp = @fopen($csvFile, 'w'))) {
            return $this->renderMessage(array(
                'result'    => 'error',
                'message'   => Yii::t('list_export', 'Cannot open the storage file for your export!'),
                'return'    => 1,
            ));
        }

        $rounds    = $export->count > $processAtOnce ? round($export->count / $processAtOnce) : 1;
        $headerSet = false;
        $offset    = 0;
        $counter   = 0;

        for ($rCount = 1; $rCount <= $rounds; $rCount++) {
            $subscribers = $export->findSubscribers($processAtOnce, $offset);
            $offset += $processAtOnce;
            if (empty($subscribers)) {
                continue;
            }

            if (!$headerSet) {
                fputcsv($fp, array_keys($subscribers[0]), ',', '"');
                $headerSet = true;
            }

            foreach ($subscribers as $subscriberData) {
                $counter++;
                $percent = round(($counter / $export->count) * 100);
                fputcsv($fp, array_values($subscriberData), ',', '"');
                $this->renderMessage(array(
                    'type'      => 'success',
                    'message'   => '['.$percent.'%] - ' . Yii::t('list_export', 'Successfully added the email "{email}" to the export list.', array(
                        '{email}' => $subscriberData['EMAIL'],
                    )),
                    'counter'   => true,
                ));
            }
        }

        fclose($fp);

        return $this->renderMessage(array(
            'result'    => 'success',
            'message'   => Yii::t('list_export', 'The export process finished, your file: {path}!', array('{path}' => $csvFile)),
            'return'    => 0,
        ));
    }

    /**
     * @param array $data
     * @return int
     */
    protected function renderMessage($data = array())
    {
        if (isset($data['message'])) {
            echo strip_tags(str_replace(array('<br />', '<br/>', '<br>'), PHP_EOL, $data['message'])) . PHP_EOL;
        }

        if (isset($data['return']) || array_key_exists('return', $data)) {
            return (int)$data['return'];
        }
    }
}
