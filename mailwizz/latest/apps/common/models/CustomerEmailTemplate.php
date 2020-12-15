<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * CustomerEmailTemplate
 *
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.0
 */

/**
 * This is the model class for table "customer_email_template".
 *
 * The followings are the available columns in table 'customer_email_template':
 * @property integer $template_id
 * @property string $template_uid
 * @property integer $customer_id
 * @property integer $category_id
 * @property string $name
 * @property string $content
 * @property string $content_hash
 * @property string $create_screenshot
 * @property string $screenshot
 * @property string $inline_css
 * @property string $minify
 * @property string $meta_data
 * @property integer $sort_order
 * @property string $date_added
 * @property string $last_updated
 *
 * The followings are the available model relations:
 * @property CampaignTemplate[] $campaignTemplates
 * @property CustomerEmailTemplateCategory $category
 * @property Customer $customer
 */
class CustomerEmailTemplate extends ActiveRecord
{
    /**
     * @var string 
     */
    public $archive;

	/**
	 * @inheritdoc
	 */
    public function tableName()
    {
        return '{{customer_email_template}}';
    }

	/**
	 * @inheritdoc
	 */
    public function rules()
    {
        $mimes = null;
        if (CommonHelper::functionExists('finfo_open')) {
            $mimes = Yii::app()->extensionMimes->get('zip')->toArray();
        }

        $rules =  array(
            array('name, content', 'required', 'on' => 'insert, update'),
            array('archive', 'required', 'on' => 'upload'),
            array('name, content', 'unsafe', 'on' => 'upload'),

            array('name', 'length', 'max'=>255),
            array('category_id', 'exist', 'className' => 'CustomerEmailTemplateCategory'),
            array('content', 'safe'),
            array('archive', 'file', 'types' => array('zip'), 'mimeTypes' => $mimes, 'allowEmpty' => true),
            array('sort_order', 'numerical', 'integerOnly' => true),
            
            array('customer_id, category_id, name', 'safe', 'on' => 'search'),
        );

        return CMap::mergeArray($rules, parent::rules());
    }

	/**
	 * @inheritdoc
	 */
    public function behaviors()
    {
        $behaviors = array(
            // will handle the upload but also the afterDelete event to delete uploaded files.
            'uploader' => array(
                'class' => 'common.components.db.behaviors.EmailTemplateUploadBehavior',
            ),
        );

        return CMap::mergeArray($behaviors, parent::behaviors());
    }

	/**
	 * @inheritdoc
	 */
    public function relations()
    {
        $relations = array(
            'campaignTemplates' => array(self::HAS_MANY, 'CampaignTemplate', 'customer_template_id'),
            'category'          => array(self::BELONGS_TO, 'CustomerEmailTemplateCategory', 'category_id'),
            'customer'          => array(self::BELONGS_TO, 'Customer', 'customer_id'),
        );

        return CMap::mergeArray($relations, parent::relations());
    }

	/**
	 * @inheritdoc
	 */
    public function attributeLabels()
    {
        $labels =  array(
            'template_id'   => Yii::t('email_templates', 'Template'),
            'template_uid'  => Yii::t('email_templates', 'Template uid'),
            'customer_id'   => Yii::t('email_templates', 'Customer'),
            'category_id'   => Yii::t('email_templates', 'Category'),
            'name'          => Yii::t('email_templates', 'Name'),
            'content'       => Yii::t('email_templates', 'Content'),
            'content_hash'  => Yii::t('email_templates', 'Content hash'),
            'create_screenshot' => Yii::t('email_templates', 'Create screenshot'),
            'screenshot'    => Yii::t('email_templates', 'Screenshot'),
            'archive'       => Yii::t('email_templates', 'Archive file'),
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
        $criteria = new CDbCriteria;
        $criteria->compare('t.name', $this->name, true);
        $criteria->compare('t.category_id', $this->category_id);
        
        if (!empty($this->customer_id)) {
            if (is_numeric($this->customer_id)) {
                $criteria->compare('t.customer_id', $this->customer_id);
            } else {
                $criteria->with['customer'] = array(
                    'condition' => 'customer.email LIKE :name OR customer.first_name LIKE :name OR customer.last_name LIKE :name',
                    'params'    => array(':name' => '%' . $this->customer_id . '%')
                );
            }
        } elseif ($this->customer_id === null) {
            $criteria->addCondition('t.customer_id IS NULL');
        }

        return new CActiveDataProvider(get_class($this), array(
            'criteria'      => $criteria,
            'pagination'    => array(
                'pageSize'  => $this->paginationOptions->getPageSize(),
                'pageVar'   => 'page',
            ),
            'sort'  => array(
                'defaultOrder' => array(
                    'last_updated'   => CSort::SORT_DESC,
                ),
            ),
        ));
    }

    /**
     * Returns the static model of the specified AR class.
     * Please note that you should have this exact method in all your CActiveRecord descendants!
     * @param string $className active record class name.
     * @return CustomerEmailTemplate the static model class
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
        if (empty($this->template_uid)) {
            $this->template_uid = $this->generateUid();
        }

        if (empty($this->name)) {
            $this->name = 'Untitled';
        }

        if ($this->content_hash != sha1($this->content)) {
            $this->create_screenshot = self::TEXT_YES;
        }

        $this->content_hash = sha1($this->content);

        return parent::beforeSave();
    }

	/**
	 * @inheritdoc
	 */
    protected function afterDelete()
    {
        // clean template files, if any.
        $storagePath = Yii::getPathOfAlias('root.frontend.assets.gallery');
        $templateFiles = $storagePath.'/'.$this->template_uid;
        if (file_exists($templateFiles) && is_dir($templateFiles)) {
            FileSystemHelper::deleteDirectoryContents($templateFiles, true, 1);
        }

        parent::afterDelete();
    }

	/**
	 * @param $template_uid
	 *
	 * @return CustomerEmailTemplate|null
	 */
    public function findByUid($template_uid)
    {
        return self::model()->findByAttributes(array(
            'template_uid' => $template_uid,
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
	 * @return array
	 */
    public function getInlineCssArray()
    {
        return $this->getYesNoOptions();
    }

	/**
	 * @return array|mixed
	 * @throws CException
	 */
    public function attributeHelpTexts()
    {
        $texts = array(
            'name' => Yii::t('email_templates', 'The name of the template, used for you to make the difference if having to many templates.'),
        );

        return CMap::mergeArray($texts, parent::attributeHelpTexts());
    }

	/**
	 * @return bool|CustomerEmailTemplate
	 * @throws CException
	 */
    public function copy()
    {
    	$copied = false;
    	
        if ($this->isNewRecord) {

	        // 1.8.0
	        Yii::app()->hooks->doAction('copy_customer_email_template', new CAttributeCollection(array(
		        'template' => $this,
		        'copied'   => $copied,
	        )));
	        
            return $copied;
        }

        $storagePath = Yii::getPathOfAlias('root.frontend.assets.gallery');
        $filesPath   = $storagePath.'/'.$this->template_uid;

        $templateUid  = $this->generateUid();
        $newFilesPath = $storagePath.'/'.$templateUid;

        if (file_exists($filesPath) && is_dir($filesPath) && mkdir($newFilesPath, 0777, true)) {
            if (!FileSystemHelper::copyOnlyDirectoryContents($filesPath, $newFilesPath)) {
                return $copied;
            }
        }

        $template = clone $this;
        $template->isNewRecord  = true;
        $template->template_id  = null;
        $template->template_uid = $templateUid;
        $template->content      = str_replace($this->template_uid, $templateUid, $this->content);
        $template->content_hash = null;
        $template->screenshot   = preg_replace('#' . $this->template_uid . '#', $templateUid, $this->screenshot, 1);
        $template->date_added   = new CDbExpression('NOW()');
        $template->last_updated = new CDbExpression('NOW()');

        if (!$template->save(false)) {
            if (file_exists($newFilesPath) && is_dir($newFilesPath)) {
                FileSystemHelper::deleteDirectoryContents($newFilesPath, true, 1);
            }
            return $copied;
        }
        
        $copied = $template;

	    // 1.8.0
	    Yii::app()->hooks->doAction('copy_customer_email_template', new CAttributeCollection(array(
		    'template' => $this,
		    'copied'   => $copied,
	    )));

        return $copied;
    }

	/**
	 * @return mixed
	 */
    public function getScreenshotSrc()
    {
        if (!empty($this->screenshot)) {
        	if (FilterVarHelper::url($this->screenshot)) {
        		return $this->screenshot;
	        }
            try {
                if ($image = @ImageHelper::resize($this->screenshot)) {
                    return $image;
                }
            } catch (Exception $e) {}
        }
        return ImageHelper::resize('/frontend/assets/files/no-template-image-320x320.jpg');
    }

	/**
	 * @param int $length
	 *
	 * @return string
	 */
    public function getShortName($length = 17)
    {
        return StringHelper::truncateLength($this->name, (int)$length);
    }

	/**
	 * @return string
	 */
    public function getUid()
    {
        return $this->template_uid;
    }
}
