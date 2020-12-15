<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * CampaignExtraTag
 *
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.5.3
 */

/**
 * This is the model class for table "campaign_extra_tag".
 *
 * The followings are the available columns in table 'campaign_extra_tag':
 * @property integer $tag_id
 * @property integer $campaign_id
 * @property string $tag
 * @property string $content
 * @property string $date_added
 * @property string $last_updated
 *
 * The followings are the available model relations:
 * @property Campaign $campaign
 */
class CampaignExtraTag extends ActiveRecord
{
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return '{{campaign_extra_tag}}';
	}

	/**
	 * @inheritdoc
	 */
	public function rules()
	{
		$rules = array(
			array('tag, content', 'required'),
            array('tag', 'length', 'min' => 1, 'max' => 50),
            array('tag', 'match', 'pattern' => '#^(([A-Z\p{Cyrillic}\p{Arabic}\p{Greek}]+)([A-Z\p{Cyrillic}\p{Arabic}\p{Greek}0-9\_]+)?([A-Z\p{Cyrillic}\p{Arabic}\p{Greek}0-9]+)?)$#u'),
            array('content', 'length', 'max' => 65535),
		);

        return CMap::mergeArray($rules, parent::rules());
	}

	/**
	 * @inheritdoc
	 */
	public function relations()
	{
        $relations = array(
			'campaign' => array(self::BELONGS_TO, 'Campaign', 'campaign_id'),
		);

        return CMap::mergeArray($relations, parent::relations());
	}

	/**
	 * @inheritdoc
	 */
	public function attributeLabels()
	{
        $labels = array(
			'tag_id'        => Yii::t('campaigns', 'Tag'),
			'campaign_id'   => Yii::t('campaigns', 'Campaign'),
			'tag'           => Yii::t('campaigns', 'Tag'),
			'content'       => Yii::t('campaigns', 'Content'),
		);

        return CMap::mergeArray($labels, parent::attributeLabels());
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return CampaignExtraTag the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
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
        return Yii::app()->params['customer.campaigns.extra_tags.prefix'];
    }
}
