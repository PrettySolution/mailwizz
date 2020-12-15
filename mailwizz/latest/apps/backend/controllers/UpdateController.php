<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * UpdateController
 *
 * Handles the actions for updating the application
 *
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.1
 */

class UpdateController extends Controller
{
    /**
     * @inheritdoc
     */
    public function init()
    {
        header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
        header("Cache-Control: post-check=0, pre-check=0", false);
        header("Pragma: no-cache");
        
        parent::init();
    }
    
    /**
     * Display the update page and execute the update
     */
    public function actionIndex()
    {
        $request = Yii::app()->request;
        $options = Yii::app()->options;
        $notify  = Yii::app()->notify->clearAll();

        $versionInFile  = MW_VERSION;
        $versionInDb    = $options->get('system.common.version');

        if (!version_compare($versionInFile, $versionInDb, '>')) {
            if ($options->get('system.common.site_status', 'online') != 'online') {
                $options->set('system.common.site_status', 'online');
            }
            $this->redirect(array('dashboard/index'));
        }

        // put the application offline
        $options->set('system.common.site_status', 'offline');

        // start the work
        if ($request->isPostRequest) {

            // make sure we have both, time and memory...
            set_time_limit(0);
            ini_set('memory_limit', -1);

            $workersPath = Yii::getPathOfAlias('backend.components.update');
            require_once $workersPath . '/UpdateWorkerAbstract.php';

            $updateWorkers  = (array)FileSystemHelper::readDirectoryContents($workersPath);

            foreach ($updateWorkers as $index => $fileName) {
                $fileName = basename($fileName, '.php');
                if (strpos($fileName, 'UpdateWorkerFor_') !== 0) {
                    unset($updateWorkers[$index]);
                    continue;
                }

                $workerVersion = str_replace('UpdateWorkerFor_', '', $fileName);
                $workerVersion = str_replace('_', '.', $workerVersion);

                // previous versions ?
                if (version_compare($workerVersion, $versionInDb, '<=')) {
                    unset($updateWorkers[$index]);
                    continue;
                }

                // next versions ?
                if (version_compare($workerVersion, $versionInFile, '>')) {
                    unset($updateWorkers[$index]);
                    continue;
                }

                $updateWorkers[$index] = $workerVersion;
            }

            sort($updateWorkers, SORT_NUMERIC | SORT_ASC);

            $db = Yii::app()->getDb();
            $db->createCommand('SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0')->execute();
            $db->createCommand('SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0')->execute();
            $db->createCommand('SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE=""')->execute();

            foreach ($updateWorkers as $workerVersion) {
                $transaction = $db->beginTransaction();
                try {
                    $notify->addInfo(Yii::t('update', 'Updating to version {version}.', array('{version}' => $workerVersion)));
                    $this->runWorker($workerVersion);
                    $notify->addInfo(Yii::t('update', 'Updated to version {version} successfully.', array('{version}' => $workerVersion)));

                    $options->set('system.common.version', $workerVersion);
                    $options->set('system.common.version_update.current_version', $workerVersion);
                    $transaction->commit();
                } catch (Exception $e) {
                    $transaction->rollback();
                    $notify->addError(Yii::t('update', 'Updating to version {version} failed with: {message}', array(
                        '{version}' => $workerVersion,
                        '{message}' => $e->getMessage()
                    )));
                    break;
                }
            }

            $db->createCommand('SET SQL_MODE=@OLD_SQL_MODE')->execute();
            $db->createCommand('SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS')->execute();
            $db->createCommand('SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS')->execute();

            if ($notify->hasError) {
                $this->redirect(array('update/index'));
            }
            
            $options->set('system.common.version', $versionInFile);
            $options->set('system.common.site_status', 'online');
            $options->set('system.common.version_update.current_version', $versionInFile);
            
            $notify->addSuccess(Yii::t('update', 'Congratulations, your application has been successfully updated to version {version}', array(
                '{version}' => '<span class="badge">'.$versionInFile.'</span>',
            )));
            
            // since 1.3.6.3 - update extensions
            $manager    = Yii::app()->extensionsManager;
            $extensions = $manager->getCoreExtensions();
            $errors     = array();
            foreach ($extensions as $id => $instance) {
                if ($manager->extensionMustUpdate($id) && !$manager->updateExtension($id)) {
                    $errors[] = Yii::t('extensions', 'The extension "{name}" has failed to update!', array(
                        '{name}' => CHtml::encode($instance->name),
                    ));
                    $errors = CMap::mergeArray($errors, (array)$manager->getErrors());
                    $manager->resetErrors();
                }
            }
            if (!empty($errors)) {
                $notify->addError($errors);
            }
            //

            // clean directories of old asset files.
            FileSystemHelper::clearCache();

            // remove the cache, can be redis for example
            Yii::app()->cache->flush();
            
            // rebuild the tables schema cache
            Yii::app()->db->schema->getTables();
            Yii::app()->db->schema->refresh();
            
            // and back
            $this->redirect(array('dashboard/index'));
        }

        $notify->addInfo(Yii::t('update', 'Please note, depending on your database size it is better to run the command line update tool instead.'));
        $notify->addInfo(Yii::t('update', 'In order to run the command line update tool, you must run the following command from a ssh shell:'));
        $notify->addInfo(sprintf('<strong>%s</strong>', CommonHelper::findPhpCliPath() . ' ' . Yii::getPathOfAlias('console') . '/console.php update'));

        $this->setData(array(
            'pageMetaTitle'     => $this->data->pageMetaTitle . ' | ' . Yii::t('update', 'Update'),
            'pageHeading'       => Yii::t('update', 'Application update'),
            'pageBreadcrumbs'   => array(
                Yii::t('update', 'Update'),
            ),
        ));

        $this->render('index', compact('versionInFile', 'versionInDb'));
    }

	/**
	 * @param $version
	 *
	 * @return bool
	 */
    protected function runWorker($version)
    {
        $workersPath    = Yii::getPathOfAlias('backend.components.update');
        $version        = str_replace('.', '_', $version);
        $className      = 'UpdateWorkerFor_' . $version;

        if (!is_file($classFile = $workersPath . '/' . $className . '.php')) {
            return false;
        }

        require_once $classFile;
        $instance = new $className();

        if ($instance instanceof UpdateWorkerAbstract) {
            $instance->run();
        }

        return true;
    }

}
