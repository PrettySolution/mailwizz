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

<div class="box box-primary borderless">
    
    <div class="box-header">
        <div class="pull-left">
	        <?php BoxHeaderContent::make(BoxHeaderContent::LEFT)
              ->add('<h3 class="box-title">' . IconHelper::make('fa-sitemap') . Yii::t('app', 'Subscribers opens info based on user agent') . '</h3>')
              ->render();
	        ?>
        </div>
        <div class="pull-right">
            
        </div>
        <div class="clearfix"><!-- --></div>
    </div>
    
    <div class="box-body campaign-devices-wrapper">
        <div class="row">
            <div class="col-lg-4">
                <h5><?php echo Yii::t('campaigns', 'Operating systems');?></h5>
                <hr />
                <div style="max-height: 200px;overflow-y: scroll;">
                    <div id="campaign-devices-os" style="width: 100%; height: 200px" data-chartdata='<?php echo json_encode($chartData['os']);?>'></div>
                </div>
            </div>
            <div class="col-lg-4">
                <h5><?php echo Yii::t('campaigns', 'Devices');?></h5>
                <hr />
                <div style="max-height: 200px;overflow-y: scroll;">
                    <div id="campaign-devices-devices" style="width: 100%; height: 200px" data-chartdata='<?php echo json_encode($chartData['device']);?>'></div>
                </div>
            </div>
            <div class="col-lg-4">
                <h5><?php echo Yii::t('campaigns', 'Browsers');?></h5>
                <hr />
                <div style="max-height: 200px;overflow-y: scroll;">
                    <div id="campaign-devices-browsers" style="width: 100%; height: 200px" data-chartdata='<?php echo json_encode($chartData['browser']);?>'></div>
                </div>
            </div>
        </div>
    </div>
</div>