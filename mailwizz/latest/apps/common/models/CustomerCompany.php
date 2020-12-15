<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * CustomerCompany
 * 
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com> 
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.0
 */
 
/**
 * This is the model class for table "customer_company".
 *
 * The followings are the available columns in table 'customer_company':
 * @property integer $company_id
 * @property integer $customer_id
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
 * @property string $fax
 * @property string $vat_number
 * @property string $date_added
 * @property string $last_updated
 *
 * The followings are the available model relations:
 * @property CompanyType $type
 * @property Country $country
 * @property Zone $zone
 * @property Customer $customer
 */
class CustomerCompany extends ActiveRecord
{

    /**
     * @return string the associated database table name
     */
    public function tableName()
    {
        return '{{customer_company}}';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        $rules = array(
            array('name, country_id, address_1, city, zip_code', 'required', 'on' => 'insert, update, register'),

            array('name, vat_number', 'length', 'max' => 100),
            array('website', 'length', 'max' => 255),
            array('website', 'url'),
            array('country_id, zone_id', 'numerical', 'integerOnly' => true, 'min' => 1),
            array('address_1, address_2, city', 'length', 'max' => 255),
            array('zone_name', 'length', 'max' => 150),
            array('zip_code', 'length', 'max' => 10),
            array('phone, fax', 'length', 'max' => 32),
            array('type_id', 'exist', 'attributeName' => null, 'className' => 'CompanyType'),
            array('country_id', 'exist', 'attributeName' => null, 'className' => 'Country'),
            array('zone_id', 'exist', 'attributeName' => null, 'className' => 'Zone'),
            array('phone, fax', 'match', 'pattern' => '/[0-9\s\-]+/'),
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
            'customer'  => array(self::BELONGS_TO, 'Customer', 'customer_id'),
        );
        
        return CMap::mergeArray($relations, parent::relations());
    }

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        $labels = array(
            'company_id'    => Yii::t('customers', 'Company'),
            'customer_id'   => Yii::t('customers', 'Customer'),
            'type_id'       => Yii::t('customers', 'Type/Industry'),
            'country_id'    => Yii::t('customers', 'Country'),
            'zone_id'       => Yii::t('customers', 'Zone'),
            'name'          => Yii::t('customers', 'Name'),
            'website'       => Yii::t('customers', 'Website'),
            'address_1'     => Yii::t('customers', 'Address'),
            'address_2'     => Yii::t('customers', 'Address 2'),
            'zone_name'     => Yii::t('customers', 'Zone name'),
            'city'          => Yii::t('customers', 'City'),
            'zip_code'      => Yii::t('customers', 'Zip code'),
            'phone'         => Yii::t('customers', 'Phone'),
            'fax'           => Yii::t('customers', 'Fax'),
            'vat_number'    => Yii::t('customers', 'VAT Number'),
        );
        
        return CMap::mergeArray($labels, parent::attributeLabels());
    }

    /**
     * Returns the static model of the specified AR class.
     * Please note that you should have this exact method in all your CActiveRecord descendants!
     * @param string $className active record class name.
     * @return CustomerCompany the static model class
     */
    public static function model($className=__CLASS__)
    {
        return parent::model($className);
    }

    public function attributeHelpTexts()
    {
        if ($this->scenario == 'register') {
            return array();
        }
        
        $texts = array(
            'name'      => Yii::t('customers', 'Your company public display name'),
            'website'   => Yii::t('customers', 'Please enter your website address url, starting with http:// or https://'),
            'zone_id'   => Yii::t('customers', 'Please select your company country zone. If none applicable, then please fill the zone name field instead.'),
            'zone_name' => Yii::t('customers', 'Please fill this field unless you have no option to select from the zone drop down.'),
            'city'      => Yii::t('customers', 'No check will be done against your city name, please be accurate!'),
            'zip_code'  => Yii::t('customers', 'No check will be done against your zip code, please be accurate!'),
            
        );
        
        return CMap::mergeArray($texts, parent::attributeHelpTexts());
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
        $htmlOptions = CMap::mergeArray($_htmlOptions, $htmlOptions);
        
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
}
