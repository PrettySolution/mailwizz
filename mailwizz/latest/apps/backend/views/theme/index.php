<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * This file is part of the MailWizz EMA application.
 * 
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com> 
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.3
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
if ($viewCollection->renderContent) { ?>
    <ul class="nav nav-tabs" style="border-bottom: 0px;">
        <?php foreach ($apps as $appName) {?>
        <li class="<?php echo $app == $appName ? 'active' : 'inactive';?>"><a href="<?php echo $this->createUrl('theme/index', array('app' => $appName))?>"><?php echo CHtml::encode(Yii::t('app', ucfirst($appName)));?></a></li>
        <?php } ?>
    </ul>
    <div class="box box-primary borderless">
        <div class="box-header">
            <div class="pull-left">
                <h3 class="box-title">
                    <?php echo IconHelper::make('glyphicon-plus-sign') . Yii::t('themes', 'Available themes');?>
                </h3>
            </div>
            <div class="pull-right">
                <?php
                if (AccessHelper::hasRouteAccess('theme/upload')) {
                    echo CHtml::link(IconHelper::make('upload') . Yii::t('themes', 'Upload theme'), '#theme-upload-modal', array('class' => 'btn btn-primary btn-flat', 'data-toggle' => 'modal', 'title' => Yii::t('themes', 'Upload theme')));
                }
                ?>
                <?php echo HtmlHelper::accessLink(IconHelper::make('refresh') . Yii::t('app', 'Refresh'), array('theme/index'), array('class' => 'btn btn-primary btn-flat', 'title' => Yii::t('app', 'Refresh')));?>
            </div>
            <div class="clearfix"><!-- --></div>
        </div>
        <div class="box-body">
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
                    'id'                => $model->modelName . '-grid',
                    'dataProvider'      => $model->getDataProvider($app),
                    'filter'            => null,
                    'filterPosition'    => 'body',
                    'filterCssClass'    => 'grid-filter-cell',
                    'itemsCssClass'     => 'table table-hover',
                    'selectableRows'    => 0,
                    'enableSorting'     => false,
                    'cssFile'           => false,
                    'pager'             => false,
                    'columns' => $hooks->applyFilters('grid_view_columns', array(
                        array(
                            'name'  => Yii::t('themes', 'Name'),
                            'value' => '$data["name"]',
                            'type'  => 'raw',
                        ),
                        array(
                            'name'  => Yii::t('themes', 'Description'),
                            'value' => '$data["description"]',
                            'type'  => 'raw',
                        ),
                        array(
                            'name'  => Yii::t('themes', 'Version'),
                            'value' => '$data["version"]',
                        ),
                        array(
                            'name'  => Yii::t('themes', 'Author'),
                            'value' => '$data["author"]',
                            'type'  => 'raw',
                        ),
                        array(
                            'name'  => Yii::t('themes', 'Website'),
                            'value' => '$data["website"]',
                            'type'  => 'raw',
                        ),
                        array(
                            'class'     => 'CButtonColumn',
                            'header'    => Yii::t('app', 'Options'),
                            'afterDelete'=> 'function(){window.location.reload();}',
                            'buttons'    => array(
                                'page' => array(
                                    'label'     => IconHelper::make('view'), 
                                    'url'       => '$data["pageUrl"]',
                                    'imageUrl'  => null,
                                    'options'   => array('title' => Yii::t('themes', 'Theme detail page'), 'class'=>'btn btn-primary btn-flat'),
                                    'visible'   => '$data["enabled"] && !empty($data["pageUrl"])',
                                ),
                                'enable' => array(
                                    'label'     => IconHelper::make('glyphicon-ok'), 
                                    'url'       => '$data["enableUrl"]',
                                    'imageUrl'  => null,
                                    'options'   => array('title' => Yii::t('app', 'Enable'), 'class'=>'btn btn-primary btn-flat'),
                                    'visible'   => 'AccessHelper::hasRouteAccess("theme/enable") && !$data["enabled"]',
                                ),
                                'disable' => array(
                                    'label'     => IconHelper::make('glyphicon-ban-circle'), 
                                    'url'       => '$data["disableUrl"]',
                                    'imageUrl'  => null,
                                    'options'   => array('title' => Yii::t('app', 'Disable'), 'class'=>'btn btn-danger btn-flat'),
                                    'visible'   => 'AccessHelper::hasRouteAccess("theme/disable") && $data["enabled"]',
                                ),   
                                'delete' => array(
                                    'label'     => IconHelper::make('glyphicon-remove'), 
                                    'url'       => '$data["deleteUrl"]',
                                    'imageUrl'  => null,
                                    'options'   => array('title' => Yii::t('app', 'Delete'), 'class'=>'btn btn-danger btn-flat delete'),
                                    'visible'   => 'AccessHelper::hasRouteAccess("theme/delete")',
                                ),   
                            ),
                            'headerHtmlOptions' => array('style' => 'text-align: right'),
                            'footerHtmlOptions' => array('align' => 'right'),
                            'htmlOptions'       => array('align' => 'right', 'class' => 'options'),
                            'template'          => '{page} {enable} {disable} {delete}'
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
            <div class="clearfix"><!-- --></div>
            </div>    
        </div>
    </div>
    
    <div class="modal fade" id="theme-upload-modal" tabindex="-1" role="dialog" aria-labelledby="theme-upload-modal-label" aria-hidden="true">
        <div class="modal-dialog">
          <div class="modal-content">
            <div class="modal-header">
              <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
              <h4 class="modal-title"><?php echo Yii::t('themes', 'Upload theme archive.')?></h4>
            </div>
            <div class="modal-body">
                 <div class="callout callout-info">
                     <?php echo Yii::t('themes', 'Please note that only zip files are allowed for upload.')?>
                 </div>
                <?php 
                $form = $this->beginWidget('CActiveForm', array(
                    'action'        => array('theme/upload', 'app' => $app),
                    'id'            => $model->modelName.'-upload-form',
                    'htmlOptions'   => array('enctype' => 'multipart/form-data'),
                ));
                ?>
                <div class="form-group">
                    <?php echo $form->labelEx($model, 'archive');?>
                    <?php echo $form->fileField($model, 'archive', $model->getHtmlOptions('archive')); ?>
                    <?php echo $form->error($model, 'archive');?>
                </div>
                <?php $this->endWidget(); ?>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-default btn-flat" data-dismiss="modal"><?php echo Yii::t('app', 'Close');?></button>
              <button type="button" class="btn btn-primary btn-flat" onclick="$('#<?php echo $model->modelName;?>-upload-form').submit();"><?php echo IconHelper::make('upload') . '&nbsp;' . Yii::t('app', 'Upload archive');?></button>
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