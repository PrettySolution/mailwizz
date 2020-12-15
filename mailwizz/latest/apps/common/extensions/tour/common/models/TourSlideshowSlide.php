<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * TourSlideshowSlides
 *
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 */

/**
 * This is the model class for table "welcome_tour_slideshow_slides".
 *
 * The followings are the available columns in table 'welcome_tour_slideshow_slides':
 * @property integer $slide_id
 * @property integer $slideshow_id
 * @property string $title
 * @property string $content
 * @property string $image
 * @property integer $sort_order
 * @property string $status
 * @property string $date_added
 * @property string $last_updated
 *
 * The followings are the available model relations:
 * @property TourSlideshow $slideshow
 */
class TourSlideshowSlide extends ActiveRecord
{
    // upload field
    public $image_up;
    
    /**
     * @return string the associated database table name
     */
    public function tableName()
    {
        return '{{tour_slideshow_slide}}';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        $mimes = null;
        if (CommonHelper::functionExists('finfo_open')) {
            $mimes = Yii::app()->extensionMimes->get(array('png', 'jpg', 'jpeg', 'gif'))->toArray();
        }
        
        $rules = array(
            array('content, sort_order, status', 'required'),
            array('title', 'length', 'max' => 100),
            array('image', 'length', 'max' => 255),
            array('status', 'length', 'max' => 8),
            array('sort_order', 'numerical', 'integerOnly' => true),
            array('sort_order', 'in', 'range' => array_keys($this->getSortOrderList())),

            array('image_up', 'file', 'types' => array('png', 'jpg', 'jpeg', 'gif'), 'mimeTypes' => $mimes, 'allowEmpty' => true),
            array('image', '_validateImage'),
            
            // The following rule is used by search().
            array('title, description, status', 'safe', 'on'=>'search'),
        );
        return CMap::mergeArray($rules, parent::rules());
    }

    /**
     * @return array relational rules.
     */
    public function relations()
    {
        $relations = array(
            'slideshow' => array(self::BELONGS_TO, 'TourSlideshow', 'slideshow_id'),
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
            'slide_id'      => $ext->t('Slide'),
            'slideshow_id'  => $ext->t('Slideshow'),
            'title'         => $ext->t('Title'),
            'content'       => $ext->t('Content'),
            'image'         => $ext->t('Image'),
            'sort_order'    => $ext->t('Sort order'),
        );

        return CMap::mergeArray($labels, parent::attributeLabels());
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
        
        $criteria->compare('slideshow_id', (int)$this->slideshow_id);
        $criteria->compare('slide_id', $this->slide_id);
        $criteria->compare('title', $this->title, true);
        $criteria->compare('content', $this->content, true);
        $criteria->compare('status', $this->status);
        
        $criteria->order = 'sort_order ASC, slide_id ASC';
        
        return new CActiveDataProvider(get_class($this), array(
            'criteria'      => $criteria,
            'pagination'    => array(
                'pageSize'  => $this->paginationOptions->getPageSize(),
                'pageVar'   => 'page',
            ),
            'sort'  => array(
                'defaultOrder'  => array(
                    'sort_order' => CSort::SORT_ASC,
                    'slide_id'   => CSort::SORT_DESC,
                ),
            ),
        ));
    }

    /**
     * Returns the static model of the specified AR class.
     * Please note that you should have this exact method in all your CActiveRecord descendants!
     * @param string $className active record class name.
     * @return WelcomeTourSlideshowSlides the static model class
     */
    public static function model($className=__CLASS__)
    {
        return parent::model($className);
    }

    protected function afterValidate()
    {
        $this->handleUploadedImage('image_up', 'image');
        parent::afterValidate();
    }

    public function getExtensionInstance()
    {
        return Yii::app()->extensionsManager->getExtensionInstance('tour');
    }

    public function getDefaultImageUrl($width, $height)
    {
        return sprintf('https://via.placeholder.com/%dx%d?text=...', $width, $height);
    }
    
    public function getImageUrl($width = 50, $height = 50, $forceSize = false)
    {
        if (empty($this->image)) {
            return $this->getDefaultImageUrl($width, $height);
        }
        return ImageHelper::resize($this->image, $width, $height, $forceSize);
    }
    
    public function handleUploadedImage($attribute, $targetAttribute)
    {
        if ($this->hasErrors()) {
            return $this;
        }
        
        if (!($image = CUploadedFile::getInstance($this, $attribute))) {
            return $this;
        }
        
        $ext = $this->getExtensionInstance();
        $storagePath = Yii::getPathOfAlias('root.frontend.assets.files.tour');
        if (!file_exists($storagePath) || !is_dir($storagePath)) {
            if (!@mkdir($storagePath, 0777, true)) {
                $this->addError($attribute, $ext->t('The logos storage directory({path}) does not exists and cannot be created!', array(
                    '{path}' => $storagePath,
                )));
                return $this;
            }
        }

        $newName = uniqid(rand(0, time())) . '-' . $image->getName();
        if (!$image->saveAs($storagePath . '/' . $newName)) {
            $this->addError($attribute, $ext->t('Cannot move the image into the correct storage folder!'));
            return $this;
        }

        $this->$targetAttribute = '/frontend/assets/files/tour/' . $newName;
        return $this;
    }
    
    public function _validateImage($attribute, $params)
    {
        if ($this->hasErrors($attribute) || empty($this->$attribute)) {
            return;
        }
        
        $ext = $this->getExtensionInstance();
        $fullPath = Yii::getPathOfAlias('root') . $this->$attribute;

	    $extensionName = strtolower(pathinfo($fullPath, PATHINFO_EXTENSION));
	    if (!in_array($extensionName, array('jpg', 'jpeg', 'png', 'gif'))) {
		    $this->addError($attribute, $ext->t('Seems that "{attr}" is not a valid image!', array(
			    '{attr}' => $this->getAttributeLabel($attribute)
		    )));
		    return;
	    }
        
        if (strpos($this->$attribute, '/frontend/assets/files/tour/') !== 0 || !is_file($fullPath) || !($info = @getimagesize($fullPath))) {
            $this->addError($attribute, $ext->t('Seems that "{attr}" is not a valid image!', array(
                '{attr}' => $this->getAttributeLabel($attribute)
            )));
            return;
        }
    }
}