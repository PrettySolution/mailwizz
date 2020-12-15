<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * ListFormCustomAsset
 * 
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com> 
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.3.4.3
 */
 
/**
 * This is the model class for table "{{list_form_custom_asset}}".
 *
 * The followings are the available columns in table '{{list_form_custom_asset}}':
 * @property integer $asset_id
 * @property integer $list_id
 * @property integer $type_id
 * @property string $asset_url
 * @property string $asset_type
 * @property string $date_added
 * @property string $last_updated
 *
 * The followings are the available model relations:
 * @property ListPage $list
 * @property ListPage $type
 */
class ListFormCustomAsset extends ActiveRecord
{
    const ASSET_TYPE_CSS = 'css';
    
    const ASSET_TYPE_JS = 'javascript';
    
    /**
     * @return string the associated database table name
     */
    public function tableName()
    {
        return '{{list_form_custom_asset}}';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        $rules = array(
            array('asset_url, asset_type', 'required'),
            array('asset_url', 'url'),
            array('asset_type', 'in', 'range' => array_keys($this->getAssetTypes())),
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
            'asset_id'   => Yii::t('lists', 'Asset'),
            'list_id'    => Yii::t('lists', 'List'),
            'type_id'    => Yii::t('lists', 'Page type'),
            'asset_url'  => Yii::t('lists', 'Asset url'),
            'asset_type' => Yii::t('lists', 'Asset type'),
        );
        return CMap::mergeArray($labels, parent::attributeLabels());
    }
    
    public function attributeHelpTexts()
    {
        $texts = array(
            'asset_id'   => '',
            'list_id'    => '',
            'type_id'    => '',
            'asset_url'  => Yii::t('lists', 'The url from where we should load the asset'),
            'asset_type' => Yii::t('lists', 'The type of the asset, css or javascript'),
        );
        
        return CMap::mergeArray($texts, parent::attributeHelpTexts());
    }
    
    public function attributePlaceholders()
    {
        $placeholders = array(
            'asset_id'   => '',
            'list_id'    => '',
            'type_id'    => '',
            'asset_url'  => Yii::t('lists', 'i.e: http://www.some-other-website.com/assets/css/my-list-file.css'),
            'asset_type' => '',
        );
        
        return CMap::mergeArray($placeholders, parent::attributePlaceholders());
    }
    
    protected function afterValidate()
    {
        if ($this->hasErrors()) {
            return parent::afterValidate();
        }
        
        $ext = @pathinfo($this->asset_url, PATHINFO_EXTENSION);
        if (($this->asset_type == self::ASSET_TYPE_CSS && $ext != 'css') || ($this->asset_type == self::ASSET_TYPE_JS && $ext != 'js')) {
            $this->addError('asset_type', Yii::t('lists', 'The url {url} must point to a valid {type} file.', array(
                '{url}'  => $this->asset_url,
                '{type}' => $this->asset_type,
            )));
        }
        
        return parent::afterValidate();
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
    
    public function getAssetTypes()
    {
        return array(
            self::ASSET_TYPE_CSS => ucfirst(Yii::t('lists', self::ASSET_TYPE_CSS)),
            self::ASSET_TYPE_JS  => ucfirst(Yii::t('lists', self::ASSET_TYPE_JS)),
        );
    }
}