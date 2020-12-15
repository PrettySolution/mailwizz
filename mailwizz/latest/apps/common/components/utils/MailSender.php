<?php if ( ! defined('MW_PATH')) exit('No direct script access allowed');

/**
 * MailSender
 * 
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com> 
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.0
 * 
 * THIS CLASS IS DEPRECATED AND WILL BE REMOVED IN FUTURE RELEASES!!!
 * We keep it here because there might be people relying on it!
 * Deprecated since 1.3.4.2
 */
 
class MailSender extends CApplicationComponent
{
    private $_transport;
    
    private $_message;
    
    private $_mailer;
    
    private $_loggerPlugin;
    
    private $_antiFloodPlugin;
    
    private $_throttlePlugin;
    
    protected $_messageId;
    
    protected $_logs = array();
    
    // this is dummy data actually, doesn't do anything, just compatibility with Mailer.php class
    public $mailer;
    
    /**
     * MailSender::init()
     * 
     * @return
     */
    public function init()
    {
        Yii::import('common.vendors.SwiftMailer.lib.classes.Swift', true);
        Yii::registerAutoloader(array('Swift', 'autoload'));
        Yii::import('common.vendors.SwiftMailer.lib.swift_init', true);
        
        parent::init();
    }
    
    /**
     * MailSender::setTransport()
     * 
     * @param mixed $params
     * @return mixed
     */
    public function setTransport($params = array())
    {
        if ($this->_transport !== null) {
            return $this;
        }
        
        $this->resetTransport()->resetMailer();
        
        $params = new CMap($params);
        if (!($transport = $this->buildTransport($params))) {
            return $this;
        }

        $this->_transport = $transport;
        $this->_mailer    = Swift_Mailer::newInstance($transport);

        if (!$params->contains('mailerPlugins') || !is_array($params->itemAt('mailerPlugins'))) {
            return $this;
        }
        
        $plugins = $params->itemAt('mailerPlugins');
        
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

        return $this;
    }

    /**
     * MailSender::setMessage()
     * 
     * @param mixed $params
     * @return mixed
     */
    public function setMessage($params = array())
    {
        $this->resetMessage();
        
        $params = new CMap($params);
        
        if (!$params->contains('from') && $params->contains('username')) {
            $params->add('from', $params->itemAt('username'));
        }
        
        $requiredKeys = array('to', 'from', 'subject');
        foreach ($requiredKeys as $key) {
            if (!$params->contains($key)) {
                return $this;
            }
        }
        
        if (!$params->contains('body') && !$params->contains('plainText')) {
            return $this;
        }
        
        $message = Swift_Message::newInstance();
        
        $this->_message   = $message;
        $this->_messageId = str_replace(array('<', '>'), '', $message->getId());
        
        if ($params->contains('headers') && is_array($params->itemAt('headers'))) {
            foreach ($params->itemAt('headers') as $name => $value) {
                $message->getHeaders()->addTextHeader($name, $value);
            }
        }
        
        if ($params->contains('returnPath')) {
            $message->setReturnPath($params->itemAt('returnPath'));
        }
        
        if ($params->contains('replyTo')) {
            $message->setReplyTo($params->itemAt('replyTo'));
        }

        $body       = $params->itemAt('body');
        $plainText  = $params->itemAt('plainText');
        
        $message->setSubject($params->itemAt('subject'));
        $message->setSender($params->itemAt('from'));
        $message->setFrom($params->itemAt('from'));
        $message->setTo($params->itemAt('to'));
        
        $fromEmail  = $message->getFrom();
        $toEmail    = $message->getTo();
        if (is_array($fromEmail)) {
            foreach ($fromEmail as $email => $name) {
                $fromEmail = $email;
                break;
            }
        }
        if (is_array($toEmail)) {
            foreach ($toEmail as $email => $name) {
                $toEmail = $email;
                break;
            }
        }
        $message->getHeaders()->addTextHeader('X-Sender', $fromEmail);
        $message->getHeaders()->addTextHeader('X-Receiver', $toEmail);
        
        if (!empty($plainText) && !empty($body)) {
            $message->setBody($plainText, 'text/plain', Yii::app()->charset);
            $message->addPart($body, 'text/html', Yii::app()->charset);
        } elseif (!empty($plainText)) {
            $message->setBody($plainText, 'text/plain', Yii::app()->charset);
        } else {
            $message->setBody($body, 'text/html', Yii::app()->charset);
        }
        
        $attachments = $params->itemAt('attachments');
        if (!empty($attachments) && is_array($attachments)) {
            $attachments = array_unique($attachments);
            foreach ($attachments as $attachment) {
                if (is_file($attachment)) {
                    $message->attach(Swift_Attachment::fromPath($attachment));
                }
            }
        }

        return $this;
    }
    
    /**
     * MailSender::getTransport()
     * 
     * @return mixed
     */
    public function getTransport()
    {
        return $this->_transport;
    }
    
    /**
     * MailSender::getMessage()
     * 
     * @return mixed
     */
    public function getMessage()
    {
        return $this->_message;
    }

    /**
     * MailSender::getMailer()
     * 
     * @return mixed
     */
    public function getMailer()
    {
        return $this->_mailer;
    }
    
    /**
     * MailSender::setLoggerPlugin()
     * 
     * @param Swift_Plugins_LoggerPlugin $loggerPlugin
     * @return MailSender
     */
    public function setLoggerPlugin(Swift_Plugins_LoggerPlugin $loggerPlugin)
    {
        $this->_loggerPlugin = $loggerPlugin;
        return $this;
    }
    
    /**
     * MailSender::getLoggerPlugin()
     * 
     * @return mixed
     */
    public function getLoggerPlugin()
    {
        return $this->_loggerPlugin;
    }
    
    /**
     * MailSender::setAntiFloodPlugin()
     * 
     * @param Swift_Plugins_AntiFloodPlugin $antiFloodPlugin
     * @return MailSender
     */
    public function setAntiFloodPlugin(Swift_Plugins_AntiFloodPlugin $antiFloodPlugin)
    {
        $this->_antiFloodPlugin = $antiFloodPlugin;
        return $this;
    }
    
    /**
     * MailSender::getAntiFloodPlugin()
     * 
     * @return mixed
     */
    public function getAntiFloodPlugin()
    {
        return $this->_antiFloodPlugin;
    }
    
    /**
     * MailSender::setThrottlePlugin()
     * 
     * @param Swift_Plugins_ThrottlerPlugin $throttlePlugin
     * @return MailSender
     */
    public function setThrottlePlugin(Swift_Plugins_ThrottlerPlugin $throttlePlugin)
    {
        $this->_throttlePlugin = $throttlePlugin;
        return $this;
    }
    
    /**
     * MailSender::getThrottlePlugin()
     * 
     * @return mixed
     */
    public function getThrottlePlugin()
    {
        return $this->_throttlePlugin;
    }
    
    /**
     * MailSender::reset()
     * 
     * @return MailSender
     */
    public function reset()
    {
        return $this->resetTransport()->resetMessage()->resetMailer()->resetPlugins()->clearLogs();
    }
    
    /**
     * MailSender::resetTransport()
     * 
     * @return MailSender
     */
    public function resetTransport()
    {
        $this->_transport = null;
        return $this;
    }
    
    /**
     * MailSender::resetMessage()
     * 
     * @return MailSender
     */
    public function resetMessage()
    {
        $this->_messageId = null;
        $this->_message = null;
        return $this;
    }
    
    /**
     * MailSender::resetMailer()
     * 
     * @return MailSender
     */
    public function resetMailer()
    {
        $this->_mailer = null;
        return $this;
    }
    
    /**
     * MailSender::resetPlugins()
     * 
     * @return MailSender
     */
    public function resetPlugins()
    {
        $this->_loggerPlugin = null;
        $this->_antiFloodPlugin = null;
        $this->_throttlePlugin = null;
        
        return $this;
    }
    
    /**
     * MailSender::send()
     * 
     * @param mixed $params
     * @param bool $reset
     * @return bool
     */
    public function send($params = array())
    {
        $this->clearLogs()->setTransport($params)->setMessage($params);
        
        if (!$this->getTransport() || !$this->getMessage()) {
            return false;            
        }
        
        try {
            $sent = (bool)$this->getMailer()->send($this->getMessage());
            if ($this->getLoggerPlugin()) {
                $this->addLog($this->getLoggerPlugin()->dump());
            }  
        } catch (Exception $e) {
            $sent = false;
            $this->addLog($e->getMessage());
        }
        
        return $sent;    
    }  
    
    /**
     * MailSender::getEmailMessage()
     * 
     * @param mixed $params
     * @return string
     */
    public function getEmailMessage(array $params = array())
    {
        return $this->reset()->setMessage($params)->getMessage()->toString();
    } 
    
    /**
     * MailSender::buildTransport()
     * 
     * @param CMap $params
     * @return mixed
     */
    protected function buildTransport(CMap $params)
    {
        if (!$params->contains('transport')) {
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
     * MailSender::buildSmtpTransport()
     * 
     * @param CMap $params
     * @return mixed
     */
    protected function buildSmtpTransport(CMap $params)
    {
        if (!function_exists('proc_open')) {
            return false;
        }
        
        $requiredKeys = array('hostname', 'username', 'password');
        $hasRequiredKeys = true;
        
        foreach ($requiredKeys as $key) {
            if (!$params->contains($key)) {
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
        } catch (Exception $e) {
            $this->addLog($e->getMessage());
            return false;
        }
        
        $transport->setUsername($params->itemAt('username'));
        $transport->setPassword($params->itemAt('password'));
        $transport->setTimeout((int)$params->itemAt('timeout'));
        
        return $transport;
    }
    
    /**
     * MailSender::buildSendmailTransport()
     * 
     * @param CMap $params
     * @return mixed
     */
    protected function buildSendmailTransport(CMap $params)
    {
        if (!$params->contains('sendmailPath') || !function_exists('proc_open')) {
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
     * MailSender::buildPhpMailTransport()
     * 
     * @param CMap $params
     * @return mixed
     */
    protected function buildPhpMailTransport(CMap $params)
    {
        if (!function_exists('mail')) {
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
     * MailSender::getEmailMessageId()
     * 
     * @return
     */
    public function getEmailMessageId()
    {
        return $this->_messageId;
    }
    
    /**
     * MailSender::addLog()
     * 
     * @param mixed $log
     * @return
     */
    public function addLog($log)
    {
        $this->_logs[] = $log;
        return $this;
    }
    
    /**
     * MailSender::getLogs()
     * 
     * @param bool $clear
     * @return
     */
    public function getLogs($clear = true)
    {
        $logs = $this->_logs;
        if ($clear) {
            $this->clearLogs();
        }
        return $logs;
    }
    
    /**
     * MailSender::getLog()
     * 
     * @param string $glue
     * @param bool $clear
     * @return
     */
    public function getLog($glue = "\n", $clear = true)
    {
        return implode($glue, $this->getLogs($clear));
    }
    
    /**
     * MailSender::clearLogs()
     * 
     * @return
     */
    public function clearLogs()
    {
        $this->_logs = array();
        return $this;
    }      
}