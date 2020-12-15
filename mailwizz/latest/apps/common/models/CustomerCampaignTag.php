<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * CustomerCampaignTag
 *
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.3.5.9
 */

/**
 * This is the model class for table "customer_campaign_tag".
 *
 * The followings are the available columns in table 'customer_campaign_tag':
 * @property integer $tag_id
 * @property string $tag_uid
 * @property integer $customer_id
 * @property string $tag
 * @property string $content
 * @property string $random
 * @property string $date_added
 * @property string $last_updated
 *
 * The followings are the available model relations:
 * @property Customer $customer
 */
class CustomerCampaignTag extends ActiveRecord
{
    /**
     * @inheritdoc
     */
	public function tableName()
	{
		return '{{customer_campaign_tag}}';
	}

	/**
	 * @inheritdoc
	 */
	public function rules()
	{
		$rules = array(
			array('customer_id, tag, content', 'required'),
			array('customer_id', 'numerical', 'integerOnly' => true),
			array('customer_id', 'exist', 'className' => 'Customer'),
			array('tag', 'length', 'min' => 1, 'max' => 50),
            array('tag', 'match', 'pattern' => '#^(([A-Z\p{Cyrillic}\p{Arabic}\p{Greek}]+)([A-Z\p{Cyrillic}\p{Arabic}\p{Greek}0-9\_]+)?([A-Z\p{Cyrillic}\p{Arabic}\p{Greek}0-9]+)?)$#u'),
			array('content', 'length', 'max' => 65535),
			array('random', 'in', 'range' => array_keys($this->getYesNoOptions())),

			array('tag, content, random', 'safe', 'on'=>'search'),
		);

		return CMap::mergeArray($rules, parent::rules());
	}

    /**
     * @inheritdoc
     */
	public function relations()
	{
		$relations = array(
			'customer' => array(self::BELONGS_TO, 'Customer', 'customer_id'),
		);

		return CMap::mergeArray($relations, parent::relations());
	}

    /**
     * @inheritdoc
     */
	public function attributeLabels()
	{
		$labels = array(
			'tag_id'		=> Yii::t('campaigns', 'Tag'),
			'customer_id' 	=> Yii::t('campaigns', 'Customer'),
			'tag' 			=> Yii::t('campaigns', 'Tag'),
			'content' 		=> Yii::t('campaigns', 'Content'),
			'random' 		=> Yii::t('campaigns', 'Random'),
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

		$criteria->compare('customer_id', $this->customer_id);
		$criteria->compare('tag', $this->tag, true);
		$criteria->compare('content', $this->content, true);
		$criteria->compare('random', $this->random);

		return new CActiveDataProvider(get_class($this), array(
            'criteria'      => $criteria,
            'pagination'    => array(
                'pageSize'  => $this->paginationOptions->getPageSize(),
                'pageVar'   => 'page',
            ),
            'sort' => array(
                'defaultOrder' => array(
                    'tag_id' => CSort::SORT_DESC,
                ),
            ),
        ));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return CustomerCampaignTag the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

    /**
     * @inheritdoc
     */
	public function attributeHelpTexts()
    {
        $texts = array(
            'tag' => Yii::t('campaigns', 'The name of the tag in uppercase letters. Please note that the {prefix} prefix will be added to your tag, therefore you will access your tag like: {like}', array(
				'{prefix}' => self::getTagPrefix(),
				'{like}'   => '[' . self::getTagPrefix() . 'YOUR_TAG_NAME]',
			)),
			'random' => Yii::t('campaigns', 'Whether to randomize the lines of text from the content box'),
			'content'=> Yii::t('campaigns', 'The tag content')
        );

        return CMap::mergeArray($texts, parent::attributeHelpTexts());
    }

    /**
     * @inheritdoc
     */
	protected function beforeSave()
    {
        if (!parent::beforeSave()) {
            return false;
        }

        if ($this->isNewRecord) {
            $this->tag_uid = $this->generateUid();
        }

        return true;
    }

    /**
     * @return string
     */
	public function getUid()
    {
        return $this->tag_uid;
    }

    /**
     * @param $tag_uid
     * @return static
     */
	public function findByUid($tag_uid)
    {
        return self::model()->findByAttributes(array(
            'tag_uid' => $tag_uid,
        ));
    }

    /**
     * @return string
     */
	public function generateUid()
    {
        $unique = StringHelper::uniqid();
        $exists = $this->findByUid($unique);

        if (!empty($exists)) {
            return $this->generateUid();
        }

        return $unique;
    }

    /**
     * @return string
     */
	public function getFullTagWithPrefix()
	{
		return '[' . self::getTagPrefix() . $this->tag . ']';
	}

    /**
     * @return mixed
     */
	public static function getTagPrefix()
	{
		return Yii::app()->params['customer.campaigns.custom_tags.prefix'];
	}
}
