<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * ListDefaultFieldsBehavior
 * 
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com> 
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.0
 */
 
class ListDefaultFieldsBehavior extends CActiveRecordBehavior 
{
	/**
	 * @param CEvent $event
	 */
    public function afterSave($event)
    {
        $type = ListFieldType::model()->findByAttributes(array(
            'identifier' => 'text',
        ));
        
        if (empty($type)) {
            return;
        }
        
        // create the default fields
	    $attributesList = array(
		    array(
			    'label'    => 'Email',
			    'tag'      => 'EMAIL',
			    'required' => ListField::TEXT_YES,
		    ),
	        array(
		        'label'     => 'First name',
		        'tag'       => 'FNAME',
		        'required'  => ListField::TEXT_NO,
	        ),
	        array(
		        'label'     => 'Last name',
		        'tag'       => 'LNAME',
		        'required'  => ListField::TEXT_NO,
	        )
        );

	    $sortOrder = 0;
	    
	    foreach ($attributesList as $attributes) {
		    
	    	$model = new ListField(); 
		    $model->attributes = $attributes;
		    $model->list_id    = $this->owner->list_id;
		    $model->type_id    = $type->type_id;
		    $model->sort_order = $sortOrder;
		    
		    $model->save();

		    $sortOrder++;
	    }
        
	    // now raise an action so any other custom field can be attached
	    try {
	    	$params = new CAttributeCollection(array(
	    		'list'          => $this->owner,
			    'lastSortOrder' => $sortOrder,
		    ));
		    Yii::app()->hooks->doAction('after_list_created_list_default_fields', $params);
	    } catch (Exception $e) {
		    
	    }
    }
}