<?php defined('MW_PATH') || exit('No direct script access allowed');
/**
 * CommonEmailTemplateTag
 *
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.6.2
 */

/**
 * This is the model class for table "{{common_email_template_tag}}".
 *
 * The followings are the available columns in table '{{common_email_template_tag}}':
 * @property integer $tag_id
 * @property integer $template_id
 * @property string $tag
 * @property string $description
 * @property string $date_added
 * @property string $last_updated
 *
 * The followings are the available model relations:
 * @property CommonEmailTemplate $template
 */
class CommonEmailTemplateTag extends ActiveRecord
{
	/**
	 * @var string 
	 */
	public $value = '';
	
	/**
	 * @inheritdoc
	 */
	public function tableName()
	{
		return '{{common_email_template_tag}}';
	}

	/**
	 * @inheritdoc
	 */
	public function rules()
	{
		$rules = array(
			array('tag', 'required'),
			array('tag', 'length', 'max' => 100),
			array('description', 'length', 'max' => 255),
		);
		return CMap::mergeArray($rules, parent::rules());
	}

	/**
	 * @inheritdoc
	 */
	public function relations()
	{
		$relations = array(
			'template' => array(self::BELONGS_TO, 'CommonEmailTemplate', 'template_id'),
		);
		return CMap::mergeArray($relations, parent::relations());
	}

	/**
	 * @inheritdoc
	 */
	public function attributeLabels()
	{
		$labels = array(
			'tag_id'        => Yii::t('common_email_templates', 'Tag'),
			'template_id'   => Yii::t('common_email_templates', 'Template'),
			'tag'           => Yii::t('common_email_templates', 'Tag'),
			'description'   => Yii::t('common_email_templates', 'Description'),
		);
		return CMap::mergeArray($labels, parent::attributeLabels());
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return CommonEmailTemplateTag the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
}
