<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * GridViewBulkAction
 * 
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com> 
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.3.5.4
 */
 
class GridViewBulkAction extends CWidget
{
    public $model;
    
    public $formAction;
    
    public function init()
    {
        parent::init();
        Yii::app()->clientScript->registerScriptFile(Yii::app()->apps->getBaseUrl('assets/js/grid-view-bulk-action.js'));
    }
    
    public function run()
    {
        $this->render('grid-view-bulk-action', array(
            'model'       => $this->model,
            'bulkActions' => $this->model->getBulkActionsList(),
            'formAction'  => $this->formAction,
        ));
    }
}