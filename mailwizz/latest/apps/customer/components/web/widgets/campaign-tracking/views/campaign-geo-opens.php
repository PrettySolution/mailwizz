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
            <?php if ($this->headingLeft && is_object($this->headingLeft)) {
                $this->headingLeft->render();
            } ?>
        </div>
        <div class="pull-right">
            <?php if ($this->headingRight && is_object($this->headingRight)) {
                $this->headingRight->render();
            } ?>
        </div>
        <div class="clearfix"><!-- --></div>
    </div>
    
    <div class="box-body geo-opens-wrapper">
        <div class="row">
            <div class="col-lg-6">
                <div style="max-height: 300px;overflow-y: scroll;">
                    <div id="campaign-geo-opens" style="width: 100%; height: 300px" data-chartdata='<?php echo json_encode($chartData);?>'></div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="geo-opens-countries-list">
                    <div class="heading">
                        <table class="table table-condensed table-hover table-stripe">
                            <tr>
                                <td></td>
                                <td><?php echo Yii::t('campaigns', 'Country');?></td>
                                <td><?php echo Yii::t('campaigns', 'Total');?></td>
                            </tr>
                        </table>
                    </div>
                    <div class="countries-list">
                        <table class="table table-condensed table-hover table-striped">
                            <?php foreach ($data as $row) { ?>
                            <tr>
                                <td><img src="<?php echo $row['flag_url'];?>" alt="<?php echo $row['country_name'];?>"  title="<?php echo $row['country_name'];?>" /></td>
                                <td><?php echo $row['country_name'] . ' ' . $row['action_links'];?> </td>
                                <td><?php echo $row['opens_count_formatted']; ?></td>
                            </tr>
                            <?php } ?>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>