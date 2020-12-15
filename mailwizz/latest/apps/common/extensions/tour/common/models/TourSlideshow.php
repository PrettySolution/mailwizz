<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * TourSlideshow
 *
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 */

/**
 * This is the model class for table "welcome_tour_slideshow".
 *
 * The followings are the available columns in table 'welcome_tour_slideshow':
 * @property integer $slideshow_id
 * @property string $name
 * @property string $application
 * @property string $status
 * @property string $date_added
 * @property string $last_updated
 *
 * The followings are the available model relations:
 * @property TourSlideshowSlide[] $slides
 * @property TourSlideshowSlide[] $slidesCount
 */
class TourSlideshow extends ActiveRecord
{
    const APPLICATION_BACKEND = 'backend';
    
    const APPLICATION_CUSTOMER = 'customer';
    
    /**
     * @return string the associated database table name
     */
    public function tableName()
    {
        return '{{tour_slideshow}}';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        $rules = array(
            array('name, application, status', 'required'),
            array('name', 'length', 'max' => 100),
            array('application', 'length', 'max' => 45),
            array('application', 'in', 'range' => array_keys($this->getApplicationsList())),
            array('status', 'length', 'max' => 8),
            array('status', 'in', 'range' => array_keys($this->getStatusesList())),
            
            // The following rule is used by search().
            array('name, application, status', 'safe', 'on'=>'search'),
        );

        return CMap::mergeArray($rules, parent::rules());
    }

    /**
     * @return array relational rules.
     */
    public function relations()
    {
        $relations = array(
            'slides'      => array(self::HAS_MANY, 'TourSlideshowSlide', 'slideshow_id'),
            'slidesCount' => array(self::STAT, 'TourSlideshowSlide', 'slideshow_id'),
        );
        return CMap::mergeArray($relations, parent::relations());
    }

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        $ext    = $this->getExtensionInstance();
        $labels = array(
            'slideshow_id'  => $ext->t('Slideshow'),
            'name'          => $ext->t('Name'),
            'application'   => $ext->t('Application'),
            
            'slidesCount'   => $ext->t('Slides count'),
        );
        return CMap::mergeArray($labels, parent::attributeLabels());
    }

    /**
     * @return array
     */
    public function attributeHelpTexts()
    {
        $ext   = $this->getExtensionInstance();
        $texts = array(
            'name'        => $ext->t('The name of the slideshow, for internal reference only'),
            'application' => $ext->t('The application where this slideshow will be shown'),
        );

        return CMap::mergeArray($texts, parent::attributeHelpTexts());
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

        $criteria->compare('name', $this->name, true);
        $criteria->compare('application', $this->application, true);
        $criteria->compare('status', $this->status);
        
        $criteria->order = 'slideshow_id DESC';
        
        return new CActiveDataProvider(get_class($this), array(
            'criteria'      => $criteria,
            'pagination'    => array(
                'pageSize'  => $this->paginationOptions->getPageSize(),
                'pageVar'   => 'page',
            ),
            'sort'  => array(
                'defaultOrder'  => array(
                    'slideshow_id' => CSort::SORT_DESC,
                ),
            ),
        ));
    }

    /**
     * Returns the static model of the specified AR class.
     * Please note that you should have this exact method in all your CActiveRecord descendants!
     * @param string $className active record class name.
     * @return WelcomeTourSlideshow the static model class
     */
    public static function model($className=__CLASS__)
    {
        return parent::model($className);
    }

    public function getExtensionInstance()
    {
        return Yii::app()->extensionsManager->getExtensionInstance('tour');
    }
    
    public function getApplicationsList()
    {
        return array(
            self::APPLICATION_BACKEND  => $this->getExtensionInstance()->t('Backend'),
            self::APPLICATION_CUSTOMER => $this->getExtensionInstance()->t('Customer'),
        );
    }
}