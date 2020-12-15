<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 */

class ExtSearchBehaviorTemplates_categoriesController extends CBehavior
{
	/**
	 * @return array
	 */
	public function searchableActions()
	{
		return array(
			'index' => array(
				'keywords'          => array('template category', 'email templates category', 'template category'),
                'childrenGenerator' => array($this, '_indexChildrenGenerator')
            ),
            'create' => array(
                'keywords' => array('create email templates category', 'create template category'),
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
	    $criteria->addCondition('customer_id = :cid');
        $criteria->addCondition('name LIKE :term');
	    $criteria->params[':cid']  = (int)Yii::app()->customer->getId();
	    $criteria->params[':term'] = '%'. $term .'%';
        $criteria->order = 'category_id DESC';
        $criteria->limit = 5;
        
        $models = CustomerEmailTemplateCategory::model()->findAll($criteria);

        $items  = array();
        foreach ($models as $model) {
            $item        = new SearchExtSearchItem();
            $item->title = $model->name;
            $item->url   = Yii::app()->createUrl('templates_categories/update', array('id' => $model->category_id));
            $item->score++;
            $items[] = $item->fields;
        }
        return $items;
    }
}
	