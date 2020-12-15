<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * CustomerApiKey
 * 
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com> 
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.0
 */
 
/**
 * This is the model class for table "customer_api_key".
 *
 * The followings are the available columns in table 'customer_api_key':
 * @property integer $key_id
 * @property integer $customer_id
 * @property string $name
 * @property string $description
 * @property string $public
 * @property string $private
 * @property string $date_added
 *
 * The followings are the available model relations:
 * @property Customer $customer
 */
class CustomerApiKey extends ActiveRecord
{
    /**
     * @return string the associated database table name
     */
    public function tableName()
    {
        return '{{customer_api_key}}';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        $rules = array(
            array('name, description, ip_whitelist, ip_blacklist', 'length', 'max' => 255),
        );
        return CMap::mergeArray($rules, parent::rules());
    }

    /**
     * @return array relational rules.
     */
    public function relations()
    {
        $relations = array(
            'customer' => array(self::BELONGS_TO, 'Customer', 'customer_id'),
        );
        
        return CMap::mergeArray($relations, parent::relations());
    }

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        $labels = array(
            'key_id'        => Yii::t('api_keys', 'Key'),
            'customer_id'   => Yii::t('api_keys', 'Customer'),
            'name'          => Yii::t('api_keys', 'Name'),
            'description'   => Yii::t('api_keys', 'Description'),
            'public'        => Yii::t('api_keys', 'Public key'),
            'private'       => Yii::t('api_keys', 'Private key'),
            'ip_whitelist'  => Yii::t('api_keys', 'Ip whitelist'),
            'ip_blacklist'  => Yii::t('api_keys', 'Ip blacklist'),
        );
        
        return CMap::mergeArray($labels, parent::attributeLabels());
    }

    public function attributePlaceholders()
    {
        $placeholders = array(
            'name'          => Yii::t('api_keys', 'My site'),
            'description'   => Yii::t('api_keys', 'This key is used on my site'),
            'ip_whitelist'  => Yii::t('api_keys', '123.123.123.123, 111.111.111.111'),
            'ip_blacklist'  => Yii::t('api_keys', '231.231.231.231, 222.222.222.222'),
        );
        return CMap::mergeArray(parent::attributePlaceholders(), $placeholders);
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
        $criteria = new CDbCriteria;
        $criteria->compare('customer_id', (int)$this->customer_id);

        return new CActiveDataProvider(get_class($this), array(
            'criteria'      => $criteria,
            'pagination'    => array(
                'pageSize'  => $this->paginationOptions->getPageSize(),
                'pageVar'   => 'page',
            ),
            'sort'=>array(
                'defaultOrder' => array(
                    'key_id'   => CSort::SORT_DESC,
                ),
            ),
        ));
    }

    /**
     * Returns the static model of the specified AR class.
     * Please note that you should have this exact method in all your CActiveRecord descendants!
     * @param string $className active record class name.
     * @return CustomerApiKey the static model class
     */
    public static function model($className=__CLASS__)
    {
        return parent::model($className);
    }

    protected function beforeValidate()
    {
        if ($this->isNewRecord) {
            if (empty($this->public)) {
                $this->public = $this->generatePublicKey();
            }

            if (empty($this->private)) {
                $this->private = $this->generatePrivateKey();
            }    
        }
        
        $lists = array('ip_whitelist', 'ip_blacklist');
        foreach ($lists as $list) {
            $_list = CommonHelper::getArrayFromString($this->$list);
            foreach ($_list as $index => $ip) {
                if (!FilterVarHelper::ip($ip)) {
                    unset($_list[$index]);
                }
            }
            $this->$list = implode(', ', $_list);
        }
        
        return parent::beforeValidate();
    }
    
    public function generatePublicKey()
    {
        $key = sha1(StringHelper::uniqid(rand(0, time()), true));
        
        $model = self::model()->findByAttributes(array(
            'public' => $key
        ));
        
        if (!empty($model)) {
            return $this->generatePublicKey();
        }
        
        return $key;
    }
    
    public function generatePrivateKey()
    {
        $key = sha1(StringHelper::uniqid(rand(0, time()), true));
        
        $model = self::model()->findByAttributes(array(
            'private' => $key
        ));
        
        if (!empty($model)) {
            return $this->generatePrivateKey();
        }
        
        return $key;
    }
}
