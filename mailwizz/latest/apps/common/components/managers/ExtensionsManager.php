<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * ExtensionsManager
 *
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.0
 */

class ExtensionsManager extends CApplicationComponent
{
    public $paths = array();

    protected $_extensions = array();

    protected $_coreExtensions = array();

    protected $_errors = array();

    protected $_coreExtensionsList = array();

    /**
     * ExtensionsManager::loadAllExtensions()
     *
     * @return mixed
     */
    public function loadAllExtensions()
    {
        static $_called = false;
        if ($_called) {
            return;
        }
        $_called = true;

        if (!is_array($this->paths) || empty($this->paths) ||  count($this->_extensions) > 0) {
            return;
        }

        $sort = array();

        foreach ($this->paths as $path) {
            if (!isset($path['alias'], $path['priority'])) {
                continue;
            }
            $sort[] = (int)$path['priority'];
        }

        if (empty($sort)) {
            return;
        }

        array_multisort($sort, $this->paths);

        foreach ($this->paths as $pathData) {

            if (!isset($pathData['alias'], $pathData['priority'])) {
                continue;
            }

            $path = $pathData['alias'];
            $_path = Yii::getPathOfAlias($path);

            if (!is_dir($_path)) {
                continue;
            }

            $extensions = FileSystemHelper::getDirectoryNames($_path);

            foreach($extensions as $extName) {

                $className = StringHelper::simpleCamelCase($extName);
                $className.='Ext';

                if (class_exists($className, false)) {
                    continue;
                }

                if (!is_file($extFilePath = $_path.'/'.$extName.'/'.$className.'.php')) {
                    continue;
                }

                $component = Yii::createComponent(array(
                    'class'    => $path.'.'. $extName.'.'.$className,
                ));

                if (!($component instanceof ExtensionInit)) {
                    continue;
                }
                
                if (in_array($extName, $this->_coreExtensionsList)) {
                    $this->_coreExtensions[$extName] = $component;
                } else {
                    $this->_extensions[$extName] = $component;
                }
            }
        }

        $sort = array();
        foreach ($this->_coreExtensions as $extName => $ext) {
            $sort[] = (int)$ext->priority;
        }
        array_multisort($sort, $this->_coreExtensions);

        $sort = array();
        foreach ($this->_extensions as $extName => $ext) {
            $sort[] = (int)$ext->priority;
        }
        array_multisort($sort, $this->_extensions);

        $extensions = array_merge($this->_coreExtensions, $this->_extensions);

        foreach ($extensions as $extName => $ext) {

            if (!$this->isExtensionEnabled($extName)) {
                continue;
            }

            $allowed = (array)$ext->allowedApps;
            $notAllowed = (array)$ext->notAllowedApps;

            if (!is_array($allowed)) {
                $allowed = array();
            }

            if (!is_array($notAllowed)) {
                $notAllowed = array();
            }

            if (count($notAllowed) == 0 && count($allowed) == 0) {
                continue;
            }

            if (count($notAllowed) > 0 && (in_array(MW_APP_NAME, $notAllowed) || array_search('*', $notAllowed) !== false)) {
                continue;
            }

            if (count($allowed) > 0 && (!in_array(MW_APP_NAME, $allowed) && array_search('*', $allowed) === false)) {
                continue;
            }

            if (MW_IS_CLI && !$ext->cliEnabled) {
                continue;
            }

            Yii::setPathOfAlias($ext->pathAlias, dirname($ext->reflection->getFilename()));

            // since 1.3.5.9
            $options = Yii::app()->options;
            if (version_compare($options->get('system.common.version'), '1.3.5.9', '<=') && !$options->get('system.extension.'.$extName.'.version')) {
                $options->set('system.extension.'.$extName.'.version', $ext->version);
            }
            
            // make sure this gets triggered only in backend web interface.
            if (Yii::app()->hasComponent('user') && Yii::app()->user->getId() && $ext->getMustUpdate()) {
                Yii::app()->notify->addInfo(Yii::t('extensions', 'The extension "{name}" needs updating from version {v1} to version {v2}! Please click {here} to run the update!', array(
                    '{name}' => $ext->name,
                    '{v1}'   => $options->get('system.extension.'.$extName.'.version', '1.0'),
                    '{v2}'   => $ext->version,
                    '{here}' => CHtml::link(Yii::t('app', 'here'), Yii::app()->createUrl("extensions/update", array("id" => $ext->dirName)))
                )));
                continue;
            }
            $ext->checkUpdate();
            //
            
            $ext->run();
        }
    }

    /**
     * ExtensionsManager::extensionExists()
     *
     * @return bool
     */
    public function extensionExists($extName)
    {
        return !empty($this->_extensions[$extName]) || !empty($this->_coreExtensions[$extName]);
    }

    /**
     * ExtensionsManager::isExtensionEnabled()
     *
     * @return bool
     */
    public function isExtensionEnabled($extName)
    {
        return $this->extensionExists($extName) && Yii::app()->options->get('system.extension.'.$extName.'.status', 'disabled') === 'enabled';
    }

    /**
     * ExtensionsManager::extensionMustUpdate()
     *
     * @return bool
     */
    public function extensionMustUpdate($extName)
    {
        if (!($instance = $this->getExtensionInstance($extName))) {
            return false;
        }
        return $instance->getMustUpdate();
    }

    /**
     * ExtensionsManager::getExtensionDatabaseVersion()
     *
     * @return string
     */
    public function getExtensionDatabaseVersion($extName, $defaultValue = '1.0')
    {
        if (!$this->extensionExists($extName)) {
            return $defaultValue;
        }
        return Yii::app()->options->get('system.extension.'.$extName.'.version', $defaultValue);
    }

    /**
     * ExtensionsManager::enableExtension()
     *
     * @return bool
     */
    public function enableExtension($extName)
    {
        if (!$this->extensionExists($extName)) {
            $this->_errors[] = Yii::t('extensions', 'The extension does not exists.');
            return false;
        }

        if ($this->isExtensionEnabled($extName)) {
            $this->_errors[] = Yii::t('extensions', 'The extension is already enabled.');
            return false;
        }

        $instance = $this->getExtensionInstance($extName);

        // since 1.3.4.5
        if (!empty($instance->minAppVersion) && version_compare(MW_VERSION, $instance->minAppVersion, '<')) {
            $this->_errors[] = Yii::t('extensions', 'The extension {ext} require your application to be at least version {version} but you are currently using version {appVersion}.', array(
				'{ext}'         => $instance->name,
                '{version}'     => $instance->minAppVersion,
                '{appVersion}'  => MW_VERSION,
			));
            return false;
        }

        if ($instance->beforeEnable() === false) {
            $this->_errors[] = Yii::t('extensions', 'Enabling the extension {ext} has failed.', array(
                '{ext}' => $instance->name,
            ));
            return false;
        }

        Yii::app()->options->set('system.extension.'.$extName.'.status', 'enabled');

        // since 1.3.5.9
        if (!Yii::app()->options->get('system.extension.'.$extName.'.version')) {
            Yii::app()->options->set('system.extension.'.$extName.'.version', $instance->version);
        }

        $instance->afterEnable();

        return true;
    }

    /**
     * ExtensionsManager::disableExtension()
     *
     * @return bool
     */
    public function disableExtension($extName)
    {
        if (!$this->extensionExists($extName)) {
            $this->_errors[] = Yii::t('extensions', 'The extension does not exists.');
            return false;
        }

        if (!$this->isExtensionEnabled($extName)) {
            return true;
        }

        $instance = $this->getExtensionInstance($extName);

        if (!$instance->getCanBeDisabled()) {
            $this->_errors[] = Yii::t('extensions', 'The extension cannot be disabled by configuration.');
            return false;
        }

        if ($instance->beforeDisable() === false) {
            $this->_errors[] = Yii::t('extensions', 'The extension could not be disabled.');
            return false;
        }

        Yii::app()->options->set('system.extension.'.$extName.'.status', 'disabled');

        $instance->afterDisable();

        return true;
    }

    /**
     * ExtensionsManager::updateExtension()
     *
     * @return bool
     */
    public function updateExtension($extName)
    {
        if (!$this->extensionExists($extName)) {
            $this->_errors[] = Yii::t('extensions', 'The extension does not exists.');
            return false;
        }

        if (!$this->isExtensionEnabled($extName)) {
            $this->_errors[] = Yii::t('extensions', 'The extension has to be enabled in order to update it.');
            return false;
        }

        if (!$this->extensionMustUpdate($extName)) {
            $this->_errors[] = Yii::t('extensions', 'The extension is already at the latest version.');
            return false;
        }

        $instance = $this->getExtensionInstance($extName);
        if (!$instance->update()) {
            $this->_errors[] = Yii::t('extensions', 'The extension could not be updated.');
            return false;
        }

        Yii::app()->options->set('system.extension.'.$extName.'.version', $instance->version);

        return true;
    }

    /**
     * ExtensionsManager::deleteExtension()
     *
     * @return bool
     */
    public function deleteExtension($extName)
    {
        if (!$this->extensionExists($extName)) {
            $this->_errors[] = Yii::t('extensions', 'The extension does not exists.');
            return false;
        }

        if (!$this->disableExtension($extName)) {
            return false;
        }

        $instance = $this->getExtensionInstance($extName);

        if (!$instance->getCanBeDeleted()) {
            $this->_errors[] = Yii::t('extensions', 'The extension cannot be deleted by configuration.');
            return false;
        }

        if ($instance->beforeDelete() === false) {
            $this->_errors[] = Yii::t('extensions', 'The extension cannot be deleted.');
            return false;
        }

        Yii::app()->options->removeCategory('system.extension.'.$extName);

        $instance->afterDelete();

        $dirToDelete = dirname($instance->getReflection()->getFilename());

        if (file_exists($dirToDelete) && is_dir($dirToDelete)) {
            FileSystemHelper::deleteDirectoryContents($dirToDelete, true, 1);
        }

        return true;
    }

    /**
     * ExtensionsManager::addError()
     *
     * @return ExtensionsManager
     */
    public function addError($error)
    {
        $this->_errors[] = $error;
        return $this;
    }

    /**
     * ExtensionsManager::hasErrors()
     *
     * @return bool
     */
    public function hasErrors()
    {
        return !empty($this->_errors);
    }

    /**
     * ExtensionsManager::getErrors()
     *
     * @return array
     */
    public function getErrors()
    {
        return $this->_errors;
    }

    /**
     * ExtensionsManager::resetErrors()
     *
     * @return array
     */
    public function resetErrors()
    {
        $this->_errors = array();
        return $this;
    }

    /**
     * ExtensionsManager::getExtensions()
     *
     * @return array
     */
    public function getExtensions()
    {
        return $this->_extensions;
    }

    /**
     * ExtensionsManager::getExtensionInstance()
     *
     * @return mixed
     */
    public function getExtensionInstance($extName)
    {
        return !empty($this->_extensions[$extName]) ? $this->_extensions[$extName] : (!empty($this->_coreExtensions[$extName]) ? $this->_coreExtensions[$extName] : null);
    }

    /**
     * ExtensionsManager::getCoreExtensions()
     *
     * @return array
     */
    public function getCoreExtensions()
    {
        return $this->_coreExtensions;
    }

    /**
     * ExtensionsManager::isCoreExtension()
     *
     * @return bool
     */
    public function isCoreExtension($extName)
    {
        return in_array($extName, $this->_coreExtensionsList);
    }

    /**
     * ExtensionsManager::setCoreExtensionsList()
     *
     * @return ExtensionsManager
     */
    public function setCoreExtensionsList(array $extensions)
    {
        $this->_coreExtensionsList = CMap::mergeArray((array)FileSystemHelper::getDirectoryNames(Yii::getPathOfAlias('common.extensions')), $extensions);
        return $this;
    }

    /**
     * ExtensionsManager::getCoreExtensionsList()
     *
     * @return array
     */
    public function getCoreExtensionsList()
    {
        return (array)$this->_coreExtensionsList;
    }

    /**
     * ExtensionsManager::getAllExtensions()
     *
     * @return array
     */
    public function getAllExtensions()
    {
        return array_merge($this->_coreExtensions, $this->_extensions);
    }
    
    /**
     * ExtensionsManager::getAllExtensionsNames()
     *
     * @return array
     */
    public function getAllExtensionsNames()
    {
        return array_keys($this->getAllExtensions());
    }

    /**
     * ExtensionsManager::runQueriesFromSqlFile()
     * 
     * @param $sqlFile
     * @return bool
     */
    public function runQueriesFromSqlFile($sqlFile)
    {
        if (!is_file($sqlFile)) {
            return false;
        }

        $queries = (array)CommonHelper::getQueriesFromSqlFile($sqlFile, Yii::app()->getDb()->tablePrefix);

        foreach ($queries as $query) {
            Yii::app()->getDb()->createCommand($query)->execute();
        }

        return true;
    }
}
