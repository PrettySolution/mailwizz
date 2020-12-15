<?php defined('MW_INSTALLER_PATH') || exit('No direct script access allowed');

/**
 * Controller
 * 
 * This is the base controller class
 * 
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com> 
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.0
 */
 
class Controller 
{
    // hold various data for views
    public $data = array();
    
    // the layout we will be using
    public $layout = 'layout';
    
    /**
     * This is a catch-all method so that child classes are free to implement a not found action.
     * It will be called when a route(controller/action) is not found.
     */
    public function actionNot_found()
    {
        $this->render('not-found');
    }
    
    /**
     * Default action for each controller.
     * Default implementation is to return a "Not Found" page
     * Child classes should implement it but it's not required, therefore the catch-all method from parent class.
     */
    public function actionIndex()
    {
        $this->actionNot_found();
    }
    
    /**
     * Render specific view file
     * 
     * The view file will use the provided {@layout} and it will be injected
     * in the place where {{CONTENT}} placeholder is defined.
     */
    public function render($viewName, $data = array(), $return = false)
    {
        if (!is_file($layout = MW_INSTALLER_PATH . '/views/' . $this->layout . '.php')) {
            return;
        }
        if (!is_file($view = MW_INSTALLER_PATH . '/views/' . $viewName . '.php')) {
            return;
        }
 
        $data = array_merge($this->data, (array)$data);
        $data['context'] = $this;
        $layout = renderFile($layout, $data, true);
        $view   = renderFile($view, $data, true);
        
        $content = str_replace('{{CONTENT}}', $view, $layout);
        
        if ($return) {
            return $content;
        }
        echo $content;
    }
    
    /**
     * Helper method to add action errors.
     * This is usually used for validation errors but can be used for other purposes as well.
     */
    public function addError($key, $value)
    {
        if (!isset($this->data['errors']) || !is_array($this->data['errors'])) {
            $this->data['errors'] = array();
        }
        
        $this->data['errors'][$key] = $value;
        return $this;
    }
    
    /**
     * Retrieve an error message set earlier with {@link Controller::addError}
     * 
     * @param string $key
     * @return mixed
     */
    public function getError($key)
    {
        return isset($this->data['errors'][$key]) ? $this->data['errors'][$key] : null;
    }
    
    /**
     * Check if the current action has errors
     * 
     * @return int
     */
    public function hasErrors()
    {
        return isset($this->data['errors']) && count($this->data['errors']) > 0;
    }
    
    /**
     * Reset all the errors for this action
     * 
     * @return {@link Controller} instance for chaining
     */
    public function resetErrors()
    {
        $this->data['errors'] = array();
        return $this;
    }
}