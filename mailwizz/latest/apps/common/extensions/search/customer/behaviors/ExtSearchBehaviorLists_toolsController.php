<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 */

class ExtSearchBehaviorLists_toolsController extends CBehavior
{
	/**
	 * @return array
	 */
	public function searchableActions()
	{
		return array(
			'index' => array(
				'keywords'          => array('tools', 'sync', 'split', 'list sync', 'lists sync', 'list split', 'lists split'),
                'keywordsGenerator' => array($this, '_indexKeywordsGenerator')
            ),
		);
	}

    /**
     * @return array
     */
    public function _indexKeywordsGenerator()
    {
        $syncToolModel  = new ListsSyncTool();
        $splitToolModel = new ListSplitTool();

        $keywords = [];

        $keywords = CMap::mergeArray($keywords, array_values($syncToolModel->attributeLabels()));
        $keywords = CMap::mergeArray($keywords, array_values($splitToolModel->attributeLabels()));

        return $keywords;
    }
}
	