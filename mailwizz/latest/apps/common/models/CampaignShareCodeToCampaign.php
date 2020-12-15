<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * CampaignShareCodeToCampaign
 *
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.7.6
 */
/**
 * This is the model class for table "{{campaign_share_code_to_campaign}}".
 *
 * The followings are the available columns in table '{{campaign_share_code_to_campaign}}':
 * @property integer $code_id
 * @property integer $campaign_id
 */
class CampaignShareCodeToCampaign extends ActiveRecord
{
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return '{{campaign_share_code_to_campaign}}';
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
        $relations = array();
        return CMap::mergeArray($relations, parent::relations());
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        $labels = array();

        return CMap::mergeArray($labels, parent::attributeLabels());
    }


    /**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return CampaignShareCodeToCampaign the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
}
