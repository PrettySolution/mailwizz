<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * PageTypeTagsBehavior
 *
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.0
 */

class PageTypeTagsBehavior extends CActiveRecordBehavior
{
    /**
     * PageTypeTagsBehavior::attach()
     *
     * @param mixed $owner
     * @return
     */
    public function attach($owner)
    {
        if (!($owner instanceof ListPageType) && !($owner instanceof ListPage)) {
            throw new CException('Invalid behavior owner!');
        }
        parent::attach($owner);
    }

    /**
     * PageTypeTagsBehavior::beforeSave()
     *
     * @param mixed $event
     * @return
     */
    public function beforeSave($event)
    {
        $tags = $this->getAvailableTags();
        $content = CHtml::decode($this->owner->content);

        if (empty($content)) {
            return;
        }

        foreach ($tags as $tag) {
            if (!isset($tag['tag']) || !isset($tag['required']) || !$tag['required']) {
                continue;
            }

            if (!isset($tag['pattern']) && strpos($content, $tag['tag']) === false) {
                $this->owner->addError('content', Yii::t('list_pages', 'The following tag is required but was not found in your content: {tag}', array(
                    '{tag}' => $tag['tag'],
                )));
                $event->isValid = false;
                break;
            } elseif (isset($tag['pattern']) && !preg_match($tag['pattern'], $content)) {
                $this->owner->addError('content', Yii::t('list_pages', 'The following tag is required but was not found in your content: {tag}', array(
                    '{tag}' => $tag['tag'],
                )));
                $event->isValid = false;
                break;
            }
        }
    }

    /**
     * PageTypeTagsBehavior::getAvailableTags()
     *
     * @param mixed $slug
     * @return array
     */
    public function getAvailableTags($slug = null, $list_id = null)
    {
        if ($slug === null) {
            if ($this->owner instanceof ListPageType) {
                $slug = $this->owner->slug;
            } else {
                $slug = $this->owner->type->slug;
            }
        }

        $availableTags = array(
            'subscribe-form' => array(
                array('tag' => '[LIST_NAME]', 'required' => false),
                array('tag' => '[LIST_FIELDS]', 'required' => true),
                array('tag' => '[SUBMIT_BUTTON]', 'required' => true),
            ),
            'unsubscribe-form' => array(
                array('tag' => '[LIST_NAME]', 'required' => false),
                array('tag' => '[UNSUBSCRIBE_EMAIL_FIELD]', 'required' => true),
                array('tag' => '[UNSUBSCRIBE_REASON_FIELD]', 'required' => false),
                array('tag' => '[SUBMIT_BUTTON]', 'required' => true),
            ),
            'subscribe-pending' => array(
                array('tag' => '[LIST_NAME]', 'required' => false),
            ),
            'subscribe-confirm' => array(
                array('tag' => '[LIST_NAME]', 'required' => false),
                array('tag' => '[UPDATE_PROFILE_URL]', 'required' => false),
            ),
            'update-profile' => array(
                array('tag' => '[LIST_NAME]', 'required' => false),
                array('tag' => '[LIST_FIELDS]', 'required' => true),
                array('tag' => '[SUBMIT_BUTTON]', 'required' => true),
                array('tag' => '[UNSUBSCRIBE_URL]', 'required' => false),
            ),
            'unsubscribe-confirm' => array(
                array('tag' => '[LIST_NAME]', 'required' => false),
                array('tag' => '[SUBSCRIBE_URL]', 'required' => false),
            ),
            'subscribe-confirm-email' => array(
                array('tag' => '[LIST_NAME]', 'required' => false),
                array('tag' => '[COMPANY_NAME]', 'required' => false),
                array('tag' => '[CURRENT_YEAR]', 'required' => false),
                array('tag' => '[SUBSCRIBE_URL]', 'required' => false),
                // 1.5.3
                array('tag' => '[UPDATE_PROFILE_URL]', 'required' => false),
                array('tag' => '[UNSUBSCRIBE_URL]', 'required' => false),
                array('tag' => '[COMPANY_FULL_ADDRESS]', 'required' => false),
            ),
            'unsubscribe-confirm-email' => array(
                array('tag' => '[LIST_NAME]', 'required' => false),
                array('tag' => '[COMPANY_NAME]', 'required' => false),
                array('tag' => '[CURRENT_YEAR]', 'required' => false),
                array('tag' => '[UNSUBSCRIBE_URL]', 'required' => false),
                // 1.5.3
                array('tag' => '[COMPANY_FULL_ADDRESS]', 'required' => false),
            ),
            'welcome-email' => array(
                array('tag' => '[LIST_NAME]', 'required' => false),
                array('tag' => '[UPDATE_PROFILE_URL]', 'required' => false),
                array('tag' => '[COMPANY_NAME]', 'required' => false),
                array('tag' => '[CURRENT_YEAR]', 'required' => false),
                array('tag' => '[UNSUBSCRIBE_URL]', 'required' => false),
                array('tag' => '[COMPANY_FULL_ADDRESS]', 'required' => false),
            ),
            'subscribe-confirm-approval' => array(
                array('tag' => '[LIST_NAME]', 'required' => false),
            ),
            'subscribe-confirm-approval-email' => array(
                array('tag' => '[LIST_NAME]', 'required' => false),
                array('tag' => '[UPDATE_PROFILE_URL]', 'required' => false),
                array('tag' => '[COMPANY_NAME]', 'required' => false),
                array('tag' => '[CURRENT_YEAR]', 'required' => false),
                array('tag' => '[UNSUBSCRIBE_URL]', 'required' => false),
                array('tag' => '[COMPANY_FULL_ADDRESS]', 'required' => false),
            ),
        );

        // since 1.3.5.9
        $canLoadCustomFields = array_keys($availableTags);
        $toUnset = array('subscribe-form', 'unsubscribe-form', 'subscribe-pending');
        $canLoadCustomFields = array_diff($canLoadCustomFields, $toUnset);
        if (!empty($list_id) && in_array($slug, $canLoadCustomFields)) {
            $criteria = new CDbCriteria();
            $criteria->select = 'tag';
            $criteria->compare('list_id', (int)$list_id);
            $fields = ListField::model()->findAll($criteria);
            foreach ($availableTags as $_slug => $tags) {
                foreach ($fields as $field) {
                    $availableTags[$_slug][] = array('tag' => '['.$field->tag.']', 'required' => false);
                }
            }
        }
        //

        return isset($availableTags[$slug]) ? $availableTags[$slug] : array();
    }
}
