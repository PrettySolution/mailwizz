<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * DeliveryServerSmtp
 * 
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com> 
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.0
 */
 
class DeliveryServerSmtp extends DeliveryServer
{
    protected $serverType = 'smtp';
    
    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        $rules = array(
            array('port, timeout', 'required'),
        );
        
        return CMap::mergeArray($rules, parent::rules());
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
        $params = (array)Yii::app()->hooks->applyFilters('delivery_server_before_send_email', $this->getParamsArray($params), $this);
        
        if ($sent = $this->getMailer()->send($params)) {
            $sent = array('message_id' => $this->getMailer()->getEmailMessageId());
            $this->logUsage();
        }
        
        Yii::app()->hooks->doAction('delivery_server_after_send_email', $params, $this, $sent);
        
        return $sent;
    }
    
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
}
