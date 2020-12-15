<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * This file is part of the MailWizz EMA application.
 *
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.5.5
 */
?>

<div class="hide_overflow">

    <div class="section section_welcome">
        <div class="bg_image" style="background-image: url('<?php echo AssetsUrl::img('bg_image_welcome_2.jpg');?>');">
            <img src="<?php echo AssetsUrl::img('bg_image_welcome_2.jpg');?>" width="" height="" alt="<?php echo Yii::t('app', 'Email marketing');?>" />
        </div>
        <div class="container">
            <div class="row">
                <div class="col-lg-8 col-md-10 col-sm-8 col-xs-11">
                    <h1><?php echo Yii::t('app', 'Email marketing.');?><span><?php echo Yii::t('app', 'Made easy, finally.');?></span></h1>
                    <?php if (Yii::app()->options->get('system.customer_registration.enabled', 'no') == 'yes') { ?>
                        <a href="<?php echo Yii::app()->apps->getAppUrl('customer', 'guest/register');?>" class="btn btn-primary btn-flat"><?php echo Yii::t('app', 'Sign up free');?></a>
                    <?php } ?>
                    <p>
                        <?php echo Yii::t('app', 'Using {siteName} you will easily grow your lists, increase conversions, and optimise your audience engagement with beautiful emails and autoresponders, high-converting web forms, list segmentation, and unique delivery tools.', array(
                            '{siteName}' => $siteName,
                        ));?>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <div class="section section_why">
        <div class="bg_image" style="background-image: url('<?php echo AssetsUrl::img('bg_image_why_2.png');?>');">
            <img src="<?php echo AssetsUrl::img('bg_image_why_2.png');?>" width="" height="" alt="<?php echo Yii::t('app', 'Send better email');?>" />
        </div>
        <div class="container">
            <div class="row">
                <div class="col-lg-9 col-lg-push-3 col-md-9 col-md-push-3 col-sm-8 col-sm-push-3 col-xs-12">
                    <h2><?php echo Yii::t('app', 'Send better email');?></h2>
                    <p><?php echo Yii::t('app', 'Whether you need to sell your products, share some big news, or tell a story, our email template builder makes it easy to create an email marketing campaign that best suit your target audience.');?></p>
                    <div class="image_wrapper">
                        <img src="<?php echo AssetsUrl::img('home_landing_1.jpg');?>" width="" height="" alt="<?php echo Yii::t('app', 'Send better email');?>" />
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="section section_support">
        <div class="bg_image" style="background-image: url('<?php echo AssetsUrl::img('bg_image_doing.png');?>');">
            <img src="<?php echo AssetsUrl::img('bg_image_doing.png');?>" width="" height="" alt="<?php echo Yii::t('app', 'See how you\'re doing');?>" />
        </div>
        <div class="container">
            <div class="row">
                <div class="col-lg-10 col-lg-push-1 col-md-10 col-md-push-1 col-sm-8 col-sm-push-2 col-xs-11">
                    <h2><?php echo Yii::t('app', 'See how you\'re doing');?></h2>
                    <p>
                        <?php echo Yii::t('app', '{siteName} reports show you how well you\'re connecting with your audience. You get detailed reports for opens, clicks, unsubscribes, bounces, complains and much more, all shown in a simple and clear way.', array(
                            '{siteName}' => $siteName,
                        ));?>
                    </p>
                    <div class="image_wrapper">
                        <img src="<?php echo AssetsUrl::img('home_landing_2.jpg');?>" width="" height="" alt="<?php echo Yii::t('app', 'See how you\'re doing');?>" />
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php if (Yii::app()->options->get('system.customer_registration.enabled', 'no') == 'yes') { ?>
    <div class="section section_contact" style="background-image: url('<?php echo AssetsUrl::img('bg_image_bottom.jpg');?>');">
        <div class="container">
            <div class="row">
                <div class="col-lg-10 col-lg-push-1 col-md-10 col-md-push-1 col-sm-8 col-sm-push-2 col-xs-11">
                    <h2><?php echo Yii::t('app', 'Get started today');?></h2>
                    <p>
                        <?php echo Yii::t('app', 'Get started today with {siteName}, reach your target audience, improve conversions and grow your business.', array(
                            '{siteName}' => $siteName,
                        ));?>
                    </p>
                    <a href="<?php echo Yii::app()->apps->getAppUrl('customer', 'guest/register');?>" class="btn btn-default btn-flat"><?php echo Yii::t('app', 'Sign up free');?></a>
                </div>
            </div>
        </div>
    </div>
    <?php } ?>
    
</div>