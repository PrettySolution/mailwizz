<?php if ( ! defined('MW_PATH')) exit('No direct script access allowed');

/**
 * MailerSwiftMailer
 *
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.3.4.2
 */

class MailerSwiftMailer extends MailerAbstract
{
    private $_transport;

    private $_message;

    private $_mailer;

    private $_loggerPlugin;

    private $_antiFloodPlugin;

    private $_throttlePlugin;

    /**
     * MailerSwiftMailer::init()
     *
     * @return
     */
    public function init()
    {
    	if (MW_COMPOSER_SUPPORT) { 
            Yii::import('common.vendors.SwiftMailer-5x.lib.classes.Swift', true);
            Yii::registerAutoloader(array('Swift', 'autoload'));
            Yii::import('common.vendors.SwiftMailer-5x.lib.swift_init', true);
        } else {
            Yii::import('common.vendors.SwiftMailer.lib.classes.Swift', true);
            Yii::registerAutoloader(array('Swift', 'autoload'));
            Yii::import('common.vendors.SwiftMailer.lib.swift_init', true);
        }
        
        parent::init();
    }

    /**
     * MailerSwiftMailer::send()
     *
     * Implements the parent abstract method
     *
     * @param mixed $params
     * @return bool
     */
    public function send($params = array())
    {
        // params
        $params = new CMap($params);

        // since 1.3.6.7
        if ((int)$params->itemAt('maxConnectionMessages') > 1) {

            $serverId = (int)$params->itemAt('server_id');
            
            if ($serverId == 0 || $this->_deliveryServerId != $serverId) {
                $this->reset();
            } else {
                $this->resetMessage()->clearLogs();
            }
            
            $this->_deliveryServerId = $serverId;

        } else {

            $this->reset();

        }
        
        $this->clearLogs()->setTransport($params)->setMessage($params);

        if (!$this->getTransport() || !$this->getMessage()) {
            return false;
        }

        // since 1.3.5.3
        Yii::app()->hooks->doAction('mailer_before_send_email', $this, $params->toArray());
        if ($this->denySending === true) {
            return false;
        }
        
        try {
            if ($sent = (bool)$this->getMailer()->send($this->getMessage())) {
                $this->addLog('OK');
            } else {
                if ($this->getLoggerPlugin()) {
                    $this->addLog($this->getLoggerPlugin()->dump());
                } else {
                    $this->addLog('NOT OK, UNKNOWN ERROR!');
                }
            }
        } catch (Exception $e) {
            $sent = false;
            $this->addLog($e->getMessage());
        }

        // since 1.3.5.3
        Yii::app()->hooks->doAction('mailer_after_send_email', $this, $params->toArray(), $sent);

        $this->_sentCounter++;
        
        // reset
        if ($this->_sentCounter >= (int)$params->itemAt('maxConnectionMessages')) {
            $this->reset(false);
        } else {
            $this->resetMessage();
        }

        return $sent;
    }

    /**
     * MailerSwiftMailer::getEmailMessage()
     *
     * Implements the parent abstract method
     *
     * @param mixed $params
     * @return mixed
     */
    public function getEmailMessage($params = array())
    {
        return $this->reset()->setMessage(new CMap($params))->getMessage()->toString();
    }

    /**
     * MailerSwiftMailer::reset()
     *
     * Implements the parent abstract method
     *
     * @return MailerSwiftMailer
     */
    public function reset($resetLogs = true)
    {
        $this->resetTransport()->resetMessage()->resetMailer()->resetPlugins();

        if ($resetLogs) {
            $this->clearLogs();
        }

        return $this;
    }

    /**
     * MailerSwiftMailer::getName()
     *
     * Implements the parent abstract method
     *
     * @return string
     */
    public function getName()
    {
        return 'SwiftMailer';
    }

    /**
     * MailerSwiftMailer::getDescription()
     *
     * Implements the parent abstract method
     *
     * @return string
     */
    public function getDescription()
    {
        return Yii::t('mailer', 'A fully compliant mailer.');
    }

    /**
     * MailerSwiftMailer::setTransport()
     *
     * @param mixed $params
     * @return mixed
     */
    protected function setTransport(CMap $params)
    {
        if ($this->_transport !== null) {
            return $this;
        }

        $this->resetTransport()->resetMailer();

        if (!($transport = $this->buildTransport($params))) {
            return $this;
        }

        $this->_transport = $transport;
        $this->_mailer    = Swift_Mailer::newInstance($transport);

        $plugins = isset($params['mailerPlugins']) ? $params['mailerPlugins'] : array();
        $plugins['loggerPlugin'] = true;

        if (!$this->getLoggerPlugin() && isset($plugins['loggerPlugin']) && $plugins['loggerPlugin']) {
            if (is_object($plugins['loggerPlugin']) && $plugins['loggerPlugin'] instanceof Swift_Plugins_LoggerPlugin) {
                $this->setLoggerPlugin($plugins['loggerPlugin']);
            } else {
                $this->setLoggerPlugin(new Swift_Plugins_LoggerPlugin(new Swift_Plugins_Loggers_ArrayLogger()));
            }
        }

        if ($plugin = $this->getLoggerPlugin()) {
            $this->_mailer->registerPlugin($plugin);
        }

        if (!$this->getAntiFloodPlugin() && isset($plugins['antiFloodPlugin']) && (is_array($plugins['antiFloodPlugin']) || is_object($plugins['antiFloodPlugin']))) {
            $data = $plugins['antiFloodPlugin'];
            if (is_object($data) && $data instanceof Swift_Plugins_AntiFloodPlugin) {
                $this->setAntiFloodPlugin($data);
            } else {
                $sendAtOnce = isset($data['sendAtOnce']) && $data['sendAtOnce'] > 0 ? $data['sendAtOnce'] : 100;
                $pause      = isset($data['pause']) && $data['pause'] > 0 ? $data['pause'] : 30;
                $this->setAntiFloodPlugin(new Swift_Plugins_AntiFloodPlugin($sendAtOnce, $pause));
            }
        }

        if ($plugin = $this->getAntiFloodPlugin()) {
            $this->_mailer->registerPlugin($plugin);
        }

        if (!$this->getThrottlePlugin() && isset($plugins['throttlePlugin']) && (is_array($plugins['throttlePlugin']) || is_object($plugins['throttlePlugin']))) {
            $data = $plugins['throttlePlugin'];
            if (is_object($data) && $data instanceof Swift_Plugins_ThrottlerPlugin) {
                $this->setThrottlePlugin($data);
            } else {
                $perMinute = isset($data['perMinute']) && $data['perMinute'] > 0 ? $data['perMinute'] : 60;
                $this->setThrottlePlugin(new Swift_Plugins_ThrottlerPlugin($perMinute, Swift_Plugins_ThrottlerPlugin::MESSAGES_PER_MINUTE));
            }
        }

        if ($plugin = $this->getThrottlePlugin()) {
            $this->_mailer->registerPlugin($plugin);
        }

        // since 1.3.5.3
        $this->_transport = Yii::app()->hooks->applyFilters('mailer_after_create_transport_instance', $this->_transport, $params->toArray(), $this);
        $this->_mailer    = Yii::app()->hooks->applyFilters('mailer_after_create_mailer_instance', $this->_mailer, $params->toArray(), $this);

        return $this;
    }

    /**
     * MailerSwiftMailer::setMessage()
     *
     * @param mixed $params
     * @return mixed
     */
    protected function setMessage(CMap $params)
    {
        $this->resetMessage();
	    
        $requiredKeys = array('to', 'from', 'subject');
        foreach ($requiredKeys as $key) {
            if (!$params->itemAt($key)) {
                return $this;
            }
        }
	    
        if (!$params->itemAt('body') && !$params->itemAt('plainText')) {
            return $this;
        }
		
        list($fromEmail, $fromName)         = $this->findEmailAndName($params->itemAt('from'));
        list($toEmail, $toName)             = $this->findEmailAndName($params->itemAt('to'));
        list($replyToEmail, $replyToName)   = $this->findEmailAndName($params->itemAt('replyTo'));
        
        if ($params->itemAt('fromName') && is_string($params->itemAt('fromName'))) {
            $fromName = $params->itemAt('fromName');
        }

        if ($params->itemAt('toName') && is_string($params->itemAt('toName'))) {
            $toName = $params->itemAt('toName');
        }

        if ($params->itemAt('replyToName') && is_string($params->itemAt('replyToName'))) {
            $replyToName = $params->itemAt('replyToName');
        }

        // dmarc policy...
        if (!$this->isCustomFromDomainAllowed($this->getDomainFromEmail($fromEmail))) {
            $fromEmail = $params->itemAt('username');
        }

        if (!FilterVarHelper::email($fromEmail)) {
            $fromEmail = $params->itemAt('from_email');
        }
        
        $replyToName  = empty($replyToName)  ? $fromName   : $replyToName;
        $replyToEmail = empty($replyToEmail) ? $fromEmail  : $replyToEmail;
        $returnEmail  = FilterVarHelper::email($params->itemAt('returnPath')) ? $params->itemAt('returnPath') : $params->itemAt('from_email');
        $returnEmail  = FilterVarHelper::email($returnEmail) ? $returnEmail : $fromEmail;
        $returnDomain = $this->getDomainFromEmail($returnEmail, 'local.host');

        // since 1.3.4.7
        $message  = null;
        $signer   = null;
        $dkimSign = $params->itemAt('signingEnabled') && $params->itemAt('dkimPrivateKey') && $params->itemAt('dkimDomain') && $params->itemAt('dkimSelector');
        if ($dkimSign && version_compare(PHP_VERSION, '5.3', '>=')) {
            $message = Swift_SignedMessage::newInstance();
            $signer  = new Swift_Signers_DKIMSigner($params->itemAt('dkimPrivateKey'), $params->itemAt('dkimDomain'), $params->itemAt('dkimSelector'));
            $signer->ignoreHeader('Return-Path')->ignoreHeader('Sender');
            $signer->setHeaderCanon('relaxed');
            $signer->setBodyCanon('relaxed');
            
            // since 1.5.2
            try {
                $signer->setHashAlgorithm('rsa-sha256');
            } catch (Exception $e) {
                $signer->setHashAlgorithm('rsa-sha1');
            }
            
            $message->attachSigner($signer);
        }

        if (empty($message)) {
            $message = Swift_Message::newInstance();
        }

        $message->setCharset(Yii::app()->charset);
        $message->setEncoder(Swift_Encoding::getQpEncoding()); // since 1.3.6.0
        $message->setMaxLineLength(990);
        $message->setId(md5(StringHelper::uniqid() . StringHelper::uniqid() . StringHelper::uniqid()) . '@' . $returnDomain);

        $this->_message   = $message;
        $this->_messageId = str_replace(array('<', '>'), '', $message->getId());

        if ($params->itemAt('headers') && is_array($params->itemAt('headers'))) {
            foreach ($params->itemAt('headers') as $header) {
                if (!is_array($header) || !isset($header['name'], $header['value'])) {
                    continue;
                }
                $message->getHeaders()->addTextHeader($header['name'], $header['value']);
            }
        }
        
        $message->setSubject($params->itemAt('subject'));
        $message->setFrom($fromEmail, $fromName);
        $message->setTo($toEmail, $toName);
        $message->setReplyTo($replyToEmail, $replyToName);
        
        // since 1.7.3
	    $addReturnPath = true;
	    if (isset(Yii::app()->params['email.custom.returnPath.enabled'])) {
		    $addReturnPath = (bool)Yii::app()->params['email.custom.returnPath.enabled'];
	    }
	    if ($addReturnPath) {
		    $message->setReturnPath($returnEmail);
	    }
	    // 
        
        // 1.3.7.1
        if ($params->itemAt('forceSender')) {
            //$message->setSender($returnEmail);
        }
        
        $message->getHeaders()->addTextHeader('X-Sender', $returnEmail);
        $message->getHeaders()->addTextHeader('X-Receiver', $toEmail);
        $message->getHeaders()->addTextHeader(sprintf('%sMailer', Yii::app()->params['email.custom.header.prefix']), 'SwiftMailer - 5.4.x');

        $body           = $params->itemAt('body');
        $plainText      = $params->itemAt('plainText');
        $onlyPlainText  = $params->itemAt('onlyPlainText') === true;

        if (empty($plainText) && !empty($body)) {
            $plainText = CampaignHelper::htmlToText($body);
        }

        if (!empty($plainText) && empty($body)) {
            $body = $plainText;
        }
        
        $embedImages = $params->itemAt('embedImages');
        if (!$onlyPlainText && !empty($embedImages) && is_array($embedImages)) {
            $cids = array();
            foreach ($embedImages as $imageData) {
                if (!isset($imageData['path'], $imageData['cid'])) {
                    continue;
                }
                if (is_file($imageData['path'])) {
                    $cids['cid:' . $imageData['cid']] = $message->embed(Swift_Image::fromPath($imageData['path']));
                }
            }
            if (!empty($cids)) {
                $body = str_replace(array_keys($cids), array_values($cids), $body);
            }
            unset($embedImages, $cids);
        }
        //

        if ($onlyPlainText) {
            $message->setBody($plainText, 'text/plain', Yii::app()->charset);
        } else {
            $message->setBody($body, 'text/html', Yii::app()->charset);
            $message->addPart($plainText, 'text/plain', Yii::app()->charset);
        }

        $attachments = $params->itemAt('attachments');
        if (!$onlyPlainText && !empty($attachments) && is_array($attachments)) {
            $attachments = array_unique($attachments);
            foreach ($attachments as $attachment) {
                if (is_file($attachment)) {
                    $message->attach(Swift_Attachment::fromPath($attachment));
                }
            }
            unset($attachments);
        }
        
        // since 1.3.6.3
        if ($signer) {
            $listHeaders = $message->getHeaders()->listAll();
            foreach ($listHeaders as $hName) {
                if (stripos($hName, 'x-') === 0) {
                    $signer->ignoreHeader($hName);
                }
            }
        }

        // since 1.3.5.3
        $this->_message = Yii::app()->hooks->applyFilters('mailer_after_create_message_instance', $message, $params->toArray(), $this);
       
        return $this;
    }

    /**
     * MailerSwiftMailer::getTransport()
     *
     * @return mixed
     */
    protected function getTransport()
    {
        return $this->_transport;
    }

    /**
     * MailerSwiftMailer::getMessage()
     *
     * @return mixed
     */
    protected function getMessage()
    {
        return $this->_message;
    }

    /**
     * MailerSwiftMailer::getMailer()
     *
     * @return mixed
     */
    protected function getMailer()
    {
        return $this->_mailer;
    }

    /**
     * MailerSwiftMailer::setLoggerPlugin()
     *
     * @param Swift_Plugins_LoggerPlugin $loggerPlugin
     * @return MailerSwiftMailer
     */
    protected function setLoggerPlugin(Swift_Plugins_LoggerPlugin $loggerPlugin)
    {
        $this->_loggerPlugin = $loggerPlugin;
        return $this;
    }

    /**
     * MailerSwiftMailer::getLoggerPlugin()
     *
     * @return mixed
     */
    protected function getLoggerPlugin()
    {
        return $this->_loggerPlugin;
    }

    /**
     * MailerSwiftMailer::setAntiFloodPlugin()
     *
     * @param Swift_Plugins_AntiFloodPlugin $antiFloodPlugin
     * @return MailerSwiftMailer
     */
    protected function setAntiFloodPlugin(Swift_Plugins_AntiFloodPlugin $antiFloodPlugin)
    {
        $this->_antiFloodPlugin = $antiFloodPlugin;
        return $this;
    }

    /**
     * MailerSwiftMailer::getAntiFloodPlugin()
     *
     * @return mixed
     */
    protected function getAntiFloodPlugin()
    {
        return $this->_antiFloodPlugin;
    }

    /**
     * MailerSwiftMailer::setThrottlePlugin()
     *
     * @param Swift_Plugins_ThrottlerPlugin $throttlePlugin
     * @return MailerSwiftMailer
     */
    protected function setThrottlePlugin(Swift_Plugins_ThrottlerPlugin $throttlePlugin)
    {
        $this->_throttlePlugin = $throttlePlugin;
        return $this;
    }

    /**
     * MailerSwiftMailer::getThrottlePlugin()
     *
     * @return mixed
     */
    protected function getThrottlePlugin()
    {
        return $this->_throttlePlugin;
    }

    /**
     * MailerSwiftMailer::resetTransport()
     *
     * @return MailerSwiftMailer
     */
    protected function resetTransport()
    {
        $this->_sentCounter = 0;
        $this->_transport   = null;
        return $this;
    }

    /**
     * MailerSwiftMailer::resetMessage()
     *
     * @return MailerSwiftMailer
     */
    protected function resetMessage()
    {
        $this->_message   = null;
        return $this;
    }

    /**
     * MailerSwiftMailer::resetMailer()
     *
     * @return MailerSwiftMailer
     */
    protected function resetMailer()
    {
        $this->_mailer = null;
        return $this;
    }

    /**
     * MailerSwiftMailer::resetPlugins()
     *
     * @return MailerSwiftMailer
     */
    protected function resetPlugins()
    {
        $this->_loggerPlugin = null;
        $this->_antiFloodPlugin = null;
        $this->_throttlePlugin = null;

        return $this;
    }

    /**
     * MailerSwiftMailer::buildTransport()
     *
     * @param CMap $params
     * @return mixed
     */
    protected function buildTransport(CMap $params)
    {
        if (!$params->itemAt('transport')) {
            $params->add('transport', 'smtp');
        }

        if ($params->itemAt('transport') == 'smtp') {
            return $this->buildSmtpTransport($params);
        }

        if ($params->itemAt('transport') == 'php-mail') {
            return $this->buildPhpMailTransport($params);
        }

        if ($params->itemAt('transport') == 'sendmail') {
            return $this->buildSendmailTransport($params);
        }

        return false;
    }

    /**
     * MailerSwiftMailer::buildSmtpTransport()
     *
     * @param CMap $params
     * @return mixed
     */
    protected function buildSmtpTransport(CMap $params)
    {
        if (!CommonHelper::functionExists('proc_open')) {
            return false;
        }

        $requiredKeys = array('hostname');
        $hasRequiredKeys = true;

        foreach ($requiredKeys as $key) {
            if (!$params->itemAt($key)) {
                $hasRequiredKeys = false;
                break;
            }
        }

        if (!$hasRequiredKeys) {
            return false;
        }

        if (!$params->itemAt('port')) {
            $params->add('port', 25);
        }

        if (!$params->itemAt('timeout')) {
            $params->add('timeout', 30);
        }

        try {
            $transport = Swift_SmtpTransport::newInstance($params->itemAt('hostname'), (int)$params->itemAt('port'), $params->itemAt('protocol'));
            if ($params->itemAt('username')) {
                $transport->setUsername($params->itemAt('username'));
            }
            if ($params->itemAt('password')) {
                $transport->setPassword($params->itemAt('password'));
            }
            $transport->setTimeout((int)$params->itemAt('timeout'));
            
            // because the old swift version does not have this option
            if (method_exists($transport, 'setStreamOptions')) {
                $transport->setStreamOptions(array(
                    'ssl' => array(
                        'allow_self_signed' => true,
                        'verify_peer'       => false,
                        'verify_peer_name'  => false,
                    )
                ));
            }
            
        } catch (Exception $e) {
            $this->addLog($e->getMessage());
            return false;
        }

        return $transport;
    }

    /**
     * MailerSwiftMailer::buildSendmailTransport()
     *
     * @param CMap $params
     * @return mixed
     */
    protected function buildSendmailTransport(CMap $params)
    {
        if (!$params->itemAt('sendmailPath') || !CommonHelper::functionExists('proc_open')) {
            return false;
        }

        $command = $params->itemAt('sendmailPath');
        $command = trim(preg_replace('/\s\-.*/', '', $command));
        $command .= ' -bs';
        $transport = false;

        try {
            $transport = Swift_SendmailTransport::newInstance($command);
        } catch (Exception $e) {
            $this->addLog($e->getMessage());
            $transport = false;
        }

        return $transport;
    }

    /**
     * MailerSwiftMailer::buildPhpMailTransport()
     *
     * @param CMap $params
     * @return mixed
     */
    protected function buildPhpMailTransport(CMap $params)
    {
        if (!CommonHelper::functionExists('mail')) {
            return false;
        }

        $transport = false;

        try {
            $transport = Swift_MailTransport::newInstance();
        } catch (Exception $e) {
            $this->addLog($e->getMessage());
            $transport = false;
        }

        return $transport;
    }

    /**
     * MailerSwiftMailer::clearLogs()
     *
     * @return
     */
    public function clearLogs()
    {
        if ($this->getLoggerPlugin()) {
            $this->getLoggerPlugin()->clear();
        }
        return parent::clearLogs();
    }
}
