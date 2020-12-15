<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * ListCustomerNotification
 * 
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com> 
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.0
 */
 
/**
 * This is the model class for table "list_customer_notification".
 *
 * The followings are the available columns in table 'list_customer_notification':
 * @property integer $list_id
 * @property string $daily
 * @property string $subscribe
 * @property string $unsubscribe
 * @property string $daily_to
 * @property string $subscribe_to
 * @property string $unsubscribe_to
 *
 * The followings are the available model relations:
 * @property Lists $list
 */
class ListCustomerNotification extends ActiveRecord
{
    /**
     * @return string the associated database table name
     */
    public function tableName()
    {
        return '{{list_customer_notification}}';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        $rules = array(
            array('daily, subscribe, unsubscribe', 'required'),
            array('daily, subscribe, unsubscribe', 'in', 'range'=>array(self::TEXT_YES, self::TEXT_NO)),
            array('daily_to, subscribe_to, unsubscribe_to', 'length', 'max'=>255),
        );
        
        return CMap::mergeArray($rules, parent::rules());
    }

    /**
     * @return array relational rules.
     */
    public function relations()
    {
        $relations = array(
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
            'list_id'           => Yii::t('lists', 'List'),
            'daily'             => Yii::t('lists', 'Daily'),
            'subscribe'         => Yii::t('lists', 'Subscribe'),
            'unsubscribe'       => Yii::t('lists', 'Unsubscribe'),
            'daily_to'          => Yii::t('lists', 'Daily To'),
            'subscribe_to'      => Yii::t('lists', 'Subscribe To'),
            'unsubscribe_to'    => Yii::t('lists', 'Unsubscribe To'),
        );
        
        return CMap::mergeArray($labels, parent::attributeLabels());
    }

    /**
     * Returns the static model of the specified AR class.
     * Please note that you should have this exact method in all your CActiveRecord descendants!
     * @param string $className active record class name.
     * @return ListCustomerNotification the static model class
     */
    public static function model($className=__CLASS__)
    {
        return parent::model($className);
    }
    
    public function getYesNoDropdownOptions()
    {
        return array(
            ""              => Yii::t('app', 'Choose'),
            self::TEXT_YES  => Yii::t('app', 'Yes'),
            self::TEXT_NO   => Yii::t('app', 'No'),
        );
    }
    
    public function attributeHelpTexts()
    {
        $texts = array(
            'subscribe'         => Yii::t('lists', 'Whether to send notifications when a new subscriber will join the list.'),
            'unsubscribe'       => Yii::t('lists', 'Whether to send notifications when a new subscriber will leave the list.'),
            'subscribe_to'      => Yii::t('lists', 'Where to send the subscribe notifications, separate multiple email addresses by a comma.'),
            'unsubscribe_to'    => Yii::t('lists', 'Where to send the unsubscribe notifications, separate multiple email addresses by a comma.'),
        );
        return CMap::mergeArray($texts, parent::attributeHelpTexts());
    }
    
    public function attributePlaceholders()
    {
        $placeholders = array(
            'subscribe'         => Yii::t('lists', ''),
            'unsubscribe'       => Yii::t('lists', ''),
            'subscribe_to'      => Yii::t('lists', 'me@mydomain.com'),
            'unsubscribe_to'    => Yii::t('lists', 'me@mydomain.com'),
        );
        return CMap::mergeArray($placeholders, parent::attributePlaceholders());
    }
}
