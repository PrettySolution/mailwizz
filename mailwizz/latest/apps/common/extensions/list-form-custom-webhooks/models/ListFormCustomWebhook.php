<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * ListFormCustomWebhook
 * 
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com> 
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.3.4.3
 */
 
/**
 * This is the model class for table "{{list_form_custom_webhook}}".
 *
 * The followings are the available columns in table '{{list_form_custom_webhook}}':
 * @property integer $webhook_id
 * @property integer $list_id
 * @property integer $type_id
 * @property string $request_url
 * @property string $request_type
 * @property string $date_added
 * @property string $last_updated
 *
 * The followings are the available model relations:
 * @property ListPage $list
 * @property ListPage $type
 */
class ListFormCustomWebhook extends ActiveRecord
{
    const REQUEST_TYPE_POST = 'post';
    
    const REQUEST_TYPE_GET = 'get';
    
    /**
     * @return string the associated database table name
     */
    public function tableName()
    {
        return '{{list_form_custom_webhook}}';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        $rules = array(
            array('request_url, request_type', 'required'),
            array('request_url', 'url'),
            array('request_type', 'in', 'range' => array_keys($this->getRequestTypes())),
        );
        return CMap::mergeArray($rules, parent::rules());
    }

    /**
     * @return array relational rules.
     */
    public function relations()
    {
        $relations = array(
            'list' => array(self::BELONGS_TO, 'ListPage', 'list_id'),
            'type' => array(self::BELONGS_TO, 'ListPage', 'type_id'),
        );
        return CMap::mergeArray($relations, parent::relations());
    }

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        $labels = array(
            'webhook_id'   => Yii::t('lists', 'Webhook'),
            'list_id'      => Yii::t('lists', 'List'),
            'type_id'      => Yii::t('lists', 'Page type'),
            'request_url'  => Yii::t('lists', 'Request url'),
            'request_type' => Yii::t('lists', 'Request type'),
        );
        return CMap::mergeArray($labels, parent::attributeLabels());
    }
    
    public function attributeHelpTexts()
    {
        $texts = array(
            'webhook_id'    => '',
            'list_id'       => '',
            'type_id'       => '',
            'request_url'   => Yii::t('lists', 'The request url for this hook'),
            'request_type'  => Yii::t('lists', 'The type of the request, post or get'),
        );
        
        return CMap::mergeArray($texts, parent::attributeHelpTexts());
    }
    
    public function attributePlaceholders()
    {
        $placeholders = array(
            'webhook_id'    => '',
            'list_id'       => '',
            'type_id'       => '',
            'request_url'   => Yii::t('lists', 'i.e: http://www.some-other-website.com/process-data-offline.php'),
            'request_type'  => '',
        );
        
        return CMap::mergeArray($placeholders, parent::attributePlaceholders());
    }

    /**
     * Returns the static model of the specified AR class.
     * Please note that you should have this exact method in all your CActiveRecord descendants!
     * @param string $className active record class name.
     * @return ListFormCustomRedirect the static model class
     */
    public static function model($className=__CLASS__)
    {
        return parent::model($className);
    }
    
    public function getRequestTypes()
    {
        return array(
            self::REQUEST_TYPE_POST => strtoupper(Yii::t('lists', self::REQUEST_TYPE_POST)),
            self::REQUEST_TYPE_GET  => strtoupper(Yii::t('lists', self::REQUEST_TYPE_GET)),
        );
    }
}