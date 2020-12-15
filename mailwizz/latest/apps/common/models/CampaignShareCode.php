<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * CampaignShareCode
 *
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.7.6
 */

/**
 * This is the model class for table "{{campaign_share_code}}".
 *
 * The followings are the available columns in table '{{campaign_share_code}}':
 * @property integer $code_id
 * @property string $code_uid
 * @property string $used
 * @property string $date_added
 * @property string $last_updated
 *
 * The followings are the available model relations:
 * @property Campaign[] $campaigns
 */
class CampaignShareCode extends ActiveRecord
{
    /**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return '{{campaign_share_code}}';
	}

    /**
     * @inheritdoc
     */
    public function rules()
    {
        $rules = array();

        return CMap::mergeArray($rules, parent::rules());
    }

    /**
     * @inheritdoc
     */
	public function relations()
	{
		$relations = array(
			'campaigns' => array(self::MANY_MANY, 'Campaign', '{{campaign_share_code_to_campaign}}(code_id, campaign_id)'),
		);
        return CMap::mergeArray($relations, parent::relations());
	}

    /**
     * @inheritdoc
     */
	public function attributeLabels()
	{
		$labels = array(
			'code_uid' => Yii::t('campaigns', 'Code'),
		);

        return CMap::mergeArray($labels, parent::attributeLabels());
	}

    /**
     * @inheritdoc
     */
    protected function beforeSave()
    {
        if (empty($this->code_uid)) {
            $this->code_uid = $this->generateCode();
        }

        return parent::beforeSave();
    }

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return CampaignShareCode the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

    /**
     * @param $code_uid
     * @return static
     */
    public function findByCode($code_uid)
    {
        return self::model()->findByAttributes(array(
            'code_uid' => $code_uid,
        ));
    }

    /**
     * @return string
     */
    public function generateCode()
    {
        $unique = StringHelper::random(40);
        $exists = $this->findByCode($unique);

        if (!empty($exists)) {
            return $this->generateCode();
        }

        return $unique;
    }
}
