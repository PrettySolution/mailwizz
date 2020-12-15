<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * EmailTemplateBuilderExt
 *
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 */

class EmailTemplateBuilderExt extends ExtensionInit
{
    // name of the extension as shown in the backend panel
    public $name = 'Email Template Builder';

    // description of the extension as shown in backend panel
    public $description = 'Drag and Drop Email Template Builder For MailWizz EMA';

    // current version of this extension
    public $version = '1.0';

    // minimum app version
    public $minAppVersion = '1.4.4';
    
    // the author name
    public $author = 'Cristian Serban';

    // author website
    public $website = 'https://www.mailwizz.com/';

    // contact email address
    public $email = 'cristian.serban@mailwizz.com';

    // in which apps this extension is not allowed to run
    public $allowedApps = array('backend', 'customer');

    // can this extension be deleted? this only applies to core extensions.
    protected $_canBeDeleted = false;

    // can this extension be disabled? this only applies to core extensions.
    protected $_canBeDisabled = true;

    // the detected language
    protected $detectedLanguage = 'en';

    // READ-ONLY
    private $_assetsAlias = 'root.frontend.assets.cache.ext-email-template-builder';

    // READ-ONLY
    private $_assetsRelativeUrl = '/frontend/assets/cache/ext-email-template-builder';

    // READ-ONLY
    private $_assetsUrl;

    public function run()
    {
        /**
         * This extension depends on ckeditor so we need to make sure it is enabled.
         */
        if (!($ckeditor = $this->getManager()->getExtensionInstance('ckeditor'))) {
            return;
        }

        /**
         * Make sure we enable the file manager
         */
        if (($this->isAppName('customer') || $this->isAppName('backend')) && !$ckeditor->getIsFilemanagerEnabled()) {
            $ckeditor->setOption('enable_filemanager_user', 1);
            $ckeditor->setOption('enable_filemanager_customer', 1);
        }
        
        // reference
        $hooks = Yii::app()->hooks;

        /**
         * Register the assets just after ckeditor is done.
         */
        $hooks->addAction('wysiwyg_editor_instance', array($this, '_createNewEditorInstance'), 99);

        /**
         * Customer area only
         */
        if ($this->isAppName('customer')) {

            /**
             * handle the builder for customer area, in the templates controller
             */
            $hooks->addAction('customer_controller_templates_before_action', array($this, '_customerControllerTemplatesBeforeAction'));

            /**
             * handle the builder for customer area, in the campaigns controller
             */
            $hooks->addAction('customer_controller_campaigns_before_action', array($this, '_customerControllerCampaignsBeforeAction'));

            /**
             * CKEditor controller
             */
            $hooks->addAction('customer_controller_ext_ckeditor_before_action', array($this, '_controllerExtCkeditorBeforeAction'));
        }

        /**
         * Backend area only
         */
        if ($this->isAppName('backend')) {

            /**
             * handle the builder for backend area, in the email templates gallery controller
             */
            $hooks->addAction('backend_controller_email_templates_gallery_before_action', array($this, '_backendControllerEmailTemplatesGalleryBeforeAction'));

            /**
             * CKEditor controller
             */
            $hooks->addAction('backend_controller_ext_ckeditor_before_action', array($this, '_controllerExtCkeditorBeforeAction'));
        }
    }

    /**
     * @return bool
     */
    public function beforeEnable()
    {
        // publish the assets
        $this->publishAssets();
        return true;
    }

    /**
     * @return bool
     */
    public function beforeDisable()
    {
        // unpublish the assets
        $this->unpublishAssets();
        return true;
    }

    /**
     * @param array $editorOptions
     */
    public function _createNewEditorInstance(array $editorOptions = array())
    {
        $this->registerAssets();
    }

    /**
     * @param CAction $action
     */
    public function _customerControllerTemplatesBeforeAction(CAction $action)
    {
        if (!in_array($action->id, array('create', 'update'))) {
            return;
        }

        // reference
        $hooks = Yii::app()->hooks;
        
        // add the button
        $hooks->addAction('before_wysiwyg_editor_right_side', array($this, '_beforeWysiwygEditorRightSide'));

        // add the code to handle the editor
        $hooks->addAction('after_wysiwyg_editor', array($this, '_afterWysiwygEditor'));

        // add the code to save the editor data
        $hooks->addAction('controller_action_save_data', array($this, '_controllerActionSaveData'));
    }

    /**
     * @param CAction $action
     */
    public function _customerControllerCampaignsBeforeAction(CAction $action)
    {
        if (!in_array($action->id, array('template'))) {
            return;
        }

        // reference
        $hooks = Yii::app()->hooks;

        // add the button
        $hooks->addAction('before_wysiwyg_editor_right_side', array($this, '_beforeWysiwygEditorRightSide'));

        // add the code to handle the editor
        $hooks->addAction('after_wysiwyg_editor', array($this, '_afterWysiwygEditor'));

        // add the code to save the editor data
        $hooks->addAction('controller_action_save_data', array($this, '_controllerActionSaveData'));
    }
    
    /**
     * @param CAction $action
     */
    public function _backendControllerEmailTemplatesGalleryBeforeAction(CAction $action)
    {
        if (!in_array($action->id, array('create', 'update'))) {
            return;
        }

        // reference
        $hooks = Yii::app()->hooks;

        // add the button
        $hooks->addAction('before_wysiwyg_editor_right_side', array($this, '_beforeWysiwygEditorRightSide'));

        // add the code to handle the editor
        $hooks->addAction('after_wysiwyg_editor', array($this, '_afterWysiwygEditor'));

        // add the code to save the editor data
        $hooks->addAction('controller_action_save_data', array($this, '_controllerActionSaveData'));
    }

    /**
     * @param $action
     */
    public function _controllerExtCkeditorBeforeAction($action)
    {
        if ($action->id != 'filemanager') {
            return;
        }
        
        // reference
        $hooks = Yii::app()->hooks;

        // add image handling code for file manager
        $hooks->addAction('ext_ckeditor_elfinder_filemanager_view_html_head', array($this, '_extCkeditorElfinderFilemanagerViewHtmlHead'));
    }

    /**
     * Add the button to toggle the editor
     * 
     * @param array $params
     */
    public function _beforeWysiwygEditorRightSide(array $params = array())
    {
        $options = array(
            'class' => 'btn btn-flat btn-primary', 
            'title' => $this->t('Toggle template builder'),
            'id'    => 'btn_' . $params['template']->modelName . '_content',
        );
        
        echo CHtml::link($this->t('Toggle template builder'), 'javascript:;', $options);
    }

    /**
     * The view after ckeditor
     * 
     * @param array $params
     */
    public function _afterWysiwygEditor(array $params = array())
    {
        $model = null;
        if (!empty($params['template'])) {
            $model = $params['template'];
        }
        
        if (empty($model) || !is_object($model) || !($model instanceof ActiveRecord)) {
            return;
        }
        
        if (!$model->asa('modelMetaData') || !method_exists($model->modelMetaData, 'getModelMetaData')) {
            return;
        }
        
        $modelName = $model->modelName;
        $builderId = $modelName . '_content';
        $ckeditor  = $this->getManager()->getExtensionInstance('ckeditor');
        $options   = array(
            'rootId'        => 'builder_' . $builderId,
            'lang'          => $this->detectedLanguage,
            'mediaBaseUrl'  => $this->getAssetsUrl() . '/static/media/',
            'ckeditor'      => array(
                'scriptUrl' => $ckeditor->getAssetsUrl() . '/ckeditor/ckeditor.js',
                'config'    => array(
                    'toolbar' => 'Emailbuilder'
                ),
            ),
        );

        if ($ckeditor->getIsFilemanagerEnabled()) {
            $options['managerUrl'] = $ckeditor->getFilemanagerUrl();
            $options['ckeditor']['config']['filebrowserBrowseUrl'] = $ckeditor->getFilemanagerUrl();
        }
        
        $json = array();
        if (($contentJson = $model->getModelMetaData()->itemAt('content_json'))) {
            if ($contentJson = CJSON::decode(base64_decode($contentJson))) {
                $json = $contentJson;
                unset($contentJson);
            }
        }
        
        if (isset(Yii::app()->params['POST'][$modelName]['content_json'])) {
            if ($contentJson = CJSON::decode(Yii::app()->params['POST'][$modelName]['content_json'])) {
                $json = $contentJson;
                unset($contentJson);
            }
        }
        
        Yii::app()->getController()->renderInternal(dirname(__FILE__) . '/views/after-editor.php', array(
            'json'      => $json,
            'options'   => $options,
            'modelName' => $modelName,
            'builderId' => $builderId,
        ));
    }

    /**
     * Save the editor data
     *
     * @param $collection
     */
    public function _controllerActionSaveData($collection)
    {
        if (!$collection->success) {
            return;
        }

        $contentJson = base64_encode('{}');

        if (isset(Yii::app()->params['POST'][$collection->template->modelName]['content_json'])) {
            $contentJson = Yii::app()->params['POST'][$collection->template->modelName]['content_json'];
            $contentJson = base64_encode(CJSON::encode(CJSON::decode($contentJson)));
        }

        $collection->template->setModelMetaData('content_json', $contentJson)->saveModelMetaData();
    }
    
    /**
     * Render the javascript code for elfinder
     */
    public function _extCkeditorElfinderFilemanagerViewHtmlHead()
    {
        $script = file_get_contents(dirname(__FILE__) . '/assets/static/js/code-elfinder.js');
        echo sprintf("<script>\n%s\n</script>", $script);
    }

    /**
     * @return $this
     */
    public function registerAssets()
    {
        static $_assetsRegistered = false;
        if ($_assetsRegistered) {
            return $this;
        }
        $_assetsRegistered = true;
        
        $assetsUrl = $this->getAssetsUrl();
        
        // find the language file, if any.
        $language     = str_replace('_', '-', Yii::app()->language);
        $languageFile = null;

        if (is_file(dirname(__FILE__) . '/assets/languages/' . $language . '.js')) {
            $languageFile = $language.'.js';
        }

        if ($languageFile === null && strpos($language, '-') !== false) {
            $language = explode('-', $language);
            $language = $language[0];
            if (is_file(dirname(__FILE__) . '/assets/languages/' . $language . '.js')) {
                $languageFile = $language . '.js';
            }
        }

        // if language found, register it.
        if ($languageFile !== null) {
            $this->detectedLanguage = $language;
            Yii::app()->clientScript->registerScriptFile($assetsUrl . '/languages/' . $languageFile);
        }
        
        // register the rest of css/js
        Yii::app()->clientScript->registerCssFile($assetsUrl . '/static/css/main.e0fa54fc.css');
        Yii::app()->clientScript->registerCssFile($assetsUrl . '/static/css/code-editor.css');
        Yii::app()->clientScript->registerScriptFile($assetsUrl . '/static/js/main.56fb5026.js');
        Yii::app()->clientScript->registerScriptFile($assetsUrl . '/static/js/code-editor.js');

        return $this;
    }

    /**
     * @return string
     */
    public function getAssetsAlias()
    {
        return $this->_assetsAlias;
    }

    /**
     * @return mixed
     */
    public function getAssetsRelativeUrl()
    {
        return Yii::app()->apps->getAppUrl('frontend', $this->_assetsRelativeUrl, false, true);
    }

    /**
     * @return mixed
     */
    public function getAssetsAbsoluteUrl()
    {
        return Yii::app()->apps->getAppUrl('frontend', $this->_assetsRelativeUrl, true, true);
    }

    /**
     * @return mixed
     */
    public function getAssetsUrl()
    {
        if ($this->_assetsUrl !== null) {
            return $this->_assetsUrl;
        }
        
        return $this->publishAssets();
    }

    /**
     * @return mixed
     */
    public function publishAssets()
    {
        $src = dirname(__FILE__) . '/assets/';
        $dst = Yii::getPathOfAlias($this->getAssetsAlias());

        if (is_dir($dst) && !MW_DEBUG) {
            return $this->_assetsUrl = $this->getAssetsAbsoluteUrl();
        }

        CFileHelper::copyDirectory($src, $dst, array('newDirMode' => 0777));
        return $this->_assetsUrl = $this->getAssetsAbsoluteUrl();
    }

    /**
     * Unpublish assets
     */
    public function unpublishAssets()
    {
        $dst = Yii::getPathOfAlias($this->getAssetsAlias());
        if (is_dir($dst)) {
            CFileHelper::removeDirectory($dst);
        }
    }
}
