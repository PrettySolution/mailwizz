<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * ListSubscriberListMove
 *
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.3.6.3
 */

/**
 * This is the model class for table "list_subscriber_list_move".
 *
 * The followings are the available columns in table 'list_subscriber_list_move':
 * @property integer $id
 * @property integer $source_subscriber_id
 * @property integer $source_list_id
 * @property integer $destination_subscriber_id
 * @property integer $destination_list_id
 * @property string $date_added
 * @property string $last_updated
 *
 * The followings are the available model relations:
 * @property ListSubscriber $subscriber
 * @property List $sourceList
 * @property ListSubscriber $sourceSubscriber
 * @property List $destinationList
 * @property ListSubscriber $destinationSubscriber
 */
class ListSubscriberListMove extends ActiveRecord
{
    /**
     * @return string the associated database table name
     */
    public function tableName()
    {
        return '{{list_subscriber_list_move}}';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        return array();
    }

    /**
     * @return array relational rules.
     */
    public function relations()
    {
        return array(
            'sourceSubscriber'      => array(self::BELONGS_TO, 'ListSubscriber', 'source_subscriber_id'),
            'sourceList'            => array(self::BELONGS_TO, 'Lists', 'source_list_id'),
            'destinationSubscriber' => array(self::BELONGS_TO, 'ListSubscriber', 'destination_subscriber_id'),
            'destinationList'       => array(self::BELONGS_TO, 'Lists', 'destination_list_id'),
        );
    }

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        return array();
    }
    
    /**
     * Returns the static model of the specified AR class.
     * Please note that you should have this exact method in all your CActiveRecord descendants!
     * @param string $className active record class name.
     * @return ListSubscriberListMove the static model class
     */
    public static function model($className=__CLASS__)
    {
        return parent::model($className);
    }
}