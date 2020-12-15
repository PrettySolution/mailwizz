<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * HooksManager
 * 
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com> 
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.0
 */
 
class HooksManager extends CApplicationComponent 
{
    private $_actionsMap;
    
    private $_filtersMap;
    
    /**
     * HooksManager::getActionsMap()
     * 
     * @return CMap instance
     */
    protected function getActionsMap()
    {
        if (!($this->_actionsMap instanceof CMap)) {
            $this->_actionsMap = new CMap();    
        }    
        
        return $this->_actionsMap;
    }
    
    /**
     * HooksManager::addAction()
     * 
     * @param mixed $tag
     * @param mixed $callback
     * @param integer $priority
     * @return HooksManager
     */
    public function addAction($tag, $callback, $priority = 10)
    {       
        if (empty($tag) || !is_callable($callback, true)) {
            return $this;
        }
        
        if (!$this->getActionsMap()->contains($tag)) {
            $this->getActionsMap()->add($tag, new CList());
        }
        
        if ($this->hasAction($tag, $callback)) {
            return $this;
        }
        
        $this->getActionsMap()->itemAt($tag)->add(array(
            'callback'    => $callback,
            'priority'    => (int)$priority,
        ));
        
        return $this;
    }
    
    /**
     * HooksManager::doAction()
     * 
     * @param mixed $tag
     * @param mixed $arg
     * @return HooksManager
     */
    public function doAction($tag, $arg = null)
    {
        if (!$this->getActionsMap()->contains($tag)) {
            return $this;
        }
        
        $actions    = $this->getActionsMap()->itemAt($tag)->toArray();
        $sort       = array();
        $callbacks  = array();
        $start      = 0;

        // array_multisort will trigger: Fatal error: Nesting level too deep - recursive dependency?
        // if there are too many callbacks with big objects, so we need to work around that!
        
        foreach ($actions as $index => $action) {
            $sort[] = (int)$action['priority'];
            
            // generate a reference key, keep it a string
            $key = 'key' . ($start++);
            
            // store the callback
            $callbacks[$key] = $action['callback'];
            
            // also store a key reference in the action
            $actions[$index]['key'] = $key;
            
            // unset the callback from the action
            unset($actions[$index]['callback']);
        }
        
        // now multisort will loop through an easy array.
        array_multisort($sort, $actions);
        
        // restore all the callbacks to their action.
        foreach ($callbacks as $key => $callback) {
            foreach ($actions as $actionIndex => $action) {
                if ($action['key'] === $key) {
                    $actions[$actionIndex]['callback'] = $callback;
                    break;
                }    
            }
        }
        
        // destroy all the old refrences to the callbacks.
        unset($callbacks);
        
        $args = func_get_args();
        $args = array_slice($args, 2);
        array_unshift($args, $arg);
        
        foreach ($actions as $action) {
            call_user_func_array($action['callback'], $args);
        }
        
        Yii::trace('Did '.$tag.' action, '.count($actions).' action hooks were triggered!');
        
        return $this;
    }
    
    /**
     * HooksManager::hasAction()
     * 
     * @param mixed $tag
     * @param mixed $callback
     * @return bool
     */
    public function hasAction($tag, $callback)
    {
        if (!$this->getActionsMap()->contains($tag)) {
            return false;
        }
        
        $actions = $this->getActionsMap()->itemAt($tag)->toArray();
        foreach ($actions as $action) {
            if ($action['callback'] === $callback) {
                return true;
            }
        }
        return false;
    }
    
    /**
     * HooksManager::hasActions()
     * 
     * @param mixed $tag
     * @return bool
     */
    public function hasActions($tag)
    {
        return $this->getActionsCount($tag) > 0;
    }
    
    /**
     * HooksManager::getActionsCount()
     * 
     * @param mixed $tag
     * @return int
     */
    public function getActionsCount($tag) 
    {
        if (!$this->getActionsMap()->contains($tag)) {
            return 0;
        }
        return $this->getActionsMap()->itemAt($tag)->getCount();
    }
    
    /**
     * HooksManager::removeAction()
     * 
     * @param mixed $tag
     * @param mixed $callback
     * @return bool
     */
    public function removeAction($tag, $callback)
    {
        if (!$this->getActionsMap()->contains($tag)) {
            return false;
        }
        
        $actions = $this->getActionsMap()->itemAt($tag)->toArray();
        foreach ($actions as $index => $action) {
            if ($action['callback'] === $callback) {
                $this->getActionsMap()->itemAt($tag)->removeAt($index);
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * HooksManager::removeAllActions()
     * 
     * @param mixed $tag
     * @return bool
     */
    public function removeAllActions($tag)
    {
        if (!$this->getActionsMap()->contains($tag)) {
            return false;
        }
        
        $this->getActionsMap()->itemAt($tag)->clear();
        
        return true;
    }

    /**
     * HooksManager::getFiltersMap()
     * 
     * @return CMap
     */
    protected function getFiltersMap()
    {
        if (!($this->_filtersMap instanceof CMap)) {
            $this->_filtersMap = new CMap();    
        }    
        
        return $this->_filtersMap;
    }
    
    /**
     * HooksManager::addFilter()
     * 
     * @param mixed $tag
     * @param mixed $callback
     * @param integer $priority
     * @return HooksManager
     */
    public function addFilter($tag, $callback, $priority = 10)
    {
        if (empty($tag) || !is_callable($callback, true)) {
            return $this;
        }
        
        if (!$this->getFiltersMap()->contains($tag)) {
            $this->getFiltersMap()->add($tag, new CList());
        }
        
        if ($this->hasFilter($tag, $callback)) {
            return $this;
        }
        
        $this->getFiltersMap()->itemAt($tag)->add(array(
            'callback'    => $callback,
            'priority'    => (int)$priority,
        ));
        
        return $this;
    }
    
    /**
     * HooksManager::applyFilters()
     * 
     * @param mixed $tag
     * @param mixed $arg
     * @return mixed
     */
    public function applyFilters($tag, $arg)
    {
        if (!$this->getFiltersMap()->contains($tag)) {
            return $arg;
        }
        
        $filters    = $this->getFiltersMap()->itemAt($tag)->toArray();
        $sort       = array();
        $callbacks  = array();
        $start      = 0;

        // array_multisort will trigger: Fatal error: Nesting level too deep - recursive dependency?
        // if there are too many callbacks with big objects, so we need to work around that!
        
        foreach ($filters as $index => $filter) {
            $sort[] = (int)$filter['priority'];
            
            // generate a reference key, keep it a string
            $key = 'key' . ($start++);
            
            // store the callback
            $callbacks[$key] = $filter['callback'];
            
            // also store a key reference in the filter
            $filters[$index]['key'] = $key;
            
            // unset the callback from the filter
            unset($filters[$index]['callback']);
        }
        
        // now multisort will loop through an easy array.
        array_multisort($sort, $filters);
        
        // restore all the callbacks to their filters.
        foreach ($callbacks as $key => $callback) {
            foreach ($filters as $filterIndex => $filter) {
                if ($filter['key'] === $key) {
                    $filters[$filterIndex]['callback'] = $callback;
                    break;
                }    
            }
        }
        
        // destroy all the old refrences to the callbacks.
        unset($callbacks);
        
        $args = func_get_args();
        $args = array_slice($args, 2);
        array_unshift($args, $arg);
        
        foreach ($filters as $filter) {
            $arg = call_user_func_array($filter['callback'], $args);
            
            // remove old arg value
            array_shift($args);
            
            // add the new one
            array_unshift($args, $arg);
        }
        
        Yii::trace('Did '.$tag.' filter, '.count($filters).' filter hooks were triggered!');
        
        return $arg;
    }
    
    /**
     * HooksManager::hasFilter()
     * 
     * @param mixed $tag
     * @param mixed $callback
     * @return bool
     */
    public function hasFilter($tag, $callback)
    {
        if (!$this->getFiltersMap()->contains($tag)) {
            return false;
        }
        
        $filters = $this->getFiltersMap()->itemAt($tag)->toArray();
        foreach ($filters as $filter) {
            if ($filter['callback'] === $callback) {
                return true;
            }
        }
        return false;
    }
    
    /**
     * HooksManager::hasFilters()
     * 
     * @param mixed $tag
     * @return bool
     */
    public function hasFilters($tag)
    {
        return $this->getFiltersCount($tag) > 0;
    }
    
    /**
     * HooksManager::getFiltersCount()
     * 
     * @param mixed $tag
     * @return int
     */
    public function getFiltersCount($tag) 
    {
        if (!$this->getFiltersMap()->contains($tag)) {
            return 0;
        }
        return $this->getFiltersMap()->itemAt($tag)->getCount();
    }
    
    /**
     * HooksManager::removeFilter()
     * 
     * @param mixed $tag
     * @param mixed $callback
     * @return bool
     */
    public function removeFilter($tag, $callback)
    {
        if (!$this->getFiltersMap()->contains($tag)) {
            return false;
        }
        
        $filters = $this->getFiltersMap()->itemAt($tag)->toArray();
        foreach ($filters as $index => $filter) {
            if ($filter['callback'] === $callback) {
                $this->getFiltersMap()->itemAt($tag)->removeAt($index);
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * HooksManager::removeAllFilters()
     * 
     * @param mixed $tag
     * @return bool
     */
    public function removeAllFilters($tag)
    {
        if (!$this->getFiltersMap()->contains($tag)) {
            return false;
        }
        
        $this->getFiltersMap()->itemAt($tag)->clear();
        
        return true;
    }
    
}
