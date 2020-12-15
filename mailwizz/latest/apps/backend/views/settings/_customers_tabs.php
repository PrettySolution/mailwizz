<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * This file is part of the MailWizz EMA application.
 * 
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com> 
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.3.4
 */
 
?>
<div class="callout callout-info">
    <?php echo Yii::t('settings', 'Please note that most of the customer settings will also be found in customer groups allowing you a fine grained control over your customers and their limits/permissions.');?>
</div>
<ul class="nav nav-tabs" style="border-bottom: 0px;">
    <li class="<?php echo $this->getAction()->getId() == 'customer_common' ? 'active' : 'inactive';?>">
        <a href="<?php echo $this->createUrl('settings/customer_common')?>">
            <?php echo Yii::t('settings', 'Common');?>
        </a>
    </li>
    <li class="<?php echo $this->getAction()->getId() == 'customer_servers' ? 'active' : 'inactive';?>">
        <a href="<?php echo $this->createUrl('settings/customer_servers')?>">
            <?php echo Yii::t('settings', 'Servers');?>
        </a>
    </li>
    <li class="<?php echo $this->getAction()->getId() == 'customer_domains' ? 'active' : 'inactive';?>">
        <a href="<?php echo $this->createUrl('settings/customer_domains')?>">
            <?php echo Yii::t('settings', 'Domains');?>
        </a>
    </li>
    <li class="<?php echo $this->getAction()->getId() == 'customer_lists' ? 'active' : 'inactive';?>">
        <a href="<?php echo $this->createUrl('settings/customer_lists')?>">
            <?php echo Yii::t('settings', 'Lists');?>
        </a>
    </li>
    <li class="<?php echo $this->getAction()->getId() == 'customer_campaigns' ? 'active' : 'inactive';?>">
        <a href="<?php echo $this->createUrl('settings/customer_campaigns')?>">
            <?php echo Yii::t('settings', 'Campaigns');?>
        </a>
    </li>
    <li class="<?php echo $this->getAction()->getId() == 'customer_surveys' ? 'active' : 'inactive';?>">
        <a href="<?php echo $this->createUrl('settings/customer_surveys')?>">
            <?php echo Yii::t('settings', 'Surveys');?>
        </a>
    </li>
    <li class="<?php echo $this->getAction()->getId() == 'customer_quota_counters' ? 'active' : 'inactive';?>">
        <a href="<?php echo $this->createUrl('settings/customer_quota_counters')?>">
            <?php echo Yii::t('settings', 'Quota counters');?>
        </a>
    </li>
    <li class="<?php echo $this->getAction()->getId() == 'customer_sending' ? 'active' : 'inactive';?>">
        <a href="<?php echo $this->createUrl('settings/customer_sending')?>">
            <?php echo Yii::t('settings', 'Sending');?>
        </a>
    </li>
    <li class="<?php echo $this->getAction()->getId() == 'customer_cdn' ? 'active' : 'inactive';?>">
        <a href="<?php echo $this->createUrl('settings/customer_cdn')?>">
            <?php echo Yii::t('settings', 'CDN');?>
        </a>
    </li>
    <li class="<?php echo $this->getAction()->getId() == 'customer_registration' ? 'active' : 'inactive';?>">
        <a href="<?php echo $this->createUrl('settings/customer_registration')?>">
            <?php echo Yii::t('settings', 'Registration');?>
        </a>
    </li>
    <li class="<?php echo $this->getAction()->getId() == 'customer_api' ? 'active' : 'inactive';?>">
        <a href="<?php echo $this->createUrl('settings/customer_api')?>">
            <?php echo Yii::t('settings', 'API');?>
        </a>
    </li>
</ul>