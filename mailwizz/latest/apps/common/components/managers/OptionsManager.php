<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * OptionsManager
 * 
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com> 
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.0
 */
 
class OptionsManager extends CApplicationComponent 
{
    const DEFAULT_CATEGORY = 'misc';

    /**
     * @var int 
     */
    public $cacheTtl = 60;

    /**
     * @var array 
     */
    protected $_options = array();

    /**
     * @var array 
     */
    protected $_categories = array();

    /**
     * @param $key
     * @param $value
     * @return $this
     * @throws CException
     */
    public function set($key, $value)
    {
        if (($existingValue = $this->get($key)) === $value || $value === null) {
            return $this;
        }
        
        $_key = $key;
        list($category, $key) = $this->getCategoryAndKey($key);
        $command = Yii::app()->db->createCommand();
        
        if ($this->get($_key) !== null) {
            $command->update('{{option}}', array(
                'value'         => is_string($value) ? $value : serialize($value),
                'is_serialized' => (int)(!is_string($value)),
                'last_updated'  => new CDbExpression('NOW()')
            ), '`category` = :c AND `key`=:k', array(':c' => $category, ':k' => $key));
        } else {
            $command->insert('{{option}}', array(
                'category'      => $category,
                'key'           => $key, 
                'value'         => is_string($value) ? $value : serialize($value),
                'is_serialized' => (int)(!is_string($value)),
                'date_added'    => new CDbExpression('NOW()'),
                'last_updated'  => new CDbExpression('NOW()')
            ));    
        }
        $this->_options[$_key] = $value;
        return $this;
    }

    /**
     * @param $key
     * @param null $defaultValue
     * @return mixed|null
     * @throws CException
     */
    public function get($key, $defaultValue = null)
    {
        // simple keys are set with default category, we need to retrieve them the same.
        $key = implode('.', $this->getCategoryAndKey($key));

        $this->loadCategory($key);
        return isset($this->_options[$key]) ? $this->_options[$key] : $defaultValue;
    }

    /**
     * @param $key
     * @param null $defaultValue
     * @return bool
     * @throws CException
     */
    public function isTrue($key, $defaultValue = null)
    {
        $value = $this->get($key, $defaultValue);
        if (empty($value)) {
            return false;
        }
        $lValue = strtolower($value);
        return $value === true || (int)$value > 0 || $lValue === 'yes' || $lValue === 'on' || ($value != null && $value != false && $lValue != 'no' && $lValue != 'off');
    }

    /**
     * @param $key
     * @param null $defaultValue
     * @return bool
     * @throws CException
     */
    public function isFalse($key, $defaultValue = null)
    {
        return !$this->isTrue($key, $defaultValue);
    }

    /**
     * @param $key
     * @return bool
     */
    public function remove($key)
    {
        if (isset($this->_options[$key])) {
            unset($this->_options[$key]);
        }
        
        list($category, $key) = $this->getCategoryAndKey($key);

        Yii::app()->db->createCommand()->delete('{{option}}', '`category` = :c AND `key` = :k', array(':c' => $category, ':k' => $key));
        return true;
    }

    /**
     * @param $category
     * @return bool
     */
    public function removeCategory($category)
    {
        if (isset($this->_categories[$category])) {
            unset($this->_categories[$category]);
        }
        
        Yii::app()->db->createCommand()->delete('{{option}}', '`category` = :c', array(':c' => $category));
        // added in 1.3.5.4
        Yii::app()->db->createCommand()->delete('{{option}}', '`category` LIKE :c', array(':c' => $category . '%'));
        
        foreach ($this->_options as $key => $value) {
            if (strpos($key, $category) === 0) {
                unset($this->_options[$key]);
            }
        }
        
        return true;
    }

    /**
     * @param $key
     * @return $this
     * @throws CException
     */
    protected function loadCategory($key)
    {
        list($category, $key) = $this->getCategoryAndKey($key);
        
        if (isset($this->_categories[$category])) {
            return $this;
        }
        
        // NOTE: add caching but be aware of the CLI problems when the cache does not invalidate!
        $command = Yii::app()->db->createCommand('SELECT `category`, `key`, `value`, `is_serialized` FROM `{{option}}` WHERE `category` = :c');
        $rows = $command->queryAll(true, array(':c' => $category));
        
        foreach ($rows as $row) {
            $this->_options[$row['category'].'.'.$row['key']] = !$row['is_serialized'] ? $row['value'] : unserialize($row['value']);
        }
        
        $this->_categories[$category] = true;
        
        return $this;
    }

    /**
     * @param $key
     * @return array
     */
    public function getCategoryAndKey($key)
    {
        $category = self::DEFAULT_CATEGORY;
        
        if (strpos($key, '.') !== false) {
            $parts = explode('.', $key);
            $key = array_pop($parts);
            $category = implode('.', $parts);
        }
        
        return array($category, $key);
    }

    /**
     * @return $this
     */
    public function resetLoaded()
    {
        $this->_options    = array();
        $this->_categories = array();
        return $this;
    }
}