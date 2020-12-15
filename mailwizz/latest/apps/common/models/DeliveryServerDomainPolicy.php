<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * DeliveryServerDomainPolicy
 *
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.3.4.5
 */

/**
 * This is the model class for table "{{delivery_server_domain_policy}}".
 *
 * The followings are the available columns in table '{{delivery_server_domain_policy}}':
 * @property integer $domain_id
 * @property integer $server_id
 * @property string $domain
 * @property string $policy
 * @property string $date_added
 * @property string $last_updated
 *
 * The followings are the available model relations:
 * @property DeliveryServer $server
 */
class DeliveryServerDomainPolicy extends ActiveRecord
{
    const POLICY_ALLOW = 'allow';

    const POLICY_DENY = 'deny';

	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return '{{delivery_server_domain_policy}}';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		$rules = array(
			array('server_id, domain, policy', 'required'),
			array('domain', 'length', 'max' => 64),
			array('policy', 'in', 'range' => array_keys($this->getPoliciesList())),
            array('server_id', 'numerical', 'integerOnly' => true),
            array('server_id', 'exist', 'className' => 'DeliveryServer'),

		);
        return CMap::mergeArray($rules, parent::rules());
	}

	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		$relations = array(
			'server' => array(self::BELONGS_TO, 'DeliveryServer', 'server_id'),
		);
        return CMap::mergeArray($relations, parent::relations());
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		$labels = array(
			'domain_id'  => Yii::t('servers', 'Domain'),
			'server_id'  => Yii::t('servers', 'Server'),
			'domain'     => Yii::t('servers', 'Domain'),
			'policy'     => Yii::t('servers', 'Policy'),
		);
        return CMap::mergeArray($labels, parent::attributeLabels());
	}

    public function attributeHelpTexts()
    {
        $texts = array();
        return CMap::mergeArray($texts, parent::attributeHelpTexts());
    }

    public function attributePlaceholders()
    {
        $placeholders = array(
			'domain'     => Yii::t('servers', 'i.e: yahoo.com'),
        );

        return CMap::mergeArray($placeholders, parent::attributePlaceholders());
    }

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return DeliveryServerDomainPolicy the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

    public function getIsAllow()
    {
        return $this->policy == self::POLICY_ALLOW;
    }

    public function getIsDeny()
    {
        return $this->policy == self::POLICY_DENY;
    }

    public function getPoliciesList()
    {
        return array(
            self::POLICY_ALLOW => ucfirst(Yii::t('servers', self::POLICY_ALLOW)),
            self::POLICY_DENY  => ucfirst(Yii::t('servers', self::POLICY_DENY)),
        );
    }
    
    /**
     * @param $server_id
     * @param $emailAddress
     * @return bool
     */
    public static function canSendToDomainOf($server_id, $emailAddress)
    {
        static $serverPolicies = array();
        static $allowedDomains = array();

        if (!isset($serverPolicies[$server_id])) {
            $serverPolicies[$server_id] = self::model()->findAll(array(
                'select'    => 'domain, policy',
                'condition' => 'server_id = :sid',
                'order'     => 'policy ASC, domain_id ASC',
                'params'    => array(':sid' => (int)$server_id),
            ));
            if (!empty($serverPolicies[$server_id])) {
                $allowPolicies = array();
                $denyPolicies  = array();
                foreach ($serverPolicies[$server_id] as $model) {
                    if ($model->getIsAllow()) {
                        $allowPolicies[] = $model;
                    } else {
                        $denyPolicies[] = $model;
                    }
                }
                $serverPolicies[$server_id] = array(
                    'allow' => $allowPolicies,
                    'deny'  => $denyPolicies,
                );
                unset($allowPolicies, $denyPolicies);
            }
        }

        // if no policy, then allow all
        if (empty($serverPolicies[$server_id])) {
            return true;
        }

        if (!isset($allowedDomains[$server_id])) {
            $allowedDomains[$server_id] = array();
        }

        $domain = $emailAddress;
        if (FilterVarHelper::email($emailAddress)) {
            $domain = explode('@', $emailAddress);
            $domain = end($domain);
        }

        if (isset($allowedDomains[$server_id][$domain])) {
            return $allowedDomains[$server_id][$domain];
        }

        foreach ($serverPolicies[$server_id]['allow'] as $model) {
            if ($model->domain == '*' || stripos($domain, $model->domain) === 0) {
                return $allowedDomains[$server_id][$domain] = true;
            }
        }

        foreach ($serverPolicies[$server_id]['deny'] as $model) {
            if ($model->domain == '*' || stripos($domain, $model->domain) === 0) {
                return $allowedDomains[$server_id][$domain] = false;
            }
        }

        return $allowedDomains[$server_id][$domain] = true;
    }
}
