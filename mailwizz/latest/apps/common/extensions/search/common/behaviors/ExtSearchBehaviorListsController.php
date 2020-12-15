<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 */

class ExtSearchBehaviorListsController extends CBehavior 
{
	/**
	 * @return array
	 */
	public function searchableActions()
	{
		return array(
			'index' => array(
				'title'     => Yii::t('lists', 'Email lists'),
				'keywords'  => array(
					'list', 'email list', 'subscribers', 'segments', 'pages', 'embed', 'subscribe', 'unsubscribe', 
					'import', 'list import', 'export', 'list export', 'custom fields', 'custom field', 'list fields'
				),
				'skip'              => array($this, '_indexSkip'),
				'childrenGenerator' => array($this, '_indexChildrenGenerator'),
			),
			'create' => array(
				'keywords'  => array('list', 'create list', 'list create', 'subscriber'),
				'skip'      => array($this, '_createSkip'),
			),
		);
	}

	/**
	 * @param SearchExtSearchItem $item
	 *
	 * @return bool
	 */
	public function _indexSkip(SearchExtSearchItem $item)
	{
		if (MW_APP_NAME == 'customer') {
			return false;
		}
		return !Yii::app()->user->getModel()->hasRouteAccess($item->route);
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

		if (MW_APP_NAME == 'customer') {
			$criteria->addCondition('customer_id = :cid');
			$criteria->params[':cid'] = (int)Yii::app()->customer->getId();
		}

		$criteria->addCondition('(name LIKE :term OR display_name LIKE :term OR description LIKE :term)');
		$criteria->params[':term'] = '%'. $term .'%';
		$criteria->order = 'list_id DESC';
		$criteria->limit = 5;
		
		$models = Lists::model()->findAll($criteria);
		$items  = array();
		foreach ($models as $model) {
			$item        = new SearchExtSearchItem();
			$item->title = $model->name;
			$item->url   = Yii::app()->createUrl('lists/overview', array('list_uid' => $model->list_uid));
			$item->score++;
			
			if (MW_APP_NAME == 'customer') {
				$item->buttons = array(
					CHtml::link(IconHelper::make('update'), array('lists/update', 'list_uid' => $model->list_uid), array('title' => Yii::t('lists', 'Update'), 'class' => 'btn btn-xs btn-primary btn-flat')),
					CHtml::link(IconHelper::make('fa-users'), array('list_subscribers/index', 'list_uid' => $model->list_uid), array('title' => Yii::t('lists', 'Subscribers'), 'class' => 'btn btn-xs btn-primary btn-flat')),
					CHtml::link(IconHelper::make('import'), array('list_import/index', 'list_uid' => $model->list_uid), array('title' => Yii::t('lists', 'Import'), 'class' => 'btn btn-xs btn-primary btn-flat')),
					CHtml::link(IconHelper::make('ion-folder'), array('list_page/index', 'list_uid' => $model->list_uid, 'type' => 'subscribe-form'), array('title' => Yii::t('lists', 'Pages'), 'class' => 'btn btn-xs btn-primary btn-flat')),
				);
			}
			
			$items[] = $item->fields;
		}
		return $items;
	}

	/**
	 * @return bool
	 */
	public function _createSkip()
	{
		if (MW_APP_NAME == 'customer') {
			$customer = Yii::app()->customer->getModel();
			return (int)$customer->getGroupOption('lists.max_lists', -1) == 0;
		}
		return true;
	}
}
	