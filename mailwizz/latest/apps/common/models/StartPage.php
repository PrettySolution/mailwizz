<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * StartPage
 *
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.3.9.2
 */

/**
 * This is the model class for table "{{start_page}}".
 *
 * The followings are the available columns in table '{{start_page}}':
 * @property integer $page_id
 * @property string $application
 * @property string $route
 * @property string $icon
 * @property string $icon_color
 * @property string $heading
 * @property string $content
 * @property string $date_added
 * @property string $last_updated
 */
class StartPage extends ActiveRecord
{
    /**
     * @var string
     */
    public $search_icon = '';
    
    /**
     * @return string the associated database table name
     */
    public function tableName()
    {
        return '{{start_page}}';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        $rules = array(
            array('application, route', 'required'),
            array('application, route, icon, heading', 'length', 'max' => 255),
            array('application', 'in', 'range' => array_keys($this->getApplications())),
            array('application', '_validateApplication'),
            array('route', '_validateRoute'),
            array('icon', 'in', 'range' => $this->getIcons()),
            array('icon_color', 'length', 'is' => 6),
            array('icon_color', '_validateIconColor'),
            
            array('application, route, icon, heading, content', 'safe', 'on' => 'search'),
        );

        return CMap::mergeArray($rules, parent::rules());
    }

    /**
     * @return array relational rules.
     */
    public function relations()
    {
        $relations = array();
        return CMap::mergeArray($relations, parent::relations());
    }

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        $labels = array(
            'page_id'     => Yii::t('start_pages', 'Page'),
            'application' => Yii::t('start_pages', 'Application'),
            'route'       => Yii::t('start_pages', 'Route'),
            'icon'        => Yii::t('start_pages', 'Icon'),
            'heading'     => Yii::t('start_pages', 'Heading'),
            'content'     => Yii::t('start_pages', 'Content'),
        );

        return CMap::mergeArray($labels, parent::attributeLabels());
    }

    /**
     * @return array
     */
    public function attributeHelpTexts()
    {
        $texts = array(
            'application' => Yii::t('start_pages', 'The application where this page applies'),
            'route'       => Yii::t('start_pages', 'The url route (controller/action) where this page applies, i.e: campaigns/index'),
            'heading'     => Yii::t('start_pages', 'The heading of the page'),

            'search_icon' => Yii::t('start_pages', 'Start by typing a few characters from the icon name'),
        );

        return CMap::mergeArray($texts, parent::attributeHelpTexts());
    }

    /**
     * @return array
     */
    public function attributePlaceholders()
    {
        $placeholders = array(
            'search_icon' => Yii::t('start_pages', 'Search icon, i.e: envelope'),
            'route'       => 'campaigns/index',
        );

        return CMap::mergeArray($placeholders, parent::attributePlaceholders());
    }

    /**
     * Retrieves a list of models based on the current search/filter conditions.
     *
     * Typical usecase:
     * - Initialize the model fields with values from filter form.
     * - Execute this method to get CActiveDataProvider instance which will filter
     * models according to data in model fields.
     * - Pass data provider to CGridView, CListView or any similar widget.
     *
     * @return CActiveDataProvider the data provider that can return the models
     * based on the search/filter conditions.
     */
    public function search()
    {
        $criteria=new CDbCriteria;

        $criteria->compare('application', $this->application);
        $criteria->compare('route', $this->route, true);
        $criteria->compare('icon', $this->icon, true);
        $criteria->compare('heading', $this->heading, true);
        $criteria->compare('content', $this->content, true);
        
        return new CActiveDataProvider(get_class($this), array(
            'criteria'   => $criteria,
            'pagination' => array(
                'pageSize' => $this->paginationOptions->getPageSize(),
                'pageVar'  => 'page',
            ),
            'sort'=>array(
                'defaultOrder' => array(
                    'page_id'     => CSort::SORT_DESC,
                ),
            ),
        ));
    }

    /**
     * Returns the static model of the specified AR class.
     * Please note that you should have this exact method in all your CActiveRecord descendants!
     * @param string $className active record class name.
     * @return PageDefaultIndex the static model class
     */
    public static function model($className=__CLASS__)
    {
        return parent::model($className);
    }

    /**
     * @inheritdoc
     */
    protected function beforeSave()
    {
        $this->content = StringHelper::decodeSurroundingTags($this->content);
        return parent::beforeSave();
    }

    /**
     * @inheritdoc
     */
    protected function afterFind()
    {
        $this->content = StringHelper::decodeSurroundingTags($this->content);
        parent::afterFind();
    }
    
    /**
     * @return array
     */
    public function getApplications()
    {
        $webApps = Yii::app()->apps->getWebApps();
        $apps    = array();
        
        foreach ($webApps as $webApp) {
            $apps[$webApp] = Yii::t('start_pages', ucfirst($webApp));
        }
        
        return $apps;
    }

    /**
     * @return array
     */
    public function getIcons()
    {
        return $this->getGlyphiconIcons() + $this->getFontAwesomeIcons() + $this->getIonIcons();
    }

    /**
     * @return array
     */
    public function getFontAwesomeIcons()
    {
        $url     = 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.5.0/css/font-awesome.min.css';
        $pattern = '#\.(fa-([a-z0-9\-\_]+)):before#i';
        
        return $this->getIconsFromUrlByPattern($url, $pattern);
    }

    /**
     * @return array
     */
    public function getIonIcons()
    {
        $url     = 'https://cdnjs.cloudflare.com/ajax/libs/ionicons/2.0.1/css/ionicons.min.css';
        $pattern = '#\.(ion-([a-z0-9\-\_]+)):before#i';

        return $this->getIconsFromUrlByPattern($url, $pattern);
    }

    /**
     * @return array
     */
    public function getGlyphiconIcons()
    {
        $url     = Yii::app()->apps->getAppUrl('frontend', 'assets/css/bootstrap.min.css', true, true);
        $pattern = '#\.(glyphicon-([a-z0-9\-\_]+)):before#i';

        return $this->getIconsFromUrlByPattern($url, $pattern);
    }

    /**
     * @param $url
     * @param $pattern
     * @return array
     */
    public function getIconsFromUrlByPattern($url, $pattern)
    {
        $cacheKey = sha1(__METHOD__ . $url . $pattern);
        if (($icons = Yii::app()->cache->get($cacheKey)) !== false) {
            return $icons;
        }
        $icons = array();

        $request = AppInitHelper::simpleCurlGet($url);
        if (empty($request['message'])) {
            return $icons;
        }

        if (!preg_match_all($pattern, $request['message'], $matches)) {
            return $icons;
        }
        
        if (empty($matches[1])) {
            return $icons;
        }

        $icons = array_filter(array_unique($matches[1]));
        $icons = Yii::app()->ioFilter->stripClean($icons);
        $icons = array_map(array('CHtml', 'encode'), $icons);
        
        Yii::app()->cache->set($cacheKey, $icons);

        return $icons;
    }

    /**
     * @param $attribute
     * @param $params
     */
    public function _validateRoute($attribute, $params)
    {
        if ($this->hasErrors($attribute)) {
            return;
        }
        
        if (strpos($this->$attribute, '/') === false) {
            $this->addError($attribute, Yii::t('start_pages', 'The route does not seem to be valid!'));
            return;
        }

        $criteria = new CDbCriteria();
        $criteria->compare('application', (string)$this->application);
        $criteria->compare('route', (string)$this->route);
        $criteria->addCondition('page_id != :pid');
        $criteria->params[':pid'] = (int)$this->page_id;
        
        $exists = self::model()->find($criteria);
        
        if (!empty($exists)) {
            $this->addError($attribute, Yii::t('start_pages', 'The application/route combo is already taken!'));
            return;
        }
    }

    /**
     * @return array
     */
    public function getAvailableTags()
    {
        return array(
            '[CUSTOMER_BASE_URL]' => Yii::t('start_pages', 'Customer base url, useful for links generation.'),
            '[BACKEND_BASE_URL]'  => Yii::t('start_pages', 'Backend base url, useful for links generation.'),
            '[FRONTEND_BASE_URL]' => Yii::t('start_pages', 'Frontend base url, useful for links generation.'),
            '[API_BASE_URL]'      => Yii::t('start_pages', 'Frontend base url, useful for links generation.'),
        );
    }

    /**
     * @param $attribute
     * @param $params
     */
    public function _validateApplication($attribute, $params)
    {
        if ($this->hasErrors($attribute)) {
            return;
        }
        
        $criteria = new CDbCriteria();
        $criteria->compare('application', (string)$this->application);
        $criteria->compare('route', (string)$this->route);
        $criteria->addCondition('page_id != :pid');
        $criteria->params[':pid'] = (int)$this->page_id;

        $exists = self::model()->find($criteria);
        
        if (!empty($exists)) {
            $this->addError($attribute, Yii::t('start_pages', 'The application/route combo is already taken!'));
            return;
        }
    }

    /**
     * @param $attribute
     * @param $params
     */
    public function _validateIconColor($attribute, $params)
    {
        if ($this->hasErrors($attribute)) {
            return;
        }
        
        if (empty($this->$attribute)) {
            return;
        }
        
        if (!CommonHelper::functionExists('ctype_xdigit')) {
            return;
        }
        
        if (!ctype_xdigit((string)$this->$attribute)) {
            $this->addError($attribute, Yii::t('start_pages', 'Given color code does not seem to be a valid hex code!'));
        }
    }
}