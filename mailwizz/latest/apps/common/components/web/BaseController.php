<?php defined('MW_PATH') || exit('No direct script access allowed'); 

/**
 * BaseController
 * 
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com> 
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.0
 */
 
class BaseController extends CController
{
    /**
     * @var CMap the data to be passed from view to view
     */
    private $_data;
    
    public function init()
    {
        parent::init();
        
        $hooks      = Yii::app()->hooks;
        $options    = Yii::app()->options;
        
        // data passed in each view.
        $this->setData(array(
            'pageMetaTitle'         => $options->get('system.common.site_name'),
            'pageMetaDescription'   => $options->get('system.common.site_description'),
            'pageMetaKeywords'      => $options->get('system.common.site_keywords'),
            'pageHeading'           => '',
            'pageBreadcrumbs'       => array(),
            'hooks'                 => $hooks,
        ));
        
        $appName = Yii::app()->apps->getCurrentAppName();
        $hooks->doAction($appName . '_controller_init');
        $hooks->doAction($appName . '_controller_'.$this->id.'_init');
        
        $this->onControllerInit(new CEvent($this));
    }
    
    public function onControllerInit(CEvent $event)
    {
        $this->raiseEvent('onControllerInit', $event);
    }
    
    public function actions()
    {
        $actions = new CMap();
        
        $appName = Yii::app()->apps->getCurrentAppName();
        $actions = Yii::app()->hooks->applyFilters($appName . '_controller_'.$this->id.'_actions', $actions);
        
        $this->onActions(new CEvent($this, array(
            'actions' => $actions,
        )));

        return $actions->toArray();
    }
    
    public function onActions(CEvent $event)
    {
        $this->raiseEvent('onActions', $event);
    }
    
    /**
     * List of behaviors usable in the controller actions
     */
    public function behaviors()
    {
        $behaviors = new CMap();
        
        $appName = Yii::app()->apps->getCurrentAppName();
        $behaviors = Yii::app()->hooks->applyFilters($appName . '_controller_behaviors', $behaviors);
        $behaviors = Yii::app()->hooks->applyFilters($appName . '_controller_'.$this->id.'_behaviors', $behaviors);
        
        $this->onBehaviors(new CEvent($this, array(
            'behaviors' => $behaviors,
        )));
  
        return $behaviors->toArray();
    }
    
    public function onBehaviors(CEvent $event)
    {
        $this->raiseEvent('onBehaviors', $event);
    }
    
    /**
     * List of filters usable in the controller
     */
    public function filters()
    {
        $filters = new CMap();
        
        $appName = Yii::app()->apps->getCurrentAppName();
        $filters = Yii::app()->hooks->applyFilters($appName . '_controller_filters', $filters);
        $filters = Yii::app()->hooks->applyFilters($appName . '_controller_'.$this->id.'_filters', $filters);
        
        $this->onFilters(new CEvent($this, array(
            'filters' => $filters,
        )));

        return $filters->toArray();
    }
    
    public function onFilters(CEvent $event)
    {
        $this->raiseEvent('onFilters', $event);
    }
    
    /**
     * This method is invoked right before an action is to be executed (after all possible filters.)
     * You may override this method to do last-minute preparation for the action.
     * @param CAction $action the action to be executed.
     * @return boolean whether the action should be executed.
     */
    protected function beforeAction($action)
    {
        $appName = Yii::app()->apps->getCurrentAppName();
        Yii::app()->hooks->doAction($appName . '_controller_before_action', $action);
        Yii::app()->hooks->doAction($appName . '_controller_'.$this->id.'_before_action', $action);
        
        $this->onBeforeAction(new CEvent($this, array(
            'action' => $action
        )));
        
        // 1.3.7.3
        if (!headers_sent()) {
            header('X-XSS-Protection: 1; mode=block');
        }
        
        return parent::beforeAction($action);
    }
    
    public function onBeforeAction(CEvent $event)
    {
        $this->raiseEvent('onBeforeAction', $event);
    }
    
    /**
     * This method is invoked right after an action is executed.
     * You may override this method to do some postprocessing for the action.
     * @param CAction $action the action just executed.
     */
    protected function afterAction($action)
    {
        $appName = Yii::app()->apps->getCurrentAppName();
        Yii::app()->hooks->doAction($appName . '_controller_after_action', $action);
        Yii::app()->hooks->doAction($appName . '_controller_'.$this->id.'_after_action', $action);
        
        $this->onAfterAction(new CEvent($this, array(
            'action' => $action
        )));
        
        parent::afterAction($action);
    }
    
    public function onAfterAction(CEvent $event)
    {
        $this->raiseEvent('onAfterAction', $event);
    }
    
    /**
     * This method is invoked at the beginning of {@link render()}.
     * You may override this method to do some preprocessing when rendering a view.
     * @param string $view the view to be rendered
     * @return boolean whether the view should be rendered.
     * @since 1.1.5
     */
    protected function beforeRender($view)
    {
        if (Yii::app()->request->enableCsrfValidation) {
            Yii::app()->clientScript->registerMetaTag(Yii::app()->request->csrfTokenName, 'csrf-token-name');
            Yii::app()->clientScript->registerMetaTag(Yii::app()->request->csrfToken, 'csrf-token-value');
        }
        
        $hooks = Yii::app()->hooks;
        
        $appName = Yii::app()->apps->getCurrentAppName();
        $hooks->doAction($appName . '_controller_before_render', $view);
        $hooks->doAction($appName . '_controller_'.$this->id.'_before_render', $view);
        
        $this->onBeforeRender(new CEvent($this, array(
            'view' => $view
        )));
        
        // register assets
        $this->_registerAssets();
                
        return parent::beforeRender($view);
    }
    
    public function onBeforeRender(CEvent $event)
    {
        $this->raiseEvent('onBeforeRender', $event);
    }
    
    /**
     * This method is invoked after the specified is rendered by calling {@link render()}.
     * Note that this method is invoked BEFORE {@link processOutput()}.
     * You may override this method to do some postprocessing for the view rendering.
     * @param string $view the view that has been rendered
     * @param string $output the rendering result of the view. Note that this parameter is passed
     * as a reference. That means you can modify it within this method.
     * @since 1.1.5
     */
    protected function afterRender($view, &$output)
    {    
        $appName = Yii::app()->apps->getCurrentAppName();
        $output = Yii::app()->hooks->applyFilters($appName . '_controller_after_render', $output, $view);
        $output = Yii::app()->hooks->applyFilters($appName . '_controller_'.$this->id.'_after_render', $output, $view);
        
        $this->onAfterRender(new CEvent($this, array(
            'view'      => $view,
            'output'    => &$output,
        )));
        
        parent::afterRender($view, $output);
    }
    
    public function onAfterRender(CEvent $event)
    {
        $this->raiseEvent('onAfterRender', $event);
    }
    
    /**
     * Overrides the parent implementation.
     * 
     * Renders a view file.
     * This method includes the view file as a PHP script
     * and captures the display result if required.
     * @param string $_viewFile_ view file
     * @param array $_data_ data to be extracted and made available to the view file
     * @param boolean $_return_ whether the rendering result should be returned as a string
     * @return string the rendering result. Null if the rendering result is not required.
     */
    public function renderInternal($_viewFile_, $_data_=null, $_return_=false)
    {
        if ($_data_ === null) {
            $_data_ = array();
        }
        
        $this->getData()->mergeWith($_data_, false);
        $_data_ = $this->getData()->toArray();
        
        return parent::renderInternal($_viewFile_, $_data_, $_return_);
    }
    
    /**
     * Render JSON instead of HTML
     * 
     * @param array $data the data to be JSON encoded
     * @param int $statusCode the status code
     * @param array $headers list of headers to send in the response
     * @param string $callback the callback for the jsonp calls
     * @return BaseController
     */
    public function renderJson($data = array(), $statusCode = 200, array $headers = array(), $callback = null)
    {
        $response = new JsonResponse();

        $response
            ->setHeaders($headers)
            ->setStatusCode($statusCode)
            ->setData($data)
            ->setCallback($callback)
            ->send();
        
        Yii::app()->end();
    }
    
    /**
     * Set data available in all views and sub views.
     * 
     */
    final public function setData($key, $value = null) 
    {
        if (!is_array($key) && $value !== null) {
            $this->getData()->mergeWith(array($key => $value), false);
        } elseif (is_array($key)) {
            $this->getData()->mergeWith($key, false);
        }
        return $this;
    }
    
    /**
     * Get data available in all views and sub views.
     * 
     * @return CAttributeCollection
     */
    protected $_pageAssetsCListInitialized = false;
    final public function getData($key = null, $defaultValue = null)
    {
        if (!($this->_data instanceof CAttributeCollection)) {
            $this->_data = new CAttributeCollection($this->_data);
            $this->_data->caseSensitive=true;
        }
        
        // special case when clist is not initialized for the keys
        if (!$this->_pageAssetsCListInitialized) {
            $cList = array('pageScripts', 'pageStyles', 'bodyClasses');
            foreach ($cList as $name) {
                if ((!$this->_data->contains($name) || !($this->_data->itemAt($name) instanceof CList))) {
                    $this->_data->add($name, new CList());
                }
            }
            $this->_pageAssetsCListInitialized = true;
        }

        if ($key !== null) {
            return $this->_data->contains($key) ? $this->_data->itemAt($key) : $defaultValue;
        }
        
        return $this->_data;
    }
    
    /**
     * Register the assets.
     *
     */
    protected function _registerAssets()
    {
        $hooks = Yii::app()->hooks;
        
        // enqueue all custom scripts and styles registered so far
        $this->getData('pageScripts')->mergeWith($hooks->applyFilters('register_scripts', new CList()));
        $this->getData('pageStyles')->mergeWith($hooks->applyFilters('register_styles', new CList()));
        
        // register jquery
        $this->getData('pageScripts')->insertAt(0, array('src' => 'jquery', 'core-script' => true));

        // register scripts
        $pageScripts  = array();
        $sort         = array();
        $_pageScripts = $this->getData('pageScripts')->toArray();
        
        foreach ($_pageScripts as $index => $item) {
            if (empty($item['src'])) {
                $this->getData('pageScripts')->removeAt($index);
                continue;
            }
            $priority       = !empty($item['priority']) ? (int)$item['priority'] : 0;
            $sort[]         = $priority + $index;
            $pageScripts[]  = $item;
        }
        array_multisort($sort, $pageScripts);

        foreach ($pageScripts as $item) {
            $htmlOptions = !empty($item['htmlOptions']) ? (array)$item['htmlOptions'] : array();
            $position    = isset($item['position']) ? (int)$item['position'] : null;
            if (!empty($item['core-script'])) {
                Yii::app()->clientScript->registerCoreScript($item['src']);
            } else {
                
                $version = substr(sha1(MW_VERSION), -8);
                $src     = $item['src'];
                $src    .= strpos($src, '?') !== false ? '&av=' . $version : '?av=' . $version;
                
                Yii::app()->clientScript->registerScriptFile($src, $position, $htmlOptions);
            }
        }
        
        // register styles
        $pageStyles   = array();
        $sort         = array();
        $_pageStyles  = $this->getData('pageStyles')->toArray();
        
        foreach ($_pageStyles as $index => $item) {
            if (empty($item['src'])) {
                $this->getData('pageStyles')->removeAt($index);
                continue;
            }
            $priority      = !empty($item['priority']) ? (int)$item['priority'] : 0;
            $sort[]        = $priority + $index;
            $pageStyles[]  = $item;
        }
        array_multisort($sort, $pageStyles);
        
        foreach ($pageStyles as $item) {
            $media = isset($item['media']) ? $item['media'] : null;
            
            $version = substr(sha1(MW_VERSION), -8);
            $src     = $item['src'];
            $src    .= strpos($src, '?') !== false ? '&av=' . $version : '?av=' . $version;
            
            Yii::app()->clientScript->registerCssFile($src, $media);
        }
    }
    
    // since 1.3.5.4 - body classes filter hook
    public function getBodyClasses()
    {
        $bodyClasses = $this->getData('bodyClasses', array())->toArray();
        $bodyClasses = array_merge($bodyClasses, array('ctrl-' . $this->id, 'act-' . $this->action->id));
        $bodyClasses = (array)Yii::app()->hooks->applyFilters('body_classes', $bodyClasses);
        $bodyClasses = implode(' ', array_map('trim', array_unique($bodyClasses)));
        return $bodyClasses;
    }
    
    // since 1.3.5.6
    public function getAfterOpeningBodyTag()
    {
        Yii::app()->hooks->addAction('after_opening_body_tag', array($this, '_gaTrackingCode'), 1000);
        Yii::app()->hooks->doAction('after_opening_body_tag', $this);
    }
    
    // since 1.3.5.6
    public function _gaTrackingCode($controller)
    {
        $trackingCode = Yii::app()->options->get('system.common.ga_tracking_code_id');
        if (empty($trackingCode)) {
            return;
        }
        echo sprintf("<script>
          (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
          (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
          m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
          })(window,document,'script','//www.google-analytics.com/analytics.js','ga');
          ga('create', '%s', 'auto');
          ga('send', 'pageview');
        </script>", $trackingCode);
    }
    
    // since 1.3.5.7
    public function getHtmlOrientation()
    {
        // $orientation = Yii::app()->locale->orientation;
        $orientation = defined('MW_HTML_ORIENTATION') ? MW_HTML_ORIENTATION : 'ltr';
        return Yii::app()->hooks->applyFilters('html_orientation', $orientation);
    }
}