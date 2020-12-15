<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * Country
 * 
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com> 
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.0
 */
 
/**
 * This is the model class for table "country".
 *
 * The followings are the available columns in table 'country':
 * @property integer $country_id
 * @property string $name
 * @property string $code
 * @property string $status
 * @property string $date_added
 * @property string $last_updated
 *
 * The followings are the available model relations:
 * @property ListCompany $listCompany
 * @property CustomerCompany $customerCompany
 * @property Tax[] $taxes
 * @property Zone[] $zones
 */
class Country extends ActiveRecord
{
    /**
     * @return string the associated database table name
     */
    public function tableName()
    {
        return '{{country}}';
    }
    
    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        $rules = array(
            array('name, code, status', 'required'),
            array('name', 'length', 'min' => 3, 'max' => 150),
            array('code', 'length', 'min' => 2, 'max' => 3),
            array('name, code', 'unique'),
            array('status', 'in', 'range' => array_keys($this->getStatusesList())),
            
            // mark them as safe for search
            array('name, code, status', 'safe', 'on' => 'search'),
        );
        
        return CMap::mergeArray($rules, parent::rules());
    }

    /**
     * @return array relational rules.
     */
    public function relations()
    {
        $relations = array(
            'listCompany'       => array(self::HAS_ONE, 'ListCompany', 'country_id'),
            'customerCompany'   => array(self::HAS_ONE, 'CustomerCompany', 'country_id'),
            'taxes'             => array(self::HAS_MANY, 'Tax', 'country_id'),
            'zones'             => array(self::HAS_MANY, 'Zone', 'country_id'),
        );
        
        return CMap::mergeArray($relations, parent::relations());
    }

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        $labels = array(
            'country_id'    => Yii::t('countries', 'Country'),
            'name'          => Yii::t('countries', 'Name'),
            'code'          => Yii::t('countries', 'Code'),
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

        $criteria->compare('name', $this->name, true);
        $criteria->compare('code', $this->code, true);
        $criteria->compare('status', $this->status);
        
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
     * @return Country the static model class
     */
    public static function model($className=__CLASS__)
    {
        return parent::model($className);
    }
    
    public static function getAsDropdownOptions()
    {
        static $options;
        if ($options !== null) {
            return $options;
        }
        $options    = array();
        $countries  = self::model()->findAll(array('select' => 'country_id, name', 'order' => 'name ASC'));
        foreach ($countries as $country) {
            $options[$country->country_id] = $country->name;
        }
        return $options;
    }
}
