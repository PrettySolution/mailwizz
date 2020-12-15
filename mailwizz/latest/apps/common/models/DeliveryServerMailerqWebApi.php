<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * DeliveryServerMailerqWebApi
 *
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.3.5.9
 *
 */

class DeliveryServerMailerqWebApi extends DeliveryServer
{
    protected $serverType = 'mailerq-web-api';

    protected $hasConnection = false;

    public $vhost = '/';

    public $exchange = 'mailerq';

    public $exchange_type = 'direct';

    public $queue = 'outbox';

    public $assigned_ips = '';

    public $ip_to_domains = '';
    
    // since 1.3.6.1
    public $canConfirmDelivery = true;

    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        $rules = array(
            array('username, password, port, vhost, exchange, exchange_type, queue', 'required'),
            array('username, password, port, vhost, exchange, exchange_type, queue', 'length', 'max' => 255),
            array('ip_to_domains, assigned_ips', 'safe'),
        );
        return CMap::mergeArray($rules, parent::rules());
    }

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        $labels = array(
            'vhost'         => Yii::t('servers', 'Virtual host'),
            'exchange'      => Yii::t('servers', 'Exchange name'),
            'exchange_type' => Yii::t('servers', 'Exchange type'),
            'queue'         => Yii::t('servers', 'Queue name'),
            'assigned_ips'  => Yii::t('servers', 'Assigned ips'),
            'ip_to_domains' => Yii::t('servers', 'Ip to domains'),
        );
        return CMap::mergeArray(parent::attributeLabels(), $labels);
    }

    public function attributeHelpTexts()
    {
        $texts = array(
            'bounce_server_id'  => Yii::t('servers', 'The server that will handle bounce emails for this Mailerq server.'),
            'hostname'          => '',
            'username'          => '',
            'port'              => '',
            'password'          => '',
            'vhost'             => '',
            'exchange'          => '',
            'exchange_type'     => '',
            'queue'             => '',
            'assigned_ips'      => '',
            'ip_to_domains'     => '',
        );
        return CMap::mergeArray(parent::attributeHelpTexts(), $texts);
    }

    public function attributePlaceholders()
    {
        $placeholders = array(
            'hostname'      => 'mailerq.domain.com',
            'username'      => '',
            'password'      => '',
            'port'          => 5672,
            'vhost'         => '/',
            'exchange'      => 'mailerq',
            'exchange_type' => 'direct',
            'queue'         => 'outbox',
            'assigned_ips'  => '123.123.123.123, 12.12.12.12, 100.1.100.1',
            'ip_to_domains' => json_encode(array(
                '11.11.11.11' => array('yahoo.*', 'gmail.*'),
                '11.11.11.12' => array('hotmail.*', 'outlook.*'),
            )),
        );
        return CMap::mergeArray(parent::attributePlaceholders(), $placeholders);
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

        if (!ArrayHelper::hasKeys($params, array('from', 'to', 'subject', 'body'))) {
            return false;
        }

        list($toEmail)   = $this->getMailer()->findEmailAndName($params['to']);
        list($fromEmail) = $this->getMailer()->findEmailAndName($params['from']);

        $sent = false;

        try {

            $channel = $this->getConnection()->channel();

            $channel->queue_declare($this->queue, false, true, false, false);
            $channel->exchange_declare($this->exchange, $this->exchange_type, false, true, false);
            $channel->queue_bind($this->queue, $this->exchange);

            $className  = '\PhpAmqpLib\Message\AMQPMessage';
            $message    = $this->getMailer()->getEmailMessage($params);
            $domainName = explode('@', $toEmail);
            $domainName = $domainName[1];

            $ips = explode(',', $this->assigned_ips);
            $ips = array_map('trim', $ips);
            $ips = array_unique($ips);
            foreach ($ips as $index => $ip) {
                if (!FilterVarHelper::ip($ip)) {
                    unset($ips[$index]);
                }
            }

            $ipToDomains = $this->getIpToDomains();
            $tempIps = array();
            foreach ($ipToDomains as $ip => $domainsRegex) {
                if (!FilterVarHelper::ip($ip)) {
                    continue;
                }
                foreach ($domainsRegex as $domainRegex) {
                    if (preg_match('#' . preg_quote($domainRegex, '/') . '#six', $domainName)) {
                        $tempIps[] = $ip;
                    }
                }
            }
            if (!empty($tempIps)) {
                $ips = array_unique($tempIps);
            }

            $sendData = array(
                'domain'    => $domainName,
                'key'	    => $this->getMailer()->getEmailMessageId(),
                'keepmime'  => 1,
                'envelope'  => $fromEmail,
                'recipient' => $toEmail,
                'mime'      => $message,
                'ips'       => $ips,
            );

            if (!empty($params['headers'])) {
                $headers = $this->parseHeadersIntoKeyValue($params['headers']);
                $headerPrefix = Yii::app()->params['email.custom.header.prefix'];
                foreach (array('subscriber', 'campaign', 'customer') as $key) {
                    $headerKey = $headerPrefix . ucfirst($key) . '-Uid';
                    if (isset($headers[$headerKey])) {
                        $sendData[$key . '_uid'] = $headers[$headerKey];
                    }
                }
            }

            if (empty($sendData['customer_uid']) && !MW_IS_CLI && Yii::app()->hasComponent('customer') &&
                Yii::app()->customer->getId() && ($_customer = Yii::app()->customer->getModel())) {
                $sendData['customer_uid'] = $_customer->customer_uid;
            }

            foreach ($sendData as $key => $val) {
                if (empty($val)) {
                    unset($sendData[$key]);
                }
            }

            $msg = new $className(json_encode($sendData), array('content_type' => 'application/json'));

            $channel->basic_publish($msg, $this->exchange, $this->queue);
            $channel->close();

            $sent = true;
        } catch (Exception $e) {
            $sent = false;
            $this->getMailer()->addLog($e->getMessage());
        }

        if ($sent) {
            $this->logUsage();
        }

        Yii::app()->hooks->doAction('delivery_server_after_send_email', $params, $this, $sent);

        return $sent;
    }

    public function getParamsArray(array $params = array())
    {
        $params['transport'] = self::TRANSPORT_MAILERQ_WEB_API;
        return parent::getParamsArray($params);
    }

    public function requirementsFailed()
    {
        if (!MW_COMPOSER_SUPPORT || !version_compare(PHP_VERSION, '5.3.1', '>=')) {
            return Yii::t('servers', 'The server type {type} requires your php version to be at least {version}!', array(
                '{type}'    => $this->serverType,
                '{version}' => '5.3.1',
            ));
        }
        return false;
    }

    public function getIpToDomains()
    {
        static $data = array();
        $id = (int)$this->server_id;
        if (!empty($data[$id])) {
            return $data[$id];
        }
        return $data[$id] = (!empty($this->ip_to_domains) && ($results = @json_decode($this->ip_to_domains, true)) && is_array($results)) ? $results : array();
    }

    public function getConnection()
    {
        static $data = array();
        $id = (int)$this->server_id;
        if (!empty($data[$id])) {
            $this->hasConnection = true;
            return $data[$id];
        }

        $className = '\PhpAmqpLib\Connection\AMQPStreamConnection';
        $connection = new $className($this->hostname, $this->port, $this->username, $this->password, $this->vhost);
        $this->hasConnection = true;
        return $data[$id] = $connection;
    }

    public function __destruct()
    {
        if ($this->hasConnection) {
            try {
                $this->getConnection()->close();
            } catch (Exception $e) {}
        }
    }

    protected function afterConstruct()
    {
        parent::afterConstruct();
        $this->port = 5672;
    }

    protected function afterFind()
    {
        $this->vhost         = $this->getModelMetaData()->itemAt('vhost');
        $this->queue         = $this->getModelMetaData()->itemAt('queue');
        $this->exchange      = $this->getModelMetaData()->itemAt('exchange');
        $this->exchange_type = $this->getModelMetaData()->itemAt('exchange_type');
        $this->assigned_ips  = $this->getModelMetaData()->itemAt('assigned_ips');
        $this->ip_to_domains = $this->getModelMetaData()->itemAt('ip_to_domains');
        parent::afterFind();
    }

    protected function beforeSave()
    {
        $results = @json_decode($this->ip_to_domains, true);
        $this->ip_to_domains = '';
        if (is_array($results)) {
            foreach ($results as $ipAddress => $domains) {
                if (!FilterVarHelper::ip($ipAddress) || !is_array($domains)) {
                    unset($results[$ipAddress]);
                    continue;
                }
                foreach ($domains as $index => $domain) {
                    if (!is_string($domain)) {
                        unset($domains[$domain]);
                    }
                }
                if (empty($domains)) {
                    unset($results[$ipAddress]);
                    continue;
                }
            }
            $this->ip_to_domains = json_encode($results);
        }

        $this->getModelMetaData()->add('vhost', $this->vhost);
        $this->getModelMetaData()->add('queue', $this->queue);
        $this->getModelMetaData()->add('exchange', $this->exchange);
        $this->getModelMetaData()->add('exchange_type', $this->exchange_type);
        $this->getModelMetaData()->add('assigned_ips', $this->assigned_ips);
        $this->getModelMetaData()->add('ip_to_domains', $this->ip_to_domains);
        return parent::beforeSave();
    }

    /**
     * @param array $params
     * @return array
     */
    public function getFormFieldsDefinition(array $params = array())
    {
        $form = new CActiveForm();
        return parent::getFormFieldsDefinition(CMap::mergeArray(array(
            'timeout'                 => null,
            'max_connection_messages' => null,
            'force_sender'            => null,
            'protocol'                => null,
            'vhost' => array(
                'visible'   => true,
                'fieldHtml' => $form->textField($this, 'vhost', $this->getHtmlOptions('vhost')),
            ),
            'queue' => array(
                'visible'   => true,
                'fieldHtml' => $form->textField($this, 'queue', $this->getHtmlOptions('queue')),
            ),
            'exchange' => array(
                'visible'   => true,
                'fieldHtml' => $form->textField($this, 'exchange', $this->getHtmlOptions('exchange')),
            ),
            'exchange_type' => array(
                'visible'   => true,
                'fieldHtml' => $form->textField($this, 'exchange_type', $this->getHtmlOptions('exchange_type')),
            ),
            'assigned_ips' => array(
                'visible'   => true,
                'fieldHtml' => $form->textField($this, 'assigned_ips', $this->getHtmlOptions('assigned_ips')),
            ),
            'ip_to_domains' => array(
                'visible'   => true,
                'fieldHtml' => $form->textField($this, 'ip_to_domains', $this->getHtmlOptions('ip_to_domains')),
            ),
            'must_confirm_delivery' => array(
                'visible'   => $this->canConfirmDelivery,
                'fieldHtml' => $form->dropDownList($this, 'must_confirm_delivery', $this->getYesNoOptions(), $this->getHtmlOptions('must_confirm_delivery')),
            ),
        ), $params));
    }
}
