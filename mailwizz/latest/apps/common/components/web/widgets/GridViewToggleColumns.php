<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * GridViewToggleColumns
 *
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.3.8.8
 */

class GridViewToggleColumns extends CWidget
{
    /**
     * @var CActiveRecord
     */
    public $model;
    
    /**
     * @var array
     */
    public $columns = array();

    /**
     * @var array
     */
    public $saveRoute = array('account/save_grid_view_columns');

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        Yii::app()->clientScript->registerScriptFile(Yii::app()->apps->getBaseUrl('assets/js/grid-view-toggle-columns.js'));
    }

    /**
     * @inheritdoc
     */
    public function run()
    {
        if (!in_array(Yii::app()->apps->getCurrentAppName(), array('customer', 'backend'))) {
            return;
        }
        Yii::app()->hooks->addFilter('grid_view_columns', array($this, '_handleGridViewColumns'), -1000);
        
        $this->render('grid-view-toggle-columns', array(
            'model'      => $this->model,
            'modelName'  => $this->model->modelName,
            'controller' => $this->controller->id,
            'action'     => $this->controller->action->id,
            'columns'    => $this->columns,
            'dbColumns'  => (array)Yii::app()->options->get($this->getOptionKey(), $this->columns)
        ));
    }

    /**
     * @param array $columns
     * @param $controller
     * @return array
     */
    public function _handleGridViewColumns(array $columns = array(), $controller)
    {
        $optionKey = $this->getOptionKey();
        $dbColumns = (array)Yii::app()->options->get($optionKey, array());
        
        // nothing to do, show all columns
        if (empty($dbColumns)) {
            return $columns;
        }
        
        $saveColumns = false;
        
        foreach ($dbColumns as $index => $column) {
            if (!in_array($column, $this->columns)) {
                unset($dbColumns[$index]);
                $saveColumns = true;
            }
        }
        
        if ($saveColumns) {
            Yii::app()->options->set($optionKey, $dbColumns);
        }
        
        foreach ($columns as $index => $column) {
            if (isset($column['class']) || !isset($column['name'])) {
                continue;
            }
            if (!in_array($column['name'], $dbColumns)) {
                unset($columns[$index]);
            }
        }
        
        return $columns;
    }

    /**
     * @return string
     */
    public function getOptionKey()
    {
        $optionKey = sprintf('%s:%s:%s', $this->model->modelName, $this->controller->id, $this->controller->action->id);

        if (Yii::app()->apps->isAppName('backend')) {
            $userId    = (int)Yii::app()->user->getId();
            $optionKey = sprintf('system.views.grid_view_columns.users.%d.%s', $userId, $optionKey);
        } else {
            $customerId = (int)Yii::app()->customer->getId();
            $optionKey  = sprintf('system.views.grid_view_columns.customers.%d.%s', $customerId, $optionKey);
        }
        
        return $optionKey;
    }
}