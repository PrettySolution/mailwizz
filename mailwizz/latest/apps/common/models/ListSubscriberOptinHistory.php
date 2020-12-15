<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * ListSubscriberOptinHistory
 *
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.3.8.8
 */

/**
 * This is the model class for table "list_subscriber_optin_history".
 *
 * The followings are the available columns in table 'list_subscriber_optin_history':
 * @property integer $subscriber_id
 * @property string $optin_ip
 * @property string $optin_date
 * @property string $optin_user_agent
 * @property string $confirm_ip
 * @property string $confirm_date
 * @property string $confirm_user_agent
 *
 * The followings are the available model relations:
 * @property ListSubscriber $subscriber
 */
class ListSubscriberOptinHistory extends ActiveRecord
{
    /**
     * @return string the associated database table name
     */
    public function tableName()
    {
        return '{{list_subscriber_optin_history}}';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        return CMap::mergeArray(array(), parent::rules());
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
        return array(
            'subscriber_id'      => Yii::t('list_subscribers', 'Subscriber'),
            'optin_ip'           => Yii::t('list_subscribers', 'Opt-in ip'),
            'optin_date'         => Yii::t('list_subscribers', 'Opt-in date'),
            'optin_user_agent'   => Yii::t('list_subscribers', 'Opt-in user agent'),
            'confirm_ip'         => Yii::t('list_subscribers', 'Confirm ip'),
            'confirm_date'       => Yii::t('list_subscribers', 'Confirm date'),
            'confirm_user_agent' => Yii::t('list_subscribers', 'Confirm user agent'),
        );
    }

    /**
     * Returns the static model of the specified AR class.
     * Please note that you should have this exact method in all your CActiveRecord descendants!
     * @param string $className active record class name.
     * @return ListSubscriberOptinHistory the static model class
     */
    public static function model($className=__CLASS__)
    {
        return parent::model($className);
    }

    /**
     * @return mixed
     */
    public function getOptinDate()
    {
        return $this->dateTimeFormatter->formatLocalizedDateTime($this->optin_date);
    }

    /**
     * @return mixed
     */
    public function getConfirmDate()
    {
        return $this->dateTimeFormatter->formatLocalizedDateTime($this->confirm_date);
    }
}