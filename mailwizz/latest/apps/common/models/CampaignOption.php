<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * CampaignOption
 *
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.3
 */

/**
 * This is the model class for table "campaign_option".
 *
 * The followings are the available columns in table 'campaign_option':
 * @property integer $campaign_id
 * @property string $url_tracking
 * @property string $open_tracking
 * @property string $json_feed
 * @property string $xml_feed
 * @property string $embed_images
 * @property string $plain_text_email
 * @property string $autoresponder_event
 * @property string $autoresponder_time_unit
 * @property integer $autoresponder_time_value
 * @property string $autoresponder_include_imported
 * @property string $autoresponder_include_current
 * @property string $autoresponder_time_min_hour
 * @property string $autoresponder_time_min_minute
 * @property string $autoresponder_open_campaign_id
 * @property string $autoresponder_sent_campaign_id
 * @property string $email_stats
 * @property int $email_stats_sent
 * @property string $cronjob
 * @property int $cronjob_enabled
 * @property int $cronjob_max_runs
 * @property int $cronjob_runs_counter
 * @property string $blocked_reason
 * @property string $giveup_counter
 * @property int $giveup_count
 * @property string $max_send_count
 * @property string $max_send_count_random
 * @property integer $tracking_domain_id
 * @property string $preheader
 * @property string $timewarp_enabled
 * @property integer $timewarp_hour
 * @property integer $timewarp_minute
 * @property string $share_reports_enabled
 * @property string $share_reports_password
 * @property int $processed_count
 * @property int $delivery_success_count
 * @property int $delivery_error_count
 * @property int $industry_processed_count
 * @property int $bounces_count
 * @property int $hard_bounces_count
 * @property int $soft_bounces_count
 * @property int $internal_bounces_count
 * @property int $opens_count
 * @property int $unique_opens_count
 * @property int $clicks_count
 * @property int $unique_clicks_count
 *
 * The followings are the available model relations:
 * @property Campaign $campaign
 * @property Campaign $autoresponderOpenCampaign
 * @property Campaign $autoresponderSentCampaign
 * @property Campaign $regularOpenUnopen
 * @property TrackingDomain $trackingDomain
 */
class CampaignOption extends ActiveRecord
{
    const AUTORESPONDER_EVENT_AFTER_SUBSCRIBE         = 'AFTER-SUBSCRIBE';
    const AUTORESPONDER_EVENT_AFTER_CAMPAIGN_OPEN     = 'AFTER-CAMPAIGN-OPEN';
    const AUTORESPONDER_EVENT_AFTER_CAMPAIGN_SENT     = 'AFTER-CAMPAIGN-SENT';

    const AUTORESPONDER_TIME_UNIT_MINUTE = 'minute';
    const AUTORESPONDER_TIME_UNIT_HOUR   = 'hour';
    const AUTORESPONDER_TIME_UNIT_DAY    = 'day';
    const AUTORESPONDER_TIME_UNIT_WEEK   = 'week';
    const AUTORESPONDER_TIME_UNIT_MONTH  = 'month';
    const AUTORESPONDER_TIME_UNIT_YEAR   = 'year';

    const REGULAR_OPEN_UNOPEN_ACTION_OPEN   = 'open';
    const REGULAR_OPEN_UNOPEN_ACTION_UNOPEN = 'unopen';
    
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return '{{campaign_option}}';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		$rules = array(
            array('url_tracking, open_tracking, json_feed, xml_feed, embed_images, plain_text_email', 'required'),
			array('url_tracking, open_tracking, json_feed, xml_feed, embed_images, plain_text_email', 'length', 'max' => 3),
            array('url_tracking, open_tracking, json_feed, xml_feed, embed_images, plain_text_email', 'in', 'range' => array_keys($this->getYesNoOptions())),
            array('email_stats, preheader', 'length', 'max' => 255),

            array('autoresponder_event, autoresponder_time_unit, autoresponder_time_value, autoresponder_include_imported, autoresponder_include_current', 'required', 'on' => 'step-confirm-ar'),
            array('autoresponder_event', 'in', 'range' => array_keys($this->getAutoresponderEvents())),
            array('autoresponder_time_unit', 'in', 'range' => array_keys($this->getAutoresponderTimeUnits())),
            array('autoresponder_time_value', 'numerical', 'integerOnly' => true, 'min' => 0, 'max' => 3650),
            array('autoresponder_include_imported, autoresponder_include_current', 'in', 'range' => array_keys($this->getYesNoOptions())),
            array('autoresponder_open_campaign_id', 'numerical', 'integerOnly' => true, 'min' => 0),
            array('autoresponder_sent_campaign_id', 'numerical', 'integerOnly' => true, 'min' => 0),
            array('autoresponder_time_min_hour', 'in', 'range' => array_keys($this->getAutoresponderTimeMinHoursList())),
            array('autoresponder_time_min_minute', 'in', 'range' => array_keys($this->getAutoresponderTimeMinMinutesList())),
            array('autoresponder_time_min_hour, autoresponder_time_min_minute', '_validateAutoresponderTimeMin'),
            
            // since 1.3.6.3
            array('max_send_count', 'length', 'max' => 11),
            array('max_send_count', 'numerical', 'integerOnly' => true, 'min' => 0),
            array('max_send_count_random', 'in', 'range' => array(0, 1)),
            
            // since 1.3.6.6
            array('tracking_domain_id', 'length', 'max' => 11),
            array('tracking_domain_id', 'numerical', 'integerOnly' => true, 'min' => 0),
            array('tracking_domain_id', 'exist', 'className' => 'TrackingDomain', 'attributeName' => 'domain_id'),
            
            // since 1.3.7.3
            array('share_reports_enabled', 'in', 'range' => array_keys($this->getYesNoOptions())),
            array('share_reports_password', 'length', 'min' => 4, 'max' => 64),
            
            // since 1.3.9.2
            array('share_reports_mask_email_addresses', 'in', 'range' => array_keys($this->getYesNoOptions())),
            
            // since 1.3.9.5
            array('cronjob_max_runs', 'numerical', 'integerOnly' => true, 'min' => -1, 'max' => 10000000),
            array('cronjob_runs_counter', 'numerical', 'integerOnly' => true, 'min' => 0),
            
            // since 1.5.2
            array('timewarp_enabled', 'in', 'range' => array_keys($this->getYesNoOptions())),
            array('timewarp_hour', 'in', 'range' => array_keys($this->getTimewarpHours())),
            array('timewarp_minute', 'in', 'range' => array_keys($this->getTimewarpMinutes())),
        );

        // since 1.3.5.3
        if (MW_COMPOSER_SUPPORT) {
            $rules[] = array('cronjob', 'validateCronExpression');
            $rules[] = array('cronjob_enabled', 'in', 'range' => array(0, 1));
        }

        return CMap::mergeArray($rules, parent::rules());
	}

	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		$relations = array(
			'campaign'                   => array(self::BELONGS_TO, 'Campaign', 'campaign_id'),
            'autoresponderOpenCampaign'  => array(self::BELONGS_TO, 'Campaign', 'autoresponder_open_campaign_id'),
            'autoresponderSentCampaign'  => array(self::BELONGS_TO, 'Campaign', 'autoresponder_sent_campaign_id'),
            'trackingDomain'             => array(self::BELONGS_TO, 'TrackingDomain', 'tracking_domain_id'),
		);

        return CMap::mergeArray($relations, parent::relations());
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		$labels = array(
			'campaign_id'        => Yii::t('campaigns', 'Campaign'),
			'url_tracking'       => Yii::t('campaigns', 'Url tracking'),
            'open_tracking'      => Yii::t('campaigns', 'Open tracking'),
			'json_feed'          => Yii::t('campaigns', 'Json feed'),
			'xml_feed'           => Yii::t('campaigns', 'Xml feed'),
            'embed_images'       => Yii::t('campaigns', 'Embed images'),
            'plain_text_email'   => Yii::t('campaigns', 'Plain text email'),
            'email_stats'        => Yii::t('campaigns', 'Email stats'),

            'autoresponder_event'            => Yii::t('campaigns', 'Autoresponder event'),
            'autoresponder_time_unit'        => Yii::t('campaigns', 'Autoresponder time unit'),
            'autoresponder_time_value'       => Yii::t('campaigns', 'Autoresponder time value'),
            'autoresponder_include_imported' => Yii::t('campaigns', 'Incl. imported subscribers'),
            'autoresponder_include_current'  => Yii::t('campaigns', 'Incl. current subscribers'),
            'autoresponder_open_campaign_id' => Yii::t('campaigns', 'Send when opening this campaign'),
            'autoresponder_sent_campaign_id' => Yii::t('campaigns', 'Send after sending this campaign'),
            'autoresponder_time_min_hour'    => Yii::t('campaigns', 'Send only at/after this time'),
            'autoresponder_time_min_minute'  => Yii::t('campaigns', 'Send only at/after this time'),
            
            'cronjob'         => Yii::t('campaigns', 'Advanced recurring'),
            'cronjob_enabled' => Yii::t('campaigns', 'Enabled'),
            
            'max_send_count'         => Yii::t('campaigns', 'Max. subscribers'),
            'max_send_count_random'  => Yii::t('campaigns', 'Randomize subscribers'),
            'tracking_domain_id'     => Yii::t('campaigns', 'Tracking domain'),
            'preheader'              => Yii::t('campaigns', 'Preheader'),
            
            'share_reports_enabled'    => Yii::t('campaigns', 'Enable stats sharing'),
            'share_reports_password'   => Yii::t('campaigns', 'Stats sharing password'),
            'share_reports_mask_email_addresses' => Yii::t('campaigns', 'Mask email addresses'),
            
            'cronjob_max_runs'     => Yii::t('campaigns', 'Max. runs'),
            'cronjob_runs_counter' => Yii::t('campaigns', 'Max. runs counter'),

            'timewarp_enabled'  => Yii::t('campaigns', 'Enable timewarp'),
            'timewarp_hour'     => Yii::t('campaigns', 'Timewarp hour'),
            'timewarp_minute'     => Yii::t('campaigns', 'Timewarp minute'),
		);

        return CMap::mergeArray($labels, parent::attributeLabels());
	}

    /**
     * @inheritdoc
     */
    protected function afterValidate()
    {
        if ($this->autoresponder_event == self::AUTORESPONDER_EVENT_AFTER_CAMPAIGN_OPEN && empty($this->autoresponder_open_campaign_id)) {
            $this->addError('autoresponder_open_campaign_id', Yii::t('campaigns', 'Please select a campaign for this autoresponder!'));
        }
        if ($this->autoresponder_event == self::AUTORESPONDER_EVENT_AFTER_CAMPAIGN_SENT && empty($this->autoresponder_sent_campaign_id)) {
            $this->addError('autoresponder_sent_campaign_id', Yii::t('campaigns', 'Please select a campaign for this autoresponder!'));
        }
        parent::afterValidate();
    }

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return CampaignOption the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

    /**
     * @return array
     */
    public function getYesNoOptionsArray()
    {
        return $this->getYesNoOptions();
    }

    /**
     * @inheritdoc
     */
    public function attributeHelpTexts()
    {
        $texts = array(
            'url_tracking'      => Yii::t('campaigns', 'Whether to enable url tracking'),
            'open_tracking'     => Yii::t('campaigns', 'Whether to enable opens tracking'),
            'json_feed'         => Yii::t('campaigns', 'Whether your campaign will parse a {feedType} feed and dynamically insert content from the feed into template', array('{feedType}' => 'json')),
            'xml_feed'          => Yii::t('campaigns', 'Whether your campaign will parse a {feedType} feed and dynamically insert content from the feed into template', array('{feedType}' => 'xml(rss)')),
            'embed_images'      => Yii::t('campaigns', 'Whether to embed images in the template instead of loading them remotely'),
            'plain_text_email'  => Yii::t('campaigns', 'Whether to generate the plain text version of the campaign email based on your html email version'),
            'email_stats'       => Yii::t('campaigns', 'Where to send the campaign stats when it finish sending, separate multiple email addresses by a comma. Leave empty to not send the stats'),

            'autoresponder_event'            => Yii::t('campaigns', 'The event timing that will trigger this autoresponder'),
            'autoresponder_time_unit'        => Yii::t('campaigns', 'The time unit for this autoresponder'),
            'autoresponder_time_value'       => Yii::t('campaigns', 'Based on the time unit, how much to wait until this autoresponder gets sent. 0 means it will be sent immediatly after event'),
            'autoresponder_include_imported' => Yii::t('campaigns', 'Whether to include imported subscribers into this autoresponder'),
            'autoresponder_include_current'  => Yii::t('campaigns', 'Whether to include current subscribers into this autoresponder. By default the AR is sent only to new subscribers'),
            'autoresponder_open_campaign_id' => Yii::t('campaigns', 'Which campaign must be opened in order to trigger this autoresponder'),
            'autoresponder_sent_campaign_id' => Yii::t('campaigns', 'Which campaign must be sent in order to trigger this autoresponder'),
            'autoresponder_time_min_hour'    => Yii::t('campaigns', 'Send the autoresonder no earlier than this time in the day. Time is UTC 00:00, take into consideration your timezone offset. Current UTC time is: {time}', array('{time}' => date('H:i:s'))),
            'autoresponder_time_min_minute'  => Yii::t('campaigns', 'Send the autoresonder no earlier than this time in the day. Time is UTC 00:00, take into consideration your timezone offset. Current UTC time is: {time}', array('{time}' => date('H:i:s'))),
            
            'max_send_count'        => Yii::t('campaigns', 'Whether to send only to this number of subscribers instead of sending to the whole list'),
            'max_send_count_random' => Yii::t('campaigns', 'If you limit the number of subscribers to which this campaigns goes to, enabling this option will pick them randomly from the list'),
            'tracking_domain_id'    => Yii::t('campaigns', 'The domain that will be used for tracking purposes, must be a DNS CNAME of the master domain.'),
            'preheader'             => Yii::t('campaigns', 'A preheader is the short summary text that follows the subject line when an email is viewed in the inbox. Many mobile, desktop and web email clients provide them to tip you off on what the email contains before you open it'),

            'share_reports_enabled'    => Yii::t('campaigns', 'Whether to allow campaign reports sharing'),
            'share_reports_password'   => Yii::t('campaigns', 'The password for accessing the reports'),
            'share_reports_mask_email_addresses' => Yii::t('campaigns', 'Whether to mask the email addresses'),

            'cronjob_max_runs'     => Yii::t('campaigns', 'The maximum number of times this campaigns is allowed to send. Set to -1 for unlimited'),

            'timewarp_enabled'  => Yii::t('campaigns', 'Send the campaign according to the subscriber timezone. For example, the campaign will send at different hours for a US subscriber versus a subscriber from EU and they will get it at their local time, regardless of their time difference. For best results, make sure you schedule the campaign with at least 24 hours in advance.'),
            'timewarp_hour'     => Yii::t('campaigns', 'The hour when the subscriber should receive this campaign according to its own timezone.'),
            'timewarp_minute'   => Yii::t('campaigns', 'The minute when the subscriber should receive this campaign according to its own timezone.'),
        );

        return CMap::mergeArray($texts, parent::attributeHelpTexts());
    }

    /**
     * @return array
     */
    public function getAutoresponderEvents()
    {
        return array(
            self::AUTORESPONDER_EVENT_AFTER_SUBSCRIBE     => Yii::t('campaigns', self::AUTORESPONDER_EVENT_AFTER_SUBSCRIBE),
            self::AUTORESPONDER_EVENT_AFTER_CAMPAIGN_OPEN => Yii::t('campaigns', self::AUTORESPONDER_EVENT_AFTER_CAMPAIGN_OPEN),
            self::AUTORESPONDER_EVENT_AFTER_CAMPAIGN_SENT => Yii::t('campaigns', self::AUTORESPONDER_EVENT_AFTER_CAMPAIGN_SENT),
        );
    }

    /**
     * @return array
     */
    public function getAutoresponderTimeUnits()
    {
        return array(
            self::AUTORESPONDER_TIME_UNIT_MINUTE    => ucfirst(Yii::t('app', self::AUTORESPONDER_TIME_UNIT_MINUTE)),
            self::AUTORESPONDER_TIME_UNIT_HOUR      => ucfirst(Yii::t('app', self::AUTORESPONDER_TIME_UNIT_HOUR)),
            self::AUTORESPONDER_TIME_UNIT_DAY       => ucfirst(Yii::t('app', self::AUTORESPONDER_TIME_UNIT_DAY)),
            self::AUTORESPONDER_TIME_UNIT_WEEK      => ucfirst(Yii::t('app', self::AUTORESPONDER_TIME_UNIT_WEEK)),
            self::AUTORESPONDER_TIME_UNIT_MONTH     => ucfirst(Yii::t('app', self::AUTORESPONDER_TIME_UNIT_MONTH)),
            self::AUTORESPONDER_TIME_UNIT_YEAR      => ucfirst(Yii::t('app', self::AUTORESPONDER_TIME_UNIT_YEAR)),
        );
    }

    /**
     * @param null $name
     * @return mixed|null|string
     */
    public function getAutoresponderEventName($name = null)
    {
        if (empty($name)) {
            $name = $this->autoresponder_event;
        }
        $names = $this->getAutoresponderEvents();
        return isset($names[$name]) ? $names[$name] : $name;
    }
    
    /**
     * @param $attribute
     * @param $params
     */
    public function validateCronExpression($attribute, $params)
    {
        if ($this->hasErrors() || $this->campaign->getIsAutoresponder() || !$this->cronjob_enabled || empty($this->$attribute)) {
            return;
        }

        if (empty($this->campaign) || !$this->campaign->getIsRegular()) {
            $this->addError($attribute, Yii::t('campaigns', 'No valid assigned campaign!'));
            return;
        }

        try {
            
            // 1.3.7.1
            $this->$attribute = trim($this->$attribute);
            if (substr($this->$attribute, 0, 1) == '*') {
                $this->$attribute = substr_replace($this->$attribute, '0', 0, 1);
            }
            //
            
            $cron = call_user_func(array('\Cron\CronExpression', 'factory'), $this->$attribute);
            $cron = $cron->getNextRunDate()->format('Y-m-d H:i:s');
        } catch (Exception $e) {
            $this->addError($attribute, $e->getMessage());
        }
    }

    /**
     * @param $reason
     * @return bool
     */
    public function setBlockedReason($reason)
    {
        if (empty($this->campaign_id)) {
            return false;
        }
        if (is_array($reason)) {
            $reason = implode('|', $reason);
        }
        $reason = StringHelper::truncateLength($reason, 255);
        Yii::app()->getDb()->createCommand()->update($this->tableName(), array('blocked_reason' => $reason), 'campaign_id = :cid', array(':cid' => (int)$this->campaign_id));
        $this->blocked_reason = $reason;
        return true;
    }

    /**
     * @param int $by
     * @return int
     */
    public function updateSendingGiveupCounter($by = 1)
    {
        if ((int)$by <= 0) {
            $this->giveup_counter = 0;
            return self::updateAll(array(
                'giveup_counter' => 0
            ), 'campaign_id = :cid', array(':cid' => (int)$this->campaign_id));
        }
        
        $this->giveup_counter += (int)$by;
        return self::updateCounters(array(
            'giveup_counter' => (int)$by
        ), 'campaign_id = :cid', array(':cid' => (int)$this->campaign_id));
    }

	/**
	 * @param $count
	 *
	 * @return int
	 */
    public function updateSendingGiveupCount($count)
    {
    	if ((int)$this->giveup_count === (int)$count) {
    		return $count;
	    }
    	
	    $this->giveup_count = (int)$count;
	    return self::updateAll(array(
		    'giveup_count' => (int)$count
	    ), 'campaign_id = :cid', array(':cid' => (int)$this->campaign_id));
    }

    /**
     * @return bool
     */
    public function getCanSetMaxSendCount()
    {
        return $this->campaign->isRegular && !empty($this->max_send_count) && $this->max_send_count > 0;
    }

    /**
     * @return bool
     */
    public function getCanSetMaxSendCountRandom()
    {
        return $this->getCanSetMaxSendCount() && !empty($this->max_send_count_random) && $this->max_send_count_random == 'yes';
    }

    /**
     * @return array
     */
    public function getTrackingDomainsArray()
    {
        static $_options = array();
        if (!empty($_options)) {
            return $_options;
        }

        $criteria = new CDbCriteria();
        $criteria->select = 'domain_id, name';

        if ($this->campaign_id && $this->campaign->customer_id) {
            $criteria->compare('customer_id', (int)$this->campaign->customer_id);
        }

        $criteria->order = 'domain_id DESC';
        $models = TrackingDomain::model()->findAll($criteria);

        $_options[''] = Yii::t('app', 'Choose');
        foreach ($models as $model) {
            $_options[$model->domain_id] = $model->name;
        }

        return $_options;
    }

    /**
     * @return bool
     */
    public function getAutoresponderIncludeImported()
    {
        return $this->autoresponder_include_imported == self::TEXT_YES;
    }

    /**
     * @return bool
     */
    public function getAutoresponderIncludeCurrent()
    {
        return $this->autoresponder_include_current == self::TEXT_YES;
    }

    /**
     * @param int $by
     * @return bool
     */
    public function incrementCronJobsRunsCounter($by = 1)
    {
        return $this->saveCounters(array(
            'cronjob_runs_counter' => (int)$by
        ));
    }

    /**
     * @param $attribute
     * @param $params
     */
    public function _validateAutoresponderTimeMin($attribute, $params)
    {
        if ($this->hasErrors($attribute)) {
            return;
        }
        
        if (empty($this->autoresponder_time_min_hour) && empty($this->autoresponder_time_min_minute)) {
            return;
        }

        if (empty($this->autoresponder_time_min_hour) && !empty($this->autoresponder_time_min_minute)) {
            $this->addError('autoresponder_time_min_hour', Yii::t('campaigns', 'Please provide a valid hour!'));
            return;
        }

        if (!empty($this->autoresponder_time_min_hour) && empty($this->autoresponder_time_min_minute)) {
            $this->addError('autoresponder_time_min_minute', Yii::t('campaigns', 'Please provide a valid minute!'));
            return;
        }
    }

    /**
     * @return array
     */
    public function getAutoresponderTimeMinHoursList()
    {
        $list = array();
        for ($i = 0; $i < 24; $i++) {
            $n = (string)$i;
            if ($n < 10) {
                $n = '0' . $n;
            }
            $list[$n] = $n;
        }
        return $list;
    }

    /**
     * @return array
     */
    public function getAutoresponderTimeMinMinutesList()
    {
        $list = array();
        for ($i = 0; $i < 60; $i++) {
            $n = (string)$i;
            if ($n < 10) {
                $n = '0' . $n;
            }
            $list[$n] = $n;
        }
        return $list;
    }

    /**
     * @return string
     */
    public function getAutoresponderTimeMinHourMinute()
    {
        if (empty($this->autoresponder_time_min_hour) || empty($this->autoresponder_time_min_minute)) {
            return '';
        }
        
        return sprintf('%s:%s', $this->autoresponder_time_min_hour, $this->autoresponder_time_min_minute);
    }

    /**
     * @return bool
     */
    public function getTimewarpEnabled()
    {
        return !empty($this->timewarp_enabled) && $this->timewarp_enabled == 'yes';
    }
    
    /**
     * @return array
     */
    public function getTimewarpHours()
    {
        $list = array();
        for ($i = 0; $i <= 23; $i++) {
            $n = (string)$i;
            if ($n < 10) {
                $n = '0' . $n;
            }
            $list[$i] = $n;
        }
        return $list;
    }

    /**
     * @return array
     */
    public function getTimewarpMinutes()
    {
        $list = array();
        for ($i = 0; $i <= 59; $i++) {
            $n = (string)$i;
            if ($n < 10) {
                $n = '0' . $n;
            }
            $list[$i] = $n;
        }
        return $list;
    }
}
