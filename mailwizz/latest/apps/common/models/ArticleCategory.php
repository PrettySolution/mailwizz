<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * ArticleCategory
 * 
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com> 
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.0
 */
 
/**
 * This is the model class for table "article_category".
 *
 * The followings are the available columns in table 'article_category':
 * @property integer $category_id
 * @property integer $parent_id
 * @property string $name
 * @property string $slug
 * @property string $description
 * @property string $status
 * @property string $date_added
 * @property string $last_updated
 *
 * The followings are the available model relations:
 * @property ArticleCategory $parent
 * @property ArticleCategory[] $categories
 * @property Article[] $articles
 */
class ArticleCategory extends ActiveRecord
{
    /**
     * @return string the associated database table name
     */
    public function tableName()
    {
        return '{{article_category}}';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        $rules = array(
            array('name, slug, status', 'required'),
            array('parent_id', 'numerical', 'integerOnly' => true),
            array('parent_id', 'exist', 'attributeName' => 'category_id'),
            array('name', 'length', 'max' => 200),
            array('slug', 'length', 'max' => 250),
            array('slug', 'unique'),
            array('status', 'in', 'range' => array(self::STATUS_ACTIVE, self::STATUS_INACTIVE)),
            array('description', 'safe'),
            
            // The following rule is used by search().
            array('name, status', 'safe', 'on' => 'search'),
        );
        
        return CMap::mergeArray($rules, parent::rules());
    }

    /**
     * @return array relational rules.
     */
    public function relations()
    {
        $relations = array(
            'parent' => array(self::BELONGS_TO, 'ArticleCategory', 'parent_id'),
            'categories' => array(self::HAS_MANY, 'ArticleCategory', 'parent_id'),
            'articles' => array(self::MANY_MANY, 'Article', '{{article_to_category}}(category_id, article_id)'),
        );
        
        return CMap::mergeArray($relations, parent::relations());
    }

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        $labels = array(
            'category_id'   => Yii::t('articles', 'Category'),
            'parent_id'     => Yii::t('articles', 'Parent'),
            'name'          => Yii::t('articles', 'Name'),
            'slug'          => Yii::t('articles', 'Slug'),
            'description'   => Yii::t('articles', 'Description'),
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
        $criteria->compare('name', $this->name, true);
        $criteria->compare('status', $this->status, true);

        return new CActiveDataProvider(get_class($this), array(
            'criteria'      => $criteria,
            'pagination'    => array(
                'pageSize'  => $this->paginationOptions->getPageSize(),
                'pageVar'   => 'page',
            ),
            'sort'  => array(
                'defaultOrder'  => array(
                    'category_id'   => CSort::SORT_ASC,
                ),
            ),
        ));
    }

    /**
     * Returns the static model of the specified AR class.
     * Please note that you should have this exact method in all your CActiveRecord descendants!
     * @param string $className active record class name.
     * @return ArticleCategory the static model class
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
        $article = new Article();
        $article->slug = $this->slug;
        $this->slug = $article->generateSlug();
        $this->slug = $this->generateSlug();

        return parent::beforeValidate();
    }
    
    public function generateSlug()
    {
        Yii::import('common.vendors.Urlify.*');
        $string = !empty($this->slug) ? $this->slug : $this->name;
        $slug = URLify::filter($string);
        $category_id = (int)$this->category_id;
        
        $criteria = new CDbCriteria();
        $criteria->addCondition('category_id != :id AND slug = :slug');
        $criteria->params = array(':id' => $category_id, ':slug' => $slug);
        $exists = self::model()->find($criteria);
        
        $i = 0;
        while (!empty($exists)) {
            ++$i;
            $slug = preg_replace('/^(.*)(\-\d+)$/six', '$1', $slug);
            $slug = URLify::filter($slug . ' '. $i);
            $criteria = new CDbCriteria();
            $criteria->addCondition('category_id != :id AND slug = :slug');
            $criteria->params = array(':id' => $category_id, ':slug' => $slug);
            $exists = self::model()->find($criteria);
        }
        
        return $slug;
    }
    
    public function _setDefaultEditorForContent(CEvent $event)
    {
        if ($event->params['attribute'] == 'description') {
            $options = array();
            if ($event->params['htmlOptions']->contains('wysiwyg_editor_options')) {
                $options = (array)$event->params['htmlOptions']->itemAt('wysiwyg_editor_options');
            }
            $options['id'] = CHtml::activeId($this, 'description');
            $options['height'] = 100;
            $options['toolbar']= 'Simple';
            $event->params['htmlOptions']->add('wysiwyg_editor_options', $options);
        }
    }
    
    public function getRelationalCategoriesArray($parentId = null, $separator = ' -> ')
    {
        $criteria = new CDbCriteria();
        $criteria->select = 'category_id, parent_id, name';
        if (empty($parentId)) {
            $criteria->condition = 'parent_id IS NULL';
        } else {
            $criteria->compare('parent_id', (int)$parentId);
        }
        $criteria->addCondition('slug IS NOT NULL');
        $criteria->order = 'slug ASC';
        
        $categories = array();
        $results = self::model()->findAll($criteria);
        foreach ($results as $result) {
            // dont allow selecting a child as a parent
            if (!empty($this->category_id) && $this->category_id == $result->category_id) {
                continue;
            }
            $categories[$result->category_id] = $result->name;
            $children = $this->getRelationalCategoriesArray($result->category_id);
            foreach ($children as $childId => $childName) {
                $categories[$childId] = $result->name . $separator . $childName;
            }
        }
        return $categories;
    }
    
    public function getParentNameTrail($separator = ' -> ')
    {
        $nameTrail = array($this->name);
        
        if (!empty($this->parent_id)) {
            $criteria = new CDbCriteria();
            $criteria->select = 'category_id, parent_id, name';
            $criteria->compare('category_id', (int)$this->parent_id);
            $parent = self::model()->find($criteria);
            
            if (!empty($parent)) {
                $nameTrail[] = $parent->getParentNameTrail();
            }    
        }
        
        $nameTrail = array_reverse($nameTrail);
        return implode($separator, $nameTrail);
    }
    
    public function getPermalink($absolute = false)
    {
        return Yii::app()->apps->getAppUrl('frontend', 'articles/' . $this->slug, $absolute);
    }
    
    public function getStatusesArray()
    {
        return array(
            ''                      => Yii::t('app', 'Choose'),
            self::STATUS_ACTIVE     => Yii::t('app', 'Active'),
            self::STATUS_INACTIVE   => Yii::t('app', 'Inactive'),
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
            'name'  => Yii::t('articles', 'My category name'),
            'slug'  => Yii::t('articles', 'my-category-name'),
        );
        
        return CMap::mergeArray($placeholders, parent::attributePlaceholders());
    }
}
