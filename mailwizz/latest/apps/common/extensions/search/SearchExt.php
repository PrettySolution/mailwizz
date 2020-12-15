<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * SearchExt
 *
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 */

class SearchExt extends ExtensionInit
{
	/**
	 * @var string 
	 */
    public $name = 'Search';

	/**
	 * @var string 
	 */
    public $description = 'Add search abilities in backend and customer area';

	/**
	 * @var string 
	 */
    public $version = '1.0';

	/**
	 * @var string 
	 */
    public $author = 'Cristian Serban';

	/**
	 * @var string 
	 */
    public $website = 'https://www.mailwizz.com/';

	/**
	 * @var string 
	 */
    public $email = 'cristian.serban@mailwizz.com';

	/**
	 * @var array 
	 */
    public $allowedApps = array('backend', 'customer');

	/**
	 * @var int 
	 */
    public $priority = 999;

	/**
	 * @var bool 
	 */
    protected $_canBeDeleted = false;

	/**
	 * @var bool 
	 */
    protected $_canBeDisabled = true;

	/**
	 * @var 
	 */
    private $_assetsUrl;

	/**
	 * @throws CException
	 */
    public function run()
    {
	    /**
	     * Import the models
	     */
        Yii::import('ext-search.common.models.*');

	    /**
	     * Register the asset files
	     */
	    Yii::app()->hooks->addFilter('register_scripts', array($this, '_registerScripts'));
	    Yii::app()->hooks->addFilter('register_styles', array($this, '_registerStyles'));

	    /**
	     * Register the modal content
	     */
	    Yii::app()->hooks->addAction('after_opening_body_tag', array($this, '_registerModalView'));

	    /**
	     * Register the search button
	     */
	    Yii::app()->hooks->addAction('layout_top_navbar_menu_items_start', array($this, '_registerSearchButton'));
	    
	    /**
	     * Add the url rules.
	     */
	    Yii::app()->urlManager->addRules(array(
		    array('ext_search/index',       'pattern'   => 'search'),
		    array('ext_search/<action>/*',  'pattern'   => 'search/<action>/*'),
		    array('ext_search/<action>',    'pattern'   => 'search/<action>'),

	    ));

	    /**
	     * And now we register the controllers for the above rules.
	     */
	    Yii::app()->controllerMap['ext_search'] = array(
		    'class'     => 'ext-search.common.controllers.Ext_searchController',
		    'extension' => $this,
	    );
    }

	/**
	 * @return mixed
	 */
    public function getAssetsUrl()
    {
        if ($this->_assetsUrl !== null) {
            return $this->_assetsUrl;
        }
        return $this->_assetsUrl = Yii::app()->assetManager->publish(dirname(__FILE__) . '/assets', false, -1, MW_DEBUG);
    }

	/**
	 * Register scripts
	 * 
	 * @param CList $scripts
	 *
	 * @return CList
	 */
    public function _registerScripts(CList $scripts)
    {
	    $scripts->add(array('src' => $this->getAssetsUrl() . '/js/search.js'));
	    return $scripts;
    }

	/**
	 * Register styles
	 * 
	 * @param CList $styles
	 *
	 * @return CList
	 */
	public function _registerStyles(CList $styles)
	{
		$styles->add(array('src' => $this->getAssetsUrl() . '/css/search.css'));
		return $styles;
	}

	/**
	 * @param $controller
	 */
	public function _registerModalView($controller)
	{
		$controller->renderFile($this->getPathOfAlias('common.views.search-modal') . '.php');
	}

	/**
	 * @param $controller
	 */
	public function _registerSearchButton($controller)
	{
		$controller->renderFile($this->getPathOfAlias('common.views.search-button') . '.php');
	}
}
