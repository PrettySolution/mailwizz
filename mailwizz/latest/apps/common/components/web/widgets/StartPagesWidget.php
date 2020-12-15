<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * StartPagesWidget
 *
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.3.9.2
 */

class StartPagesWidget extends CWidget
{
    /**
     * @var CAttributeCollection
     */
    public $collection;

    /**
     * @var bool
     */
    public $enabled = false;
    
    /**
     * @var string
     */
    public $application = '';

    /**
     * @var string
     */
    public $route = '';

    /**
     * @var StartPage 
     */
    public $page;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        Yii::app()->clientScript->registerCssFile(Yii::app()->apps->getBaseUrl('assets/css/start-page.css'));
    }
    
    /**
     * @inheritdoc
     */
    public function run()
    {
        if (!$this->enabled || !$this->collection->renderGrid) {
            return;
        }
        
        if (!$this->application) {
            $this->application = Yii::app()->apps->getCurrentAppName();
        }
        
        if (!$this->route) {
            $this->route = Yii::app()->getController()->getRoute();
        }
        
        if (!$this->page || !($this->page instanceof StartPage)) {
            $this->page = StartPage::model()->findByAttributes(array(
                'application' => $this->application,
                'route'       => $this->route,
            ));
        }
        
        if (empty($this->page)) {
            return;
        }
        
        $this->collection->renderGrid = false;
        
        $searchReplace = array(
            '[CUSTOMER_BASE_URL]'   => Yii::app()->apps->getAppBaseUrl('customer'),
            '[BACKEND_BASE_URL]'    => Yii::app()->apps->getAppBaseUrl('backend'),
            '[FRONTEND_BASE_URL]'   => Yii::app()->apps->getAppBaseUrl('frontend'),
            '[API_BASE_URL]'        => Yii::app()->apps->getAppBaseUrl('api'),
        );
        
        $page        = $this->page;
        $pageContent = $page->content;
        $pageHeading = $page->heading;
        $pageContent = str_replace(array_keys($searchReplace), array_values($searchReplace), $pageContent);
        $pageHeading = str_replace(array_keys($searchReplace), array_values($searchReplace), $pageHeading);
        
        $this->render('start-page', array(
            'page'        => $page,
            'pageContent' => $pageContent,
            'pageHeading' => $pageHeading,
        ));
    }
}