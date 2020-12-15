<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * ExtensionInit
 *
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.0
 */

abstract class ExtensionInit extends CApplicationComponent
{
    public $name = 'Missing extension name';

    public $author = 'Unknown';

    public $website = 'javascript:;';

    public $email = 'missing@email.com';

    public $description = 'Missing extension description';

    public $priority = 0;

    public $version = '1.0';

    public $minAppVersion = '1.0';

    public $cliEnabled = false;

    public $notAllowedApps = array();

    public $allowedApps = array();

    protected $_canBeDisabled = true;

    protected $_canBeDeleted = true;

    // data to be passed in extension between callbacks mostly
    protected $_data;

    /**
     * ExtensionInit::getIsEnabled()
     *
     * @return bool
     */
    public final function getIsEnabled()
    {
        return $this->getManager()->isExtensionEnabled($this->getDirName());
    }

    /**
     * ExtensionInit::getCanBeDisabled()
     *
     * @return bool
     */
    public final function getCanBeDisabled()
    {
        if ($this->getManager()->isCoreExtension($this->getDirName())) {
            return $this->_canBeDisabled;
        }
        return true;
    }

    /**
     * ExtensionInit::getCanBeDeleted()
     *
     * @return bool
     */
    public final function getCanBeDeleted()
    {
        if ($this->getManager()->isCoreExtension($this->getDirName())) {
            return $this->_canBeDeleted;
        }
        return true;
    }

    /**
     * ExtensionInit::setOption()
     *
     * @param string $key
     * @param mixed $value
     * @return bool
     */
    public final function setOption($key, $value)
    {
        if (empty($key)) {
            return false;
        }

        return Yii::app()->options->set('system.extension.'.$this->getDirName().'.data.'.$key, $value);
    }

    /**
     * ExtensionInit::getOption()
     *
     * @param string $key
     * @param mixed $defaultValue
     * @return mixed
     */
    public final function getOption($key, $defaultValue = null)
    {
        if (empty($key)) {
            return $defaultValue;
        }

        return Yii::app()->options->get('system.extension.'.$this->getDirName().'.data.'.$key, $defaultValue);
    }

    /**
     * ExtensionInit::removeOption()
     *
     * @param string $key
     * @return bool
     */
    public final function removeOption($key)
    {
        if (empty($key)) {
            return false;
        }

        return Yii::app()->options->remove('system.extension.'.$this->getDirName().'.data.'.$key);
    }

    /**
     * ExtensionInit::removeAllOptions()
     *
     * @return bool
     */
    public final function removeAllOptions()
    {
        return Yii::app()->options->removeCategory('system.extension.'.$this->getDirName().'.data');
    }

    /**
     * ExtensionInit::getReflection()
     *
     * @return ReflectionClass
     */
    public final function getReflection()
    {
        static $_reflection;
        if ($_reflection) {
            return $_reflection;
        }
        return $_reflection = new ReflectionClass($this);
    }

    /**
     * ExtensionInit::getDirName()
     *
     * @return string
     */
    public final function getDirName()
    {
        static $_dirName;
        if ($_dirName) {
            return $_dirName;
        }

        $reflection = $this->getReflection();
        return $_dirName = basename(dirname($reflection->getFilename()));
    }

    /**
     * ExtensionInit::getPathAlias()
     *
     * @param $append string
     * @return string
     */
    final public function getPathAlias($append = '')
    {
        return 'ext-' . $this->getDirName() . ($append ? '.' . $append : '');
    }

    /**
     * ExtensionInit::getPathOfAlias()
     * 
     * @param string $append
     * @return mixed
     */
    final public function getPathOfAlias($append = '')
    {
        return Yii::getPathOfAlias($this->getPathAlias($append));
    }

    /**
     * ExtensionInit::t()
     *
     * @param $message
     * @param array $params
     * @since 1.3.6.2
     * @return string
     */
    final public function t($message, array $params = array())
    {
        return Yii::t(str_replace('-', '_', $this->getPathAlias()), $message, $params);
    }

    /**
     * ExtensionInit::getManager()
     *
     * @return ExtensionsManager
     */
    final public function getManager()
    {
        return Yii::app()->extensionsManager;
    }

    /**
     * ExtensionInit::isAppName()
     *
     * @param string $name
     * @return bool
     */
    final public function isAppName($name)
    {
        return Yii::app()->apps->isAppName($name);
    }

    /**
     * ExtensionInit::getPageUrl()
     *
     * Used so that extensions can register a landing page.
     * @since 1.1
     *
     * @return mixed
     */
    public function getPageUrl()
    {

    }

    /**
     * ExtensionInit::beforeEnable()
     *
     * @return bool
     */
    public function beforeEnable()
    {
        return true;
    }

    /**
     * ExtensionInit::afterEnable()
     *
     * @return
     */
    public function afterEnable()
    {
    }

    /**
     * ExtensionInit::beforeDisable()
     *
     * @return bool
     */
    public function beforeDisable()
    {
        return true;
    }

    /**
     * ExtensionInit::afterDisable()
     *
     * @return
     */
    public function afterDisable()
    {
    }

    /**
     * ExtensionInit::beforeDelete()
     *
     * @return bool
     */
    public function beforeDelete()
    {
        return true;
    }

    /**
     * ExtensionInit::afterDelete()
     *
     * @return
     */
    public function afterDelete()
    {
    }

    /**
     * ExtensionInit::checkUpdate()
     *
     * @return
     */
    public function checkUpdate()
    {
    }

    /**
     * ExtensionInit::update()
     *
     * @return
     */
    public function update()
    {
        return true;
    }

    /**
     * ExtensionInit::run()
     *
     * @return
     */
    abstract public function run();

    /**
     * ExtensionInit::setData()
     *
     * @param string $key
     * @param mixed $value
     * @return {@CAttributeCollection}
     */
    final public function setData($key, $value = null)
    {
        if (!is_array($key) && $value !== null) {
            $this->getData()->mergeWith(array($key => $value), false);
        } elseif (is_array($key)) {
            $this->getData()->mergeWith($key, false);
        }
        return $this;
    }

    /**
     * ExtensionInit::getData()
     *
     * @param mixed $key
     * @param mixed $defaultValue
     * @return mixed
     */
    final public function getData($key = null, $defaultValue = null)
    {
        if (!($this->_data instanceof CAttributeCollection)) {
            $this->_data = new CAttributeCollection($this->_data);
            $this->_data->caseSensitive=true;
        }

        if ($key !== null) {
            return $this->_data->contains($key) ? $this->_data->itemAt($key) : $defaultValue;
        }

        return $this->_data;
    }

    /**
     * ExtensionInit::getMustUpdate()
     *
     * @return bool
     */
    final public function getMustUpdate()
    {
        return $this->getIsEnabled() && version_compare($this->getDatabaseVersion(), $this->version, '<');
    }

    /**
     * ExtensionInit::getDatabaseVersion()
     *
     * @return bool
     */
    final public function getDatabaseVersion($defaultValue = '1.0')
    {
        return $this->getManager()->getExtensionDatabaseVersion($this->getDirName(), $defaultValue);
    }

    /**
     * ExtensionInit::runQueriesFromSqlFile()
     * 
     * @param $sqlFile
     * @return bool
     */
    final public function runQueriesFromSqlFile($sqlFile)
    {
        return $this->getManager()->runQueriesFromSqlFile($sqlFile);
    }
}
