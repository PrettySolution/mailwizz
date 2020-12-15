<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 */

class ExtSearchBehaviorApi_keysController extends CBehavior
{
	/**
	 * @return array
	 */
	public function searchableActions()
	{
		return array(
			'index' => array(
				'keywords'          => array('api access', 'key', 'keys', 'api key', 'api keys'),
                'skip'              => array($this, '_skip'),
                'childrenGenerator' => array($this, '_indexChildrenGenerator'),
            ),
            'generate' => array(
                'keywords'          => array('create api key', 'api access'),
                'skip'              => array($this, '_skip')
            ),
		);
	}

    /**
     * @return bool
     */
    public function _skip()
    {
        if (Yii::app()->options->get('system.common.api_status') != 'online') {
            return true;
        } elseif (Yii::app()->customer->getModel()->getGroupOption('api.enabled', 'yes') != 'yes') {
            return true;
        }

        return false;
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
        $criteria->addCondition('(name LIKE :term OR description LIKE :term OR public LIKE :term OR private LIKE :term)');
        $criteria->params[':term'] = '%'. $term .'%';
        $criteria->params[':cid']  = (int)Yii::app()->customer->getId();
        $criteria->order = 'key_id DESC';
        $criteria->limit = 5;

        $models = CustomerApiKey::model()->findAll($criteria);

        $items = array();
        foreach ($models as $model) {
            $item        = new SearchExtSearchItem();
            $item->title = !empty($model->name) ? $model->name : Yii::t('api_keys', 'Api key: {key}', array('{key}' => $model->public));
            $item->url   = Yii::app()->createUrl('api_keys/update', array('id' => $model->key_id));
            $item->score++;
            $items[] = $item->fields;
        }
        return $items;
    }
}
	