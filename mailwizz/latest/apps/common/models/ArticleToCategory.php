<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * ArticleToCategory
 * 
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com> 
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.0
 */
 
/**
 * This is the model class for table "article_to_category".
 *
 * The followings are the available columns in table 'article_to_category':
 * @property integer $article_id
 * @property integer $category_id
 */
class ArticleToCategory extends ActiveRecord
{
    /**
     * @return string the associated database table name
     */
    public function tableName()
    {
        return '{{article_to_category}}';
    }
    
    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        $rules = array(
            array('article_id', 'exist', 'className' => 'Article'),
            array('category_id', 'exist', 'className' => 'ArticleCategory'),
        );
        
        return CMap::mergeArray($rules, parent::rules());
    }
    
    /**
     * @return array relational rules.
     */
    public function relations()
    {
        $relations = array(
            'categories' => array(self::HAS_MANY, 'ArticleCategory', 'category_id'),
            'articles' => array(self::HAS_MANY, 'Article', 'article_id'),
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
            'category_id'   => Yii::t('articles', 'Category'),
        );
        
        return CMap::mergeArray($labels, parent::attributeLabels());
    }

    /**
     * Returns the static model of the specified AR class.
     * Please note that you should have this exact method in all your CActiveRecord descendants!
     * @param string $className active record class name.
     * @return ArticleToCategory the static model class
     */
    public static function model($className=__CLASS__)
    {
        return parent::model($className);
    }
}
