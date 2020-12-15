<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 */

class ExtSearchBehaviorCustomersController extends CBehavior 
{
	/**
	 * @return array
	 */
	public function searchableActions()
	{
		return array(
			'index' => array(
				'keywords'          => array('customer', 'customer list', 'customers list'),
				'childrenGenerator' => array($this, '_indexChildrenGenerator')
			),
            'create' => array(
                'keywords'  => array('create customer', 'customer create'),
            ),
		);
	}

	/**
	 * @param $term
	 * @param SearchExtSearchItem|null $parent
	 *
	 * @return array
	 */
	public function _indexChildrenGenerator($term, SearchExtSearchItem $parent = null)
	{
		$criteria = new CDbCriteria();
		$criteria->addCondition('first_name LIKE :term OR last_name LIKE :term OR email LIKE :term');
		$criteria->params[':term'] = '%'. $term .'%';
        $criteria->order = 'customer_id DESC';
        $criteria->limit = 5;
        
        $models = Customer::model()->findAll($criteria);
		$items  = array();
		foreach ($models as $model) {
			$item        = new SearchExtSearchItem();
			$item->title = $model->getFullName();
			$item->url   = Yii::app()->createUrl('customers/update', array('id' => $model->customer_id));
			$item->score++;
			$items[] = $item->fields;
		}
		return $items;
	}
}
	