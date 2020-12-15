<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * ListSubscriberFieldCache
 *
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.3.6.2
 */

/**
 * This is the model class for table "list_subscriber_field_cache".
 *
 * The followings are the available columns in table 'list_subscriber_field_cache':
 * @property integer $subscriber_id
 * @property string $data
 *
 * The followings are the available model relations:
 * @property ListSubscriber $subscriber
 */
class ListSubscriberFieldCache extends ActiveRecord
{
    /**
     * @return string the associated database table name
     */
    public function tableName()
    {
        return '{{list_subscriber_field_cache}}';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        $rules = array();
        return CMap::mergeArray($rules, parent::rules());
    }

    /**
     * @return array relational rules.
     */
    public function relations()
    {
        $relations = array(
            'subscriber' => array(self::BELONGS_TO, 'ListSubscriber', 'subscriber_id'),
        );

        return CMap::mergeArray($relations, parent::relations());
    }

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        $labels = array();
        return CMap::mergeArray($labels, parent::attributeLabels());
    }

    /**
     * Returns the static model of the specified AR class.
     * Please note that you should have this exact method in all your CActiveRecord descendants!
     * @param string $className active record class name.
     * @return ListSubscriberFieldCache the static model class
     */
    public static function model($className=__CLASS__)
    {
        return parent::model($className);
    }
    
    protected function beforeSave()
    {
        $this->data = json_encode($this->data);
        return parent::beforeSave();
    }

    protected function afterSave()
    {
        $this->data = json_decode($this->data, true);
        if (!is_array($this->data)) {
            $this->data = array();
        }
        parent::afterSave();
    }

    protected function afterFind()
    {
        $this->data = json_decode($this->data, true);
        if (!is_array($this->data)) {
            $this->data = array();
        }
        parent::afterFind();
    }
}
