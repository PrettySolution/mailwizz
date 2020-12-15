<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * Zone
 * 
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com> 
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.0
 */
 
/**
 * This is the model class for table "zone".
 *
 * The followings are the available columns in table 'zone':
 * @property integer $zone_id
 * @property integer $country_id
 * @property string $name
 * @property string $code
 * @property string $status
 * @property string $date_added
 * @property string $last_updated
 *
 * The followings are the available model relations:
 * @property ListCompany[] $listCompanies
 * @property UserCompany[] $userCompanies
 * @property Tax[] $taxes
 * @property Country $country
 */
class Zone extends ActiveRecord
{
    /**
     * @return string the associated database table name
     */
    public function tableName()
    {
        return '{{zone}}';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        $rules = array(
            array('country_id, name, code, status', 'required'),
            array('country_id', 'exist', 'className' => 'Country'),
            array('name', 'length', 'min' => 3, 'max' => 150),
            array('code', 'length', 'min' => 1, 'max' => 50),
            
            array('status', 'in', 'range' => array_keys($this->getStatusesList())),
            
            // mark them as safe for search
            array('country_id, name, code, status', 'safe', 'on' => 'search'),
        );
        
        return CMap::mergeArray($rules, parent::rules());
    }

    /**
     * @return array relational rules.
     */
    public function relations()
    {
        $relations = array(
            'listCompanies'     => array(self::HAS_MANY, 'ListCompany', 'zone_id'),
            'userCompanies'     => array(self::HAS_MANY, 'UserCompany', 'zone_id'),
            'taxes'             => array(self::HAS_MANY, 'Tax', 'zone_id'),
            'country'           => array(self::BELONGS_TO, 'Country', 'country_id'),
        );
        
        return CMap::mergeArray($relations, parent::relations());
    }

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        $labels = array(
            'zone_id'       => Yii::t('zones', 'Zone'),
            'country_id'    => Yii::t('zones', 'Country'),
            'name'          => Yii::t('zones', 'Name'),
            'code'          => Yii::t('zones', 'Code'),
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
        $criteria=new CDbCriteria;
        
        if (!empty($this->country_id) && is_string($this->country_id)) {
            $criteria->with['country'] = array(
                'together' => true,
                'joinType' => 'INNER JOIN',
            );
            $criteria->compare('country.name', $this->country_id, true);
        } else {
            $criteria->compare('t.country_id', $this->country_id);
        }
        
        $criteria->compare('t.name', $this->name, true);
        $criteria->compare('t.code', $this->code, true);
        $criteria->compare('t.status', $this->status);
        
        return new CActiveDataProvider(get_class($this), array(
            'criteria'      => $criteria,
            'pagination'    => array(
                'pageSize'  => $this->paginationOptions->getPageSize(),
                'pageVar'   => 'page',
            ),
            'sort'=>array(
                'defaultOrder' => array(
                    'name'     => CSort::SORT_ASC,
                ),
            ),
        ));
    }
    
    /**
     * Returns the static model of the specified AR class.
     * Please note that you should have this exact method in all your CActiveRecord descendants!
     * @param string $className active record class name.
     * @return Zone the static model class
     */
    public static function model($className=__CLASS__)
    {
        return parent::model($className);
    }

    /**
     * @param $countryId
     * @return array
     */
    public static function getAsDropdownOptionsByCountryId($countryId)
    {
        static $options = array();
        $countryId      = (int)$countryId > 0 ? $countryId : 0;
        if (isset($options[$countryId]) || array_key_exists($countryId, $options)) {
            return $options[$countryId];
        }
        if ($countryId == 0) {
            return $options[0] = array();
        }
        $options[$countryId] = array();
        $zones = self::model()->findAll(array(
            'select'    => 'zone_id, name', 
            'condition' => 'country_id = :cid', 
            'params'    => array(':cid' => (int)$countryId), 
            'order'     => 'name ASC'
        ));
        foreach ($zones as $zone) {
            $options[$countryId][$zone->zone_id] = $zone->name;
        }
        return $options[$countryId];
    }
}
