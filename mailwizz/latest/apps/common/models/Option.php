<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * Option
 * 
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com> 
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.0
 */
 
/**
 * This is the model class for table "option".
 *
 * The followings are the available columns in table 'option':
 * @property string $category
 * @property string $key
 * @property string $value
 * @property integer $is_serialized
 * @property string $date_added
 * @property string $last_updated
 */
class Option extends ActiveRecord
{
    private static $_index = 0;
    
    /**
     * @return string the associated database table name
     */
    public function tableName()
    {
        return '{{option}}';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        $rules = array(
            array('category, key, value', 'required'),
            array('is_serialized', 'numerical', 'integerOnly'=>true),
            array('category, key', 'length', 'max'=>100),
        );
        
        return CMap::mergeArray($rules, parent::rules());
    }

    /**
     * @return array relational rules.
     */
    public function relations()
    {
        // NOTE: you may need to adjust the relation name and the related
        // class name for the relations automatically generated below.
        $relations = array();
        
        return CMap::mergeArray($relations, parent::relations());
    }

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        $labels = array(
            'category'      => Yii::t('options', 'Category'),
            'key'           => Yii::t('options', 'Key'),
            'value'         => Yii::t('options', 'Value'),
            'is_serialized' => Yii::t('options', 'Is serialized'),
        );
        
        return CMap::mergeArray($labels, parent::attributeLabels());
    }

    /**
     * Returns the static model of the specified AR class.
     * Please note that you should have this exact method in all your CActiveRecord descendants!
     * @param string $className active record class name.
     * @return Option the static model class
     */
    public static function model($className=__CLASS__)
    {
        return parent::model($className);
    }
    
    public function getIndex()
    {
        return self::$_index++;
    }
}
