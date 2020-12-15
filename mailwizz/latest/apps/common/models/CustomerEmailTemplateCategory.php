<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * CustomerEmailTemplateCategory
 *
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.3.9.5
 */

/**
 * This is the model class for table "{{customer_email_template_category}}".
 *
 * The followings are the available columns in table '{{customer_email_template_category}}':
 * @property integer $category_id
 * @property integer $customer_id
 * @property string $name
 * @property string $date_added
 * @property string $last_updated
 *
 * The followings are the available model relations:
 * @property Customer $customer
 * @property CustomerEmailTemplate[] $templates
 * @property CustomerEmailTemplate $templatesCount
 */
class CustomerEmailTemplateCategory extends ActiveRecord
{
    /**
     * @return string the associated database table name
     */
    public function tableName()
    {
        return '{{customer_email_template_category}}';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        $rules = array(
            array('name', 'required'),
            array('name', 'length', 'max'=>255),
            
            array('category_id, customer_id, name', 'safe', 'on'=>'search'),
        );

        return CMap::mergeArray($rules, parent::rules());
    }

    /**
     * @return array relational rules.
     */
    public function relations()
    {
        $relations = array(
            'customer'  => array(self::BELONGS_TO, 'Customer', 'customer_id'),
            'templates' => array(self::HAS_MANY, 'CustomerEmailTemplate', 'category_id'),
            'templatesCount' => array(self::STAT, 'CustomerEmailTemplate', 'category_id'),
        );

        return CMap::mergeArray($relations, parent::relations());
    }

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        $labels = array(
            'category_id' => Yii::t('email_templates', 'Category'),
            'customer_id' => Yii::t('email_templates', 'Customer'),
            'name'        => Yii::t('email_templates', 'Name'),
            
            'templatesCount' => Yii::t('email_templates', 'Templates'),
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
        $criteria = new CDbCriteria;
        
        if (!empty($this->customer_id)) {
            if (is_numeric($this->customer_id)) {
                $criteria->compare('t.customer_id', $this->customer_id);
            } else {
                $criteria->with['customer'] = array(
                    'condition' => 'customer.email LIKE :name OR customer.first_name LIKE :name OR customer.last_name LIKE :name',
                    'params'    => array(':name' => '%' . $this->customer_id . '%')
                );
            }
        } elseif ($this->customer_id === null) {
            $criteria->addCondition('t.customer_id IS NULL');
        }

        if (!empty($this->category_id)) {
            if (is_numeric($this->category_id)) {
                $criteria->compare('t.category_id', $this->category_id);
            } else {
                $criteria->with['category'] = array(
                    'condition' => 'category.name LIKE :name',
                    'params'    => array(':name' => '%' . $this->category_id . '%')
                );
            }
        }
        
        $criteria->compare('t.name', $this->name, true);
		
        // force order by name
	    $criteria->order = 't.name ASC';
	    
        return new CActiveDataProvider(get_class($this), array(
            'criteria'      => $criteria,
            'pagination'    => array(
                'pageSize'  => $this->paginationOptions->getPageSize(),
                'pageVar'   => 'page',
            ),
            'sort'  => array(
                'defaultOrder' => array(
                    't.category_id'   => CSort::SORT_DESC,
                ),
            ),
        ));
    }

    /**
     * Returns the static model of the specified AR class.
     * Please note that you should have this exact method in all your CActiveRecord descendants!
     * @param string $className active record class name.
     * @return CustomerEmailTemplateCategory the static model class
     */
    public static function model($className=__CLASS__)
    {
        return parent::model($className);
    }

	/**
	 * @param null $customerId
	 *
	 * @return array
	 */
    public static function getAllAsOptions($customerId = null)
    {
        $options = array();
        
        $criteria = new CDbCriteria();
        $criteria->select = 'category_id, name';
        if ($customerId) {
            $criteria->compare('customer_id', (int)$customerId);
        } else {
            $criteria->addCondition('customer_id IS NULL');
        }
        $criteria->order = 'name ASC';
        $models = self::model()->findAll($criteria);
        
        foreach ($models as $model) {
            $options[$model->category_id] = $model->name;
        }
        
        return $options;
    }
}