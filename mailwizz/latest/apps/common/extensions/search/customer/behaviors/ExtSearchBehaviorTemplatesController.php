<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 */

class ExtSearchBehaviorTemplatesController extends CBehavior
{
	/**
	 * @return array
	 */
	public function searchableActions()
	{
		return array(
			'index' => array(
				'keywords'          => array('gallery', 'email templates'),
                'childrenGenerator' => array($this, '_indexChildrenGenerator')
            ),
            'gallery' => array(
                'keywords'          => array('gallery', 'gallery email templates gallery', 'import email template', 'import template'),
                'childrenGenerator' => array($this, '_galleryChildrenGenerator')
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
        $criteria->order = 'template_id DESC';
        $criteria->limit = 5;
        
        $models = CustomerEmailTemplate::model()->findAll($criteria);
        $items  = array();
        foreach ($models as $model) {
            $item        = new SearchExtSearchItem();
            $item->title = $model->name;
            $item->url   = Yii::app()->createUrl('templates/update', array('template_uid' => $model->template_uid));
            $item->score++;
            $items[] = $item->fields;
        }
        return $items;
    }

    /**
     * @param $term
     * @param SearchExtSearchItem|null $parent
     *
     * @return array
     */
    public function _galleryChildrenGenerator($term, SearchExtSearchItem $parent = null)
    {
        $criteria = new CDbCriteria();
	    $criteria->addCondition('customer_id IS NULL');
        $criteria->addCondition('name LIKE :term');
        $criteria->params[':term'] = '%'. $term .'%';
        $criteria->limit = 5;
        
        $models = CustomerEmailTemplate::model()->findAll($criteria);
        $items  = array();
        foreach ($models as $model) {
            $item        = new SearchExtSearchItem();
            $item->title = $model->name;
            $item->url   = Yii::app()->createUrl('templates/gallery_import', array('template_uid' => $model->template_uid));
            $item->score++;
            $items[] = $item->fields;
        }
        return $items;
    }
}
	