<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * ListFormCustomRedirect
 * 
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com> 
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.3.3.1
 */
 
/**
 * This is the model class for table "{{list_form_custom_redirect}}".
 *
 * The followings are the available columns in table '{{list_form_custom_redirect}}':
 * @property integer $redirect_id
 * @property integer $list_id
 * @property integer $type_id
 * @property string $url
 * @property integer $timeout
 * @property string $date_added
 * @property string $last_updated
 *
 * The followings are the available model relations:
 * @property ListPage $list
 * @property ListPage $type
 */
class ListFormCustomRedirect extends ActiveRecord
{
    /**
     * @return string the associated database table name
     */
    public function tableName()
    {
        return '{{list_form_custom_redirect}}';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        $rules = array(
            array('url', 'length', 'min' => 3, 'max' => 255),
            array('timeout', 'numerical', 'integerOnly' => true, 'min' => 0, 'max' => 60),
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
            'redirect_id'   => Yii::t('lists', 'Redirect'),
            'list_id'       => Yii::t('lists', 'List'),
            'type_id'       => Yii::t('lists', 'Type'),
            'url'           => Yii::t('lists', 'Url'),
            'timeout'       => Yii::t('lists', 'Timeout'),
        );
        return CMap::mergeArray($labels, parent::attributeLabels());
    }
    
    public function attributeHelpTexts()
    {
        $texts = array(
            'redirect_id'   => '',
            'list_id'       => '',
            'type_id'       => '',
            'url'           => Yii::t('lists', 'The url where to redirect the subscriber'),
            'timeout'       => Yii::t('lists', 'The number of seconds to wait until redirect the subscriber'),
        );
        
        return CMap::mergeArray($texts, parent::attributeHelpTexts());
    }
    
    public function attributePlaceholders()
    {
        $placeholders = array(
            'redirect_id'   => '',
            'list_id'       => '',
            'type_id'       => '',
            'url'           => Yii::t('lists', 'i.e: http://www.some-other-website.com/my-redirect-page.php'),
            'timeout'       => Yii::t('lists', 'i.e: 10'),
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
}