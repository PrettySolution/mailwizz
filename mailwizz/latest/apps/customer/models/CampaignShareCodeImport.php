<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * CampaignShareCodeImport
 *
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.7.6
 */

class CampaignShareCodeImport extends FormModel
{
    public $code        = '';
    public $list_id     = 0;
    public $customer_id = 0;

    private $_campaign_share_code;
    private $_list;

    /**
     * @return array
     */
    public function rules()
    {
        return array(
            array('list_id, code', 'required'),
            array('list_id', 'numerical', 'integerOnly' => true),
            array('code', 'length', 'is' => 40),

            array('list_id', '_validateList'),
            array('code', '_validateCode'),

            array('customer_id', 'unsafe'),
        );
    }

    /**
     * @return array
     */
    public function attributeLabels()
    {
        return array(
            'code'    => Yii::t('campaigns', 'Code'),
            'list_id' => Yii::t('campaigns', 'List'),
        );
    }

    /**
     * @return array
     */
    public function attributeHelpTexts()
    {
        return array();
    }

    /**
     * @param $attribute
     * @param $params
     */
    public function _validateList($attribute, $params)
    {
        if (empty($this->$attribute)) {
            return;
        }

        if ($this->getList()) {
            return;
        }

        $this->addError($attribute, Yii::t('campaigns', 'The list you choose is not a valid list.'));
    }

    /**
     * @param $attribute
     * @param $params
     */
    public function _validateCode($attribute, $params)
    {
        if (empty($this->$attribute)) {
            return;
        }

        if ($this->hasErrors($attribute)) {
            return;
        }

        if ($this->getCampaignShareCode()) {
            return;
        }

        $this->addError($attribute, Yii::t('campaigns', 'The sharing code you provided is not a valid campaign sharing code.'));
    }

    /**
     * @return mixed
     */
    public function getListsAsDropDownOptionsByCustomerId()
    {
        $this->customer_id = (int)$this->customer_id;
        static $options = array();
        if (isset($options[$this->customer_id])) {
            return $options[$this->customer_id];
        }
        $options[$this->customer_id] = array();

        $criteria = new CDbCriteria();
        $criteria->select = 'list_id, name';
        $criteria->compare('customer_id', $this->customer_id);
        $criteria->addNotInCondition('status', array(Lists::STATUS_PENDING_DELETE, Lists::STATUS_ARCHIVED));
        $criteria->order = 'name ASC';
        
        $models = Lists::model()->findAll($criteria);

        foreach ($models as $model) {
            $options[$this->customer_id][$model->list_id] = $model->name;
        }

        return $options[$this->customer_id];
    }

    /**
     * @return mixed
     */
    public function getCampaignShareCode()
    {
        if ($this->_campaign_share_code !== null) {
            return $this->_campaign_share_code;
        }

        if (empty($this->code) || empty($this->customer_id)) {
            return false;
        }

        $criteria = new CDbCriteria();
        $criteria->compare('code_uid', $this->code);
        $criteria->addNotInCondition('used', array(CampaignShareCode::TEXT_YES));

        return $this->_campaign_share_code = CampaignShareCode::model()->find($criteria);
    }

    /**
     * @return array|bool|mixed|null
     */
    public function getList()
    {
        if ($this->_list !== null) {
            return $this->_list;
        }

        if (empty($this->list_id) || empty($this->customer_id)) {
            return false;
        }

        $criteria = new CDbCriteria();
        $criteria->compare('list_id', (int)$this->list_id);
        $criteria->compare('customer_id', (int)$this->customer_id);
        $criteria->addNotInCondition('status', array(Lists::STATUS_PENDING_DELETE, Lists::STATUS_ARCHIVED));

        return $this->_list = Lists::model()->find($criteria);
    }
}