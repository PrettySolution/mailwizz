<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * ThemeInit
 * 
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com> 
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.0
 */
 
abstract class ThemeInit extends CApplicationComponent
{
    public $name = 'Missing theme name';
    
    public $author = 'Unknown';
    
    public $website = 'javascript:;';
    
    public $email;
    
    public $description = 'Missing theme description';

    public $version = '1.0';
    
    public final function getReflection()
    {
        static $_reflection;
        if ($_reflection) {
            return $_reflection;
        }
        return $_reflection = new ReflectionClass($this);
    }
    
    public final function getDirName()
    {
        static $_dirName;
        if ($_dirName) {
            return $_dirName;
        }
        return $_dirName = basename(dirname($this->getReflection()->getFilename()));
    }
    
    public final function getPathAlias()
    {
        return 'theme-' . $this->getDirName();
    }
    
    public final function setOption($key, $value)
    {
        if (empty($key)) {
            return;
        }
        $appName = Yii::app()->apps->getCurrentAppName();
        return Yii::app()->options->set('system.theme.'.$appName.'.'.$this->getDirName().'.data.'.$key, $value);
    }
    
    public final function getOption($key, $defaultValue = null)
    {
        if (empty($key)) {
            return;
        }
        $appName = Yii::app()->apps->getCurrentAppName();
        return Yii::app()->options->get('system.theme.'.$appName.'.'.$this->getDirName().'.data.'.$key, $defaultValue);
    }

    public final function removeOption($key)
    {
        if (empty($key)) {
            return;
        }
        $appName = Yii::app()->apps->getCurrentAppName();
        return Yii::app()->options->remove('system.theme.'.$appName.'.'.$this->getDirName().'.data.'.$key);
    }

    public final function removeAllOptions()
    {
        $appName = Yii::app()->apps->getCurrentAppName();
        return Yii::app()->options->removeCategory('system.theme.'.$appName.'.'.$this->getDirName().'.data');
    }
    
    public final function getBaseUrl()
    {
        return Yii::app()->theme->getBaseUrl();
    }
    
    public final function getBasePath()
    {
        return Yii::app()->theme->getBasePath();    
    }
    
    public function settingsPage()
    {
        throw new CHttpException(404, Yii::t('app', 'The requested page does not exist.'));
    }
    
    public function getPageUrl()
    {
        
    }
    
    public function beforeEnable()
    {
        return true;
    }

    public function afterEnable()
    {
    }

    public function beforeDisable()
    {
        return true;
    }

    public function afterDisable()
    {
    }

    public function beforeDelete()
    {
        return true;
    }

    public function afterDelete()
    {
    }
    
    abstract public function run();
    
} 