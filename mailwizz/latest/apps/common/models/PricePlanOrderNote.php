<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * PricePlanOrderNote
 * 
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com> 
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.3.4.5
 */

/**
 * This is the model class for table "{{price_plan_order_note}}".
 *
 * The followings are the available columns in table '{{price_plan_order_note}}':
 * @property integer $note_id
 * @property integer $order_id
 * @property integer $customer_id
 * @property integer $user_id
 * @property string $note
 * @property string $date_added
 * @property string $last_updated
 *
 * The followings are the available model relations:
 * @property PricePlanOrder $order
 * @property Customer $customer
 * @property User $user
 */
class PricePlanOrderNote extends ActiveRecord
{
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return '{{price_plan_order_note}}';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		$rules = array(
			array('note', 'required'),
			array('note', 'length', 'max'=>255),
		);
        return CMap::mergeArray($rules, parent::rules());
	}

	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		$relations = array(
			'order'     => array(self::BELONGS_TO, 'PricePlanOrder', 'order_id'),
            'customer'  => array(self::BELONGS_TO, 'Customer', 'customer_id'),
            'user'      => array(self::BELONGS_TO, 'User', 'user_id'),
		);
        return CMap::mergeArray($relations, parent::relations());
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		$labels = array(
			'note_id'    => Yii::t('orders', 'Note'),
			'order_id'   => Yii::t('orders', 'Order'),
			'note'       => Yii::t('orders', 'Note'),
		);
        return CMap::mergeArray($labels, parent::attributeLabels());
	}
    
    /**
     * @return array attribute placeholders
     */
    public function attributePlaceholders()
    {
        $placeholders = array(
			'note'       => Yii::t('orders', 'If you have particular notes about this order, please type them here...'),
		);
        
        return CMap::mergeArray($placeholders, parent::attributePlaceholders());
    }
    
    /**
     * @return array attribute help texts
     */
    public function attributeHelpTexts()
    {
        $texts = array(
			'note'  => Yii::t('orders', 'If you have particular notes about this order, please type them here...'),
		);
        
        return CMap::mergeArray($texts, parent::attributeHelpTexts());
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
        
		$criteria->compare('t.order_id',$this->order_id);
		$criteria->order = 't.note_id ASC';
        
		return new CActiveDataProvider(get_class($this), array(
            'criteria'   => $criteria,
            'pagination' => array(
                'pageSize' => $this->paginationOptions->getPageSize(),
                'pageVar'  => 'page',
            ),
            'sort'=>array(
                'defaultOrder' => array(
                    't.note_id'  => CSort::SORT_ASC,
                ),
            ),
        ));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return PricePlanOrderNote the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
    
    public function getAuthor()
    {
        if (!empty($this->user_id)) {
            return $this->user->getFullName() . ' (' . Yii::t('orders', 'Admin') . ')';
        } elseif (!empty($this->customer_id)) {
            return $this->customer->getFullName() . ' (' . Yii::t('orders', 'Customer') . ')';
        }
        return null;
    }
    
    public function getAuthorAndDate()
    {
        $out = '';
        if (($author = $this->getAuthor())) {
            $out .= Yii::t('orders', 'By {author} at ', array('{author}' => $author));
        }
        $out .= $this->getDateAdded();
        return $out;
    }
}
