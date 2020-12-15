<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * This file is part of the MailWizz EMA application.
 * 
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com> 
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 */
 
?>


<div class="price-plan-payment">
    <div class="row">
        <div class="col-xs-12">
            <h2 class="page-header">
                <i class="fa fa-credit-card"></i> <?php echo $pricePlan->name;?>
                <small class="pull-right">
                    <?php echo $order->getAttributeLabel('order_uid');?> <b><?php echo $order->uid;?></b>, 
                    <?php echo $order->getAttributeLabel('date_added')?>: <?php echo $order->dateAdded;?>
                </small>
            </h2>                            
        </div>
    </div>

    <div class="row invoice-info">
        <?php if ($hooks->applyFilters('price_plan_order_payment_from_to_layout', 'from-to') == 'from-to') { ?>
            <div class="col-sm-4 invoice-col">
                <?php echo Yii::t('orders', $hooks->applyFilters('price_plan_order_payment_from_text', 'Payment from'));?>
                <address>
                    <?php echo $order->htmlPaymentFrom;?>
                </address>
            </div>
            <div class="col-sm-4 invoice-col pull-<?php echo $hooks->applyFilters('price_plan_order_payment_to_position', 'xright');?>">
                <?php echo Yii::t('orders', $hooks->applyFilters('price_plan_order_payment_to_text', 'Payment to'));?>
                <address>
                    <?php echo $order->htmlPaymentTo;?>
                </address>
            </div>
        <?php } else { ?>
            <div class="col-sm-4 invoice-col">
                <?php echo Yii::t('orders', $hooks->applyFilters('price_plan_order_payment_to_text', 'Payment to'));?>
                <address>
                    <?php echo $order->htmlPaymentTo;?>
                </address>
            </div>
            <div class="col-sm-4 invoice-col">
                <?php echo Yii::t('orders', $hooks->applyFilters('price_plan_order_payment_from_text', 'Payment from'));?>
                <address>
                    <?php echo $order->htmlPaymentFrom;?>
                </address>
            </div>
        <?php } ?>
        <div class="col-sm-4 invoice-col">&nbsp;</div>
    </div>
    
    <hr />
    
    <div class="row">
        <div class="col-xs-12 table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th><?php echo Yii::t('orders', 'This order applies for the "{planName}" pricing plan.', array('{planName}' => $pricePlan->name));?></th>
                    </tr>                                    
                </thead>
                <tbody>
                    <tr>
                        <td><?php echo $pricePlan->description;?></td>
                    </tr>
                </tbody>
            </table>                            
        </div>
    </div>
 
    <hr />
    
    <div class="row no-print">
        <div class="col-xs-12">
            <p class="lead" style="margin-bottom: 0px;"><?php echo Yii::t('orders', 'Notes');?>:</p>
        </div>
        <div class="form-group col-lg-12"> 
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
                ), $this),
            ), $this));  
            ?>    
            </div>
        </div>
    </div>

    <hr />
    
    <div class="row">
        <div class="col-xs-6 no-print">
            <p class="lead" style="margin-bottom: 0px;"><?php echo Yii::t('orders', 'Transaction info')?>:</p>
            <div class="table-responsive">
            <?php 
            /**
             * This hook gives a chance to prepend content or to replace the default grid view content with a custom content.
             * Please note that from inside the action callback you can access all the controller view
             * variables via {@CAttributeCollection $collection->controller->data}
             * In case the content is replaced, make sure to set {@CAttributeCollection $collection->renderGrid} to false 
             * in order to stop rendering the default content.
             * @since 1.3.3.1
             */
            $hooks->doAction('before_grid_view', $collection = new CAttributeCollection(array(
                'controller'    => $this,
                'renderGrid'    => true,
            )));
            
            // and render if allowed
            if ($collection->renderGrid) {
                $this->widget('zii.widgets.grid.CGridView', $hooks->applyFilters('grid_view_properties', array(
                    'ajaxUrl'           => $this->createUrl($this->route),
                    'id'                => $transaction->modelName.'-grid',
                    'dataProvider'      => $transaction->search(),
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
                            'name'  => 'payment_gateway_name',
                            'value' => '$data->payment_gateway_name',
                            'filter'=> false,
                        ),
                        array(
                            'name'  => 'payment_gateway_transaction_id',
                            'value' => 'wordwrap($data->payment_gateway_transaction_id, 30, "<br />", true)',
                            'type'  => 'raw',
                            'filter'=> false,
                        ),
                        array(
                            'name'  => 'status',
                            'value' => '$data->getStatusName()',
                            'filter'=> false,
                        ),
                        array(
                            'name'  => 'date_added',
                            'value' => '$data->dateAdded',
                            'filter'=> false,
                        ),
                    ), $this),
                ), $this));  
            }
            /**
             * This hook gives a chance to append content after the grid view content.
             * Please note that from inside the action callback you can access all the controller view
             * variables via {@CAttributeCollection $collection->controller->data}
             * @since 1.3.3.1
             */
            $hooks->doAction('after_grid_view', new CAttributeCollection(array(
                'controller'    => $this,
                'renderedGrid'  => $collection->renderGrid,
            )));
            ?>    
            </div>
        </div>
        <div class="col-xs-6">
            <p class="lead"><?php echo Yii::t('orders', 'Amount')?>:</p>
            <div class="table-responsive">
                <table class="table">
                    <tr>
                        <th style="width:50%"><?php echo Yii::t('orders', 'Subtotal')?>:</th>
                        <td><?php echo $order->formattedSubtotal;?></td>
                    </tr>
                    <tr>
                        <th><?php echo Yii::t('orders', 'Tax')?>:</th>
                        <td><?php echo $order->formattedTaxValue;?></td>
                    </tr>
                    <tr>
                        <th><?php echo Yii::t('orders', 'Discount')?>:</th>
                        <td><?php echo $order->formattedDiscount;?></td>
                    </tr>
                    <tr>
                        <th><?php echo Yii::t('orders', 'Total')?>:</th>
                        <td><?php echo $order->formattedTotal;?></td>
                    </tr>
                    <tr>
                        <th><?php echo Yii::t('orders', 'Status')?>:</th>
                        <td><?php echo $order->statusName;?></td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
    
    <hr />
    
    <div class="row no-print">
        <div class="col-xs-12">
            <div class="pull-right">
                <button class="btn btn-success btn-flat" onclick="window.print();"><i class="fa fa-print"></i> <?php echo Yii::t('app', 'Print');?></button>
                <a href="<?php echo $this->createUrl('orders/email_invoice', array('id' => $order->order_id));?>" class="btn btn-success btn-flat"><i class="fa fa-envelope"></i> <?php echo Yii::t('orders', 'Email invoice');?></a>
                <a target="_blank" href="<?php echo $this->createUrl('orders/pdf', array('id' => $order->order_id));?>" class="btn btn-success btn-flat"><i class="fa fa-clipboard"></i> <?php echo Yii::t('orders', 'View invoice');?></a>
                <a href="<?php echo $this->createUrl('orders/update', array('id' => $order->order_id));?>" class="btn btn-primary btn-flat"><?php echo IconHelper::make('update') . '&nbsp;' . Yii::t('orders', 'Update this order');?></a>
                <a href="<?php echo $this->createUrl('orders/index');?>" class="btn btn-primary btn-flat"><?php echo IconHelper::make('back') . '&nbsp;' . Yii::t('orders', 'Back to orders');?></a>    
            </div>
        </div>
    </div>
</div>
