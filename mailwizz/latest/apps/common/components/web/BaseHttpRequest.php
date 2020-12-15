<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * HttpRequest
 * 
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com> 
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.0
 */
 
class BaseHttpRequest extends CHttpRequest
{
    /**
     * @var bool
     */
    public $globalsCleaned = false;

    /**
     * @var array
     */
    public $noCsrfValidationRoutes = array();

    /**
     * HttpRequest::normalizeRequest()
     * 
     * Normalizes the request data.
     * This method strips off slashes in request data if get_magic_quotes_gpc() returns true.
     * It also performs CSRF validation if {@link enableCsrfValidation} is true.
     * 
     */
    protected function normalizeRequest()
    {
        parent::normalizeRequest();
        if ($this->getIsPostRequest() && $this->enableCsrfValidation && !$this->checkCurrentRoute()) {
            Yii::app()->detachEventHandler('onBeginRequest', array($this, 'validateCsrfToken'));
        }           
    }
    
    /**
     * HttpRequest::checkCurrentRoute()
     * 
     * @return bool
     */
    protected function checkCurrentRoute() 
    {
        foreach ($this->noCsrfValidationRoutes as $route) {
            if (($pos = strpos($route, "*")) !== false) {
                $route = substr($route, 0, $pos - 1);
                if (strpos($this->pathInfo, $route) === 0) {
                    return false;
                }  
            } elseif ($this->pathInfo === $route) {
                return false;
            }  
        }
        return true;
    }
    
    /**
     * HttpRequest::getPost()
     * 
     * @param string $name
     * @param mixed $defaultValue
     * @return mixed
     */
    public function getPost($name, $defaultValue = null) 
    {
        if (!$this->globalsCleaned) {
            Yii::app()->ioFilter->cleanGlobals();
        }
        
        if ($name === null) {
            return $_POST;
        }
        
        return parent::getPost($name, $defaultValue);
    }

    /**
     * HttpRequest::getQuery()
     * 
     * @param string $name
     * @param mixed $defaultValue
     * @return mixed
     */
    public function getQuery($name, $defaultValue = null)
    {
        if (!$this->globalsCleaned) {
            Yii::app()->ioFilter->cleanGlobals();
        }
        
        if ($name === null) {
            return $_GET;
        }
        
        return parent::getQuery($name, $defaultValue);
    }
    
    /**
     * HttpRequest::getPostPut()
     * 
     * @param string $name
     * @param mixed $defaultValue
     * @return mixed
     */
    public function getPostPut($name, $defaultValue = null)
    {
        return $this->getPost($name, $this->getPut($name, $defaultValue));
    }
    
    /**
     * HttpRequest::getPut()
     * 
     * @param string $name
     * @param mixed $defaultValue
     * @return mixed
     */
    public function getPut($name, $defaultValue = null)
    {
        if ($name === null) {
            return Yii::app()->ioFilter->stripClean($this->getRestParams());
        }
        
        return Yii::app()->ioFilter->stripClean(parent::getPut($name, $defaultValue));
    }
    
    /**
     * HttpRequest::getDelete()
     * 
     * @param string $name
     * @param mixed $defaultValue
     * @return mixed
     */
    public function getDelete($name, $defaultValue = null)
    {
        if ($name === null) {
            return Yii::app()->ioFilter->stripClean($this->getRestParams());
        }
        
        return Yii::app()->ioFilter->stripClean(parent::getDelete($name, $defaultValue));
    }

    /**
     * HttpRequest::getPatch()
     *
     * @param string $name
     * @param mixed $defaultValue
     * @return mixed
     */
    public function getPatch($name, $defaultValue = null)
    {
        if ($name === null) {
            return Yii::app()->ioFilter->stripClean($this->getRestParams());
        }

        return Yii::app()->ioFilter->stripClean(parent::getPatch($name, $defaultValue));
    }
    
    /**
     * HttpRequest::getServer()
     * 
     * @param string $name
     * @param mixed $defaultValue
     * @return mixed
     */
    public function getServer($name, $defaultValue = null)
    {
        if (!$this->globalsCleaned) {
            Yii::app()->ioFilter->cleanGlobals();
        }
        
        if ($name === null) {
            return $_SERVER;
        }
        
        $name = strtoupper($name);
        return isset($_SERVER[$name]) ? $_SERVER[$name] : $defaultValue;
    }

    /**
     * HttpRequest::getOriginalPost()
     *
     * @param string $name
     * @param mixed $defaultValue
     * @return mixed
     */
    public function getOriginalPost($name, $defaultValue = null)
    {
        if (!$this->globalsCleaned) {
            Yii::app()->ioFilter->cleanGlobals();
        }
        
        if ($name === null) {
            return isset(Yii::app()->params['POST']) ? Yii::app()->params['POST'] : array();
        }
        
        return isset(Yii::app()->params['POST'][$name]) ? Yii::app()->params['POST'][$name] : $defaultValue;
    }

    /**
     * HttpRequest::getOriginalQuery()
     *
     * @param string $name
     * @param mixed $defaultValue
     * @return mixed
     */
    public function getOriginalQuery($name, $defaultValue = null)
    {
        if (!$this->globalsCleaned) {
            Yii::app()->ioFilter->cleanGlobals();
        }
        
        if ($name === null) {
            return isset(Yii::app()->params['GET']) ? Yii::app()->params['GET'] : array();
        }
        
        return isset(Yii::app()->params['GET'][$name]) ? Yii::app()->params['GET'][$name] : $defaultValue;
    }
    
}