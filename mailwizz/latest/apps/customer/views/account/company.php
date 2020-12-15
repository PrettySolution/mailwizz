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
if ($viewCollection->renderContent) { ?>
    <div class="tabs-container">
    <?php 
    echo $this->renderTabs();
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
    
    // and render only if allowed
    if ($collection->renderForm) {
        $form = $this->beginWidget('CActiveForm'); 
        ?>
        <div class="box box-primary borderless">
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
                    <div class="col-lg-6">
                        <div class="form-group">
                            <?php echo $form->labelEx($company, 'name');?>
                            <?php echo $form->textField($company, 'name', $company->getHtmlOptions('name')); ?>
                            <?php echo $form->error($company, 'name');?>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="form-group">
                            <?php echo $form->labelEx($company, 'website');?>
                            <?php echo $form->urlField($company, 'website', $company->getHtmlOptions('website')); ?>
                            <?php echo $form->error($company, 'website');?>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-6">
                        <div class="form-group">
                            <?php echo $form->labelEx($company, 'country_id');?>
                            <?php echo $company->getCountriesDropDown(); ?>
                            <?php echo $form->error($company, 'country_id');?>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="form-group">
                            <?php echo $form->labelEx($company, 'zone_id');?>
                            <?php echo $company->getZonesDropDown(); ?>
                            <?php echo $form->error($company, 'zone_id');?>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-6">
                        <div class="form-group">
                            <?php echo $form->labelEx($company, 'address_1');?>
                            <?php echo $form->textField($company, 'address_1', $company->getHtmlOptions('address_1')); ?>
                            <?php echo $form->error($company, 'address_1');?>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="form-group">
                            <?php echo $form->labelEx($company, 'address_2');?>
                            <?php echo $form->textField($company, 'address_2', $company->getHtmlOptions('address_2')); ?>
                            <?php echo $form->error($company, 'address_2');?>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-4 zone-name-wrap">
                        <div class="form-group">
                            <?php echo $form->labelEx($company, 'zone_name');?>
                            <?php echo $form->textField($company, 'zone_name', $company->getHtmlOptions('zone_name')); ?>
                            <?php echo $form->error($company, 'zone_name');?>
                        </div>
                    </div>
                    <div class="col-lg-4 city-wrap">
                        <div class="form-group">
                            <?php echo $form->labelEx($company, 'city');?>
                            <?php echo $form->textField($company, 'city', $company->getHtmlOptions('city')); ?>
                            <?php echo $form->error($company, 'city');?>
                        </div>
                    </div>
                    <div class="col-lg-4 zip-wrap">
                        <div class="form-group">
                            <?php echo $form->labelEx($company, 'zip_code');?>
                            <?php echo $form->textField($company, 'zip_code', $company->getHtmlOptions('zip_code')); ?>
                            <?php echo $form->error($company, 'zip_code');?>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-4">
                        <div class="form-group">
                            <?php echo $form->labelEx($company, 'phone');?>
                            <?php echo $form->textField($company, 'phone', $company->getHtmlOptions('phone')); ?>
                            <?php echo $form->error($company, 'phone');?>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="form-group">
                            <?php echo $form->labelEx($company, 'fax');?>
                            <?php echo $form->textField($company, 'fax', $company->getHtmlOptions('fax')); ?>
                            <?php echo $form->error($company, 'fax');?>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="form-group">
                            <?php echo $form->labelEx($company, 'type_id');?>
                            <?php echo $form->dropDownList($company, 'type_id', CMap::mergeArray(array('' => Yii::t('app', 'Please select')), CompanyType::getListForDropDown()), $company->getHtmlOptions('type_id')); ?>
                            <?php echo $form->error($company, 'type_id');?>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-4">
                        <div class="form-group">
                            <?php echo $form->labelEx($company, 'vat_number');?>
                            <?php echo $form->textField($company, 'vat_number', $company->getHtmlOptions('vat_number')); ?>
                            <?php echo $form->error($company, 'vat_number');?>
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