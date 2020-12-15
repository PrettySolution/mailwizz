<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * ThemeManager
 * 
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com> 
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.3
 */
 
class ThemeManager extends CThemeManager
{
    protected $themesInstances = array();
    
    protected $_errors = array();
       
    /**
     * Override parent implementation
     * 
	 * @param string $name name of the theme to be retrieved
	 * @return CTheme the theme retrieved. Null if the theme does not exist.
	 */
    public function getTheme($name)
	{
		$themePath = $this->getBasePath() . DIRECTORY_SEPARATOR . $name;
        $className = StringHelper::simpleCamelCase($name);
        $className.='Theme';
        
        if (!is_dir($themePath) || !is_file($themePath . DIRECTORY_SEPARATOR . $className . '.php')) {
            return null;
        }

		$class = Yii::import($this->themeClass, true);
		return new $class($name, $themePath, $this->getBaseUrl().'/'.$name);
	}
    
    /**
     * Override parent implementation
     * 
	 * @return array list of available theme names
	 */
	public function getThemeNames()
	{
        return array_keys($this->getAppThemes());
	}

	/**
     * Override parent implementation
     * 
	 * @return string the base path for all themes. Defaults to "WebRootPath/themes".
	 */
	public function getBasePath()
	{
		return parent::getBasePath();
	}

	/**
     * Override parent implementation
     * 
	 * @param string $value the base path for all themes.
	 * @throws CException if the base path does not exist
	 */
	public function setBasePath($value)
	{
		parent::setBasePath($value);
	}

	/**
     * Override parent implementation
     * 
	 * @return string the base URL for all themes. Defaults to "/WebRoot/themes".
	 */
	public function getBaseUrl()
	{
		return parent::getBaseUrl();
	}

	/**
     * Override parent implementation
     * 
	 * @param string $value the base URL for all themes.
	 */
	public function setBaseUrl($value)
	{
		parent::setBaseUrl($value);
	}
    
    /**
     * Start custom implementation of the manager
     */
    
    public function setAppTheme($appName = null)
    {
        if (Yii::app()->theme) {
            return true;
        }
        
        $appName = $this->correctAppName($appName);

        $enabledThemeName = Yii::app()->options->get('system.theme.'.$appName.'.enabled_theme');
        if (empty($enabledThemeName)) {
            return false;
        }
        
        if (!$this->isThemeEnabled($enabledThemeName, $appName)) {
            return false;
        }
        
        $this->registerAssets();
        
        Yii::app()->theme = $enabledThemeName;
        
        $instance = $this->getThemeInstance($enabledThemeName, $appName);

        Yii::setPathOfAlias($instance->getPathAlias(), dirname($instance->getReflection()->getFilename()));
        
        $instance->run();

        return true;
    }
    
    public function getAppThemes($appName = null)
    {
        $apps    = Yii::app()->apps;
        $appName = $this->correctAppName($appName);
        
        static $themes = array();
        if(!empty($themes[$appName])) {
            return (array)$themes[$appName];
        }
        
        $themes[$appName]   = array();
        $webApps            = $apps->getWebApps();
        $searchReplace      = array();
        
        foreach ($webApps as $webApp) {
            $searchReplace['/'. $webApp .'/'] = '/'. $appName .'/';
        }
        
		$basePath = $this->getBasePath();
        $basePath = str_replace(array_keys($searchReplace), array_values($searchReplace), $basePath);
        
        if (!is_dir($basePath)) {
            return $themes[$appName];
        }
        
        $themesFolders = (array)FileSystemHelper::getDirectoryNames($basePath);
        $reservedNames = (array)$webApps;
        
        foreach ($themesFolders as $folderName) {
            if (in_array($folderName, $reservedNames)) {
                continue;
            }
            $className = StringHelper::simpleCamelCase($folderName);
            $className.='Theme';
            
            if(!is_file($classFile = $basePath . DIRECTORY_SEPARATOR . $folderName . DIRECTORY_SEPARATOR . $className . '.php')) {
                continue;
            }
            
            $themes[$appName][$folderName] = $classFile;
        }
        
        array_multisort($themes[$appName], SORT_ASC, SORT_REGULAR);
        
		return $themes[$appName];
    }
    
    public function getThemesInstances($appName = null)
    {
        $appName    = $this->correctAppName($appName);
        $themes     = $this->getAppThemes($appName);
        $instances  = array();
        
        foreach ($themes as $themeName => $initClass) {
            if ($instance = $this->getThemeInstance($themeName, $appName)) {
                $instances[] = $instance;
            }
        }
        
        return $instances;
    }
    
    public function getThemeInstance($themeName, $appName = null)
    {
        $appName = $this->correctAppName($appName);
        $themes  = $this->getAppThemes($appName);
        
        if (!$this->themeExists($themeName, $appName)) {
            return null;
        }

        if (isset($this->themesInstances[$appName][$themeName])) {
            return $this->themesInstances[$appName][$themeName];
        }
        
        require_once $themes[$themeName];
        $className = basename($themes[$themeName], '.php');
        
        if (!isset($this->themesInstances[$appName]) || !is_array($this->themesInstances[$appName])) {
            $this->themesInstances[$appName] = array();
        }
        
        return $this->themesInstances[$appName][$themeName] = new $className();
    }
    
    public function themeExists($themeName, $appName = null)
    {
        $appName = $this->correctAppName($appName);
        return array_key_exists($themeName, $this->getAppThemes($appName));
    }
    
    public function isThemeEnabled($themeName, $appName = null)
    {
        $appName = $this->correctAppName($appName);
        return $this->themeExists($themeName, $appName) && Yii::app()->options->get('system.theme.'.$appName.'.enabled_theme') == $themeName;
    }
    
    public function enableTheme($themeName, $appName = null)
    {
        $appName = $this->correctAppName($appName);
        
        if (!$this->themeExists($themeName, $appName)) {
            $this->_errors[] = Yii::t('themes', 'The theme does not exists.');
            return false;
        }
        
        if ($this->isThemeEnabled($themeName, $appName)) {
            $this->_errors[] = Yii::t('themes', 'The theme is already enabled.');
            return false;
        }
        
        $instance = $this->getThemeInstance($themeName, $appName);

        if ($instance->beforeEnable() === false) {
            $this->_errors[] = Yii::t('themes', 'Enabling the theme {theme} has failed.', array(
                '{theme}' => $instance->name,
            ));
            return false;
        }
        
        Yii::app()->options->set('system.theme.'.$appName.'.enabled_theme', $themeName);

        $instance->afterEnable();

        return true;
    }
    
    public function disableTheme($themeName, $appName = null)
    {
        $appName = $this->correctAppName($appName);
        
        if (!$this->themeExists($themeName, $appName)) {
            $this->_errors[] = Yii::t('themes', 'The theme does not exists.');
            return false;
        }
        
        if (!$this->isThemeEnabled($themeName, $appName)) {
            return true;
        }
        
        $instance = $this->getThemeInstance($themeName, $appName);

        if ($instance->beforeDisable() === false) {
            $this->_errors[] = Yii::t('themes', 'The theme could not be disabled.');
            return false;
        }
 
        Yii::app()->options->remove('system.theme.'.$appName.'.enabled_theme');
        
        $instance->afterDisable();

        return true;
    }
    
    public function deleteTheme($themeName, $appName = null)
    {
        $appName = $this->correctAppName($appName);
        
        if (!$this->themeExists($themeName, $appName)) {
            $this->_errors[] = Yii::t('themes', 'The theme does not exists.');
            return false;
        }
        
        if (!$this->disableTheme($themeName, $appName)) {
            return false;
        }
        
        $instance = $this->getThemeInstance($themeName, $appName);

        if ($instance->beforeDelete() === false) {
            $this->_errors[] = Yii::t('themes', 'The theme cannot be deleted.');
            return false;
        }
        
        Yii::app()->options->remove('system.theme.'.$appName.'.enabled_theme');
        Yii::app()->options->removeCategory('system.theme.'.$appName.'.'.$instance->getDirName());
        Yii::app()->options->removeCategory('system.theme.'.$appName.'.'.$instance->getDirName().'.data');
        
        $instance->afterDelete();

        $dirToDelete = dirname($instance->getReflection()->getFilename());
        
        if (file_exists($dirToDelete) && is_dir($dirToDelete)) {
            FileSystemHelper::deleteDirectoryContents($dirToDelete, true, 1);
        }
        
        return true;
    }
  
    public function addError($error)
    {
        $this->_errors[] = $error;
        return $this;
    }
    
    public function hasErrors()
    {
        return !empty($this->_errors);
    }

    public function getErrors()
    {
        return $this->_errors;
    }
    
    public function correctAppName($appName = null)
    {
        $apps = Yii::app()->apps;
        if (empty($appName)) {
            $appName = $apps->getCurrentAppName();
        }
        return $appName;
    }
    
    protected final function registerAssets()
    {
        $appName    = Yii::app()->apps->getCurrentAppName();
        $component  = $appName.'SystemInit';
        
        if (!Yii::app()->hasComponent($component)) {
            return;
        }
        
        $component = Yii::app()->getComponent($component);
        if (!method_exists($component, 'registerAssets')) {
            return;
        }
        
        $reflection = new ReflectionMethod($component, 'registerAssets');
        if (!$reflection->isPublic()) {
            return;
        }
        
        $component->registerAssets();
    }
}