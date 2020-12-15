<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * DeliveryServerSendmail
 *
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.3.2
 */

class DeliveryServerSendmail extends DeliveryServer
{
    /**
     * @var string
     */
    protected $serverType = 'sendmail';

    /**
     * @var string
     */
    public $sendmail_path = '/usr/sbin/sendmail';

    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        $rules = array(
            array('sendmail_path', 'required'),
        );

        return CMap::mergeArray($rules, parent::rules());
    }

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        $labels = array(
            'sendmail_path' => Yii::t('servers', 'Sendmail path'),
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

    /**
     * @param array $params
     * @return array
     */
    public function sendEmail(array $params = array())
    {
        $params = (array)Yii::app()->hooks->applyFilters('delivery_server_before_send_email', $this->getParamsArray($params), $this);

        if ($sent = $this->getMailer()->send($params)) {
            $sent = array('message_id' => $this->getMailer()->getEmailMessageId());
            $this->logUsage();
        }

        Yii::app()->hooks->doAction('delivery_server_after_send_email', $params, $this, $sent);

        return $sent;
    }

    /**
     * @inheritdoc
     */
    protected function afterConstruct()
    {
        if ($path = $this->getModelMetaData()->itemAt('sendmail_path')) {
            $this->sendmail_path = $path;
        }

        parent::afterConstruct();
    }

    /**
     * @inheritdoc
     */
    protected function afterFind()
    {
        if ($path = $this->getModelMetaData()->itemAt('sendmail_path')) {
            $this->sendmail_path = $path;
        }

        parent::afterFind();
    }

    /**
     * @param array $params
     * @return array
     */
    public function getParamsArray(array $params = array())
    {
        $params['transport']    = self::TRANSPORT_SENDMAIL;
        $params['sendmailPath'] = $this->sendmail_path;
        return parent::getParamsArray($params);
    }

    /**
     * @inheritdoc
     */
    protected function beforeValidate()
    {
        $this->hostname = 'sendmail.local.host';
        $this->port     = null;
        $this->timeout  = null;

        return parent::beforeValidate();
    }

    /**
     * @inheritdoc
     */
    protected function beforeSave()
    {
        $this->getModelMetaData()->add('sendmail_path', $this->sendmail_path);
        return parent::beforeSave();
    }

    /**
     * @return array
     */
    public function attributeHelpTexts()
    {
        $texts = array(
            'sendmail_path'    => Yii::t('servers', 'The path to the sendmail executable, usually "{path}"', array('{path}' => '/usr/sbin/sendmail')),
        );

        return CMap::mergeArray(parent::attributeHelpTexts(), $texts);
    }

    /**
     * @return array
     */
    public function attributePlaceholders()
    {
        $placeholders = array(
            'sendmail_path'    => Yii::t('servers', 'i.e: /usr/sbin/sendmail'),
        );

        return CMap::mergeArray(parent::attributePlaceholders(), $placeholders);
    }

    /**
     * @return bool|string
     */
    public function requirementsFailed()
    {
        if (!CommonHelper::functionExists('proc_open') && !CommonHelper::functionExists('popen')) {
            return Yii::t('servers', 'The server type {type} requires following functions to be active on your host: {functions}!', array(
                '{type}'      => $this->serverType,
                '{functions}' => 'proc_open ' . Yii::t('app', 'or') . ' popen',
            ));
        }
        return parent::requirementsFailed();
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
            'sendmail_path'           => array(
                'visible'   => true,
                'fieldHtml' => $form->textField($this, 'sendmail_path', $this->getHtmlOptions('sendmail_path')),
            )
        ), $params));
    }
}
