<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * This file is part of the MailWizz EMA application.
 * 
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com> 
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.3.3.1
 */

/**
 * This hook gives a chance to prepend content or to replace the default view content with a custom content.
 * Please note that from inside the action callback you can access all the controller view
 * variables via {@CAttributeCollection $collection->controller->data}
 * In case the content is replaced, make sure to set {@CAttributeCollection $collection->renderContent} to false 
 * in order to stop rendering the default content.
 * @since 1.3.3.1
 */
$hooks->doAction('before_view_file_content', $viewCollection = new CAttributeCollection(array(
    'controller'    => $this,
    'renderContent' => true,
)));

// and render if allowed
if ($viewCollection->renderContent) {
    /**
     * This hook gives a chance to prepend content before the active form or to replace the default active form entirely.
     * Please note that from inside the action callback you can access all the controller view variables 
     * via {@CAttributeCollection $collection->controller->data}
     * In case the form is replaced, make sure to set {@CAttributeCollection $collection->renderForm} to false 
     * in order to stop rendering the default content.
     * @since 1.3.3.1
     */
    $hooks->doAction('before_active_form', $collection = new CAttributeCollection(array(
        'controller'    => $this,
        'renderForm'    => true,
    )));
    
    // and render if allowed
    if ($collection->renderForm) {
        $form = $this->beginWidget('CActiveForm');
        ?>
        <div class="box box-primary borderless">
            <div class="box-header">
                <div class="pull-left">
                    <?php BoxHeaderContent::make(BoxHeaderContent::LEFT)
                        ->add('<h3 class="box-title">' . IconHelper::make('glyphicon-filter') . $pageHeading . '</h3>')
                        ->render();
                    ?>
                </div>
                <div class="pull-right">
                    <?php BoxHeaderContent::make(BoxHeaderContent::RIGHT)
                        ->addIf(CHtml::link(IconHelper::make('create') . Yii::t('app', 'Create new'), array('bounce_servers/create'), array('class' => 'btn btn-primary btn-flat', 'title' => Yii::t('app', 'Create new'))), !$server->isNewRecord)
                        ->add(CHtml::link(IconHelper::make('cancel') . Yii::t('app', 'Cancel'), array('bounce_servers/index'), array('class' => 'btn btn-primary btn-flat', 'title' => Yii::t('app', 'Cancel'))))
                        ->add(CHtml::link(IconHelper::make('info'), '#page-info', array('class' => 'btn btn-primary btn-flat', 'title' => Yii::t('app', 'Info'), 'data-toggle' => 'modal')))
                        ->render();
                    ?>
                </div>
                <div class="clearfix"><!-- --></div>
            </div>
            <div class="box-body">
                <?php 
                /**
                 * This hook gives a chance to prepend content before the active form fields.
                 * Please note that from inside the action callback you can access all the controller view variables 
                 * via {@CAttributeCollection $collection->controller->data}
                 * @since 1.3.3.1
                 */
                $hooks->doAction('before_active_form_fields', new CAttributeCollection(array(
                    'controller'    => $this,
                    'form'          => $form    
                )));
                ?>
                <div class="row">
                    <div class="col-lg-3">
                        <div class="form-group">
                            <?php echo $form->labelEx($server, 'hostname');?>
                            <?php echo $form->textField($server, 'hostname', $server->getHtmlOptions('hostname')); ?>
                            <?php echo $form->error($server, 'hostname');?>
                        </div>
                    </div>
                    <div class="col-lg-3">
                        <div class="form-group">
                            <?php echo $form->labelEx($server, 'username');?>
                            <?php echo $form->textField($server, 'username', $server->getHtmlOptions('username')); ?>
                            <?php echo $form->error($server, 'username');?>
                        </div>
                    </div>
                    <div class="col-lg-3">
                        <div class="form-group">
                            <?php echo $form->labelEx($server, 'password');?>
                            <?php echo $form->passwordField($server, 'password', $server->getHtmlOptions('password', array('value' => ''))); ?>
                            <?php echo $form->error($server, 'password');?>
                        </div>
                    </div>
                    <div class="col-lg-3">
                        <div class="form-group">
                            <?php echo $form->labelEx($server, 'email');?>
                            <?php echo $form->emailField($server, 'email', $server->getHtmlOptions('email')); ?>
                            <?php echo $form->error($server, 'email');?>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-3">
                        <div class="form-group">
                            <?php echo $form->labelEx($server, 'service');?>
                            <?php echo $form->dropDownList($server, 'service', $server->getServicesArray(), $server->getHtmlOptions('service')); ?>
                            <?php echo $form->error($server, 'service');?>
                        </div>
                    </div>
                    <div class="col-lg-3">
                        <div class="form-group">
                            <?php echo $form->labelEx($server, 'port');?>
                            <?php echo $form->numberField($server, 'port', $server->getHtmlOptions('port')); ?>
                            <?php echo $form->error($server, 'port');?>
                        </div>
                    </div>
                    <div class="col-lg-3">
                        <div class="form-group">
                            <?php echo $form->labelEx($server, 'protocol');?>
                            <?php echo $form->dropDownList($server, 'protocol', $server->getProtocolsArray(), $server->getHtmlOptions('protocol')); ?>
                            <?php echo $form->error($server, 'protocol');?>
                        </div>
                    </div>
                    <div class="col-lg-3">
                        <div class="form-group">
                            <?php echo $form->labelEx($server, 'validate_ssl');?>
                            <?php echo $form->dropDownList($server, 'validate_ssl', $server->getValidateSslOptions(), $server->getHtmlOptions('validate_ssl')); ?>
                            <?php echo $form->error($server, 'validate_ssl');?>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-3">
                        <div class="form-group">
                            <?php echo $form->labelEx($server, 'search_charset');?>
                            <?php echo $form->textField($server, 'search_charset', $server->getHtmlOptions('search_charset')); ?>
                            <?php echo $form->error($server, 'search_charset');?>
                        </div>
                    </div>
                    <div class="col-lg-3">
                        <div class="form-group">
                            <?php echo $form->labelEx($server, 'disable_authenticator');?>
                            <?php echo $form->textField($server, 'disable_authenticator', $server->getHtmlOptions('disable_authenticator')); ?>
                            <?php echo $form->error($server, 'disable_authenticator');?>
                        </div>
                    </div>
                    <div class="col-lg-3">
                        <div class="form-group">
                            <?php echo $form->labelEx($server, 'delete_all_messages');?>
                            <?php echo $form->dropDownList($server, 'delete_all_messages', $server->getYesNoOptions(), $server->getHtmlOptions('delete_all_messages')); ?>
                            <?php echo $form->error($server, 'delete_all_messages');?>
                        </div>
                    </div>
                </div>
                <?php 
                /**
                 * This hook gives a chance to append content after the active form fields.
                 * Please note that from inside the action callback you can access all the controller view variables 
                 * via {@CAttributeCollection $collection->controller->data}
                 * @since 1.3.3.1
                 */
                $hooks->doAction('after_active_form_fields', new CAttributeCollection(array(
                    'controller'    => $this,
                    'form'          => $form    
                )));
                ?> 
                <div class="clearfix"><!-- --></div>                           
            </div>
            <div class="box-footer">
                <div class="pull-right">
                    <button type="submit" class="btn btn-primary btn-flat"><?php echo IconHelper::make('save') . Yii::t('app', 'Save changes');?></button>
                </div>
                <div class="clearfix"><!-- --></div>
            </div>
        </div>
        <?php 
        $this->endWidget(); 
    } 
    /**
     * This hook gives a chance to append content after the active form.
     * Please note that from inside the action callback you can access all the controller view variables 
     * via {@CAttributeCollection $collection->controller->data}
     * @since 1.3.3.1
     */
    $hooks->doAction('after_active_form', new CAttributeCollection(array(
        'controller'      => $this,
        'renderedForm'    => $collection->renderForm,
    )));
    ?>
    <!-- modals -->
    <div class="modal modal-info fade" id="page-info" tabindex="-1" role="dialog">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title"><?php echo IconHelper::make('info') . Yii::t('app',  'Info');?></h4>
                </div>
                <div class="modal-body">
                    <?php
                    $text = 'Please note that the server settings will be checked when you save the server and the save process will be denied if there are any connection errors.<br />
                    Also, this is a good chance to see how long it takes from the moment you hit the save button till the moment the changes are saved  because this is the same amount of time it will take the script to connect to the server and retrieve the bounced emails.<br />
                    Some of the servers, like gmail for example, are very slow if you use a hostname(i.e: imap.gmail.com). If that\'s the case, then simply instead of the hostname, use the IP address.<br />
                    You can use a service like <a target="_blank" href="http://www.hcidata.info/host2ip.htm">hcidata.info</a> to find out the IP address of any hostname.';
                    echo Yii::t('servers', StringHelper::normalizeTranslationString($text));
                    ?>
                </div>
            </div>
        </div>
    </div>
<?php 
}
/**
 * This hook gives a chance to append content after the view file default content.
 * Please note that from inside the action callback you can access all the controller view
 * variables via {@CAttributeCollection $collection->controller->data}
 * @since 1.3.3.1
 */
$hooks->doAction('after_view_file_content', new CAttributeCollection(array(
    'controller'        => $this,
    'renderedContent'   => $viewCollection->renderContent,
)));