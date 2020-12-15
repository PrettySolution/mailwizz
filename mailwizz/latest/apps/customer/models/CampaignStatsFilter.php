<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * CampaignStatsFilter
 *
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.3.9.5
 */

class CampaignStatsFilter extends Campaign
{
    /**
     * @const string
     */
    const ACTION_VIEW = 'view';

    /**
     * @const string
     */
    const ACTION_EXPORT = 'export';
    
    /**
     * @var array
     */
    public $lists = array();

    /**
     * @var array
     */
    public $campaigns = array();

    /**
     * @var string
     */
    public $date_start;

    /**
     * @var string
     */
    public $date_end;

    /**
     * @var string
     */
    public $action;

    /**
     * Returns the static model of the specified AR class.
     * Please note that you should have this exact method in all your CActiveRecord descendants!
     * @param string $className active record class name.
     * @return Campaign the static model class
     */
    public static function model($className=__CLASS__)
    {
        return parent::model($className);
    }

    /**
     * @return array
     */
    public function rules()
    {
        return array(
            array('lists, campaigns, date_start, date_end, action', 'safe'),
        );
    }

    /**
     * @return array
     */
    public function attributeLabels()
    {
        return CMap::mergeArray(parent::attributeLabels(), array(
            
            'lists'      => Yii::t('campaigns', 'Lists'),
            'campaigns'  => Yii::t('campaigns', 'Campaigns'),
            'date_start' => Yii::t('campaigns', 'Send at (start)'),
            'date_end'   => Yii::t('campaigns', 'Send at (end)'),
            'action'     => Yii::t('campaigns', 'Action'),
            
            'name'              => Yii::t('campaigns', 'Campaign'),
            'subject'           => Yii::t('campaigns', 'Subject'),
            'subscribersCount'  => Yii::t('campaigns', 'Subscribers'),
            'deliverySuccess'   => Yii::t('campaigns', 'Delivery'),
            'uniqueOpens'       => Yii::t('campaigns', 'Opens'),
            'allOpens'          => Yii::t('campaigns', 'All opens'),
            'uniqueClicks'      => Yii::t('campaigns', 'Clicks'),
            'allClicks'         => Yii::t('campaigns', 'All clicks'),
            'unsubscribes'      => Yii::t('campaigns', 'Unsubscribes'),
            'bounces'           => Yii::t('campaigns', 'Bounces'),
            'softBounces'       => Yii::t('campaigns', 'Bounces (S)'),
            'hardBounces'       => Yii::t('campaigns', 'Bounces (H)'),
            'internalBounces'   => Yii::t('campaigns', 'Bounces (I)'),
            'listName'          => Yii::t('campaigns', 'List'),
            'sendAt'            => Yii::t('campaigns', 'Send date'),
        ));
    }

    /**
     * @return string
     */
    public function getSubscribersCount()
    {
        return $this->getStats()->getProcessedCount(true);
    }

    /**
     * @return string
     */
    public function getDeliverySuccess()
    {
        return $this->getStats()->getDeliverySuccessCount(true) . ' ('. $this->getStats()->getDeliverySuccessRate(true) .'%)';
    }

    /**
     * @return string
     */
    public function getUniqueOpens()
    {
        return $this->getStats()->getUniqueOpensCount(true) . ' ('. $this->getStats()->getUniqueOpensRate(true) .'%)';
    }

    /**
     * @return string
     */
    public function getAllOpens()
    {
        return $this->getStats()->getOpensCount(true) . ' ('. $this->getStats()->getOpensRate(true) .'%)';
    }

    /**
     * @return string
     */
    public function getUniqueClicks()
    {
        return $this->getStats()->getUniqueClicksCount(true) . ' ('. $this->getStats()->getUniqueClicksRate(true) .'%)';
    }

    /**
     * @return string
     */
    public function getAllClicks()
    {
        return $this->getStats()->getClicksCount(true) . ' ('. $this->getStats()->getClicksRate(true) .'%)';
    }

    /**
     * @return string
     */
    public function getUnsubscribes()
    {
        return $this->getStats()->getUnsubscribesCount(true) . ' ('. $this->getStats()->getUnsubscribesRate(true) .'%)';
    }

    /**
     * @return string
     */
    public function getBounces()
    {
        return $this->getStats()->getBouncesCount(true) . ' ('. $this->getStats()->getBouncesRate(true) .'%)';
    }

    /**
     * @return string
     */
    public function getSoftBounces()
    {
        return $this->getStats()->getSoftBouncesCount(true) . ' ('. $this->getStats()->getSoftBouncesRate(true) .'%)';
    }

    /**
     * @return string
     */
    public function getHardBounces()
    {
        return $this->getStats()->getHardBouncesCount(true) . ' ('. $this->getStats()->getHardBouncesRate(true) .'%)';
    }

    /**
     * @return string
     */
    public function getInternalBounces()
    {
        return $this->getStats()->getInternalBouncesCount(true) . ' ('. $this->getStats()->getInternalBouncesRate(true) .'%)';
    }

    /**
     * @return string
     */
    public function getListName()
    {
        return !empty($this->list) ? $this->list->name : '';
    }

    /**
     * @return array
     */
    public function getFilterActionsList()
    {
        $actions = array(
            self::ACTION_VIEW    => Yii::t('campaigns', 'View'),
            self::ACTION_EXPORT  => Yii::t('campaigns', 'Export'),
        );
        
        if (!empty($this->customer_id) && $this->customer->getGroupOption('campaigns.can_export_stats', 'yes') != 'yes') {
            unset($actions[self::ACTION_EXPORT]);
        }
        
        return $actions;
    }

    /**
     * @return bool
     */
    public function getIsExportAction()
    {
        return $this->action == self::ACTION_EXPORT;
    }

    /**
     * @return bool
     */
    public function getIsViewAction()
    {
        return $this->action == self::ACTION_VIEW;
    }

    /**
     * @return CActiveDataProvider
     */
    public function search()
    {
        $criteria = new CDbCriteria;
        $criteria->with = array();
        $criteria->compare('t.customer_id', (int)$this->customer_id);
        $criteria->compare('t.type', self::TYPE_REGULAR);
        $criteria->compare('t.status', self::STATUS_SENT);
        
        if (!empty($this->lists) && is_array($this->lists)) {
            $this->lists = array_filter(array_unique(array_map('intval', array_map('trim', $this->lists))));
            if (!empty($this->lists)) {
                $criteria->addInCondition('t.list_id', $this->lists);
            }
        }

        if (!empty($this->campaigns) && is_array($this->campaigns)) {
            $this->campaigns = array_filter(array_unique(array_map('intval', array_map('trim', $this->campaigns))));
            if (!empty($this->campaigns)) {
                $criteria->addInCondition('t.campaign_id', $this->campaigns);
            }
        }
        
        if (!empty($this->date_start) && !empty($this->date_end)) {
            $criteria->compare('t.send_at', '>=' . date('Y-m-d', strtotime($this->date_start)));
            $criteria->compare('t.send_at', '<=' . date('Y-m-d', strtotime($this->date_end)));
        } elseif (!empty($this->send_at_start)) {
            $criteria->compare('t.send_at', '>=' . date('Y-m-d', strtotime($this->date_start)));
        } elseif (!empty($this->send_at_end)) {
            $criteria->compare('t.send_at', '<=' . date('Y-m-d', strtotime($this->date_end)));
        }

        $criteria->order = 't.campaign_id DESC';

        return new CActiveDataProvider(get_class($this), array(
            'criteria'      => $criteria,
            'pagination'    => array(
                'pageSize'  => 10,
                'pageVar'   => 'page',
            ),
            'sort'  => array(
                'defaultOrder'  => array(
                    't.campaign_id'   => CSort::SORT_DESC,
                ),
            ),
        ));
    }

    /**
     * @return string
     */
    public function getDatePickerFormat()
    {
        return 'yy-mm-dd';
    }

    /**
     * @return string
     */
    public function getDatePickerLanguage()
    {
        $language = Yii::app()->getLanguage();
        if (strpos($language, '_') === false) {
            return $language;
        }
        $language = explode('_', $language);

        return $language[0];
    }

    /**
     * @return bool
     */
    public function getHasFilters()
    {
        $attributes = array(
            'action', 'lists', 'campaigns', 'date_start', 'date_end'
        );
        
        foreach ($attributes as $attribute) {
            if (!empty($this->$attribute)) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * @param $customerId
     * @return array
     */
    public static function getCampaignsForCampaignFilterDropdown($customerId)
    {
        $options = array();

        $criteria = new CDbCriteria();
        $criteria->select = 'campaign_id, name';
        $criteria->compare('customer_id', (int)$customerId);
        $criteria->compare('type', self::TYPE_REGULAR);
        $criteria->compare('status', self::STATUS_SENT);
        $criteria->order = 'campaign_id DESC';

        $models = Campaign::model()->findAll($criteria);
        foreach ($models as $model) {
            $options[$model->campaign_id] = $model->name;
        }

        return $options;
    }
}
