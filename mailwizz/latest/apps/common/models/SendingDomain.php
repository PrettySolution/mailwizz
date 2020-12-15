<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * SendingDomain
 *
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.3.4.7
 */

/**
 * This is the model class for table "{{sending_domain}}".
 *
 * The followings are the available columns in table '{{sending_domain}}':
 * @property integer $domain_id
 * @property integer $customer_id
 * @property string $name
 * @property string $dkim_private_key
 * @property string $dkim_public_key
 * @property string $locked
 * @property string $verified
 * @property string $signing_enabled
 * @property string $date_added
 * @property string $last_updated
 *
 * The followings are the available model relations:
 * @property Customer $customer
 */
class SendingDomain extends ActiveRecord
{
    // both constants are deprecated and will be removed.
    const DKIM_SELECTOR = 'mailer';
    const DKIM_FULL_SELECTOR = 'mailer._domainkey';

	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return '{{sending_domain}}';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		$rules = array(
			array('name', 'required'),
			array('customer_id', 'numerical', 'integerOnly' => true),
            array('customer_id', 'exist', 'className' => 'Customer'),
			array('name', 'length', 'max' => 64),
            array('name', 'match', 'pattern' => '/\w+\.\w{2,10}(\.(\w{2,10}))?/i'),
            array('name', 'unique'),
            array('dkim_private_key', 'match', 'pattern' => '/-----BEGIN\sRSA\sPRIVATE\sKEY-----(.*)-----END\sRSA\sPRIVATE\sKEY-----/sx'),
            array('dkim_public_key', 'match', 'pattern' => '/-----BEGIN\sPUBLIC\sKEY-----(.*)-----END\sPUBLIC\sKEY-----/sx'),
            array('dkim_private_key, dkim_public_key', 'length', 'max' => 10000),
			array('locked, verified, signing_enabled', 'length', 'max' => 3),
            array('locked, verified, signing_enabled', 'in', 'range' => array_keys($this->getYesNoOptions())),

			// The following rule is used by search().
			array('customer_id, name, locked, verified, signing_enabled', 'safe', 'on'=>'search'),
		);
        return CMap::mergeArray($rules, parent::rules());
	}

	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		$relations = array(
			'customer' => array(self::BELONGS_TO, 'Customer', 'customer_id'),
		);
        return CMap::mergeArray($relations, parent::relations());
	}

    /**
	 * @return array scopes.
	 */
    public function scopes()
    {
        $scopes = new CMap(array(
            'verified' => array(
                'condition' => $this->getTableAlias(false, false).'.`verified` = :vf',
                'params'    => array(':vf' => self::TEXT_YES),
            ),
            'signingEnabled' => array(
                'condition' => $this->getTableAlias(false, false).'.`signing_enabled` = :se',
                'params'    => array(':se' => self::TEXT_YES),
            ),
        ));
        return CMap::mergeArray($scopes, parent::scopes());
    }

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		$labels = array(
			'domain_id'          => Yii::t('sending_domains', 'Domain'),
			'customer_id'        => Yii::t('sending_domains', 'Customer'),
			'name'               => Yii::t('sending_domains', 'Domain name'),
			'dkim_private_key'   => Yii::t('sending_domains', 'Dkim private key'),
			'dkim_public_key'    => Yii::t('sending_domains', 'Dkim public key'),
			'locked'             => Yii::t('sending_domains', 'Locked'),
			'verified'           => Yii::t('sending_domains', 'Verified'),
            'signing_enabled'    => Yii::t('sending_domains', 'DKIM Signing')
		);
        return CMap::mergeArray($labels, parent::attributeLabels());
	}

    /**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeHelpTexts()
	{
		$labels = array(
            'customer_id'        => Yii::t('sending_domains', 'If this domain is verified in behalf of a customer, choose the customer.'),
			'name'               => Yii::t('sending_domains', 'Domain name, i.e: example.com'),
			'verified'           => Yii::t('sending_domains', 'Set this to yes only if you already have DNS records set for this domain.'),
            'locked'             => Yii::t('sending_domains', 'Whether this domain is locked and the customer cannot modify or delete it.'),
            'signing_enabled'    => Yii::t('sending_domains', 'Whether we should use DKIM to sign outgoing campaigns for this domain.'),
            'dkim_private_key'   => Yii::t('sending_domains', 'DKIM private key, leave this empty to be auto-generated. Please do not edit this record unless you really know what you are doing.'),
			'dkim_public_key'    => Yii::t('sending_domains', 'DKIM public key, leave this empty to be auto-generated. Please do not edit this record unless you really know what you are doing.'),
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

		$criteria->compare('t.name', $this->name, true);
        $criteria->compare('t.locked', $this->locked);
        $criteria->compare('t.verified', $this->verified);
        $criteria->compare('t.signing_enabled', $this->signing_enabled);

        $criteria->order = 't.domain_id DESC';

		return new CActiveDataProvider(get_class($this), array(
            'criteria'   => $criteria,
            'pagination' => array(
                'pageSize' => $this->paginationOptions->getPageSize(),
                'pageVar'  => 'page',
            ),
            'sort'=>array(
                'defaultOrder' => array(
                    't.domain_id'  => CSort::SORT_DESC,
                ),
            ),
        ));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return SendingDomain the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

    /**
     * @return bool
     */
    public function getIsVerified()
    {
        return $this->verified === self::TEXT_YES;
    }

    /**
     * @return bool
     */
    public function getIsLocked()
    {
        return $this->locked == self::TEXT_YES;
    }

    /**
     * @param $verified
     * @return bool|int
     */
    public function saveVerified($verified)
    {
        if (empty($this->domain_id)) {
            return false;
        }
        if ($verified && $verified == $this->verified) {
            return true;
        }
        if ($verified) {
            $this->verified = $verified;
        }
        $attributes = array('verified' => $this->verified);
        $this->last_updated = $attributes['last_updated'] = new CDbExpression('NOW()');
        return Yii::app()->getDb()->createCommand()->update($this->tableName(), $attributes, 'domain_id = :id', array(':id' => (int)$this->domain_id));
    }

    /**
     * @return bool
     */
    public function getSigningEnabled()
    {
        return $this->signing_enabled == self::TEXT_YES;
    }

    /**
     * @inheritdoc
     */
    protected function beforeSave()
    {
        if (!$this->isNewRecord) {
            $keys  = array('name', 'dkim_private_key', 'dkim_public_key');
            $model = self::model()->findByPk($this->domain_id);
            foreach ($keys as $key) {
                if ($model->$key != $this->$key) {
                    $this->verified = self::TEXT_NO;
                    break;
                }
            }
        }
        return parent::beforeSave();
    }

    /**
     * @inheritdoc
     */
    protected function afterValidate()
    {
        if (!$this->hasErrors()) {
            $this->setDefaultDkimKeys()->generateDkimKeys();
        }
        parent::afterValidate();
    }

    /**
     * @inheritdoc
     */
    protected function afterConstruct()
    {
        $this->setDefaultDkimKeys();
        parent::afterConstruct();
    }

    /**
     * @inheritdoc
     */
    protected function afterFind()
    {
        $this->setDefaultDkimKeys();
        parent::afterFind();
    }

    /**
     * @return SendingDomain
     */
    public function setDefaultDkimKeys()
    {
        if (empty($this->dkim_private_key)) {
            $this->dkim_private_key = DnsTxtHelper::getDefaultDkimPrivateKey();
        }
        
        if (empty($this->dkim_public_key)) {
            $this->dkim_public_key = DnsTxtHelper::getDefaultDkimPublicKey();
        }
        
        return $this;
    }

    /**
     * @return bool
     */
    public function generateDkimKeys()
    {
        if (!empty($this->dkim_public_key) && !empty($this->dkim_private_key)) {
            return true;
        }
        
        $result = DnsTxtHelper::generateDkimKeys();
        if (!empty($result['errors'])) {
            $this->addError('name', $result['errors'][0]);
            return false;
        }
        
        if (!empty($result['private_key']) && !empty($result['public_key'])) {
            $this->dkim_private_key = $result['private_key'];
            $this->dkim_public_key  = $result['public_key'];
            unset($result);
            return true;
        }
        
        return false;
    }

    /**
     * @return string
     */
    public function getDnsTxtDkimSelectorToAdd()
    {
        // since 1.3.6.6
        if (!($key = DnsTxtHelper::getDefaultDkimPublicKey())) {
            $key = $this->dkim_public_key;
        }
        
        $record = sprintf('%s         TXT     "v=DKIM1; k=rsa; p=%s;"', DnsTxtHelper::getDkimFullSelector(), DnsTxtHelper::cleanDkimKey($key));

        // since 1.3.5.9
        $record = Yii::app()->hooks->applyFilters('sending_domain_get_dns_txt_dkim_record', $record, $this);

        return $record;
    }

    /**
     * @return string
     */
    public function getDnsTxtSpfRecordToAdd()
    {
        $smtpHosts = array();
        
        // since 1.3.6.6
        if (!($spf = DnsTxtHelper::getDefaultSpfValue())) {
            
            $criteria  = new CDbCriteria();
            $criteria->select    = '`t`.`hostname`';
            $criteria->addCondition('`t`.`status` = :st AND (`t`.`customer_id` = :cid OR `t`.`customer_id` IS NULL)');
            $criteria->addInCondition('t.type', array('smtp', 'smtp-amazon'));
            $criteria->params[':st']  = DeliveryServer::STATUS_ACTIVE;
            $criteria->params[':cid'] = (int)$this->customer_id;
            $servers = DeliveryServer::model()->findAll($criteria);
            foreach ($servers as $server) {
                $smtpHosts[] = sprintf('a:%s', $server->hostname);
            }
            if (isset($_SERVER['HTTP_HOST'])) {
                $smtpHosts[] = sprintf('a:%s', $_SERVER['HTTP_HOST']);
            }
            if (isset($_SERVER['SERVER_ADDR'])) {
                $blocks = explode('.', $_SERVER['SERVER_ADDR']);
                if (count($blocks) == 4) {
                    $smtpHosts[] = sprintf('ip4:%s', $_SERVER['SERVER_ADDR']);
                } else {
                    $smtpHosts[] = sprintf('ip6:%s', $_SERVER['SERVER_ADDR']);
                }
            }
            
            $spf = implode(" ", array_filter(array_unique($smtpHosts)));
            $spf = sprintf("v=spf1 mx a ptr %s ~all", $spf);
        }
        
        $record = sprintf('%s.      IN TXT     "%s"', $this->name, $spf);

        // since 1.3.5.9
        $record = Yii::app()->hooks->applyFilters('sending_domain_get_dns_txt_spf_record', $record, $this, $smtpHosts);

        return $record;
    }

    /**
     * @param $email
     * @param $customer_id
     * @return mixed|null|static
     */
    public function findVerifiedByEmailForCustomer($email, $customer_id)
    {
        if (!FilterVarHelper::email($email)) {
            return null;
        }

        static $domains = array();

        $parts  = explode('@', $email);
        $domain = $parts[1];

        if (isset($domains[$domain]) || array_key_exists($domain, $domains)) {
            return $domains[$domain];
        }

        $criteria = new CDbCriteria();
        $criteria->compare('t.name', $domain);
        $criteria->compare('t.verified', self::TEXT_YES);
        $criteria->compare('t.customer_id', $customer_id);

        return $domains[$domain] = self::model()->find($criteria);
    }

    /**
     * @param $email
     * @return mixed|null|static
     */
    public function findVerifiedByEmailForSystem($email)
    {
        if (!FilterVarHelper::email($email)) {
            return null;
        }

        static $domains = array();

        $parts  = explode('@', $email);
        $domain = $parts[1];

        if (isset($domains[$domain]) || array_key_exists($domain, $domains)) {
            return $domains[$domain];
        }

        $criteria = new CDbCriteria();
        $criteria->compare('t.name', $domain);
        $criteria->compare('t.verified', self::TEXT_YES);
        $criteria->addCondition('t.customer_id IS NULL');

        return $domains[$domain] = self::model()->find($criteria);
    }

    /**
     * @param $email
     * @param null $customer_id
     * @return mixed|null|SendingDomain
     */
    public function findVerifiedByEmail($email, $customer_id = null)
    {
        $domain = null;
        if ($customer_id > 0) {
            $domain = $this->findVerifiedByEmailForCustomer($email, $customer_id);
        }
        if (!$domain) {
            $domain = $this->findVerifiedByEmailForSystem($email);
        }
        return $domain;
    }

    /**
     * Proxy method
     * 
     * @return array
     */
    public function getRequirementsErrors()
    {
        return DnsTxtHelper::getDkimRequirementsErrors();
    }
    
    /**
     * Proxy method
     * 
     * @return mixed|string
     */
    public function getCleanPublicKey()
    {
        return DnsTxtHelper::cleanDkimKey($this->dkim_public_key);
    }

    /**
     * Proxy method
     * 
     * @return mixed
     */
    public static function getDkimSelector()
    {
        return DnsTxtHelper::getDkimSelector();
    }

    /**
     * Proxy method
     * 
     * @return mixed
     */
    public static function getDkimFullSelector()
    {
        return DnsTxtHelper::getDkimFullSelector();
    }
}
