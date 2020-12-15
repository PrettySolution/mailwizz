<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * PageTypeEmailSubjectBehavior
 *
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.3.8.8
 */

class PageTypeEmailSubjectBehavior extends CActiveRecordBehavior
{
    /**
     * @var string
     */
    public $email_subject = '';
    
    /**
     * @param CComponent $owner
     * @throws CException
     */
    public function attach($owner)
    {
        if (!($owner instanceof ListPageType) && !($owner instanceof ListPage)) {
            throw new CException('Invalid behavior owner!');
        }
        parent::attach($owner);
    }

    /**
     * @param CModelEvent $event
     */
    public function beforeSave($event)
    {
        if ($this->getCanHaveEmailSubject()) {
            $this->owner->getModelMetaData()->add('email_subject', $this->email_subject);
        }
    }

    /**
     * @param CModelEvent $event
     */
    public function afterFind($event)
    {
        if ($this->getCanHaveEmailSubject()) {
            if (!($this->email_subject = $this->owner->getModelMetaData()->itemAt('email_subject'))) {
                $this->email_subject = $this->getDefaultEmailSubject();
            }
        }
    }

    /**
     * @return bool
     */
    public function getCanHaveEmailSubject()
    {
        return stripos($this->getTheSlug(), 'email') !== false;
    }

    /**
     * @return string
     */
    public function getDefaultEmailSubject()
    {
        if (!$this->getCanHaveEmailSubject()) {
            return '';
        }

        if ($this->getTheSlug() == 'subscribe-confirm-email') {
            return Yii::t('list_subscribers', 'Please confirm your subscription');
        }

        if ($this->getTheSlug() == 'unsubscribe-confirm-email') {
            return Yii::t('list_subscribers', 'Please confirm your unsubscription');
        }

        if ($this->getTheSlug() == 'welcome-email') {
            return Yii::t('list_subscribers', 'Thank you for your subscription!');
        }

        if ($this->getTheSlug() == 'subscribe-confirm-approval-email') {
            return Yii::t('list_subscribers', 'Your subscription has been approved!');
        }
    }

    /**
     * @return string
     */
    public function getTheSlug()
    {
        if ($this->owner instanceof ListPage) {
            if (empty($this->owner->type)) {
                return '';
            }
            return $this->owner->type->slug;
        }
        return $this->owner->slug;
    }
}
