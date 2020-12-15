<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * ListCompany
 *
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.0
 */

/**
 * This is the model class for table "list_company".
 *
 * The followings are the available columns in table 'list_company':
 * @property integer $list_id
 * @property integer $type_id
 * @property integer $country_id
 * @property integer $zone_id
 * @property string $name
 * @property string $website
 * @property string $address_1
 * @property string $address_2
 * @property string $zone_name
 * @property string $city
 * @property string $zip_code
 * @property string $phone
 * @property string $address_format
 *
 * The followings are the available model relations:
 * @property CompanyType $type
 * @property Country $country
 * @property Zone $zone
 * @property Lists $list
 */
class ListCompany extends ActiveRecord
{
    public $defaultAddressFormat = "[COMPANY_NAME]\n[COMPANY_ADDRESS_1] [COMPANY_ADDRESS_2]\n[COMPANY_CITY] [COMPANY_ZONE] [COMPANY_ZIP]\n[COMPANY_COUNTRY]\n[COMPANY_WEBSITE]";

    /**
     * @return string the associated database table name
     */
    public function tableName()
    {
        return '{{list_company}}';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        $rules = array(
            array('name, country_id, address_1, city, zip_code, address_format', 'required'),

            array('name', 'length', 'max' => 100),
            array('website', 'length', 'max' => 255),
            array('website', 'url'),
            array('country_id, zone_id', 'numerical', 'integerOnly' => true, 'min' => 1),
            array('address_1, address_2, city, address_format', 'length', 'max' => 255),
            array('zone_name', 'length', 'max' => 150),
            array('zip_code', 'length', 'max' => 10),
            array('phone', 'length', 'max' => 32),
            array('type_id', 'exist', 'attributeName' => null, 'className' => 'CompanyType'),
            array('country_id', 'exist', 'attributeName' => null, 'className' => 'Country'),
            array('zone_id', 'exist', 'attributeName' => null, 'className' => 'Zone'),
            array('zone_name', 'match', 'pattern' => '/[a-zA-Z\s\-\.]+/'),
            array('phone', 'match', 'pattern' => '/[0-9\s\-]+/'),
        );

        return CMap::mergeArray($rules, parent::rules());
    }

    /**
     * @return array relational rules.
     */
    public function relations()
    {
        $relations = array(
            'type'      => array(self::BELONGS_TO, 'CompanyType', 'type_id'),
            'country'   => array(self::BELONGS_TO, 'Country', 'country_id'),
            'zone'      => array(self::BELONGS_TO, 'Zone', 'zone_id'),
            'list'      => array(self::BELONGS_TO, 'Lists', 'list_id'),
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
            'type_id'           => Yii::t('lists', 'Type/Industry'),
            'country_id'        => Yii::t('lists', 'Country'),
            'zone_id'           => Yii::t('lists', 'Zone'),
            'name'              => Yii::t('lists', 'Name'),
            'website'           => Yii::t('lists', 'Website'),
            'address_1'         => Yii::t('lists', 'Address 1'),
            'address_2'         => Yii::t('lists', 'Address 2'),
            'zone_name'         => Yii::t('lists', 'Zone name'),
            'city'              => Yii::t('lists', 'City'),
            'zip_code'          => Yii::t('lists', 'Zip code'),
            'phone'             => Yii::t('lists', 'Phone'),
            'address_format'    => Yii::t('lists', 'Address format'),
        );

        return CMap::mergeArray($labels, parent::attributeLabels());
    }

    /**
     * Returns the static model of the specified AR class.
     * Please note that you should have this exact method in all your CActiveRecord descendants!
     * @param string $className active record class name.
     * @return ListCompany the static model class
     */
    public static function model($className=__CLASS__)
    {
        return parent::model($className);
    }

    protected function afterConstruct()
    {
        if (!$this->address_format) {
            $this->address_format = $this->defaultAddressFormat;
        }
        parent::afterConstruct();
    }

    protected function afterFind()
    {
        if (!$this->address_format) {
            $this->address_format = $this->defaultAddressFormat;
        }
        parent::afterFind();
    }

    protected function beforeValidate()
    {
        $tags = $this->getAvailableTags();
        $content = CHtml::decode($this->address_format);
        $hasErrors = false;
        foreach ($tags as $tag) {
            if (!isset($tag['tag']) || !isset($tag['required']) || !$tag['required']) {
                continue;
            }

            if (!isset($tag['pattern']) && strpos($content, $tag['tag']) === false) {
                $this->addError('address_format', Yii::t('lists', 'The following tag is required but was not found in your content: {tag}', array(
                    '{tag}' => $tag['tag'],
                )));
                $hasErrors = true;
            } elseif (isset($tag['pattern']) && !preg_match($tag['pattern'], $content)) {
                $this->addError('address_format', Yii::t('lists', 'The following tag is required but was not found in your content: {tag}', array(
                    '{tag}' => $tag['tag'],
                )));
                $hasErrors = true;
            }
        }

        if ($hasErrors) {
            return false;
        }

        return parent::beforeValidate();
    }

    public function mergeWithCustomerCompany(CustomerCompany $company)
    {
        $attributes = array(
            'name', 'website', 'type_id', 'country_id', 'zone_id', 'address_1', 'address_2',
            'zone_name', 'city', 'zip_code', 'phone'
        );

        foreach ($attributes as $attribute) {
            $this->$attribute = $company->$attribute;
        }

        return $this;
    }

    public function getCountriesDropDown(array $htmlOptions = array())
    {
        static $_countries = array();

        if (empty($_countries)) {
            $_countries[""] = Yii::t('app', 'Please select');

            $criteria = new CDbCriteria();
            $criteria->select = 'country_id, name';
	        $criteria->order  = 'name ASC';
            $models = Country::model()->findAll($criteria);

            foreach ($models as $model) {
                $_countries[$model->country_id] = $model->name;
            }
        }

        $_htmlOptions = $this->getHtmlOptions('country_id', array('data-placement' => 'right'));
        $_htmlOptions['data-zones-by-country-url'] = Yii::app()->createUrl('account/zones_by_country');
        $htmlOptions  = CMap::mergeArray($_htmlOptions, $htmlOptions);
        
        return CHtml::activeDropDownList($this, 'country_id', $_countries, $htmlOptions);
    }

    public function getZonesDropDown(array $htmlOptions = array())
    {
        $zones = array('' => Yii::t('app', 'Please select'));

        $criteria = new CDbCriteria();
        $criteria->select = 'zone_id, name';
        $criteria->compare('country_id', (int)$this->country_id);
        $_zones = Zone::model()->findAll($criteria);

        foreach ($_zones as $zone) {
            $zones[$zone->zone_id] = $zone->name;
        }

        $_htmlOptions = $this->getHtmlOptions('zone_id', array('data-placement' => 'left'));
        $htmlOptions  = CMap::mergeArray($_htmlOptions, $htmlOptions);
        
        return CHtml::activeDropDownList($this, 'zone_id', $zones, $htmlOptions);
    }

    public function getAvailableTags()
    {
        return array(
            array('tag' => '[COMPANY_NAME]', 'required' => true),
            array('tag' => '[COMPANY_WEBSITE]', 'required' => false),
            array('tag' => '[COMPANY_ADDRESS_1]', 'required' => true),
            array('tag' => '[COMPANY_ADDRESS_2]', 'required' => false),
            array('tag' => '[COMPANY_CITY]', 'required' => true),
            array('tag' => '[COMPANY_ZONE]', 'required' => false),
	        array('tag' => '[COMPANY_ZONE_CODE]', 'required' => false),
            array('tag' => '[COMPANY_ZIP]', 'required' => false),
            array('tag' => '[COMPANY_COUNTRY]', 'required' => false),
	        array('tag' => '[COMPANY_COUNTRY_CODE]', 'required' => false),
        );
    }

    public function getFormattedAddress()
    {
        $searchReplace = array(
            '[COMPANY_NAME]'            => $this->name,
            '[COMPANY_WEBSITE]'         => $this->website,
            '[COMPANY_ADDRESS_1]'       => $this->address_1,
            '[COMPANY_ADDRESS_2]'       => $this->address_2,
            '[COMPANY_CITY]'            => $this->city,
            '[COMPANY_ZONE]'            => !empty($this->zone) ? $this->zone->name : $this->zone_name,
            '[COMPANY_ZONE_CODE]'       => !empty($this->zone) ? $this->zone->code : $this->zone_name,
            '[COMPANY_ZIP]'             => $this->zip_code,
            '[COMPANY_COUNTRY]'         => !empty($this->country) ? $this->country->name : null,
            '[COMPANY_COUNTRY_CODE]'    => !empty($this->country) ? $this->country->code : null,
        );

        return str_replace(array_keys($searchReplace), array_values($searchReplace), $this->address_format);
    }
}
