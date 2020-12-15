<?php defined('MW_PATH') || exit('No direct script access allowed');
/**
 * CommonEmailTemplate
 *
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.6.2
 */

/**
 * This is the model class for table "{{common_email_template}}".
 *
 * The followings are the available columns in table '{{common_email_template}}':
 * @property integer $template_id
 * @property string $name
 * @property string $slug
 * @property string $subject
 * @property string $content
 * @property string $removable
 * @property string $date_added
 * @property string $last_updated
 *
 * The followings are the available model relations:
 * @property CommonEmailTemplateTag[] $tags
 */
class CommonEmailTemplate extends ActiveRecord
{
	/**
	 * @inheritdoc
	 */
	public function tableName()
	{
		return '{{common_email_template}}';
	}

	/**
	 * @inheritdoc
	 */
	public function rules()
	{
		$rules = array(
			array('name, subject, content', 'required'),
			array('name', 'length', 'max' => 150),
			array('subject', 'length', 'max' => 255),
			array('slug', 'length', 'max' => 255),
			array('slug', 'unique'),
			
			array('name, slug, subject', 'safe', 'on' => 'search'),
		);

		return CMap::mergeArray($rules, parent::rules());
	}

	/**
	 * @inheritdoc
	 */
	public function relations()
	{
		$relations = array(
			'tags' => array(self::HAS_MANY, 'CommonEmailTemplateTag', 'template_id'),
		);
		return CMap::mergeArray($relations, parent::relations());
	}

	/**
	 * @inheritdoc
	 */
	public function attributeLabels()
	{
		$labels = array(
			'template_id'   => Yii::t('common_email_templates', 'Template'),
			'name'          => Yii::t('common_email_templates', 'Name'),
			'slug'          => Yii::t('common_email_templates', 'Slug'),
			'subject'       => Yii::t('common_email_templates', 'Subject'),
			'content'       => Yii::t('common_email_templates', 'Content'),
		);
		return CMap::mergeArray($labels, parent::attributeLabels());
	}

	/**
	 * @inheritdoc
	 */
	public function attributeHelpTexts()
	{
		$texts = array(
			'name'          => Yii::t('common_email_templates', 'The name of the template, used internally mostly'),
			'subject'       => Yii::t('common_email_templates', 'The subject which will be used for this email'),
			'content'       => Yii::t('common_email_templates', 'The email content'),
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
		$criteria = new CDbCriteria;
		
		$criteria->compare('name', $this->name, true);
		$criteria->compare('slug', $this->slug, true);
		$criteria->compare('subject', $this->subject, true);

		return new CActiveDataProvider(get_class($this), array(
			'criteria'   => $criteria,
			'pagination' => array(
				'pageSize' => $this->paginationOptions->getPageSize(),
				'pageVar'  => 'page',
			),
			'sort' => array(
				'defaultOrder' => array(
					'template_id' => CSort::SORT_DESC,
				),
			),
		));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return CommonEmailTemplate the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

	/**
	 * @inheritdoc
	 */
	protected function beforeValidate()
	{
		if (empty($this->slug)) {
			Yii::import('common.vendors.Urlify.*');
			$this->slug = URLify::filter($this->name);
		}
		return parent::beforeValidate();
	}

	/**
	 * @param $slug
	 *
	 * @return static|null
	 */
	public static function findBySlug($slug)
	{
		return self::model()->findByAttributes(array(
			'slug' => $slug
		));
	}

	/**
	 * @param $slug
	 * @param array $params
	 * @param array $tags
	 *
	 * @return array
	 */
	public static function getAsParamsArrayBySlug($slug, array $params = array(), array $tags = array())
	{
		if (!($model = self::findBySlug($slug))) {
			return CMap::mergeArray(array(
				'subject' => '',
				'body'    => '',
			), $params);
		}
		
		return CMap::mergeArray($params, array(
			'subject' => $model->getParsedSubject($tags),
			'body'    => $model->getParsedContent($tags),
		));
	}

	/**
	 * @return array
	 */
	public static function getCommonTags()
	{
		$options    = Yii::app()->options;
		$attributes = array(
			array(
				'tag'           => '[DATE]', 
				'description'   => Yii::t('common_email_templates', 'Shows the current date'), 
				'value'         => date('Y-m-d')
			),
			array(
				'tag'           => '[YEAR]',
				'description'   => Yii::t('common_email_templates', 'Shows the current year'),
				'value'         => date('Y')
			),
			array(
				'tag'           => '[MONTH]',
				'description'   => Yii::t('common_email_templates', 'Shows the current month'),
				'value'         => date('m')
			),
			array(
				'tag'           => '[DAY]',
				'description'   => Yii::t('common_email_templates', 'Shows the current day'),
				'value'         => date('d')
			),
			array(
				'tag'           => '[SITE_NAME]',
				'description'   => Yii::t('common_email_templates', 'Shows the site name'),
				'value'         => $options->get('system.common.site_name', 'Marketing website'),
			)
		);
		
		$tags = array();
		foreach ($attributes as $attr) {
			$tag = new CommonEmailTemplateTag();
			foreach ($attr as $key => $value) {
				$tag->$key = $value;
			}
			$tags[] = $tag;
		}
		
		return $tags;
	}

	/**
	 * @return array
	 */
	public function getAllTags()
	{
		return CMap::mergeArray($this->isNewRecord ? array() : (!empty($this->tags) ? $this->tags : array()), self::getCommonTags());
	}
	
	/**
	 * @param array $tags
	 *
	 * @return mixed
	 */
	public function getParsedSubject(array $tags = array())
	{
		foreach (self::getCommonTags() as $tag) {
			if (!isset($tags[$tag->tag])) {
				$tags[ $tag->tag ] = $tag->value;
			}
		}
		return str_replace(array_keys($tags), array_values($tags), $this->subject);
	}

	/**
	 * @param array $tags
	 *
	 * @return mixed
	 */
	public function getParsedContent(array $tags = array())
	{
		foreach (self::getCommonTags() as $tag) {
			if (!isset($tags[$tag->tag])) {
				$tags[ $tag->tag ] = $tag->value;
			}
		}
		return str_replace(array_keys($tags), array_values($tags), $this->content);
	}

    /**
     * @return array
     */
	public static function getCoreTemplatesDefinitions()
    {
        static $definitions;
        if ($definitions === null) {
            $location    = Yii::getPathOfAlias('common.data.emails');
            $definitions = require $location . '/definitions.php';
        }
        return (array)$definitions;
    }

    /**
     * @param $id
     * @return bool
     * @throws CException
     */
	public static function reinstallCoreTemplateByDefinitionId($id)
    {
        $definitions = self::getCoreTemplatesDefinitions();
        $definition = array();

        foreach ($definitions as $def) {
            if ($def['slug'] === $id) {
                $definition = $def;
                break;
            }
        }

        if (empty($definition)) {
            return false;
        }

        return self::reinstallCoreTemplateByDefinition($definition);
    }

    /**
     * @param array $definition
     * @return bool
     * @throws CException
     */
	public static function reinstallCoreTemplateByDefinition(array $definition)
    {
        $layout   = Yii::app()->options->get('system.email_templates.common');
        $location = Yii::getPathOfAlias('common.data.emails');

        if (!MW_IS_CLI) {
            $context = Yii::app()->controller;
        } else {
            $context = Yii::app()->command;
        }

        self::model()->deleteAllByAttributes(array(
            'slug' => $definition['slug']
        ));

        $model = new self();
        $model->attributes = $definition;

        $content = $context->renderFile($location . '/' . $model->slug . '.php', array(), true);
        $model->content = str_replace('[CONTENT]', $content, $layout);

        if (!$model->save()) {
            return false;
        }

        foreach ($definition['tags'] as $attributes) {
            $tag              = new CommonEmailTemplateTag();
            $tag->attributes  = $attributes;
            $tag->template_id = $model->template_id;
            $tag->save();
        }

        return true;
    }

    /**
     * @throws CException
     */
	public static function reinstallCoreTemplates()
	{
		foreach (self::getCoreTemplatesDefinitions() as $definition) {
			self::reinstallCoreTemplateByDefinition($definition);
		}
	}
}
