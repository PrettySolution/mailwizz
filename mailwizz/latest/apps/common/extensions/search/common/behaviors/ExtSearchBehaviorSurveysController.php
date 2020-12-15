<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 */

class ExtSearchBehaviorSurveysController extends CBehavior 
{
	/**
	 * @return array
	 */
	public function searchableActions()
	{
		return array(
			'index' => array(
				'title'     => Yii::t('surveys', 'Surveys'),
				'keywords'  => array(
					'survey', 'responders', 'segments', 'respond', 'custom fields', 'custom field', 'survey fields'
				),
				'skip'              => array($this, '_indexSkip'),
				'childrenGenerator' => array($this, '_indexChildrenGenerator'),
			),
			'create' => array(
				'keywords'  => array('survey', 'create survey', 'survey create', 'responder'),
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
		$criteria->order = 'survey_id DESC';
		$criteria->limit = 5;
		
		$models = Survey::model()->findAll($criteria);
		$items  = array();
		foreach ($models as $model) {
			$item        = new SearchExtSearchItem();
			$item->title = $model->name;
			$item->url   = Yii::app()->createUrl('surveys/overview', array('survey_uid' => $model->survey_uid));
			$item->score++;
			
			if (MW_APP_NAME == 'customer') {
				$item->buttons = array(
					CHtml::link(IconHelper::make('update'), array('surveys/update', 'survey_uid' => $model->survey_uid), array('title' => Yii::t('surveys', 'Update'), 'class' => 'btn btn-xs btn-primary btn-flat')),
					CHtml::link(IconHelper::make('fa-users'), array('survey_responders/index', 'survey_uid' => $model->survey_uid), array('title' => Yii::t('surveys', 'Responders'), 'class' => 'btn btn-xs btn-primary btn-flat')),
					CHtml::link(IconHelper::make('fa-list'), array('survey_fields/index', 'survey_uid' => $model->survey_uid), array('title' => Yii::t('surveys', 'Custom fields'), 'class' => 'btn btn-xs btn-primary btn-flat')),
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
			return (int)$customer->getGroupOption('surveys.max_surveys', -1) == 0;
		}
		return true;
	}
}
	