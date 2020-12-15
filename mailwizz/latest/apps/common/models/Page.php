<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * Page
 * 
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com> 
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.5.5
 */
 
/**
 * This is the model class for table "page".
 *
 * The followings are the available columns in table 'page':
 * @property integer $page_id
 * @property string $title
 * @property string $slug
 * @property string $content
 * @property string $status
 * @property string $date_added
 * @property string $last_updated
 *
 */
class Page extends ActiveRecord
{
    /**
     * @return string the associated database table name
     */
    public function tableName()
    {
        return '{{page}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        $rules = array(
            array('title, slug, content, status', 'required'),
            array('title', 'length', 'max' => 200),
            array('slug', 'length', 'max' => 255),
            array('slug', 'unique'),
            array('status', 'in', 'range' => array_keys($this->getStatusesList())),
            
            // The following rule is used by search().
            array('title, status', 'safe', 'on' => 'search'),
        );
        
        return CMap::mergeArray($rules, parent::rules());
    }

    /**
     * @inheritdoc
     */
    public function relations()
    {
        $relations = array();
        return CMap::mergeArray($relations, parent::relations());
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        $labels = array(
            'page_id'    => Yii::t('pages', 'Page'),
            'title'      => Yii::t('pages', 'Title'),
            'slug'       => Yii::t('pages', 'Slug'),
            'content'    => Yii::t('pages', 'Content'),
        );
        
        return CMap::mergeArray($labels, parent::attributeLabels());
    }

    /**
     * @inheritdoc
     */
    public function attributeHelpTexts()
    {
        $texts = array();
        return CMap::mergeArray($texts, parent::attributeHelpTexts());
    }

    /**
     * @inheritdoc
     */
    public function attributePlaceholders()
    {
        $placeholders = array(
            'title' => Yii::t('pages', 'My page title'),
            'slug'  => Yii::t('pages', 'my-page-title'),
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
        $criteria = new CDbCriteria;

        $criteria->compare('title', $this->title, true);
        $criteria->compare('status', $this->status);

        return new CActiveDataProvider(get_class($this), array(
            'criteria'      => $criteria,
            'pagination'    => array(
                'pageSize'  => $this->paginationOptions->getPageSize(),
                'pageVar'   => 'page',
            ),
            'sort'  => array(
                'defaultOrder' => array(
                    'page_id' => CSort::SORT_DESC,
                ),
            ),
        ));
    }

    /**
     * Returns the static model of the specified AR class.
     * Please note that you should have this exact method in all your CActiveRecord descendants!
     * @param string $className active record class name.
     * @return Page the static model class
     */
    public static function model($className=__CLASS__)
    {
        return parent::model($className);
    }

    /**
     * @inheritdoc
     */
    protected function beforeValidate()
    {
        $this->slug = $this->generateSlug();

        return parent::beforeValidate();
    }

    /**
     * @return string
     * @throws CException
     */
    public function generateSlug()
    {
        Yii::import('common.vendors.Urlify.*');
        $string  = !empty($this->slug) ? $this->slug : $this->title;
        $slug    = URLify::filter($string);
        $page_id = (int)$this->page_id;

        $criteria = new CDbCriteria();
        $criteria->addCondition('page_id != :id AND slug = :slug');
        $criteria->params = array(':id' => $page_id, ':slug' => $slug);
        $exists = self::model()->find($criteria);
        
        $i = 0;
        while (!empty($exists)) {
            ++$i;
            $slug = preg_replace('/^(.*)(\d+)$/six', '$1', $slug);
            $slug = URLify::filter($slug . ' '. $i);
            $criteria = new CDbCriteria();
            $criteria->addCondition('page_id != :id AND slug = :slug');
            $criteria->params = array(':id' => $page_id, ':slug' => $slug);
            $exists = self::model()->find($criteria);
        }

        return $slug;
    }

    /**
     * @param bool $absolute
     * @return mixed
     */
    public function getPermalink($absolute = false)
    {
        return Yii::app()->apps->getAppUrl('frontend', 'page/' . $this->slug, $absolute);
    }

    /**
     * @param int $length
     * @return string
     */
    public function getExcerpt($length = 200) 
    {
        return StringHelper::truncateLength($this->content, $length);
    }

    /**
     * @return bool
     */
    public function getIsActive()
    {
        return $this->status == self::STATUS_ACTIVE;
    }

    /**
     * @param $slug
     * @return static
     */
    public static function findBySlug($slug)
    {
        return self::model()->findByAttributes(array('slug' => $slug, 'status' => self::STATUS_ACTIVE));
    }
}
