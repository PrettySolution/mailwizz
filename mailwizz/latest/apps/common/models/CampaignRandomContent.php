<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * CampaignRandomContent
 *
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.3.9.5
 */

/**
 * This is the model class for table "{{campaign_random_content}}".
 *
 * The followings are the available columns in table '{{campaign_random_content}}':
 * @property integer $id
 * @property integer $campaign_id
 * @property string $name
 * @property string $content
 *
 * The followings are the available model relations:
 * @property Campaign $campaign
 */
class CampaignRandomContent extends ActiveRecord
{
    /**
     * @return string the associated database table name
     */
    public function tableName()
    {
        return '{{campaign_random_content}}';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        $rules = array(
            array('name, content', 'required'),
            array('name', 'length', 'max' => 50),
        );

        return CMap::mergeArray($rules, parent::rules());
    }

    /**
     * @return array relational rules.
     */
    public function relations()
    {
        $relations = array(
            'campaign' => array(self::BELONGS_TO, 'Campaign', 'campaign_id'),
        );

        return CMap::mergeArray($relations, parent::relations());
    }

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        $labels = array(
            'id'          => Yii::t('campaigns', 'ID'),
            'campaign_id' => Yii::t('campaigns', 'Campaign'),
            'name'        => Yii::t('campaigns', 'Name'),
            'content'     => Yii::t('campaigns', 'Content'),
        );

        return CMap::mergeArray($labels, parent::attributeLabels());
    }

    /**
     * Returns the static model of the specified AR class.
     * Please note that you should have this exact method in all your CActiveRecord descendants!
     * @param string $className active record class name.
     * @return CampaignRandomContent the static model class
     */
    public static function model($className=__CLASS__)
    {
        return parent::model($className);
    }

    /**
     * @inheritdoc
     */
    protected function beforeSave()
    {
        $criteria = new CDbCriteria();
        $criteria->compare('id', '!=' . (int)$this->id);
        $criteria->compare('campaign_id', (int)$this->campaign_id);
        $criteria->compare('name', (string)$this->name);
        
        $model = self::model()->find($criteria);
        if (!empty($model)) {
            $this->addError('name', Yii::t('campaigns', 'Seems that this name is already taken for this campaign!'));
            return false;
        }
        
        return parent::beforeSave();
    }
}