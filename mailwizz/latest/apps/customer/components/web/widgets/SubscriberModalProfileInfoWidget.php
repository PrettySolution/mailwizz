<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * SubscriberModalProfileInfoWidget
 *
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.3.9.8
 */

class SubscriberModalProfileInfoWidget extends CWidget
{
    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        Yii::app()->clientScript->registerScriptFile(AssetsUrl::js('subscriber-modal-profile-info.js'));
    }

    /**
     * @inheritdoc
     */
    public function run()
    {
        $this->render('subscriber-modal-profile-info');
    }
}