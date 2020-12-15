<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * This file is part of the MailWizz EMA application.
 *
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.0
 */

?>
<!DOCTYPE html>
<html dir="<?php echo $this->htmlOrientation;?>">
<head>
    <meta charset="<?php echo Yii::app()->charset;?>">
    <title><?php echo CHtml::encode($pageMetaTitle);?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?php echo CHtml::encode($pageMetaDescription);?>">
    <!--[if lt IE 9]>
    <script src="//oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
    <script src="//oss.maxcdn.com/libs/respond.js/1.3.0/respond.min.js"></script>
    <![endif]-->
</head>
<body class="<?php echo $this->bodyClasses;?>">
    <?php $this->afterOpeningBodyTag;?>
    <div class="wrapper">
        <header class="navbar navbar-default">
            <div class="col-lg-10 col-lg-push-1 col-md-10 col-md-push-1 col-sm-12 col-xs-12">
                <div class="navbar-header">
                    <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
                        <span class="sr-only"><?php echo Yii::t('app', 'Toggle navigation');?></span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                    </button>
                    <a class="navbar-brand" href="<?php echo Yii::app()->homeUrl;?>" title="<?php echo Yii::app()->options->get('system.common.site_name');?>">
                        <span><span><?php echo Yii::app()->options->get('system.common.site_name');?></span></span>
                    </a>
                </div>
                <div id="navbar" class="navbar-collapse collapse">
                    <!--<ul class="nav navbar-nav">
                        <li class=""><a href="#">1</a></li>
                        <li class=""><a href="#">2</a></li>
                        <li class=""><a href="#">3</a></li>
                    </ul>-->
                    <ul class="nav navbar-nav navbar-right">
                        <?php if (Yii::app()->options->get('system.customer_registration.enabled', 'no') == 'yes') { ?>
                            <li class="hidden-xs">
                                <a href="<?php echo Yii::app()->apps->getAppUrl('customer', 'guest/register');?>" class="btn btn-default btn-flat" title="<?php echo Yii::t('app', 'Sign up');?>">
                                    <?php echo Yii::t('app', 'Sign up');?>
                                </a>
                            </li>
                            <li class="hidden-lg hidden-md hidden-sm">
                                <a href="<?php echo Yii::app()->apps->getAppUrl('customer', 'guest/register');?>" class="" title="<?php echo Yii::t('app', 'Sign up');?>">
                                    <?php echo Yii::t('app', 'Sign up');?>
                                </a>
                            </li>
                        <?php } ?>
                        <li class="">
                            <a href="<?php echo Yii::app()->apps->getAppUrl('customer', 'guest/index');?>" title="<?php echo Yii::t('app', 'Login');?>">
                                <?php echo Yii::t('app', 'Login');?>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </header>
        <div class="content-wrapper">
            <div class="container-fluid">
                <div class="row row-large">
                    <div class="container-fluid-large col-lg-10 col-lg-push-1 col-md-10 col-md-push-1 col-sm-12 col-xs-12">
                        <div id="notify-container">
                            <?php echo Yii::app()->notify->show();?>
                        </div>
                        <?php echo $content;?>
                    </div>
                </div>
            </div>
        </div>
        <footer class="main-footer">
            <div class="container">
                <div class="row">
                    <div class="col-lg-3 col-md-3 col-sm-12 col-xs-12">
                        <span class="copyright">©<?php echo date('Y')?> <?php echo Yii::t('app', 'All rights reserved.');?></span>
                    </div>
                    <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
                        <ul class="links">
                            <?php if ($page = Page::findBySlug('terms-and-conditions')) { ?>
                                <li><a href="<?php echo $page->permalink;?>" title="<?php echo $page->title;?>"><?php echo $page->title;?></a></li>
                            <?php } ?>
                            <?php if ($page = Page::findBySlug('privacy-policy')) { ?>
                                <li><a href="<?php echo $page->permalink;?>" title="<?php echo $page->title;?>"><?php echo $page->title;?></a></li>
                            <?php } ?>
                            <li><a href="<?php echo $this->createUrl('articles/index');?>" title="<?php echo Yii::t('app', 'Articles');?>"><?php echo Yii::t('app', 'Articles');?></a></li>
                            <li><a href="<?php echo $this->createUrl('lists/block_address');?>" title="<?php echo Yii::t('app', 'Block my email');?>"><?php echo Yii::t('app', 'Block my email');?></a></li>
                        </ul>
                    </div>
                    <div class="col-lg-3 col-md-3 col-sm-12 col-xs-12">
                        <ul class="social">
                            <?php foreach (array('facebook', 'twitter', 'linkedin', 'instagram', 'youtube') as $item) { 
                                if (!($url = Yii::app()->options->get('system.social_links.' . $item, ''))) {
                                    continue;
                                }
                                ?>
                                <li>
                                    <a href="<?php echo $url;?>" title="<?php echo ucfirst($item);?>" target="_blank">
                                        <i class="fa fa-<?php echo $item;?>"></i>
                                    </a>
                                </li>
                            <?php } ?>
                        </ul>
                    </div>
                </div>
            </div>
            <?php $hooks->doAction('layout_footer_html', $this);?>
        </footer>
    </div>
</body>
</html>