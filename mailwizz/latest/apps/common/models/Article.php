<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * Article
 * 
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com> 
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.0
 */
 
/**
 * This is the model class for table "article".
 *
 * The followings are the available columns in table 'article':
 * @property integer $article_id
 * @property string $title
 * @property string $slug
 * @property string $content
 * @property string $status
 * @property string $date_added
 * @property string $last_updated
 *
 * The followings are the available model relations:
 * @property ArticleCategory[] $categories
 * @property ArticleCategory[] $activeCategories
 */
class Article extends ActiveRecord
{
    const STATUS_PUBLISHED = 'published';
    
    const STATUS_UNPUBLISHED = 'unpublished';

    /**
     * @return string the associated database table name
     */
    public function tableName()
    {
        return '{{article}}';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        $rules = array(
            array('title, slug, content, status', 'required'),
            array('title', 'length', 'max' => 200),
            array('slug', 'length', 'max' => 255),
            array('status', 'in', 'range' => array(self::STATUS_PUBLISHED, self::STATUS_UNPUBLISHED)),
            
            // The following rule is used by search().
            array('title, status', 'safe', 'on' => 'search'),
        );
        
        return CMap::mergeArray($rules, parent::rules());
    }

    /**
     * @return array relational rules.
     */
    public function relations()
    {
        $relations = array(
            'categories' => array(self::MANY_MANY, 'ArticleCategory', '{{article_to_category}}(article_id, category_id)'),
            'activeCategories' => array(self::MANY_MANY, 'ArticleCategory', '{{article_to_category}}(article_id, category_id)', 'condition' => 'activeCategories.status = :st', 'params' => array(':st' => ArticleCategory::STATUS_ACTIVE)),
        );
        
        return CMap::mergeArray($relations, parent::relations());
    }

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        $labels = array(
            'article_id'    => Yii::t('articles', 'Article'),
            'title'         => Yii::t('articles', 'Title'),
            'slug'          => Yii::t('articles', 'Slug'),
            'content'       => Yii::t('articles', 'Content'),
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

        $criteria->compare('title', $this->title, true);
        $criteria->compare('status', $this->status, true);

        return new CActiveDataProvider(get_class($this), array(
            'criteria'      => $criteria,
            'pagination'    => array(
                'pageSize'  => $this->paginationOptions->getPageSize(),
                'pageVar'   => 'page',
            ),
            'sort'  => array(
                'defaultOrder' => array(
                    'article_id' => CSort::SORT_DESC,
                ),
            ),
        ));
    }

    /**
     * Returns the static model of the specified AR class.
     * Please note that you should have this exact method in all your CActiveRecord descendants!
     * @param string $className active record class name.
     * @return Article the static model class
     */
    public static function model($className=__CLASS__)
    {
        return parent::model($className);
    }
    
    protected function afterConstruct()
    {
        $this->fieldDecorator->onHtmlOptionsSetup = array($this, '_setDefaultEditorForContent');
        parent::afterConstruct();
    }
    
    protected function afterFind()
    {
        $this->fieldDecorator->onHtmlOptionsSetup = array($this, '_setDefaultEditorForContent');
        parent::afterFind();
    }

    protected function beforeValidate()
    {
        $category = new ArticleCategory();
        $category->slug = $this->slug;
        $this->slug = $category->generateSlug();
        $this->slug = $this->generateSlug();

        return parent::beforeValidate();
    }
    
    public function generateSlug()
    {
        Yii::import('common.vendors.Urlify.*');
        $string = !empty($this->slug) ? $this->slug : $this->title;
        $slug = URLify::filter($string);
        $article_id = (int)$this->article_id;

        $criteria = new CDbCriteria();
        $criteria->addCondition('article_id != :id AND slug = :slug');
        $criteria->params = array(':id' => $article_id, ':slug' => $slug);
        $exists = self::model()->find($criteria);
        
        $i = 0;
        while (!empty($exists)) {
            ++$i;
            $slug = preg_replace('/^(.*)(\d+)$/six', '$1', $slug);
            $slug = URLify::filter($slug . ' '. $i);
            $criteria = new CDbCriteria();
            $criteria->addCondition('article_id != :id AND slug = :slug');
            $criteria->params = array(':id' => $article_id, ':slug' => $slug);
            $exists = self::model()->find($criteria);
        }

        return $slug;
    }
    
    public function _setDefaultEditorForContent(CEvent $event)
    {
        if ($event->params['attribute'] == 'content') {
            $options = array();
            if ($event->params['htmlOptions']->contains('wysiwyg_editor_options')) {
                $options = (array)$event->params['htmlOptions']->itemAt('wysiwyg_editor_options');
            }
            $options['id'] = CHtml::activeId($this, 'content');
            $event->params['htmlOptions']->add('wysiwyg_editor_options', $options);
        }
    }
    
    public function getSelectedCategoriesArray()
    {
        $selectedCategories = array();
        if (!$this->isNewRecord) {
            $categories = ArticleToCategory::model()->findAllByAttributes(array('article_id' => (int)$this->article_id));
            foreach ($categories as $category) {
                $selectedCategories[] = $category->category_id;
            }
        }
        return $selectedCategories;    
    }
    
    public function getAvailableCategoriesArray()
    {
        $category = new ArticleCategory();
        return $category->getRelationalCategoriesArray();
    }
    
    public function getPermalink($absolute = false)
    {
        return Yii::app()->apps->getAppUrl('frontend', 'article/' . $this->slug, $absolute);
    }
    
    public function getStatusesArray()
    {
        return array(
            ''                          => Yii::t('app', 'Choose'),
            self::STATUS_PUBLISHED      => Yii::t('articles', 'Published'),
            self::STATUS_UNPUBLISHED    => Yii::t('articles', 'Unpublished'),
        );
    }
    
    public function getStatusText()
    {
        $statuses = $this->getStatusesArray();
        return isset($statuses[$this->status]) ? $statuses[$this->status] : $this->status;
    }
    
    public function attributeHelpTexts()
    {
        $texts = array();
        return CMap::mergeArray($texts, parent::attributeHelpTexts());
    }
    
    public function attributePlaceholders()
    {
        $placeholders = array(
            'title' => Yii::t('articles', 'My article title'),
            'slug'  => Yii::t('articles', 'my-article-title'),
        );
        
        return CMap::mergeArray($placeholders, parent::attributePlaceholders());
    }
    
    public function getExcerpt($length = 200) 
    {
        return StringHelper::truncateLength($this->content, $length);
    }
}
