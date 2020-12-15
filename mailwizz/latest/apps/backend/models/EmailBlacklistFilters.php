<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * EmailBlacklistFilters
 *
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.3.7.1
 */

class EmailBlacklistFilters extends EmailBlacklist
{
    /**
     * flag for view list
     */
    const ACTION_VIEW = 'view';

    /**
     * flag for export
     */
    const ACTION_EXPORT = 'export';
    
    /**
     * flag for delete
     */
    const ACTION_DELETE = 'delete';
    
    /**
     * @var string $email
     */
    public $email;

    /**
     * @var string
     */
    public $reason;

    /**
     * @var string
     */
    public $date_start;

    /**
     * @var string
     */
    public $date_end;
    
    /**
     * @var string $action
     */
    public $action;

    /**
     * @var bool
     */
    public $hasSetFilters = false;

    /**
     * @return array
     */
    public function rules()
    {
        return array(
            array('action', 'in', 'range' => array_keys($this->getActionsList())),
            array('email, reason, date_start, date_end', 'safe'),
        );
    }

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        return CMap::mergeArray(parent::attributeLabels(), array(
            'action'     => Yii::t('email_blacklist', 'Action'),
            'email'      => Yii::t('email_blacklist', 'Email'),
            'reason'     => Yii::t('email_blacklist', 'Reason'),
            'date_start' => Yii::t('email_blacklist', 'Date start'),
            'date_end'   => Yii::t('email_blacklist', 'Date end'),
        ));
    }

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributePlaceholders()
    {
        return array(
            'email'  => 'name@domain.com',
            'reason' => 'unknown recipient',
            'date_start' => date('Y-m-d', strtotime('-1 week')),
            'date_end'   => date('Y-m-d', strtotime('+1 week')),
        );
    }
    
    /**
     * Returns the static model of the specified AR class.
     * Please note that you should have this exact method in all your CActiveRecord descendants!
     * @param string $className active record class name.
     * @return ListSubscriber the static model class
     */
    public static function model($className=__CLASS__)
    {
        return parent::model($className);
    }

    /**
     * @inheritdoc
     */
    public function beforeValidate()
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public function afterValidate()
    {
        return true;
    }

    /**
     * @return array
     */
    public function getActionsList()
    {
        return array(
            self::ACTION_VIEW    => Yii::t('list_subscriber', ucfirst(self::ACTION_VIEW)),
            self::ACTION_EXPORT  => Yii::t('list_subscriber', ucfirst(self::ACTION_EXPORT)),
            self::ACTION_DELETE  => Yii::t('list_subscriber', ucfirst(self::ACTION_DELETE)),
        );
    }

    /**
     * @return string
     */
    public function getIsExportAction()
    {
        return $this->action == self::ACTION_EXPORT;
    }

    /**
     * @return bool
     */
    public function getIsDeleteAction()
    {
        return $this->action == self::ACTION_DELETE;
    }

    /**
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function getEmails($limit = 1000, $offset = 0)
    {
        $criteria = $this->buildEmailsCriteria();
        $criteria->limit  = $limit;
        $criteria->offset = $offset;
        return EmailBlacklist::model()->findAll($criteria);
    }

    /**
     * @return CDbCriteria
     */
    public function buildEmailsCriteria()
    {

        $criteria = new CDbCriteria();
        if ($this->reason == '[EMPTY]') {
            $criteria->addCondition('t.reason = ""');
        } else {
            $criteria->compare('t.reason', $this->reason, true);
        }
        
        if (!empty($this->date_start)) {
            $criteria->addCondition('DATE(t.date_added) >= :ds');
            $criteria->params[':ds'] = $this->date_start;
        }

        if (!empty($this->date_end)) {
            $criteria->addCondition('DATE(t.date_added) <= :de');
            $criteria->params[':de'] = $this->date_end;
        }
        
        if (!empty($this->email)) {
            if (strpos($this->email, ',') !== false) {
                $emails = CommonHelper::getArrayFromString($this->email, ',');
                foreach ($emails as $index => $email) {
                    if (!FilterVarHelper::email($email)) {
                        unset($emails[$index]);
                    }
                }
                if (!empty($emails)) {
                    $criteria->addInCondition('t.email', $emails);
                }
            } else {
                $criteria->compare('t.email', $this->email, true);
            }
        }

        $criteria->order  = 't.email_id DESC';

        return $criteria;
    }

    /**
     * @return CActiveDataProvider
     */
    public function getActiveDataProvider()
    {
        return new CActiveDataProvider(get_class($this), array(
            'criteria'      => $this->buildEmailsCriteria(),
            'pagination'    => array(
                'pageSize'  => $this->paginationOptions->getPageSize(),
                'pageVar'   => 'page',
            ),
            'sort'  => array(
                'defaultOrder'  => array(
                    't.email_id'   => CSort::SORT_DESC,
                ),
            ),
        ));
    }
    
    /**
     * @param array $emailIds
     * @return int
     */
    public function deleteEmailsByIds(array $emailIds = array())
    {
        $emailIds = array_filter(array_unique(array_map('intval', $emailIds)));

        $command = Yii::app()->db->createCommand();
        $emails  = $command->select('email')->from('{{email_blacklist}}')->where(array('and',
            array('in', 'email_id', $emailIds),
        ))->queryAll();

        if (empty($emails)) {
            return 0;
        }

        $count   = count($emails);
        $_emails = array();
        foreach ($emails as $email) {
            $_emails[] = $email['email'];
        }
        
        $emails = array_chunk($_emails, 100);
        foreach ($emails as $emailsChunk) {
            
            // delete rom global BL
            $command = Yii::app()->db->createCommand();
            $command->delete('{{email_blacklist}}', array('and',
                array('in', 'email', $emailsChunk),
            ));
            
            // delete from customer BL
            $command->delete('{{customer_email_blacklist}}', array('and',
                array('in', 'email', $emailsChunk),
            ));
            
            // 1.4.4
            $reconfirmDeleted = Yii::app()->options->get('system.email_blacklist.reconfirm_blacklisted_subscribers_on_blacklist_delete', 'yes') === 'yes';
            
            if ($reconfirmDeleted) {
                // update list subscribers
                $command = Yii::app()->db->createCommand();
                $command->update('{{list_subscriber}}', array('status' => ListSubscriber::STATUS_CONFIRMED), array('and',
                    array('in', 'email', $emailsChunk),
                    array('in', 'status', array(ListSubscriber::STATUS_BLACKLISTED)),
                ));
            }
        }
        
        return $count;
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
}
