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
            <header class="main-header">
                <nav class="navbar navbar-static-top">
                    <!-- Sidebar toggle button-->
                    <a href="#" class="sidebar-toggle" data-toggle="offcanvas" role="button">
                        <span class="sr-only">Toggle navigation</span>
                    </a>
                    <div class="navbar-custom-menu">
                        <ul class="nav navbar-nav">
                            <?php $hooks->doAction('layout_top_navbar_menu_items_start', $this);?>
                            <li class="dropdown messages-menu">
                                <a href="javascript:;" class="header-messages dropdown-toggle" data-url="<?php echo Yii::app()->createUrl('messages/header');?>" data-toggle="dropdown" title="<?php echo Yii::t('customers', 'Messages');?>">
                                    <i class="fa fa-envelope"></i>
                                    <span class="label label-success"></span>
                                </a>
                                <ul class="dropdown-menu">
                                    <li class="header"> <!----> </li>
                                    <li>
                                        <ul class="menu">
                                            <li></li>
                                        </ul>
                                    </li>
                                    <li class="footer">
                                        <a href="<?php echo Yii::app()->createUrl('messages/index');?>"><?php echo Yii::t('messages', 'See all messages');?></a>
                                    </li>
                                </ul>
                            </li>
                            <li class="dropdown user user-menu">
                                <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                                    <i class="glyphicon glyphicon-user"></i>
                                    <span><?php echo ($fullName = Yii::app()->user->getModel()->getFullName()) ? CHtml::encode($fullName) : Yii::t('app', 'Welcome');?> <i class="caret"></i></span>
                                </a>
                                <ul class="dropdown-menu">
                                    <li class="user-header bg-light-blue">
                                        <img src="<?php echo Yii::app()->user->getModel()->getAvatarUrl(90, 90);?>" class="img-circle"/>
                                        <p>
                                            <?php echo ($fullName = Yii::app()->user->getModel()->getFullName()) ? CHtml::encode($fullName) : Yii::t('app', 'Welcome');?>
                                        </p>
                                    </li>
                                    <li class="user-footer">
                                        <div class="pull-left">
                                            <a href="<?php echo $this->createUrl('account/index');?>" class="btn btn-default btn-flat"><?php echo Yii::t('app', 'My Account');?></a>
                                        </div>
                                        <div class="pull-right">
                                            <a href="<?php echo $this->createUrl('account/logout');?>" class="btn btn-default btn-flat"><?php echo Yii::t('app', 'Logout');?></a>
                                        </div>
                                    </li>
                                </ul>
                            </li>
                            <?php $hooks->doAction('layout_top_navbar_menu_items_end', $this);?>
                        </ul>
                    </div>
    
                </nav>
            </header>
            <aside class="main-sidebar">
                <section class="sidebar">

                    <div class="user-panel">
                        <?php if (!$hooks->hasActions('backend_layout_main_sidebar_user_panel')) { ?>
                            <div class="pull-left image">
                                <img src="<?php echo Yii::app()->user->getModel()->getAvatarUrl(90, 90);?>" class="img-circle" />
                            </div>
                            <div class="pull-left info">
                                <p><?php echo ($fullName = Yii::app()->user->getModel()->getFullName()) ? CHtml::encode($fullName) : Yii::t('app', 'Welcome');?></p>
                            </div>
                        <?php } else {
                            $hooks->doAction('backend_layout_main_sidebar_user_panel', $this);
                        } ?>
                    </div>
                    
                    <?php $this->widget('backend.components.web.widgets.LeftSideNavigationWidget');?>
                    <?php if (Yii::app()->options->get('system.common.show_backend_timeinfo', 'no') == 'yes' && version_compare(MW_VERSION, '1.3.4.4', '>=')) { ?>
                        <div class="timeinfo">
                            <div class="pull-left"><?php echo Yii::t('app', 'Local time')?></div>
                            <div class="pull-right"><?php echo Yii::app()->user->getModel()->dateTimeFormatter->formatDateTime();?></div>
                            <div class="clearfix"><!-- --></div>
                            <div class="pull-left"><?php echo Yii::t('app', 'System time')?></div>
                            <div class="pull-right"><?php echo date('Y-m-d H:i:s');?></div>
                            <div class="clearfix"><!-- --></div>
                        </div>
                    <?php } ?>
                </section>
                <!-- /.sidebar -->
            </aside>
            <div class="content-wrapper">
                <section class="content-header">
                    <h1><?php echo !empty($pageHeading) ? $pageHeading : '&nbsp;';?></h1>
                    <?php
                    $this->widget('zii.widgets.CBreadcrumbs', array(
                        'tagName'               => 'ol',
                        'separator'             => '',
                        'htmlOptions'           => array('class' => 'breadcrumb'),
                        'activeLinkTemplate'    => '<li><a href="{url}">{label}</a>  <span class="divider"></span></li>',
                        'inactiveLinkTemplate'  => '<li class="active">{label} </li>',
                        'homeLink'              => CHtml::tag('li', array(), CHtml::link(Yii::t('app', 'Dashboard'), $this->createUrl('dashboard/index')) . '<span class="divider"></span>' ),
                        'links'                 => $hooks->applyFilters('layout_page_breadcrumbs', $pageBreadcrumbs),
                    ));
                    ?>
                </section>
                <section class="content">
                    <div id="notify-container">
                        <?php echo Yii::app()->notify->show();?>
                    </div>
                    <?php echo $content;?>
                </section>
            </div>
            <footer class="main-footer">
                <?php $hooks->doAction('layout_footer_html', $this);?>
                <div class="pull-right no-print">
                    <?php echo Yii::t('app', 'Processed by version {version} in {seconds} seconds using {memory} mb of memory', array(
                        '{version}' => MW_VERSION,
                        '{seconds}' => round(Yii::getLogger()->getExecutionTime(), 3),
                        '{memory}'  => round(Yii::getLogger()->getMemoryUsage() / 1024 / 1024, 3),
                    ));?>
                </div>
                <div class="clearfix"><!-- --></div>
            </footer>
             
        </div>
    </body>
</html>