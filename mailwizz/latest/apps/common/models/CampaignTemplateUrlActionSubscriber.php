<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * CampaignTemplateUrlActionSubscriber
 * 
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com> 
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.3.4.3
 */
 
/**
 * This is the model class for table "{{campaign_template_url_action_subscriber}}".
 *
 * The followings are the available columns in table '{{campaign_template_url_action_subscriber}}':
 * @property string $url_id
 * @property integer $campaign_id
 * @property integer $list_id
 * @property integer $template_id
 * @property string $url
 * @property string $action
 * @property string $date_added
 * @property string $last_updated
 *
 * The followings are the available model relations:
 * @property CampaignTemplate $template
 * @property List $list
 * @property Campaign $campaign
 */
class CampaignTemplateUrlActionSubscriber extends ActiveRecord
{
    /**
     * Flag for copy
     */
    const ACTION_COPY = 'copy';

    /**
     * Flag for move
     */
    const ACTION_MOVE = 'move';
    
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return '{{campaign_template_url_action_subscriber}}';
	}

	/**
	 * @inheritdoc
	 */
	public function rules()
	{
		$rules = array(
			array('url, action, list_id', 'required'),
            array('url', '_validateUrl'),
			array('action', 'length', 'max'=>5),
            array('action', 'in', 'range' => array_keys($this->getActions())),
            array('list_id', 'numerical', 'integerOnly' => true),
            array('list_id', 'exist', 'className' => 'Lists'),
		);
        
        return CMap::mergeArray($rules, parent::rules());
	}

    /**
     * @inheritdoc
     */
	public function relations()
	{
		$relations = array(
			'template'   => array(self::BELONGS_TO, 'CampaignTemplate', 'template_id'),
			'list'       => array(self::BELONGS_TO, 'List', 'list_id'),
			'campaign'   => array(self::BELONGS_TO, 'Campaign', 'campaign_id'),
		);
        
        return CMap::mergeArray($relations, parent::relations());
	}

    /**
     * @inheritdoc
     */
	public function attributeLabels()
	{
		$labels = array(
			'url_id'         => Yii::t('campaigns', 'Url'),
			'campaign_id'    => Yii::t('campaigns', 'Campaign'),
			'list_id'        => Yii::t('campaigns', 'To list'),
			'template_id'    => Yii::t('campaigns', 'Template'),
			'url'            => Yii::t('campaigns', 'Url'),
			'action'         => Yii::t('campaigns', 'Action'),
		);
        
        return CMap::mergeArray($labels, parent::attributeLabels());
	}

    /**
     * @inheritdoc
     */
    public function attributeHelpTexts()
    {
        $texts = array(
            'list_id'        => Yii::t('campaigns', 'The target list for the selected action'),
			'url'            => Yii::t('campaigns', 'Trigger the selected action when the subscriber will access this url'),
			'action'         => Yii::t('campaigns', 'What action to take against the subscriber when the url is accessed'),
        );
        
        return CMap::mergeArray($texts, parent::attributeHelpTexts());
    }

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return CampaignTemplateUrlActionSubscriber the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

    /**
     * @return array
     */
    public function getActions()
    {
        return array(
            self::ACTION_COPY => ucfirst(Yii::t('app', self::ACTION_COPY)),
            self::ACTION_MOVE => ucfirst(Yii::t('app', self::ACTION_MOVE)),
        );
    }

    /**
     * @inheritdoc
     */
	protected function beforeSave()
	{
		$this->url = StringHelper::normalizeUrl($this->url);
		return parent::beforeSave();
	}

    /**
     * @param $attribute
     * @param $params
     */
	public function _validateUrl($attribute, $params)
    {
        if ($this->hasErrors($attribute)) {
            return;
        }
        
        // if this is a URL tag
        if (preg_match('/^\[([A-Z_]+)_URL\]$/', $this->$attribute, $matches)) {
            return;
        }
        
        // if this is a regular url
        $validator = new CUrlValidator();
        if ($validator->validateValue($this->$attribute)) {
            return;
        }

        return $this->addError($attribute, Yii::t('campaigns', 'Please provide a valid url!'));
    }
}
