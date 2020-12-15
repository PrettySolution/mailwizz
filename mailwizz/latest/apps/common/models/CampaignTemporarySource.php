<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * CampaignTemporarySource
 * 
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com> 
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.3.4.5
 */
 
/**
 * This is the model class for table "{{campaign_temporary_source}}".
 *
 * The followings are the available columns in table '{{campaign_temporary_source}}':
 * @property integer $source_id
 * @property integer $campaign_id
 * @property integer $list_id
 * @property integer $segment_id
 *
 * The followings are the available model relations:
 * @property Campaign $campaign
 * @property List $list
 * @property ListSegment $segment
 */
class CampaignTemporarySource extends ActiveRecord
{
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return '{{campaign_temporary_source}}';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		$rules = array(
            array('campaign_id, list_id', 'required'),
            array('campaign_id, list_id, segment_id', 'numerical', 'integerOnly' => true),
            array('campaign_id', 'exist', 'className' => 'Campaign'),
            array('list_id', 'exist', 'className' => 'Lists'),
            array('segment_id', 'exist', 'className' => 'ListSegment'),
        );
        return CMap::mergeArray($rules, parent::rules());
	}

	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		$relations = array(
			'campaign'   => array(self::BELONGS_TO, 'Campaign', 'campaign_id'),
			'list'       => array(self::BELONGS_TO, 'Lists', 'list_id'),
			'segment'    => array(self::BELONGS_TO, 'ListSegment', 'segment_id'),
		);
        return CMap::mergeArray($relations, parent::relations());
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		$labels = array(
			'source_id'      => Yii::t('campaigns', 'Source'),
			'campaign_id'    => Yii::t('campaigns', 'Campaign'),
			'list_id'        => Yii::t('campaigns', 'List'),
			'segment_id'     => Yii::t('campaigns', 'Segment'),
		);
        return CMap::mergeArray($labels, parent::attributeLabels());
	}
    
    protected function beforeSave()
    {
        if ($this->list_id == $this->campaign->list_id) {
            if (empty($this->segment_id) || $this->segment_id === $this->campaign->segment_id) {
                return false;
            }    
        }
        
        $criteria = new CDbCriteria();
        $criteria->compare('campaign_id', (int)$this->campaign_id);
        $criteria->compare('list_id', (int)$this->list_id);
        $criteria->compare('segment_id', $this->segment_id);
        if (self::model()->count($criteria) > 0) {
            return false;
        }
        
        return parent::beforeSave();
    }
    
	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return CampaignTemporarySource the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
    
    public function getName()
    {
        if (empty($this->segment_id)) {
            return $this->list->name;
        }
        return $this->list->name . '/' . $this->segment->name;
    }
}
