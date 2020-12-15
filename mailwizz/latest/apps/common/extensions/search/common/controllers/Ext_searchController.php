<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 */

class Ext_searchController extends Controller
{
	/**
	 * @var 
	 */
    public $extension;

	/**
	 * @return string
	 */
    public function getViewPath()
    {
        return Yii::getPathOfAlias('ext-search.common.views');
    }

	/**
	 * @return string
	 * @throws CException
	 * @throws ReflectionException
	 */
    public function actionIndex()
    {
	    /**
	     * Allow only ajax requests here
	     */
    	if (!Yii::app()->request->isAjaxRequest) {
    		return $this->redirect(array('dashboard/index'));
	    }
    	
	    $search = new SearchExtSearch();
	    $search->term = Yii::app()->request->getQuery('term');

	    return $this->renderPartial('search-results', array(
		    'results' => $search->getResults(),
	    ));
    }
}
