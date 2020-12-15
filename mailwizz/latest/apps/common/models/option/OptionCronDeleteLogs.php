<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * OptionCronDeleteLogs
 *
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.7.9
 */

class OptionCronDeleteLogs extends OptionBase
{
	/**
	 * @var string 
	 */
    protected $_categoryName = 'system.cron.delete_logs';

	/**
	 * @var string 
	 */
    public $delete_campaign_delivery_logs = 'no';

	/**
	 * @var string 
	 */
	public $delete_campaign_bounce_logs = 'no';

	/**
	 * @var string
	 */
	public $delete_campaign_open_logs = 'no';

	/**
	 * @var string
	 */
	public $delete_campaign_click_logs = 'no';

	/**
	 * @inheritdoc
	 */
    public function rules()
    {
        $rules = array(
            array('delete_campaign_delivery_logs, delete_campaign_bounce_logs, delete_campaign_open_logs, delete_campaign_click_logs', 'required'),
            array('delete_campaign_delivery_logs, delete_campaign_bounce_logs, delete_campaign_open_logs, delete_campaign_click_logs', 'in', 'range' => array_keys($this->getYesNoOptions())),
        );

        return CMap::mergeArray($rules, parent::rules());
    }

	/**
	 * @inheritdoc
	 */
    public function attributeLabels()
    {
        $labels = array(
            'delete_campaign_delivery_logs' => Yii::t('settings', 'Delete campaign delivery logs'),
            'delete_campaign_bounce_logs'   => Yii::t('settings', 'Delete campaign bounce logs'),
            'delete_campaign_open_logs'     => Yii::t('settings', 'Delete campaign open logs'),
            'delete_campaign_click_logs'    => Yii::t('settings', 'Delete campaign click logs'),
        );

        return CMap::mergeArray($labels, parent::attributeLabels());
    }

	/**
	 * @inheritdoc
	 */
    public function attributeHelpTexts()
    {
        $texts = array(
            'delete_campaign_delivery_logs' => Yii::t('settings', 'Whether to delete the campaign delivery logs after the campaign has been sent. If this is enabled, you will not be able to see the logs related to delivery but it will improve overall system performance. Keep in mind that we purge the logs after {n} days since the campaign finishes sending.', array(
                '{n}' => Yii::app()->params['campaign.delivery.logs.delete.days_back']
            )),

            'delete_campaign_bounce_logs' => Yii::t('settings', 'Whether to delete the campaign bounce logs after the campaign has been sent. If this is enabled, you will not be able to see the logs related to bounces but it will improve overall system performance. Keep in mind that we purge the logs after {n} days since the campaign finishes sending.', array(
	            '{n}' => Yii::app()->params['campaign.bounce.logs.delete.days_back']
            )),

            'delete_campaign_open_logs' => Yii::t('settings', 'Whether to delete the campaign open logs after the campaign has been sent. If this is enabled, you will not be able to see the logs related to opens but it will improve overall system performance. Keep in mind that we purge the logs after {n} days since the campaign finishes sending.', array(
	            '{n}' => Yii::app()->params['campaign.open.logs.delete.days_back']
            )),

            'delete_campaign_click_logs' => Yii::t('settings', 'Whether to delete the campaign click logs after the campaign has been sent. If this is enabled, you will not be able to see the logs related to clicks but it will improve overall system performance. Keep in mind that we purge the logs after {n} days since the campaign finishes sending.', array(
	            '{n}' => Yii::app()->params['campaign.click.logs.delete.days_back']
            )),
        );

        return CMap::mergeArray($texts, parent::attributeHelpTexts());
    }
}
