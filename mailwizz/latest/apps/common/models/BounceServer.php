<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * BounceServer
 *
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.0
 */

/**
 * This is the model class for table "bounce_server".
 *
 * The followings are the available columns in table 'bounce_server':
 * @property integer $server_id
 * @property integer $customer_id
 * @property string $hostname
 * @property string $username
 * @property string $password
 * @property string $email
 * @property string $service
 * @property integer $port
 * @property string $protocol
 * @property string $validate_ssl
 * @property string $locked
 * @property string $status
 * @property string $date_added
 * @property string $last_updated
 *
 * The followings are the available model relations:
 * @property DeliveryServer[] $deliveryServers
 * @property Customer $customer
 */
class BounceServer extends ActiveRecord
{
    /**
     * @var bool 
     */
    public $settingsChanged = false;

    /**
     * @var string 
     */
    public $mailBox = 'INBOX';

    /**
     * Flag
     */
    const STATUS_CRON_RUNNING = 'cron-running';

    /**
     * Flag
     */
    const STATUS_HIDDEN = 'hidden';

    /**
     * Flag
     */
    const STATUS_DISABLED = 'disabled';

    /**
     * @return string the associated database table name
     */
    public function tableName()
    {
        return '{{bounce_server}}';
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return CMap::mergeArray(array(
            'passwordHandler' => array(
                'class' => 'common.components.db.behaviors.RemoteServerPasswordHandlerBehavior'
            ),
        ), parent::behaviors());
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        $rules = array(
            array('hostname, username, password, port, service, protocol, validate_ssl', 'required'),

            array('hostname, username', 'length', 'min' => 3, 'max'=>150),
            array('password', 'length', 'min' => 3, 'max' => 255),
            array('email', 'email', 'validateIDN' => true),
            array('port', 'numerical', 'integerOnly'=>true),
            array('port', 'length', 'min'=> 2, 'max' => 5),
            array('protocol', 'in', 'range' => array_keys($this->getProtocolsArray())),
            array('customer_id', 'exist', 'className' => 'Customer', 'attributeName' => 'customer_id', 'allowEmpty' => true),
            array('locked', 'in', 'range' => array_keys($this->getYesNoOptions())),

            // since 1.3.5.5
            array('disable_authenticator, search_charset', 'length', 'max' => 50),
            array('delete_all_messages', 'in', 'range' => array_keys($this->getYesNoOptions())),
            //

            array('hostname, username, service, port, protocol, status, customer_id', 'safe', 'on' => 'search'),
        );

        return CMap::mergeArray($rules, parent::rules());
    }

    /**
     * @inheritdoc
     */
    public function relations()
    {
        $relations = array(
            'deliveryServers'   => array(self::HAS_MANY, 'DeliveryServer', 'bounce_server_id'),
            'customer'          => array(self::BELONGS_TO, 'Customer', 'customer_id'),
        );

        return CMap::mergeArray($relations, parent::relations());
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        $labels = array(
            'server_id'     => Yii::t('servers', 'Server'),
            'customer_id'   => Yii::t('servers', 'Customer'),
            'hostname'      => Yii::t('servers', 'Hostname'),
            'username'      => Yii::t('servers', 'Username'),
            'password'      => Yii::t('servers', 'Password'),
            'email'         => Yii::t('servers', 'Email'),
            'service'       => Yii::t('servers', 'Service'),
            'port'          => Yii::t('servers', 'Port'),
            'protocol'      => Yii::t('servers', 'Protocol'),
            'validate_ssl'  => Yii::t('servers', 'Validate ssl'),
            'locked'        => Yii::t('servers', 'Locked'),

            // since 1.3.5.5
            'disable_authenticator' => Yii::t('servers', 'Disable authenticator'),
            'search_charset'        => Yii::t('servers', 'Search charset'),
            'delete_all_messages'   => Yii::t('servers', 'Delete all messages'),
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
        $criteria=new CDbCriteria;

        if (!empty($this->customer_id)) {
            if (is_numeric($this->customer_id)) {
                $criteria->compare('t.customer_id', $this->customer_id);
            } else {
                $criteria->with = array(
                    'customer' => array(
                        'joinType'  => 'INNER JOIN',
                        'condition' => 'CONCAT(customer.first_name, " ", customer.last_name) LIKE :name',
                        'params'    => array(
                            ':name'    => '%' . $this->customer_id . '%',
                        ),
                    )
                );
            }
        }

        $criteria->compare('t.hostname', $this->hostname, true);
        $criteria->compare('t.username', $this->username, true);
        $criteria->compare('t.email', $this->email, true);
        $criteria->compare('t.service', $this->service);
        $criteria->compare('t.port', $this->port);
        $criteria->compare('t.protocol', $this->protocol);
        $criteria->compare('t.status', $this->status);

        $criteria->addNotInCondition('t.status', array(self::STATUS_HIDDEN));

	    $criteria->order = 't.hostname ASC';
	    
        return new CActiveDataProvider(get_class($this), array(
            'criteria'      => $criteria,
            'pagination'    => array(
                'pageSize'  => $this->paginationOptions->getPageSize(),
                'pageVar'   => 'page',
            ),
            'sort'  => array(
                'defaultOrder'  => array(
                    't.server_id' => CSort::SORT_DESC,
                ),
            ),
        ));
    }

    /**
     * Returns the static model of the specified AR class.
     * Please note that you should have this exact method in all your CActiveRecord descendants!
     * @param string $className active record class name.
     * @return BounceServer the static model class
     */
    public static function model($className=__CLASS__)
    {
        return parent::model($className);
    }

    /**
     * @inheritdoc
     */
    protected function afterValidate()
    {
        $this->settingsChanged = false;

        if (!$this->isNewRecord && !MW_IS_CLI) {
            if (empty($this->customer_id)) {
                $this->locked = self::TEXT_NO;
            }

            if (get_class($this) == 'BounceServer') {
                $model = self::model()->findByPk((int)$this->server_id);
                $keys  = array('hostname', 'username', 'password', 'email', 'service', 'port', 'protocol', 'validate_ssl');
                foreach ($keys as $key) {
                    if (!empty($this->$key) && $this->$key != $model->$key) {
                        $this->settingsChanged = true;
                        break;
                    }
                }

                if ($this->settingsChanged) {
                    if (!empty($this->deliveryServers)) {
                        $deliveryServers = $this->deliveryServers;
                        foreach ($deliveryServers as $server) {
                            $server->status = DeliveryServer::STATUS_INACTIVE;
                            $server->save(false);
                        }
                    }
                }    
            }
        }

        return parent::afterValidate();
    }

    /**
     * @return bool
     */
    protected function beforeSave()
    {
        return parent::beforeSave();
    }

    /**
     * @return bool
     */
    protected function beforeDelete()
    {
        if (!$this->getCanBeDeleted()) {
            return false;
        }

        return parent::beforeDelete();
    }

    /**
     * @inheritdoc
     */
    public function attributeHelpTexts()
    {
        $texts = array(
            'hostname'      => Yii::t('servers', 'The hostname of your IMAP/POP3 server.'),
            'username'      => Yii::t('servers', 'The username of your IMAP/POP3 server, usually something like you@domain.com.'),
            'password'      => Yii::t('servers', 'The password of your IMAP/POP3 server, used in combination with your username to authenticate your request.'),
            'email'         => Yii::t('servers', 'Only if your login username to this server is not an email address. If left empty, the username will be used.'),
            'service'       => Yii::t('servers', 'The type of your server.'),
            'port'          => Yii::t('servers', 'The port of your IMAP/POP3 server, usually for IMAP this is 143 and for POP3 it is 110. If you are using SSL, then the port for IMAP is 993 and for POP3 it is 995.'),
            'protocol'      => Yii::t('servers', 'The security protocol used to access this server. If unsure, select NOTLS.'),
            'validate_ssl'  => Yii::t('servers', 'When using SSL/TLS, whether to validate the certificate or not.'),
            'locked'        => Yii::t('servers', 'Whether this server is locked and assigned customer cannot change or delete it'),

            // since 1.3.5.5
            'disable_authenticator' => Yii::t('servers', 'If in order to establish the connection you need to disable an authenticator, you can type it here. I.E: GSSAPI.'),
            'search_charset'        => Yii::t('servers', 'Search charset, defaults to UTF-8 but might require to leave empty for some servers or explictly use US-ASCII.'),
            'delete_all_messages'   => Yii::t('servers', 'By default only messages related to the application are deleted. If this is enabled, all messages from the box will be deleted.'),
        );

        return CMap::mergeArray($texts, parent::attributeHelpTexts());
    }

    /**
     * @return array
     */
    public function getServicesArray()
    {
        return array(
            'imap' => 'IMAP',
            'pop3' => 'POP3',
        );
    }

    /**
     * @return string
     */
    public function getServiceName()
    {
        $services = $this->getServicesArray();
        return !empty($this->service) && !empty($services[$this->service]) ? $services[$this->service] : '---';
    }

    /**
     * @return array
     */
    public function getProtocolsArray()
    {
        return array(
            'tls'   => 'TLS',
            'ssl'   => 'SSL',
            'notls' => 'NOTLS',
        );
    }

    /**
     * @return string
     */
    public function getProtocolName()
    {
        $protocols = $this->getProtocolsArray();
        return !empty($this->protocol) && !empty($protocols[$this->protocol]) ? $protocols[$this->protocol] : Yii::t('app', 'Default');
    }

    /**
     * @return array
     */
    public function getValidateSslOptions()
    {
        return array(
            self::TEXT_NO   => Yii::t('app', 'No'),
            self::TEXT_YES  => Yii::t('app', 'Yes'),
        );
    }

	/**
	 * @param array $params
	 *
	 * @return string
	 */
    public function getConnectionString(array $params = array())
    {
	    $params = CMap::mergeArray(array(
            '[HOSTNAME]'        => $this->hostname,
            '[PORT]'            => $this->port,
            '[SERVICE]'         => $this->service,
            '[PROTOCOL]'        => $this->protocol,
            '[MAILBOX]'         => $this->mailBox,
            '[/VALIDATE_CERT]'  => '',
        ), $params);

        if (($this->protocol == 'ssl' || $this->protocol == 'tls') && $this->validate_ssl == self::TEXT_NO) {
	        $params['[/VALIDATE_CERT]'] = '/novalidate-cert';
        }

        // 1.8.5
	    $params = Yii::app()->hooks->applyFilters('servers_imap_connection_string_search_replace_params', $params, $this);

        $connectionString = '{[HOSTNAME]:[PORT]/[SERVICE]/[PROTOCOL][/VALIDATE_CERT]}[MAILBOX]';
        $connectionString = str_replace(array_keys($params), array_values($params), $connectionString);
        return $connectionString;
    }

    /**
     * @return bool
     */
    public function getCanBeDeleted()
    {
        return !in_array($this->status, array(self::STATUS_CRON_RUNNING));
    }

    /**
     * @return bool
     */
    public function getCanBeUpdated()
    {
        return !in_array($this->status, array(self::STATUS_CRON_RUNNING, self::STATUS_HIDDEN));
    }

    /**
     * @return bool
     */
    public function getIsLocked()
    {
        return $this->locked === self::TEXT_YES;
    }

    /**
     * @return array
     */
    public function getStatusesList()
    {
        return array(
            self::STATUS_ACTIVE         => ucfirst(Yii::t('app', self::STATUS_ACTIVE)),
            self::STATUS_CRON_RUNNING   => ucfirst(Yii::t('app', self::STATUS_CRON_RUNNING)),
            self::STATUS_INACTIVE       => ucfirst(Yii::t('app', self::STATUS_INACTIVE)),
            self::STATUS_DISABLED       => ucfirst(Yii::t('app', self::STATUS_DISABLED)),
        );
    }

    /**
     * @return array
     */
    public function getImapOpenParams()
    {
        $params = array();
        if (!empty($this->disable_authenticator)) {
            $params['DISABLE_AUTHENTICATOR'] = $this->disable_authenticator;
        }
        return $params;
    }

    /**
     * @return null|string
     */
    public function getSearchCharset()
    {
        return !empty($this->search_charset) ? strtoupper($this->search_charset) : null;
    }

    /**
     * @return bool
     */
    public function getDeleteAllMessages()
    {
        return (bool)(!empty($this->delete_all_messages) && $this->delete_all_messages == self::TEXT_YES);
    }

    /**
     * @return bool
     */
    public function testConnection()
    {
        $this->validate();
        if ($this->hasErrors()) {
            return false;
        }

        if (!CommonHelper::functionExists('imap_open')) {
            $this->addError('hostname', Yii::t('servers', 'The IMAP extension is missing from your PHP installation.'));
            return false;
        }

        $conn   = @imap_open($this->getConnectionString(), $this->username, $this->password, null, 1, $this->getImapOpenParams());
        $errors = imap_errors();
        $error  = null;
        
        if (!empty($errors) && is_array($errors)) {
            $errors = array_unique(array_values($errors));
            $error  = implode('<br />', $errors);

            // since 1.3.5.8
            if (stripos($error, 'insecure server advertised') !== false) {
                $error = null;
            }
        }

        if (empty($error) && empty($conn)) {
            $error = Yii::t('servers', 'Unknown error while opening the connection!');
        }

        // since 1.3.5.9
        if (!empty($error) && stripos($error, 'Mailbox is empty') !== false) {
            $error = null;
        }

        if (!empty($error)) {
            $this->addError('hostname', $error);
            return false;
        }

        $results = @imap_search($conn, "NEW", null, $this->getSearchCharset());
        $errors  = imap_errors();
        $error   = null;
        if (!empty($errors) && is_array($errors)) {
            $errors = array_unique(array_values($errors));
            $error = implode('<br />', $errors);
        }
        @imap_close($conn);

        // since 1.3.5.7
        if (!empty($error) && stripos($error, 'Mailbox is empty') !== false) {
            $error = null;
        }

        if (!empty($error)) {
            $this->addError('hostname', $error);
            return false;
        }

        return true;
    }

    /**
     * @param null $status
     * @return bool
     */
    public function saveStatus($status = null)
    {
        if (empty($this->server_id)) {
            return false;
        }

        if ($status && $status == $this->status) {
            return true;
        }
        
        if ($status) {
            $this->status = $status;
        }
        
        $attributes = array('status' => $this->status);
        $this->last_updated = $attributes['last_updated'] = new CDbExpression('NOW()');

	    // 1.7.9
	    Yii::app()->hooks->doAction($this->buildHookName(array('suffix' => 'before_savestatus')), $this);
	    //
	    
	    $result = (bool)Yii::app()->getDb()->createCommand()->update($this->tableName(), $attributes, 'server_id = :sid', array(':sid' => (int)$this->server_id));

	    // 1.7.9
	    Yii::app()->hooks->doAction($this->buildHookName(array('suffix' => 'after_savestatus')), $this, $result);
	    //
	    
	    return $result;
    }

    /**
     * @return bool|BounceServer
     * @throws CException
     */
    public function copy()
    {
        $copied = false;

        if ($this->isNewRecord) {
            return $copied;
        }

        $transaction = Yii::app()->db->beginTransaction();

        try {

            $server = clone $this;
            $server->isNewRecord  = true;
            $server->server_id    = null;
            $server->status       = self::STATUS_DISABLED;
            $server->date_added   = new CDbExpression('NOW()');
            $server->last_updated = new CDbExpression('NOW()');

            if (!$server->save(false)) {
                throw new CException($server->shortErrors->getAllAsString());
            }

            $transaction->commit();
            $copied = $server;
        } catch (Exception $e) {
            $transaction->rollback();
        }

        return $copied;
    }

    /**
     * @return bool
     */
    public function getIsDisabled()
    {
        return $this->status == self::STATUS_DISABLED;
    }

    /**
     * @return bool
     */
    public function getIsActive()
    {
        return $this->status == self::STATUS_ACTIVE;
    }

    /**
     * @return bool
     */
    public function enable()
    {
        if (!$this->getIsDisabled()) {
            return false;
        }
        $this->status = self::STATUS_ACTIVE;
        return $this->save(false);
    }

    /**
     * @return bool
     */
    public function disable()
    {
        if (!$this->getIsActive()) {
            return false;
        }
        $this->status = self::STATUS_DISABLED;
        return $this->save(false);
    }

	/**
	 * @param array $params
	 * @return bool
	 */
	public function processRemoteContents(array $params = array())
	{
		$mailBoxes = (array)Yii::app()->params['servers.imap.search.mailboxes'];
		if (!empty($params['mailbox'])) {
			$mailBoxes[] = $params['mailbox'];
		}
		$mailBoxes = array_filter(array_unique(array_map('strtoupper', $mailBoxes)));
		$mailBoxes = !empty($mailBoxes) ? $mailBoxes : array($this->mailBox);

		foreach ($mailBoxes as $mailBox) {
			$this->_processRemoteContents(CMap::mergeArray($params, array('mailbox' => $mailBox)));
		}
		return true;
	}

    /**
     * @param array $params
     * @return bool
     */
    protected function _processRemoteContents(array $params = array())
    {
	    // 1.4.4
	    $logger = !empty($params['logger']) && is_callable($params['logger']) ? $params['logger'] : null;
	    
	    // 1.8.8
	    if ($logger) {
		    call_user_func($logger, sprintf('Acquiring lock for server ID %d.', $this->server_id));
	    }
	    
        $mutexKey = sha1('imappop3box' . serialize($this->getAttributes(array('hostname', 'username', 'password'))));
        if (!Yii::app()->mutex->acquire($mutexKey, 5)) {
        	
	        // 1.8.8
	        if ($logger) {
		        call_user_func($logger, sprintf('Seems that server ID %d is already locked and processing.', $this->server_id));
	        }
	        
            return false;
        }
	    
        // 1.8.8
	    if ($logger) {
		    call_user_func($logger, sprintf('Lock for server ID %d has been acquired.', $this->server_id));
	    }
	    
        try {

            if (!$this->getIsActive()) {
                throw new Exception('The server is inactive!', 1);
            }

            // put proper status
            $this->saveStatus(self::STATUS_CRON_RUNNING);

            // make sure the BounceHandler class is loaded
            Yii::import('common.vendors.BounceHandler.*');

            $options = Yii::app()->options;

            if ($this instanceof FeedbackLoopServer) {
                $processLimit    = (int)$options->get('system.cron.process_feedback_loop_servers.emails_at_once', 500);
                $processDaysBack = (int)$options->get('system.cron.process_feedback_loop_servers.days_back', 3);
            } else {
                $processLimit    = (int)$options->get('system.cron.process_bounce_servers.emails_at_once', 500);
                $processDaysBack = (int)$options->get('system.cron.process_bounce_servers.days_back', 3);
            }

            // close the db connection because it will time out!
            Yii::app()->getDb()->setActive(false);
            
            $headerPrefix   = Yii::app()->params['email.custom.header.prefix'];
            $headerPrefixUp = strtoupper($headerPrefix);
            
	        $connectionStringSearchReplaceParams = array();
	        if (!empty($params['mailbox'])) {
		        $connectionStringSearchReplaceParams['[MAILBOX]'] = $params['mailbox'];
	        }
	        $connectionString = $this->getConnectionString($connectionStringSearchReplaceParams);
	        
            $bounceHandler = new BounceHandler($connectionString, $this->username, $this->password, array(
                'deleteMessages'                => true,
                'deleteAllMessages'             => $this->getDeleteAllMessages(),
                'processLimit'                  => $processLimit,
                'searchCharset'                 => $this->getSearchCharset(),
                'imapOpenParams'                => $this->getImapOpenParams(),
                'processDaysBack'               => $processDaysBack,
                'processOnlyFeedbackReports'    => ($this instanceof FeedbackLoopServer),
                'requiredHeaders'               => array(
                    $headerPrefix . 'Campaign-Uid',
                    $headerPrefix . 'Subscriber-Uid'
                ),
                'logger' => $logger,
            ));

            // 1.4.4
            if ($logger) {
	            $mailbox = isset($connectionStringSearchReplaceParams['[MAILBOX]']) ? $connectionStringSearchReplaceParams['[MAILBOX]'] : $this->mailBox;
	            call_user_func($logger, sprintf('Searching for results in the "%s" mailbox...', $mailbox));
            }

            // fetch the results
            $results = $bounceHandler->getResults();

            // 1.4.4
            if ($logger) {
                call_user_func($logger, sprintf('Found %d results.', count($results)));
            }

            // re-open the db connection
            Yii::app()->getDb()->setActive(true);

            // done
            if (empty($results)) {
                $this->saveStatus(self::STATUS_ACTIVE);
                throw new Exception('No results!', 1);
            }

            foreach ($results as $result) {

                foreach ($result['originalEmailHeadersArray'] as $key => $value) {
                    unset($result['originalEmailHeadersArray'][$key]);
                    $result['originalEmailHeadersArray'][strtoupper($key)] = $value;
                }

                if (!isset($result['originalEmailHeadersArray'][$headerPrefixUp . 'CAMPAIGN-UID'], $result['originalEmailHeadersArray'][$headerPrefixUp . 'SUBSCRIBER-UID'])) {
                    continue;
                }

                $campaignUid   = trim($result['originalEmailHeadersArray'][$headerPrefixUp . 'CAMPAIGN-UID']);
                $subscriberUid = trim($result['originalEmailHeadersArray'][$headerPrefixUp . 'SUBSCRIBER-UID']);

                // 1.4.4
                if ($logger) {
                    call_user_func($logger, sprintf('Processing campaign uid: %s and subscriber uid %s.', $campaignUid, $subscriberUid));
                }

                $campaign = Campaign::model()->findByUid($campaignUid);
                if (empty($campaign)) {
                    // 1.4.4
                    if ($logger) {
                        call_user_func($logger, sprintf('Campaign uid: %s was not found anymore.', $campaignUid));
                    }
                    continue;
                }

                $subscriber = ListSubscriber::model()->findByAttributes(array(
                    'list_id'        => $campaign->list->list_id,
                    'subscriber_uid' => $subscriberUid,
                    'status'         => ListSubscriber::STATUS_CONFIRMED,
                ));

                if (empty($subscriber)) {

                    // 1.4.4
                    if ($logger) {
                        call_user_func($logger, sprintf('Subscriber uid: %s was not found anymore.', $subscriberUid));
                    }

                    continue;
                }

                if (in_array($result['bounceType'], array(BounceHandler::BOUNCE_SOFT, BounceHandler::BOUNCE_HARD))) {

                    $count = CampaignBounceLog::model()->countByAttributes(array(
                        'campaign_id'   => $campaign->campaign_id,
                        'subscriber_id' => $subscriber->subscriber_id,
                    ));

                    if (!empty($count)) {
                        continue;
                    }

                    $bounceLog = new CampaignBounceLog();
                    $bounceLog->campaign_id     = $campaign->campaign_id;
                    $bounceLog->subscriber_id   = $subscriber->subscriber_id;
                    $bounceLog->message         = $result['diagnosticCode'];
                    $bounceLog->bounce_type     = $result['bounceType'] == BounceHandler::BOUNCE_HARD ? BounceHandler::BOUNCE_HARD : CampaignBounceLog::BOUNCE_SOFT;
                    $bounceLog->save();

                    // since 1.3.5.9
                    if ($bounceLog->bounce_type == CampaignBounceLog::BOUNCE_HARD) {
                        $subscriber->addToBlacklist($bounceLog->message);
                    }

                    // 1.4.4
                    if ($logger) {
                        call_user_func($logger, sprintf('Subscriber uid: %s is %s bounced with the message: %s.', $subscriberUid, (string)$bounceLog->bounce_type, (string)$bounceLog->message));
                    }

                } elseif ($result['bounceType'] == BounceHandler::FEEDBACK_LOOP_REPORT) {

                	$_message = 'DELETED';
                	
                    if ($options->get('system.cron.process_feedback_loop_servers.subscriber_action', 'unsubscribe') == 'delete') {
                        
                    	$subscriber->delete();
                    
                    } else {

	                    $_message = 'Unsubscribed';
                        $subscriber->saveStatus(ListSubscriber::STATUS_UNSUBSCRIBED);

                        $count = CampaignTrackUnsubscribe::model()->countByAttributes(array(
                            'campaign_id'   => $campaign->campaign_id,
                            'subscriber_id' => $subscriber->subscriber_id,
                        ));

                        if (empty($count)) {
                            $trackUnsubscribe = new CampaignTrackUnsubscribe();
                            $trackUnsubscribe->campaign_id = $campaign->campaign_id;
                            $trackUnsubscribe->subscriber_id = $subscriber->subscriber_id;
                            $trackUnsubscribe->note = $_message = 'Unsubscribed via FBL Report!';
                            $trackUnsubscribe->save(false);
                        }

                        // since 1.4.4 - complaints go into their own tables
                        $count = CampaignComplainLog::model()->countByAttributes(array(
                            'campaign_id'   => $campaign->campaign_id,
                            'subscriber_id' => $subscriber->subscriber_id,
                        ));

                        if (empty($count)) {
                            $complaintLog = new CampaignComplainLog();
                            $complaintLog->campaign_id = $campaign->campaign_id;
                            $complaintLog->subscriber_id = $subscriber->subscriber_id;
                            $complaintLog->message = $_message = 'Abuse complaint via FBL Report!';
                            $complaintLog->save(false);
                        }
                        //
                    }

                    // 1.4.4
                    if ($logger) {
                        call_user_func($logger, sprintf('Subscriber uid: %s is %s bounced with the message: %s.', $subscriberUid, (string)$result['bounceType'], (string)$_message));
                    }

                } elseif ($result['bounceType'] == BounceHandler::BOUNCE_INTERNAL) {

                    $bounceLog = new CampaignBounceLog();
                    $bounceLog->campaign_id     = $campaign->campaign_id;
                    $bounceLog->subscriber_id   = $subscriber->subscriber_id;
                    $bounceLog->message         = !empty($result['diagnosticCode']) ? $result['diagnosticCode'] : 'Internal Bounce';
                    $bounceLog->bounce_type     = BounceHandler::BOUNCE_INTERNAL;
                    $bounceLog->save();

                    // 1.4.4
                    if ($logger) {
                        call_user_func($logger, sprintf('Subscriber uid: %s is %s bounced with the message: %s.', $subscriberUid, (string)$bounceLog->bounce_type, (string)$bounceLog->message));
                    }
                }
            }

            // mark the server as active once again
            $this->saveStatus(self::STATUS_ACTIVE);
            
        } catch (Exception $e) {

            if ($e->getCode() == 0) {
                Yii::log($e->getMessage(), CLogger::LEVEL_ERROR);
            }

	        if ($logger) {
		        call_user_func($logger, $e->getMessage());
	        }
        }

	    // 1.8.8
	    if ($logger) {
		    call_user_func($logger, sprintf('Releasing lock for server ID %d.', $this->server_id));
	    }
	    
        Yii::app()->mutex->release($mutexKey);
        
        return true;
    }
}
