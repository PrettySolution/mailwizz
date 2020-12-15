<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * This is the model class for table "campaign_attachment".
 *
 * The followings are the available columns in table 'campaign_attachment':
 * @property integer $attachment_id
 * @property integer $campaign_id
 * @property string $file
 * @property string $name
 * @property integer $size
 * @property string $extension
 * @property string $mime_type
 * @property string $date_added
 * @property string $last_updated
 *
 * The followings are the available model relations:
 * @property Campaign $campaign
 */
class CampaignAttachment extends ActiveRecord
{
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return '{{campaign_attachment}}';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		$rules = array(
            array('campaign_id, file', 'required'),
            array('name, size, extension, mime_type', 'required', 'except' => 'multi-upload'),
            array('name, size, extension, mime_type', 'unsafe', 'on' => 'multi-upload'),
            
            array('file', 'file', 
                'types'      => $this->getAllowedExtensions(), 
                'mimeTypes'  => ($this->getAllowedMimeTypes() === array() ? null : $this->getAllowedMimeTypes()), 
                'maxSize'    => $this->getAllowedFileSize(), 
                'maxFiles'   => $this->getAllowedFilesCount(), 
                'allowEmpty' => true,
                'on'         => 'multi-upload'
            ),

            array('campaign_id', 'exist', 'className' => 'Campaign'),
            array('name', 'match', 'pattern' => '/\w+/i'),
            array('size', 'numerical', 'integerOnly' => true, 'min' => 0, 'max' => $this->getAllowedFileSize()),
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
			'attachment_id'  => Yii::t('campaigns', 'Attachment'),
			'campaign_id'    => Yii::t('campaigns', 'Campaign'),
			'file'           => Yii::t('campaigns', 'File'),
			'name'           => Yii::t('campaigns', 'Name'),
			'size'           => Yii::t('campaigns', 'Size'),
            'extension'      => Yii::t('campaigns', 'Extension'),
			'mime_type'      => Yii::t('campaigns', 'Mime type'),
		);
        
        return CMap::mergeArray($labels, parent::attributeLabels());
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return CampaignAttachment the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

    protected function afterValidate()
    {
        if ($this->hasErrors()) {
            return parent::afterValidate(); 
        }
        
        if ($this->scenario == 'multi-upload') {
            $this->handleMultiFileUpload();
        }

        return parent::afterValidate();    
    }

    protected function afterDelete()
    {
        if (is_file($file = Yii::getPathOfAlias('root') . $this->file)) {
            @unlink($file);
        }
        parent::afterDelete();
    }
    
    public function validateAndSave()
    {
        return $this->validate();
    }

    public function getAllowedExtensions()
    {
        $extensions = array(
            'png', 'jpg', 'jpeg', 'gif',
            'pdf', 'doc', 'docx', 'xls', 'xlsx',
            'ppt', 'pptx',
        );
        $extensions = (array)Yii::app()->options->get('system.campaign.attachments.allowed_extensions', array());
        $extensions = (array)Yii::app()->hooks->applyFilters('campaign_attachments_allowed_extensions', $extensions);
        return $extensions;
    }
    
    public function getAllowedMimeTypes()
    {
        if (!CommonHelper::functionExists('finfo_open')) {
            return array();
        }
        $mimes = array();
        foreach (array('pdf', 'doc', 'xls', 'ppt') as $type) {
            $mimes = CMap::mergeArray($mimes, Yii::app()->extensionMimes->get($type)->toArray());
        }
        $mimes = (array)Yii::app()->options->get('system.campaign.attachments.allowed_mime_types', array());
        $mimes = (array)Yii::app()->hooks->applyFilters('campaign_attachments_allowed_mime_types', $mimes);
        return $mimes;
    }
    
    public function getAllowedFileSize()
    {
        $size = 1048576; // 1 mb
        $size = (int)Yii::app()->options->get('system.campaign.attachments.allowed_file_size', $size);
        $size = (int)Yii::app()->hooks->applyFilters('campaign_attachments_allowed_file_size', $size);
        return $size;
    }
    
    public function getAllowedFilesCount()
    {
        $count = 5;
        $count = (int)Yii::app()->options->get('system.campaign.attachments.allowed_files_count', $count);
        $count = (int)Yii::app()->hooks->applyFilters('campaign_attachments_allowed_files_count', $count);
        return $count;
    }
    
    public function getAbsolutePath()
    {
        if (!($relativePath = $this->getRelativePath())) {
            return null;
        }
        return Yii::getPathOfAlias('root') . $relativePath;
    }
    
    public function getRelativePath()
    {
        if (empty($this->campaign)) {
            return null;
        }
        
        return sprintf('/frontend/assets/files/campaign-attachments/%s/', $this->campaign->campaign_uid);
    }
    
    protected function handleMultiFileUpload()
    {
        $absolute = $this->getAbsolutePath();
        if (empty($absolute) || (!file_exists($absolute) && !is_dir($absolute) && !@mkdir($absolute, 0777, true))) {
            return;
        } 
        
        $files = CUploadedFile::getInstances($this, 'file');
        if (empty($files)) {
            return;
        }

        foreach ($files as $file) {
            $model = new self();
            $model->campaign_id  = $this->campaign_id;
            $model->file         = $this->getRelativePath() . $file->name;
            $model->name         = $file->name;
            $model->size         = $file->size;
            $model->extension    = $file->extensionName;
            $model->mime_type    = $file->type;
            
            if (!$model->save()) {
                continue;
            }
            
            if (!$file->saveAs($absolute .  $file->name)) {
                $model->delete();
            }
        }
    }

}
