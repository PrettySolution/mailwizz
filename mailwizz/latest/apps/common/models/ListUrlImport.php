<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * ListUrlImport
 *
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.3.9.5
 */

/**
 * This is the model class for table "{{list_url_import}}".
 *
 * The followings are the available columns in table '{{list_url_import}}':
 * @property integer $url_id
 * @property integer $list_id
 * @property string $url
 * @property integer $failures
 * @property string $status
 * @property string $date_added
 * @property string $last_updated
 *
 * The followings are the available model relations:
 * @property Lists $list
 */
class ListUrlImport extends ActiveRecord
{
    /**
     * @return string the associated database table name
     */
    public function tableName()
    {
        return '{{list_url_import}}';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        $rules = array(
            array('url, status', 'required'),
            array('url', 'length', 'max'=>255),
            array('url', 'url'),
            array('url', '_validateUrl'),
            array('status', 'in', 'range' => array_keys($this->getStatusesList())),
        );

        return CMap::mergeArray($rules, parent::rules());
    }

    /**
     * @return array relational rules.
     */
    public function relations()
    {
        $relations = array(
            'list' => array(self::BELONGS_TO, 'Lists', 'list_id'),
        );

        return CMap::mergeArray($relations, parent::relations());
    }

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        $labels = array(
            'url_id'    => Yii::t('lists', 'Url'),
            'list_id'   => Yii::t('lists', 'List'),
            'url'       => Yii::t('lists', 'Url'),
            'failures'  => Yii::t('lists', 'Failures'),
        );

        return CMap::mergeArray($labels, parent::attributeLabels());
    }

    /**
     * Returns the static model of the specified AR class.
     * Please note that you should have this exact method in all your CActiveRecord descendants!
     * @param string $className active record class name.
     * @return ListUrlImport the static model class
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
        if ((int)$this->failures >= 3) {
            $this->failures = 0;
            $this->status   = self::STATUS_INACTIVE;
        }
        
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
        
        if (!in_array($this->getExtension(), array('.csv', '.txt'))) {
            $this->addError($attribute, Yii::t('lists', 'Please make sure your url points to a .txt or a .csv file!'));
            return;
        }
        
        if (!$this->getIsUrlValid()) {
            $this->addError($attribute, Yii::t('lists', 'The specific url does not seem to be valid, please double check it and try again.'));
            return;
        }
    }

    /**
     * @return bool
     */
    public function getIsUrlValid()
    {
        if (empty($this->url) || !FilterVarHelper::url($this->url)) {
            return false;
        }

        if (!in_array($this->getExtension(), array('.csv', '.txt'))) {
            return false;
        }
        
        $ch = curl_init($this->url);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_NOBODY, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_AUTOREFERER , true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

        if (ini_get('open_basedir') == '' && ini_get('safe_mode') != 'On') {
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        }
        
        curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ((int)$httpCode < 200 || (int)$httpCode > 400) {
            return false;
        }
        
        return true;
    }

    /**
     * @return string
     */
    public function getDownloadPath()
    {
        $basePath = Yii::getPathOfAlias('common.runtime.list-import-url');
        return $basePath . '/' . (int)$this->url_id . $this->getExtension();
    }
    
    public function getExtension()
    {
        if (empty($this->url)) {
            return '';
        }

        $ext = explode('.', $this->url);
        $ext = '.' . end($ext);
        
        return $ext;
    }

    /**
     * @return bool
     */
    public function download()
    {
        if ($this->getIsNewRecord()) {
            return false;
        }

        $storagePath = dirname($this->getDownloadPath());
        if (!file_exists($storagePath) || !@is_dir($storagePath)) {
            if (!@mkdir($storagePath)) {
                return false;
            }
        }
        
        if (is_file($this->getDownloadPath())) {
            @unlink($this->getDownloadPath());
        }
        @touch($this->getDownloadPath());
        @chmod($this->getDownloadPath(), 0777);
        
        if (!($fp = @fopen($this->getDownloadPath(), 'w+'))) {
            return false;
        }

        $ch = curl_init($this->url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_AUTOREFERER , true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_FILE, $fp);

        if (ini_get('open_basedir') == '' && ini_get('safe_mode') != 'On') {
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        }
        
        // make sure we do't download over the limit.
        if (defined('CURLOPT_PROGRESSFUNCTION')) {
            curl_setopt($ch, CURLOPT_BUFFERSIZE, 128);
            curl_setopt($ch, CURLOPT_NOPROGRESS, false);
            curl_setopt($ch, CURLOPT_PROGRESSFUNCTION, array($this, '_curlProgressFunction'));
        }

        curl_exec($ch);
        curl_close($ch);
        fclose($fp);
        
        return true;
    }

    /**
     * Callback for curl progress 
     * 
     * @param $downloadSize
     * @param $downloaded
     * @param $uploadSize
     * @param $uploaded
     * @return int
     */
    public function _curlProgressFunction($downloadSize, $downloaded, $uploadSize, $uploaded)
    {
        static $fileSizeLimit;
        if ($fileSizeLimit === null) {
            $fileSizeLimit = (int)Yii::app()->options->get('system.importer.file_size_limit', 1024 * 1024 * 1); // 1 mb by default
        }

        // returning non-0 breaks the connection!
        return ($downloaded > $fileSizeLimit) ? 1 : 0;
    }
}