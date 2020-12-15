<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * FileMutex
 *
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.3.4.3
 */

/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */


class FileMutex extends BaseMutex
{
    /**
     * @var string the directory to store mutex files. You may use path alias here.
     * Defaults to the "mutex" subdirectory under the application runtime path.
     */
    public $mutexPath = 'common.runtime.mutex';

    /**
     * @var integer the permission to be set for newly created mutex files.
     * This value will be used by PHP chmod() function. No umask will be applied.
     * If not set, the permission will be determined by the current environment.
     */
    public $fileMode = 0666;

    /**
     * @var integer the permission to be set for newly created directories.
     * This value will be used by PHP chmod() function. No umask will be applied.
     * Defaults to 0775, meaning the directory is read-writable by owner and group,
     * but read-only for other users.
     */
    public $dirMode = 0777;

    /**
     * @var $_files stores all opened lock files. Keys are lock names and values are file handles.
     */
    private $_files = array();

    /**
     * Initializes mutex component implementation dedicated for UNIX, GNU/Linux, Mac OS X, and other UNIX-like
     * operating systems.
     * @throws InvalidConfigException
     */
    public function init()
    {
        $this->mutexPath = Yii::getPathOfAlias($this->mutexPath);
        if (!is_dir($this->mutexPath)) {
            $this->createDirectory($this->mutexPath, $this->dirMode, true);
        }
        parent::init();
    }

    /**
     * Acquires lock by given name.
     * @param string $name of the lock to be acquired.
     * @param integer $timeout to wait for lock to become released.
     * @return boolean acquiring result.
     */
    protected function acquireLock($name, $timeout = 0)
    {
        $fileName = $this->mutexPath . '/' . md5($name) . '.lock';
        $file = fopen($fileName, 'w+');
        if ($file === false) {
            return false;
        }
        if ($this->fileMode !== null) {
            @chmod($fileName, $this->fileMode);
        }
        $waitTime = 0;
        while (!flock($file, LOCK_EX | LOCK_NB)) {
            $waitTime++;
            if ($waitTime > $timeout) {
                fclose($file);
                return false;
            }
            sleep(1);
        }
        $this->_files[$name] = $file;

        return true;
    }

    /**
     * Releases lock by given name.
     * @param string $name of the lock to be released.
     * @return boolean release result.
     */
    protected function releaseLock($name)
    {
        if (!isset($this->_files[$name]) || !flock($this->_files[$name], LOCK_UN)) {
            return false;
        } else {
            fclose($this->_files[$name]);
            unset($this->_files[$name]);
            return true;
        }
    }

    /**
     * Helper method to create the directory if missing
     */
    public function createDirectory($path, $mode = 0775, $recursive = true)
    {
        if (is_dir($path)) {
            return true;
        }
        $parentDir = dirname($path);
        if ($recursive && !is_dir($parentDir)) {
            $this->createDirectory($parentDir, $mode, true);
        }
        $result = @mkdir($path, $mode);
        @chmod($path, $mode);

        return $result;
    }
}
