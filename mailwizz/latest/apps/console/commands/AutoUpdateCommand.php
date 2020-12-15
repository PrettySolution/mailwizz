<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * AutoUpdateCommand
 *
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.5.1
 *
 */

class AutoUpdateCommand extends ConsoleCommand
{
    /**
     * @var bool 
     */
    protected $canNotify = false;
    
    /**
     * @return int
     */
    public function actionIndex()
    {
        $this->stdout('Acquiring the mutex lock...');
        
        $result   = 1;
        $mutexKey = sha1(__METHOD__);
        
        if (!Yii::app()->mutex->acquire($mutexKey, 5)) {
            $this->stdout('Unable to acquire the mutex lock!');
            return $result;
        }
        
        try {

            Yii::app()->hooks->doAction('console_command_auto_update_before_process', $this);
            
            $result = $this->process();

            if ($this->canNotify) {
                $this->sendNotifications();
            }

            Yii::app()->hooks->doAction('console_command_auto_update_after_process', $this);
            
        } catch (Exception $e) {

            $this->stdout(__LINE__ . ': ' .  $e->getMessage());
            Yii::log($e->getMessage(), CLogger::LEVEL_ERROR);
            
            $result = 1;
        }
        
        Yii::app()->mutex->release($mutexKey);

        return $result;
    }

    /**
     * @return int
     * @throws CException
     */
    protected function process()
    {
        $this->stdout('Checking the system for all functions and binaries...');
        foreach (array('exec') as $func) {
            if (!CommonHelper::functionExists($func)) {
                $this->stdout('Following function is required but is disabled in the PHP config: ' . $func);
                return 1;
            }
        }
        
        foreach (array('curl', 'unzip', 'cp') as $bin) {
            $command  = sprintf('if command -v %s >/dev/null; then echo 1; else echo 0; fi', $bin);
            $lastLine = exec($command, $output, $status);
            if ((int)$status !== 0 || (int)$lastLine !== 1) {
                $this->stdout('Following binary is required but was not found: ' . $bin);
                return 1;
            }
        }
        unset($output);
        $this->stdout('All functions and binaries are in place, we can continue...');
        
        $this->stdout('Fetching latest version number...');
        $headers = '"-H Accept: application/json" -H "Content-Type: application/json"';
        $url     = 'https://www.mailwizz.com/api/site/version';
        $command = sprintf('curl -s %s %s', $headers, $url);
        exec($command, $output, $status);
        if ((int)$status !== 0 || empty($output)) {
            $this->stdout('Cannot use curl to fetch version information from the api!');
            return 1;
        }
        $json = array_shift($output);
        unset($output);
        
        $data = json_decode($json);
        if (empty($data->current_version)) {
            $this->stdout('Cannot decode latest version info...');
            return 1;
        }
        
        $options       = Yii::app()->options;
        $licenseKey    = $options->get('system.license.purchase_code', '');
        $dbVersion     = $options->get('system.common.version', '1.0');
        $latestVersion = $data->current_version;
        
        if (!version_compare($latestVersion, $dbVersion, '>')) {
            $this->stdout('Already at the latest version, nothing to do.');
            return 0;
        }

        /* put a flag for latest version number */
        define('MW_AUTOUPDATE_VERSION', $latestVersion);
        
        // from this point onwards we can notify
        $this->canNotify = true;
        
        $storage = Yii::getPathOfAlias('common.runtime.auto-update');
        if (!file_exists($storage) && !mkdir($storage, 0777)) {
            $this->stdout('Cannot create the storage dir: ' . $storage);
            return 1;
        }
        
        $updateFile = $storage . '/update-' . $latestVersion . '.zip';
        if (is_file($updateFile)) {
            $this->stdout('Unlinking existing update file...');
            unlink($updateFile); 
        }

        if (is_file($updateFile)) {
            $this->stdout('Unable to unlink existing update file!');
            return 1;
        }
        
        $updateFolder = $storage . '/update-' . $latestVersion;
        if (file_exists($updateFolder) && is_dir($updateFolder)) {
            FileSystemHelper::deleteDirectoryContents($updateFolder, true, 1);
        }
        
        // try to backup the app before downloading a huge update.
        $this->tryToBackup();
        
        $this->stdout('Fetching the file signature...');
        $headers = '"-H Accept: application/json" -H "Content-Type: application/json"';
        $url     = 'https://www.mailwizz.com/api/download/update/' . $latestVersion . '/signature';
        $command = sprintf('curl -s %s %s', $headers, $url);
        exec($command, $output, $status);
        if ((int)$status !== 0 || empty($output)) {
            $this->stdout('Cannot use curl to fetch version signature from the api!');
            return 1;
        }
        $json = array_shift($output);
        unset($output);
        
        $data = json_decode($json);
        if (empty($data->signature)) {
            $this->stdout('Cannot decode latest version signature...');
            return 1;
        }
        $updateSignature = $data->signature;
        if (strlen($updateSignature) != 40 || !StringHelper::isSha1($updateSignature)) {
            $this->stdout('The latest version signature seems to be invalid!');
            return 1;
        }
        $this->stdout('The file signature is ' . $updateSignature);
        $this->stdout('Downloading the update file, this might take a while...');

        // close the external connections
        $this->setExternalConnectionsActive(false);
        
        $headers  = ' -H "Accept: application/zip" -H "Content-Type: application/zip"';
        $headers .= sprintf(' -H "X-LICENSEKEY: %s"', $licenseKey);
        $url      = 'https://www.mailwizz.com/api/download/update/' . $latestVersion;
        
        $command  = sprintf('curl -s -o %s %s %s', $updateFile, $headers, $url);
        exec($command, $output, $status);

        // open the external connections
        $this->setExternalConnectionsActive(true);
        
        if ((int)$status !== 0) {
            $this->stdout('Cannot use curl to fetch the update version!');
            return 1;
        }
        
        if (!is_file($updateFile)) {
            $this->stdout('Unable to download the update file!');
            return 1;
        }
        
        $this->stdout('Download complete, checking the signature...');
        if (sha1_file($updateFile) != $updateSignature) {
            unlink($updateFile);
            $this->stdout('The signature does not match!');
            return 1;
        }

        // close the external connections
        $this->setExternalConnectionsActive(false);
        
        $this->stdout('Server response is correct, unzipping the file...');
        $command  = 'unzip -o %s -d %s >/dev/null';
        $command  = sprintf($command, $updateFile, $storage . '/');
        exec($command, $output, $status);

        // open the external connections
        $this->setExternalConnectionsActive(true);
        
        if ((int)$status !== 0) {
            $this->stdout('Unable to unzip the archive!');
            return 1;
        }

        // put the app offline now
        $this->setAppOnline(false);
        
        // wait for campaigns that still process
        $this->stdout('Waiting for running campaigns to finish...');
        $maxWait     = 3600 * 12; // 12 hours
        $currentWait = 0;
        $waitSeconds = 30;
        
        while (true) {
            
            if ($currentWait >= $maxWait) {
                break;
            }

            // open the external connections
            $this->setExternalConnectionsActive(true);
            
            // count the campaigns which are processing
            $count = Campaign::model()->countByAttributes(array(
                'status' => Campaign::STATUS_PROCESSING
            ));
            
            // if count is empty, nothing is processing anymore
            if (empty($count)) {
                $this->stdout('Done waiting for campaigns to finish, it took ' . $currentWait . ' seconds!');
                break;
            }

            // close the external connections
            $this->setExternalConnectionsActive(false);
            
            // increement the current wait period
            $currentWait += $waitSeconds;
            
            // and wait again
            sleep($waitSeconds);
        }
        
        // if we waited for too long...
        if ($currentWait >= $maxWait) {
            $this->stdout('After waiting for ' . $currentWait . ' seconds, campaigns are still running, thus we giveup!');
            
            // put the app online
            $this->setAppOnline(true);
            
            return 1;
        }
        
        // close the external connections
        $this->setExternalConnectionsActive(false);
        
        $this->stdout('The archive has been unzipped successfully, trying to copy the files over...');
        $command  = 'cp -Rf %s %s >/dev/null';
        $command  = sprintf($command, $updateFolder . '/*', Yii::getPathOfAlias('root') . '/');
        exec($command, $output, $status);

        // open the external connections
        $this->setExternalConnectionsActive(true);
        
        if ((int)$status !== 0) {
            $this->stdout('Unable to copy the files in the right location!');
            return 1;
        }
        $this->stdout('The files where copied successfully!');

        $this->stdout('Starting the upgrade process...');
        $updateSuccess = ($this->runUpdateCommand() === 0);
        if (!$updateSuccess) {
            $this->stdout('The upgrade process has failed!');
        } else {
            $this->stdout('The upgrade process finished successfully!');
        }
        
        $this->stdout('Removing the existing update file...');
        if (is_file($updateFile)) {
            unlink($updateFile);
        }

        $this->stdout('Removing the existing update folder...');
        if (file_exists($updateFolder) && is_dir($updateFolder)) {
            FileSystemHelper::deleteDirectoryContents($updateFolder, true, 1);
        }

        // put the app online/offline depending on the update result
        $this->setAppOnline($updateSuccess);

        $this->stdout('Done!');
        
        return 0;
    }
    
    /**
     * @return bool
     */
    protected function tryToBackup()
    {
        $this->stdout('Trying to backup the app before the upgrade...');
        if (!(Yii::app()->extensionsManager->isExtensionEnabled('backup-manager'))) {
            $this->stdout('The backup manager extension is missing or is disabled, no backup can be made!');
            return false;
        }

        $this->stdout('Starting the backup process..');
        
        $extension = Yii::app()->extensionsManager->getExtensionInstance('backup-manager');
        $snapshot  = new BackupManagerSnapshot();
        $snapshot->path = $extension->getOption('storage_path');
        $snapshot->backup();

        $this->stdout('Finished the backup process, here is the output: ');
        $messages = $snapshot->getBackupLogger()->toArray();
        foreach ($messages as $message) {
            $this->stdout(trim(preg_replace('/\[(.*)?\]/i', '', $message), ' -'));
        }
    }

    /**
     * @return int
     */
    public function runUpdateCommand()
    {
        $argv = array(
            $_SERVER['argv'][0],
            'update',
            '--interactive=0'
        );

        foreach ($_SERVER['argv'] as $arg) {
            if ($arg == '--verbose=1') {
                $argv[] = $arg;
                break;
            }
        }
        
        try {
            $runner = clone Yii::app()->getCommandRunner();
            $run    = (int)$runner->run($argv);
        } catch (Exception $e) {
            Yii::log($e->getMessage(), CLogger::LEVEL_ERROR);
            $run = 1;
        }
        
        return $run;
    }

    /**
     * @inheritdoc
     */
    public function stdout($message, $timer = true, $separator = "\n", $store = true)
    {
        return parent::stdout($message, $timer, $separator, $store);
    }

    /**
     * @param bool $online
     */
    public function setAppOnline($online = true)
    {
        $options = Yii::app()->options;
        if ($online) {
            $options->set('system.common.site_status', 'online');
            $options->set('system.common.api_status', 'online');
        } else {
            $options->set('system.common.site_status', 'offline');
            $options->set('system.common.api_status', 'offline');
        }
    }

    /**
     * @return void
     */
    public function sendNotifications()
    {
        $users = User::model()->findAllByAttributes(array(
            'status'    => User::STATUS_ACTIVE,
            'removable' => User::TEXT_NO,
        ));

        $options = Yii::app()->options;
	    $params  = CommonEmailTemplate::getAsParamsArrayBySlug('auto-update-notification',
		    array(
			    'subject' => Yii::t('customers', 'Automatic update notification!'),
		    ), array(
			    '[LOGS]'  => implode('<br />', $this->stdoutLogs),
		    )
	    );
	    
        foreach ($users as $user) {
            $email = new TransactionalEmail();
            $email->to_name   = $user->getFullName();
            $email->to_email  = $user->email;
            $email->from_name = $options->get('system.common.site_name', 'Marketing website');
            $email->subject   = $params['subject'];
            $email->body      = $params['body'];
            $email->save();

            // add a notification message too
            $message = new UserMessage();
            $message->title   = 'Automatic update notification!';
            $message->message = implode('<br />', $this->stdoutLogs);
            $message->user_id = $user->user_id;
            $message->save();
        }
    }
}
