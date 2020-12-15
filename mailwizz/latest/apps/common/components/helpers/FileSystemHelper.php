<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * FileSystemHelper
 * 
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com> 
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.0
 */
 
class FileSystemHelper 
{
    /**
     * FileSystemHelper::getTmpDirectory()
     * 
     * @return string
     */
    public static function getTmpDirectory()
    {
        static $tempDir;
        if ($tempDir !== null) {
            return $tempDir;
        }
        
        if (CommonHelper::functionExists('sys_get_temp_dir')) {
            $tmp = @sys_get_temp_dir();
            if (!empty($tmp) && is_dir($tmp) && is_writable($tmp)) {
                return $tempDir = $tmp;
            }
        }
        
        foreach (array('TMP', 'TEMP', 'TMPDIR') as $evar) {
            if ($tmp = @getenv($evar)) {
                if (file_exists($tmp) && is_dir($tmp) && is_writable($tmp)) {
                    return $tempDir = $tmp;
                }
            }    
        } 

        $tmp = Yii::getPathOfAlias('common.runtime.tmp');
        if (!file_exists($tmp) || !is_dir($tmp)) {
            @mkdir($tmp, 0777, true);
        }
        
        return $tempDir = $tmp;
    }
    
    /**
     * FileSystemHelper::getDirectoryNames()
     * 
     * @param string $path
     * @return array
     */
    public static function getDirectoryNames($path)
    {        
        return array_map('basename', array_values(self::getDirectoriesRecursive($path)));
    }
    
    /**
     * FileSystemHelper::getDirectoriesRecursive()
     * 
     * @param string $path
     * @param integer $maxDepth
     * @return array
     */
    public static function getDirectoriesRecursive($path, $maxDepth = 0)
    {
        $directories = array();

        if (!is_dir($path)) {
            return $directories;
        }
        
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($path), RecursiveIteratorIterator::CHILD_FIRST
        );
        $iterator->setMaxDepth($maxDepth);

        foreach ($iterator as $file) {
            if (!$file->isDir() || in_array($file->getFilename(), array('.', '..')) ) {
                continue;
            }
            
            $directories[] = $file->__toString();
        }
        
        return $directories;
    }
    
    /**
     * FileSystemHelper::deleteDirectoryContents()
     * 
     * @param string $path
     * @param bool $delDir
     * @param integer $level
     * @return bool
     */
    public static function deleteDirectoryContents($path, $delDir = false, $level = 0)
    {
        $path = rtrim($path, DIRECTORY_SEPARATOR);

        if (!($currentDir = @opendir($path))) {
            return false;
        }

        while (false !== ($fileName = @readdir($currentDir))) {
            if ($fileName != "." and $fileName != "..") {
                if (is_dir($path.DIRECTORY_SEPARATOR.$fileName)) {
                    if (substr($fileName, 0, 1) != '.') {
                        self::deleteDirectoryContents($path.DIRECTORY_SEPARATOR.$fileName, $delDir, $level + 1);
                    }
                } else {
                    @unlink($path.DIRECTORY_SEPARATOR.$fileName);
                }
            }
        }
        @closedir($currentDir);

        if ($delDir == true AND $level > 0) {
            return @rmdir($path);
        }
        
        return true;
    }
    
    /**
     * FileSystemHelper::readDirectoryContents()
     * 
     * @param string $sourceDir
     * @param bool $includePath
     * @param bool $recursive
     * @return mixed
     */
    public static function readDirectoryContents($sourceDir, $includePath = false, $recursive = false)
    {
        static $fileData = array();

        if ($fp = @opendir($sourceDir)) {
            if ($recursive === false) {
                $fileData = array();
                $sourceDir = rtrim(realpath($sourceDir), DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR;
            }

            while (FALSE !== ($file = readdir($fp))) {
                if (@is_dir($sourceDir.$file) && strncmp($file, '.', 1) !== 0) {
                    self::readDirectoryContents($sourceDir.$file.DIRECTORY_SEPARATOR, $includePath, true);
                }
                elseif (strncmp($file, '.', 1) !== 0) {
                    $fileData[] = $includePath ? $sourceDir.$file : $file;
                }
            }
            return $fileData;
        } else {
            return false;
        }
    }
    
    /**
     * FileSystemHelper::copyDirectoryContents()
     * 
     * @param string $source
     * @param string $destination
     * @return bool
     */
    public static function copyDirectoryContents($source, $destination) 
    {
        if (!file_exists($source) || !is_dir($source) || !is_readable($source)) {
            return false;
        }
        
        if ((!file_exists($destination) || !is_dir($destination)) && !@mkdir($destination, 0777, true)) {
            return false;
        }
        
        $result = true;
        foreach ($iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($source), RecursiveIteratorIterator::SELF_FIRST) as $item) {
            
            if ($item->isDir() && in_array($item->getFilename(), array('.', '..'))) {
                continue;
            }
            
            if ($item->isDir()) {
                $result = @mkdir($destination . DIRECTORY_SEPARATOR . $iterator->getSubPathName(), 0777, true);
            } else {
                $result = @copy($item, $destination . DIRECTORY_SEPARATOR . $iterator->getSubPathName());
            }
            
            if (!$result) {
                break;
            }
        }
        
        return $result;
    }
    
    /**
     * it will only copy the contents of the directory into the new destination 
     * which will be created if not exists.
     */
    /**
     * FileSystemHelper::copyOnlyDirectoryContents()
     * 
     * @param string $source
     * @param string $destination
     * @return bool
     */
    public static function copyOnlyDirectoryContents($source, $destination) 
    {
        if (!file_exists($source) || !is_dir($source) || !is_readable($source)) {
            return false;
        }
        
        if ((!file_exists($destination) || !is_dir($destination)) && !@mkdir($destination, 0777, true)) {
            return false;
        }
        
        $result = true;
        foreach ($iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($source), RecursiveIteratorIterator::LEAVES_ONLY) as $item) {
            
            if ($item->isDir() && in_array($item->getFilename(), array('.', '..'))) {
                continue;
            }
            
            if ($item->isDir()) {
                $result = @mkdir($destination . DIRECTORY_SEPARATOR . $iterator->getSubPathName(), 0777, true);
            } else {
                $result = @copy($item, $destination . DIRECTORY_SEPARATOR . $iterator->getSubPathName());
            }
            
            if (!$result) {
                break;
            }
        }
        
        return $result;
    }

    /**
     * @param $filePath
     * @return null|string
     */
    public static function getFileContents($filePath)
    {
        if (!is_file($filePath)) {
            return null;
        }
        
        $contents = '';
        if (CommonHelper::functionExists('fopen')) {
            if ($handle = @fopen($filePath, "r")) {
                while (($buffer = fgets($handle, 4096)) !== false) {
                    $contents .= $buffer;
                }
                fclose($handle);
            }
            return $contents;
        }
        
        if (CommonHelper::functionExists('file_get_contents')) {
            return file_get_contents($filePath);
        }
        
        return null;
    }

    /**
     * @param array $extraAliases
     * @return array
     */
    public static function clearCache(array $extraAliases = array())
    {
        $messages  = array();
        $gitignore = null;
        if (is_file($filePath = Yii::getPathOfAlias('common.data.gitignore') . '.txt') && is_readable($filePath)) {
            $gitignore = file_get_contents($filePath);
        }

        $aliases = CMap::mergeArray((array)Yii::app()->params['cache.directory.aliases'], $extraAliases);
        $aliases = array_unique($aliases);
        
        foreach ($aliases as $alias) {
            
            // make sure we only flush cache folders
            if (substr($alias, -5) != 'cache') {
                continue;
            }
            
            // and procced deleting
            $directory = Yii::getPathOfAlias($alias);
            if (file_exists($directory) && is_dir($directory)) {
                $messages[] = sprintf('Clearing the "%s" directory...', $directory);
                FileSystemHelper::deleteDirectoryContents($directory, true, 0);
                if (!empty($gitignore)) {
                    $messages[] = sprintf('Creating the "%s" file', $directory . '/.gitignore');
                    file_put_contents($directory . '/.gitignore', $gitignore);
                }
            }
        }

        return $messages;
    }
}