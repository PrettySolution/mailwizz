<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * OptionCustomization
 * 
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com> 
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.3.5.4
 */
 
class OptionCustomization extends OptionBase
{
    /**
     * @var string 
     */
    protected $_categoryName = 'system.customization';

    /**
     * @var string 
     */
    public $backend_logo_text = '';

    /**
     * @var string 
     */
    public $customer_logo_text = '';

    /**
     * @var string 
     */
    public $frontend_logo_text = '';

    /**
     * @var string 
     */
    public $backend_skin = 'blue';

    /**
     * @var string 
     */
    public $customer_skin = 'blue';

    /**
     * @var string 
     */
    public $frontend_skin = 'blue';

    /**
     * @var string
     */
    public $backend_logo;

    /**
     * @var string
     */
    public $backend_logo_up;

    /**
     * @var string
     */
    public $backend_login_bg;

    /**
     * @var string
     */
    public $backend_login_bg_up;
    
    /**
     * @var string
     */
    public $customer_logo;

    /**
     * @var string
     */
    public $customer_logo_up;

    /**
     * @var string
     */
    public $customer_login_bg;

    /**
     * @var string
     */
    public $customer_login_bg_up;

    /**
     * @var string
     */
    public $frontend_logo;

    /**
     * @var string
     */
    public $frontend_logo_up;

    /**
     * @return array
     */
    public function rules()
    {
        $mimes = null;
        if (CommonHelper::functionExists('finfo_open')) {
            $mimes = Yii::app()->extensionMimes->get(array('png', 'jpg', 'jpeg', 'gif'))->toArray();
        }
        
        $rules = array(
            array('backend_logo_up, customer_logo_up, frontend_logo_up', 'file', 'types' => array('png', 'jpg', 'jpeg', 'gif'), 'mimeTypes' => $mimes, 'allowEmpty' => true),
            array('backend_logo, customer_logo, frontend_logo', '_validateLogoFile'),

            array('backend_login_bg_up, customer_login_bg_up', 'file', 'types' => array('png', 'jpg', 'jpeg', 'gif'), 'mimeTypes' => $mimes, 'allowEmpty' => true),
            array('backend_login_bg, customer_login_bg', '_validateLoginBgFile'),
            
            array('backend_logo_text, customer_logo_text, frontend_logo_text', 'length', 'max' => 100),
            array('backend_skin, customer_skin, frontend_skin', 'length', 'max' => 100),
        );
        return CMap::mergeArray($rules, parent::rules());    
    }

    /**
     * @return array
     */
    public function attributeLabels()
    {
        $labels = array(
            'backend_logo_text'  => Yii::t('settings', 'Backend logo text'),
            'customer_logo_text' => Yii::t('settings', 'Customer logo text'),
            'frontend_logo_text' => Yii::t('settings', 'Frontend logo text'),
            'backend_skin'       => Yii::t('settings', 'Backend skin'),
            'customer_skin'      => Yii::t('settings', 'Customer skin'),
            'frontend_skin'      => Yii::t('settings', 'Frontend skin'),
            'backend_logo'       => Yii::t('settings', 'Backend logo'),
            'customer_logo'      => Yii::t('settings', 'Customer logo'),
            'frontend_logo'      => Yii::t('settings', 'Frontend logo'),
            'backend_login_bg'   => Yii::t('settings', 'Backend login background image'),
            'customer_login_bg'  => Yii::t('settings', 'Customer login background image'),
        );
        
        return CMap::mergeArray($labels, parent::attributeLabels());    
    }

    /**
     * @return array
     */
    public function attributePlaceholders()
    {
        $placeholders = array(
            'backend_logo_text'  => Yii::t('app', 'Backend area'),
            'customer_logo_text' => Yii::t('app', 'Customer area'),
            'frontend_logo_text' => Yii::t('app', 'Frontend area'),
        );
        return CMap::mergeArray($placeholders, parent::attributePlaceholders());
    }

    /**
     * @return array
     */
    public function attributeHelpTexts()
    {
        $texts = array(
            'backend_logo_text'  => Yii::t('settings', 'The text shown in backend area as the logo. Leave empty to use the defaults.'),
            'customer_logo_text' => Yii::t('settings', 'The text shown in customer area as the logo. Leave empty to use the defaults.'),
            'frontend_logo_text' => Yii::t('settings', 'The text shown in frontend as the logo. Leave empty to use the defaults.'),
            'backend_skin'       => Yii::t('settings', 'The CSS skin to be used in backend area.'),
            'customer_skin'      => Yii::t('settings', 'The CSS skin to be used in customer area.'),
            'frontend_skin'      => Yii::t('settings', 'The CSS skin to be used in frontend area.'),
        );
        
        return CMap::mergeArray($texts, parent::attributeHelpTexts());
    }

    /**
     * @inheritdoc
     */
    protected function afterValidate()
    {
        parent::afterValidate();
        
        $this
            ->handleUploadedLogo('backend_logo_up', 'backend_logo')
            ->handleUploadedLogo('customer_logo_up', 'customer_logo')
            ->handleUploadedLogo('frontend_logo_up', 'frontend_logo')

            ->handleUploadedLoginBg('backend_login_bg_up', 'backend_login_bg')
            ->handleUploadedLoginBg('customer_login_bg_up', 'customer_login_bg');
    }

    /**
     * @param array $options
     * @return string
     */
    public static function buildHeaderLogoHtml(array $options = array())
    {
        $apps     = Yii::app()->apps;
        $instance = new self;
        
        if (empty($options['linkUrl']) && $apps->isAppName('frontend')) {
            $options['linkUrl'] = $apps->getAppBaseUrl('frontend', true, true);
        }

        $options  = array_merge(array(
            'app'       => $apps->getCurrentAppName(),
            'linkUrl'   => Yii::app()->createUrl('dashboard/index'),
            'linkClass' => 'logo icon',
        ), $options);
        
        if ($url = $instance->getLogoUrlByApp($options['app'], 220, 50)) {
            $text = CHtml::image($url, '', array('width' => 220, 'height' => 50));
        } elseif ($_text = $instance->getLogoTextByApp($options['app'])) {
            $text = $_text;
        } else {
            $text = Yii::t('app', ucfirst($options['app']) . ' area');
        }
        
        return CHtml::link($text, $options['linkUrl'], array('class' => $options['linkClass']));
    }

    /**
     * @param $app
     * @param int $width
     * @param int $height
     * @return bool|mixed
     */
    public function getLogoUrlByApp($app, $width = 50, $height = 50)
    {
        $attribute = $app . '_logo';
        if (!isset($this->$attribute) || empty($this->$attribute)) {
            return false;
        }
        return ImageHelper::resize($this->$attribute, $width, $height);
    }

    /**
     * @param $app
     * @return bool|mixed
     */
    public function getLogoTextByApp($app)
    {
        $attribute = $app . '_logo_text';
        if (!isset($this->$attribute) || empty($this->$attribute)) {
            return false;
        }
        return $this->$attribute;
    }

    /**
     * @param int $width
     * @param int $height
     * @param bool $forceSize
     * @return mixed|string
     */
    public function getBackendLogoUrl($width = 50, $height = 50, $forceSize = false)
    {
        if (empty($this->backend_logo)) {
            return $this->getDefaultLogoUrl($width, $height);
        }
        return ImageHelper::resize($this->backend_logo, $width, $height, $forceSize);
    }

    /**
     * @param int $width
     * @param int $height
     * @param bool $forceSize
     * @return mixed|string
     */
    public function getBackendLoginBgUrl($width = 50, $height = 50, $forceSize = false)
    {
        if (empty($this->backend_login_bg)) {
            return $this->getDefaultLoginBgUrl($width, $height);
        }
        return ImageHelper::resize($this->backend_login_bg, $width, $height, $forceSize);
    }

    /**
     * @param int $width
     * @param int $height
     * @param bool $forceSize
     * @return mixed|string
     */
    public function getCustomerLogoUrl($width = 50, $height = 50, $forceSize = false)
    {
        if (empty($this->customer_logo)) {
            return $this->getDefaultLogoUrl($width, $height);
        }
        return ImageHelper::resize($this->customer_logo, $width, $height, $forceSize);
    }

    /**
     * @param int $width
     * @param int $height
     * @param bool $forceSize
     * @return mixed|string
     */
    public function getCustomerLoginBgUrl($width = 50, $height = 50, $forceSize = false)
    {
        if (empty($this->customer_login_bg)) {
            return $this->getDefaultLoginBgUrl($width, $height);
        }
        return ImageHelper::resize($this->customer_login_bg, $width, $height, $forceSize);
    }

    /**
     * @param int $width
     * @param int $height
     * @param bool $forceSize
     * @return mixed|string
     */
    public function getFrontendLogoUrl($width = 50, $height = 50, $forceSize = false)
    {
        if (empty($this->frontend_logo)) {
            return $this->getDefaultLogoUrl($width, $height);
        }
        return ImageHelper::resize($this->frontend_logo, $width, $height, $forceSize);
    }

    /**
     * @param $width
     * @param $height
     * @return string
     */
    public function getDefaultLogoUrl($width, $height)
    {
        return sprintf('https://via.placeholder.com/%dx%d?text=...', $width, $height);
    }

    /**
     * @param $width
     * @param $height
     * @return string
     */
    public function getDefaultLoginBgUrl($width, $height)
    {
        return ImageHelper::resize('/assets/img/login-background.jpeg', $width, $height);
    }

    /**
     * @param $appName
     * @return array
     */
    public function getAppSkins($appName)
    {
        $skins = array('');
        $paths = array('root.assets.css', 'root.'.$appName.'.assets.css');
        foreach ($paths as $path) {
            foreach ((array)glob(Yii::getPathOfAlias($path) . '/skin-*.css') as $file) {
                $fileName = basename($file, '.css');
                if (strpos($fileName, 'skin-') === 0) {
                    $skins[] = $fileName;
                }
            }    
        }
        
        $_skins = array_unique($skins);
        $skins  = array();
        foreach ($_skins as $skin) {
            $skinName = str_replace('skin-', '', $skin);
            $skinName = preg_replace('/[^a-z0-9]/i', ' ', $skinName);
            $skinName = ucwords($skinName);
            $skins[$skin] = str_replace(' Min', ' (Minified)', $skinName);
        }
        return $skins;
    }

    /**
     * @param $attribute
     * @param $targetAttribute
     * @return $this
     */
    protected function handleUploadedLogo($attribute, $targetAttribute)
    {
        if ($this->hasErrors()) {
            return $this;
        }

        if (!($logo = CUploadedFile::getInstance($this, $attribute))) {
            return $this;
        }

        $storagePath = Yii::getPathOfAlias('root.frontend.assets.files.logos');
        if (!file_exists($storagePath) || !is_dir($storagePath)) {
            if (!@mkdir($storagePath, 0777, true)) {
                $this->addError($attribute, Yii::t('settings', 'The logos storage directory({path}) does not exists and cannot be created!', array(
                    '{path}' => $storagePath,
                )));
                return $this;
            }
        }

        $newAvatarName = uniqid(rand(0, time())) . '-' . $logo->getName();
        if (!$logo->saveAs($storagePath . '/' . $newAvatarName)) {
            $this->addError($attribute, Yii::t('customers', 'Cannot move the logo into the correct storage folder!'));
            return $this;
        }

        $this->$targetAttribute = '/frontend/assets/files/logos/' . $newAvatarName;
        return $this;
    }

    /**
     * @param $attribute
     * @param $targetAttribute
     * @return $this
     */
    protected function handleUploadedLoginBg($attribute, $targetAttribute)
    {
        if ($this->hasErrors()) {
            return $this;
        }

        if (!($logo = CUploadedFile::getInstance($this, $attribute))) {
            return $this;
        }

        $storagePath = Yii::getPathOfAlias('root.frontend.assets.files.login-bg');
        if (!file_exists($storagePath) || !is_dir($storagePath)) {
            if (!@mkdir($storagePath, 0777, true)) {
                $this->addError($attribute, Yii::t('settings', 'The logos storage directory({path}) does not exists and cannot be created!', array(
                    '{path}' => $storagePath,
                )));
                return $this;
            }
        }

        $newAvatarName = uniqid(rand(0, time())) . '-' . $logo->getName();
        if (!$logo->saveAs($storagePath . '/' . $newAvatarName)) {
            $this->addError($attribute, Yii::t('customers', 'Cannot move the logo into the correct storage folder!'));
            return $this;
        }

        $this->$targetAttribute = '/frontend/assets/files/login-bg/' . $newAvatarName;
        return $this;
    }

    /**
     * @param $attribute
     * @param $params
     */
    public function _validateLogoFile($attribute, $params)
    {
        if ($this->hasErrors($attribute) || empty($this->$attribute)) {
            return;
        }
        
        $fullPath = Yii::getPathOfAlias('root') . $this->$attribute;
        $extensionName = strtolower(pathinfo($fullPath, PATHINFO_EXTENSION));
        if (!in_array($extensionName, array('jpg', 'jpeg', 'png', 'gif'))) {
	        $this->addError($attribute, Yii::t('settings', 'Seems that "{attr}" is not a valid image!', array(
		        '{attr}' => $this->getAttributeLabel($attribute)
	        )));
	        return;
        }
        
        if (strpos($this->$attribute, '/frontend/assets/files/logos/') !== 0 || !is_file($fullPath) || !($info = @getimagesize($fullPath))) {
            $this->addError($attribute, Yii::t('settings', 'Seems that "{attr}" is not a valid image!', array(
                '{attr}' => $this->getAttributeLabel($attribute)
            )));
            return;
        }
    }

    /**
     * @param $attribute
     * @param $params
     */
    public function _validateLoginBgFile($attribute, $params)
    {
        if ($this->hasErrors($attribute) || empty($this->$attribute)) {
            return;
        }
        
        $fullPath = Yii::getPathOfAlias('root') . $this->$attribute;
	    
        $extensionName = strtolower(pathinfo($fullPath, PATHINFO_EXTENSION));
	    if (!in_array($extensionName, array('jpg', 'jpeg', 'png', 'gif'))) {
		    $this->addError($attribute, Yii::t('settings', 'Seems that "{attr}" is not a valid image!', array(
			    '{attr}' => $this->getAttributeLabel($attribute)
		    )));
		    return;
	    }
	    
        if (strpos($this->$attribute, '/frontend/assets/files/login-bg/') !== 0 || !is_file($fullPath) || !($info = @getimagesize($fullPath))) {
            $this->addError($attribute, Yii::t('settings', 'Seems that "{attr}" is not a valid image!', array(
                '{attr}' => $this->getAttributeLabel($attribute)
            )));
            return;
        }
    }

}
