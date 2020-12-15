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
        $form = $this->beginWidget('CActiveForm'); ?>
        <div class="box box-primary borderless">
            <div class="box-header">
                <div class="pull-left">
                    <?php BoxHeaderContent::make(BoxHeaderContent::LEFT)
                        ->add('<h3 class="box-title">' . IconHelper::make('glyphicon-credit-card') . $pageHeading . '</h3>')
                        ->render();
                    ?>
                </div>
                <div class="pull-right">
                    <?php BoxHeaderContent::make(BoxHeaderContent::RIGHT)
                        ->addIf(HtmlHelper::accessLink(IconHelper::make('create') . Yii::t('app', 'Create new'), array('orders/create'), array('class' => 'btn btn-primary btn-flat', 'title' => Yii::t('app', 'Create new'))), !$order->isNewRecord)
                        ->add(HtmlHelper::accessLink(IconHelper::make('cancel') . Yii::t('app', 'Cancel'), array('orders/index'), array('class' => 'btn btn-primary btn-flat', 'title' => Yii::t('app', 'Cancel'))))
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
                    <div class="col-lg-4">
                        <div class="form-group">
                            <?php echo $form->labelEx($order, 'customer_id');?>
                            <?php echo $form->hiddenField($order, 'customer_id', $order->getHtmlOptions('customer_id')); ?>
                            <?php
                            $this->widget('zii.widgets.jui.CJuiAutoComplete',array(
                                'name'          => 'customer',
                                'value'         => !empty($order->customer_id) ? $order->customer->getFullName() : '',
                                'source'        => $this->createUrl('customers/autocomplete'),
                                'cssFile'       => false,
                                'options'       => array(
                                    'minLength' => '2',
                                    'select'    => 'js:function(event, ui) {
                                $("#'.CHtml::activeId($order, 'customer_id').'").val(ui.item.customer_id);
                            }',
                                    'search'    => 'js:function(event, ui) {
                                $("#'.CHtml::activeId($order, 'customer_id').'").val("");
                            }',
                                    'change'    => 'js:function(event, ui) {
                                if (!ui.item) {
                                    $("#'.CHtml::activeId($order, 'customer_id').'").val("");
                                }
                            }',
                                ),
                                'htmlOptions'   => $order->getHtmlOptions('customer_id'),
                            ));
                            ?>
                            <?php echo $form->error($order, 'customer_id');?>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="form-group">
                            <?php echo $form->labelEx($order, 'plan_id');?>
                            <?php echo $form->hiddenField($order, 'plan_id', $order->getHtmlOptions('plan_id')); ?>
                            <?php
                            $this->widget('zii.widgets.jui.CJuiAutoComplete',array(
                                'name'          => 'plan',
                                'value'         => !empty($order->plan_id) ? $order->plan->name : '',
                                'source'        => $this->createUrl('price_plans/autocomplete'),
                                'cssFile'       => false,
                                'options'       => array(
                                    'minLength' => '2',
                                    'select'    => 'js:function(event, ui) {
                                $("#'.CHtml::activeId($order, 'plan_id').'").val(ui.item.plan_id);
                            }',
                                    'search'    => 'js:function(event, ui) {
                                $("#'.CHtml::activeId($order, 'plan_id').'").val("");
                            }',
                                    'change'    => 'js:function(event, ui) {
                                if (!ui.item) {
                                    $("#'.CHtml::activeId($order, 'plan_id').'").val("");
                                }
                            }',
                                ),
                                'htmlOptions'   => $order->getHtmlOptions('plan_id'),
                            ));
                            ?>
                            <?php echo $form->error($order, 'plan_id');?>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="form-group">
                            <?php echo $form->labelEx($order, 'promo_code_id');?>
                            <?php echo $form->hiddenField($order, 'promo_code_id', $order->getHtmlOptions('promo_code_id')); ?>
                            <?php
                            $this->widget('zii.widgets.jui.CJuiAutoComplete',array(
                                'name'          => 'promoCode',
                                'value'         => !empty($order->promo_code_id) ? $order->promoCode->code : '',
                                'source'        => $this->createUrl('promo_codes/autocomplete'),
                                'cssFile'       => false,
                                'options'       => array(
                                    'minLength' => '2',
                                    'select'    => 'js:function(event, ui) {
                                $("#'.CHtml::activeId($order, 'promo_code_id').'").val(ui.item.promo_code_id);
                            }',
                                    'search'    => 'js:function(event, ui) {
                                $("#'.CHtml::activeId($order, 'promo_code_id').'").val("");
                            }',
                                    'change'    => 'js:function(event, ui) {
                                if (!ui.item) {
                                    $("#'.CHtml::activeId($order, 'promo_code_id').'").val("");
                                }
                            }',
                                ),
                                'htmlOptions'   => $order->getHtmlOptions('promo_code_id'),
                            ));
                            ?>
                            <?php echo $form->error($order, 'promo_code_id');?>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-4">
                        <div class="form-group">
                            <?php echo $form->labelEx($order, 'subtotal');?>
                            <?php echo $form->textField($order, 'subtotal', $order->getHtmlOptions('subtotal')); ?>
                            <?php echo $form->error($order, 'subtotal');?>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="form-group">
                            <?php echo $form->labelEx($order, 'discount');?>
                            <?php echo $form->textField($order, 'discount', $order->getHtmlOptions('discount')); ?>
                            <?php echo $form->error($order, 'discount');?>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="form-group">
                            <?php echo $form->labelEx($order, 'total');?>
                            <?php echo $form->textField($order, 'total', $order->getHtmlOptions('total')); ?>
                            <?php echo $form->error($order, 'total');?>
                        </div>
                    </div>
                </div>      
                <div class="row">
                    <div class="col-lg-4">
                        <div class="form-group">
                            <?php echo $form->labelEx($order, 'tax_id');?>
                            <?php echo $form->dropDownList($order, 'tax_id', CMap::mergeArray(array(''=>'---'), Tax::getAsDropdownOptions()), $order->getHtmlOptions('tax_id')); ?>
                            <?php echo $form->error($order, 'tax_id');?>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="form-group">
                            <?php echo $form->labelEx($order, 'tax_percent');?>
                            <?php echo $form->textField($order, 'tax_percent', $order->getHtmlOptions('tax_percent')); ?>
                            <?php echo $form->error($order, 'tax_percent');?>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="form-group">
                            <?php echo $form->labelEx($order, 'tax_value');?>
                            <?php echo $form->textField($order, 'tax_value', $order->getHtmlOptions('tax_value')); ?>
                            <?php echo $form->error($order, 'tax_value');?>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-4">
                        <div class="form-group">
                            <?php echo $form->labelEx($order, 'status');?>
                            <?php echo $form->dropDownList($order, 'status', $order->getStatusesList(), $order->getHtmlOptions('status')); ?>
                            <?php echo $form->error($order, 'status');?>
                        </div>
                    </div>
                </div>
                <hr />
                <div class="row">
                    <div class="col-lg-12">
                        <div class="form-group">
                            <?php echo $form->label($note, 'note');?>
                            <?php echo $form->textArea($note, 'note', $note->getHtmlOptions('note')); ?>
                            <?php echo $form->error($note, 'note');?>
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
                <div class="row">
                    <div class="col-lg-12">
                        <div class="table-responsive">
                            <?php
                            $this->widget('zii.widgets.grid.CGridView', $hooks->applyFilters('grid_view_properties', array(
                                'ajaxUrl'           => $this->createUrl($this->route, array('id' => (int)$order->order_id)),
                                'id'                => $note->modelName.'-grid',
                                'dataProvider'      => $note->search(),
                                'filter'            => null,
                                'filterPosition'    => 'body',
                                'filterCssClass'    => 'grid-filter-cell',
                                'itemsCssClass'     => 'table table-hover',
                                'selectableRows'    => 0,
                                'enableSorting'     => false,
                                'cssFile'           => false,
                                'pagerCssClass'     => 'pagination pull-right',
                                'pager'             => array(
                                    'class'         => 'CLinkPager',
                                    'cssFile'       => false,
                                    'header'        => false,
                                    'htmlOptions'   => array('class' => 'pagination')
                                ),
                                'columns' => $hooks->applyFilters('grid_view_columns', array(
                                    array(
                                        'name'  => 'author',
                                        'value' => '$data->getAuthor()',
                                    ),
                                    array(
                                        'name'  => 'note',
                                        'value' => '$data->note',
                                    ),
                                    array(
                                        'name'  => 'date_added',
                                        'value' => '$data->dateAdded',
                                    ),
                                    array(
                                        'class'     => 'CButtonColumn',
                                        'header'    => Yii::t('app', 'Options'),
                                        'footer'    => $note->paginationOptions->getGridFooterPagination(),
                                        'buttons'   => array(
                                            'delete' => array(
                                                'label'     => IconHelper::make('delete'),
                                                'url'       => 'Yii::app()->createUrl("orders/delete_note", array("id" => $data->note_id))',
                                                'imageUrl'  => null,
                                                'options'   => array('title' => Yii::t('app', 'Delete'), 'class' => 'btn btn-danger btn-flat delete'),
                                            ),
                                        ),
                                        'headerHtmlOptions' => array('style' => 'text-align:right'),
                                        'footerHtmlOptions' => array('align' => 'right'),
                                        'htmlOptions'       => array('align' => 'right', 'class' => 'options'),
                                        'template'          => '{delete}'
                                    ),
                                ), $this),
                            ), $this));
                            ?>
                        </div>
                    </div>
                </div> 
            </div>
            <div class="box-footer">
                <div class="pull-right">
                    <?php if (!$order->isNewRecord) { ?>
                    <a href="<?php echo $this->createUrl('orders/view', array('id' => $order->order_id));?>" class="btn btn-primary btn-flat"><?php echo IconHelper::make('view') . Yii::t('orders', 'View order');?></a>
                    <?php } ?>
                    <button type="submit" class="btn btn-primary btn-flat"><?php echo IconHelper::make('save') . Yii::t('app', 'Save changes');?></button>
                </div>
                <div class="clearfix"><!-- --></div>
            </div>
        </div>
        <!-- modals -->
        <div class="modal modal-info fade" id="page-info" tabindex="-1" role="dialog">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                        <h4 class="modal-title"><?php echo IconHelper::make('info') . Yii::t('app',  'Info');?></h4>
                    </div>
                    <div class="modal-body">
                        <?php echo Yii::t('orders', 'Please note that any order added/changed from this area is not verified nor it goes through a payment gateway.');?><br />
                        <?php echo Yii::t('orders', 'Updating orders from this area is useful for offline orders mostly or for payment corrections.');?><br />
                        <?php echo Yii::t('orders', 'If the order is incomplete, pending or due and changed to complete, the customer will be affected and the price plan will be assigned properly.');?><br />
                    </div>
                </div>
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