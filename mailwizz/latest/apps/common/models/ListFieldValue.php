<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * ListFieldValue
 * 
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com> 
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.0
 */
 
/**
 * This is the model class for table "list_field_value".
 *
 * The followings are the available columns in table 'list_field_value':
 * @property integer $value_id
 * @property integer $field_id
 * @property integer $subscriber_id
 * @property string $value
 * @property string $date_added
 * @property string $last_updated
 *
 * The followings are the available model relations:
 * @property ListField $field
 * @property ListSubscriber $subscriber
 */
class ListFieldValue extends ActiveRecord
{
    /**
     * @return string the associated database table name
     */
    public function tableName()
    {
        return '{{list_field_value}}';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        $rules = array(
            array('value', 'length', 'max'=>255),
        );
        
        return CMap::mergeArray($rules, parent::rules());
    }

    /**
     * @return array relational rules.
     */
    public function relations()
    {
        $relations = array(
            'field'      => array(self::BELONGS_TO, 'ListField', 'field_id'),
            'subscriber' => array(self::BELONGS_TO, 'ListSubscriber', 'subscriber_id'),
        );
        
        return CMap::mergeArray($relations, parent::relations());
    }

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        $labels = array(
            'value_id'      => Yii::t('list_fields', 'Value'),
            'field_id'      => Yii::t('list_fields', 'Field'),
            'subscriber_id' => Yii::t('list_fields', 'Subscriber'),
            'value'         => Yii::t('list_fields', 'Value')
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
        $criteria = new CDbCriteria;

        return new CActiveDataProvider(get_class($this), array(
            'criteria'      => $criteria,
            'pagination'    => array(
                'pageSize'  => $this->paginationOptions->getPageSize(),
                'pageVar'   => 'page',
            ),
            'sort'  => array(
                'defaultOrder'  => array(
                    'value_id'  => CSort::SORT_DESC,
                ),
            ),
        ));
    }

    /**
     * Returns the static model of the specified AR class.
     * Please note that you should have this exact method in all your CActiveRecord descendants!
     * @param string $className active record class name.
     * @return ListFieldValue the static model class
     */
    public static function model($className=__CLASS__)
    {
        return parent::model($className);
    }
    
    public function attributeHelpTexts()
    {
        $texts = array(
            'value' => null
        );

        return CMap::mergeArray($texts, parent::attributeHelpTexts());
    }
    
    protected function afterSave()
    {
        parent::afterSave();
        
        // since 1.3.6.2 - this forces cache refresh
        if (MW_PERF_LVL && MW_PERF_LVL & MW_PERF_LVL_ENABLE_SUBSCRIBER_FIELD_CACHE) {
            $this->subscriber->getAllCustomFieldsWithValues(true);
        }
    }
}
