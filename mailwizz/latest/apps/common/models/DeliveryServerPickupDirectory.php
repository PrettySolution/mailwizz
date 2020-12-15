<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * DeliveryServerPickupDirectory
 *
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.3.2
 */

class DeliveryServerPickupDirectory extends DeliveryServer
{
    protected $serverType = 'pickup-directory';

    public $pickup_directory_path;

    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        $rules = array(
            array('pickup_directory_path', 'required'),
            array('pickup_directory_path', '_validateDirectoryPath'),
        );

        return CMap::mergeArray($rules, parent::rules());
    }

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        $labels = array(
            'pickup_directory_path' => Yii::t('servers', 'Pickup directory path')
        );

        return CMap::mergeArray(parent::attributeLabels(), $labels);
    }

    /**
     * Returns the static model of the specified AR class.
     * Please note that you should have this exact method in all your CActiveRecord descendants!
     * @param string $className active record class name.
     * @return DeliveryServer the static model class
     */
    public static function model($className=__CLASS__)
    {
        return parent::model($className);
    }

    public function sendEmail(array $params = array())
    {
        $dirPath = $this->pickup_directory_path;

        static $canWrite;
        if ($canWrite === null) {
            $canWrite = (!empty($dirPath) && file_exists($dirPath) && is_dir($dirPath) && is_writable($dirPath));
        }

        if (!$canWrite) {
            return false;
        }

        $params = (array)Yii::app()->hooks->applyFilters('delivery_server_before_send_email', $this->getParamsArray($params), $this);

        $dirPath  = rtrim($dirPath, '/\\');
        $message  = $this->getMailer()->getEmailMessage($params);
        $filePath = $this->pickup_directory_path . DIRECTORY_SEPARATOR . $this->getMailer()->getEmailMessageId() . '.eml';

        if ($sent = @file_put_contents($filePath, $message)) {
            $sent = array('message_id' => $this->getMailer()->getEmailMessageId());
            $this->getMailer()->addLog('OK');
            $this->logUsage();
        }

        Yii::app()->hooks->doAction('delivery_server_after_send_email', $params, $this, $sent);

        return $sent;
    }

    protected function afterConstruct()
    {
        $this->pickup_directory_path = $this->getModelMetaData()->itemAt('pickup_directory_path');
        parent::afterConstruct();
    }

    protected function afterFind()
    {
        $this->pickup_directory_path = $this->getModelMetaData()->itemAt('pickup_directory_path');
        parent::afterFind();
    }

    public function getParamsArray(array $params = array())
    {
        $params['transport']             = self::TRANSPORT_PICKUP_DIRECTORY;
        $params['pickup_directory_path'] = $this->pickup_directory_path;
        return parent::getParamsArray($params);
    }

    protected function beforeValidate()
    {
        $this->hostname = 'pickup-directory.local.host';
        $this->port     = null;
        $this->timeout  = null;

        return parent::beforeValidate();
    }

    protected function beforeSave()
    {
        $this->getModelMetaData()->add('pickup_directory_path', $this->pickup_directory_path);
        return parent::beforeSave();
    }

    public function attributeHelpTexts()
    {
        $texts = array(
            'pickup_directory_path'    => Yii::t('servers', 'The path where the messages must be saved in order to be picked up by your MTA'),
        );

        return CMap::mergeArray(parent::attributeHelpTexts(), $texts);
    }

    public function attributePlaceholders()
    {
        $placeholders = array(
            'pickup_directory_path' => Yii::t('servers', 'i.e: /home/username/pickup'),
        );

        return CMap::mergeArray(parent::attributePlaceholders(), $placeholders);
    }

    public function _validateDirectoryPath($attribute, $params)
    {
        if (empty($this->$attribute)) {
            $this->addError($attribute, Yii::t('servers', 'The attribute "{attribute}" cannot be blank!', array(
                '{attribute}' => $this->getAttributeLabel($attribute),
            )));
            return;
        }

        $directory = @realpath($this->$attribute);
        if (empty($directory) || !file_exists($directory) || !is_dir($directory) || !is_writable($directory)) {
            $this->addError($attribute, Yii::t('servers', 'The directory "{dir}" must exist and be writable by the web server process!', array(
                '{dir}' => $this->$attribute,
            )));
            return;
        }

        $this->$attribute = $directory;
    }

    /**
     * @return bool
     */
    public function getCanEmbedImages()
    {
        return true;
    }

    /**
     * @param array $params
     * @return array
     */
    public function getFormFieldsDefinition(array $params = array())
    {
        $form = new CActiveForm();
        return parent::getFormFieldsDefinition(CMap::mergeArray(array(
            'hostname'                => null,
            'username'                => null,
            'password'                => null,
            'port'                    => null,
            'protocol'                => null,
            'timeout'                 => null,
            'max_connection_messages' => null,
            'pickup_directory_path'   => array(
                'visible'   => true,
                'fieldHtml' => $form->textField($this, 'pickup_directory_path', $this->getHtmlOptions('pickup_directory_path')),
            )
        ), $params));
    }
}
