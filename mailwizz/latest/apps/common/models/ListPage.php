<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * ListPage
 * 
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com> 
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.0
 */
 
/**
 * This is the model class for table "list_page".
 *
 * The followings are the available columns in table 'list_page':
 * @property integer $list_id
 * @property integer $type_id
 * @property string $content
 * @property string $meta_data
 * @property string $date_added
 * @property string $last_updated
 *
 * The followings are the available model relations:
 * @property ListPageType $type
 * @property Lists $list
 */
class ListPage extends ActiveRecord
{
    public function primaryKey()
    {
        return array('list_id', 'type_id');
    }
    
    /**
     * @return string the associated database table name
     */
    public function tableName()
    {
        return '{{list_page}}';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        $rules = array(
            array('content', 'safe'),
            
            // meta data
            array('email_subject', 'length', 'max' => 500),
        );
        
        return CMap::mergeArray($rules, parent::rules());
    }
    
    /**
     * @return array available behaviors.
     */
    public function behaviors()
    {
        $behaviors = array(
            'tags' => array(
                'class' => 'common.components.db.behaviors.PageTypeTagsBehavior'
            ),
            'emailSubject' => array(
                'class' => 'common.components.db.behaviors.PageTypeEmailSubjectBehavior'
            ),
        );
        
        return CMap::mergeArray($behaviors, parent::behaviors());
    }

    /**
     * @return array relational rules.
     */
    public function relations()
    {
        $relations = array(
            'type' => array(self::BELONGS_TO, 'ListPageType', 'type_id'),
            'list' => array(self::BELONGS_TO, 'Lists', 'list_id'),
        );
        
        return CMap::mergeArray($relations, parent::relations());
    }

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        $labels = array(
            'list_id'     => Yii::t('list_pages', 'List'),
            'type_id'     => Yii::t('list_pages', 'Type'),
            'content'     => Yii::t('list_pages', 'Content'),

            // meta data
            'email_subject' => Yii::t('list_pages', 'Email subject'),
        );
        
        return CMap::mergeArray($labels, parent::attributeLabels());
    }

    /**
     * Returns the static model of the specified AR class.
     * Please note that you should have this exact method in all your CActiveRecord descendants!
     * @param string $className active record class name.
     * @return ListDisplay the static model class
     */
    public static function model($className=__CLASS__)
    {
        return parent::model($className);
    }

    protected function beforeDelete()
    {
        return false;
    }
    
    protected function beforeSave()
    {
        $this->content = StringHelper::decodeSurroundingTags($this->content);
        return parent::beforeSave();
    }
}
