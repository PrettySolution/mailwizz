<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * This file is part of the MailWizz EMA application.
 * 
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com> 
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.3.4.7
 */
 
?>

<div class="box box-primary borderless">
    <div class="box-header">
        <h3 class="box-title">
            <?php 
            if ($domain->isVerified) {
                echo Yii::t('sending_domains', 'This domain has been verified');
            } else {
                echo Yii::t('sending_domains', 'Verify this domain');
            }
            ?>
        </h3>
    </div>
    <div class="box-body">
        <div class="callout">
            <p>
                <?php echo Yii::t('sending_domains', 'Please edit your DNS records for {domain} domain and add the following TXT record: ', array('{domain}' => '<strong>' . $domain->name . '</strong>' ));?>
                <textarea class="form-control" rows="5"><?php echo $domain->getDnsTxtDkimSelectorToAdd();?></textarea>
                <br />
                <?php echo Yii::t('sending_domains', 'For best delivery rates, your domain SPF record must look like:');?><br />
                <textarea class="form-control" rows="3"><?php echo $domain->getDnsTxtSpfRecordToAdd();?></textarea><br />
                <?php if (!$domain->isVerified) { ?>
                <?php echo Yii::t('sending_domains', 'After you have added the DNS records for your domain, please click the Verify DNS records button below to verify your domain.');?><br />
                <?php echo Yii::t('sending_domains', 'Please note that it can take up to 48 hours for DNS changes to propagate. If verification fails now, please try again later.');?><br />
                <?php } ?>
            </p>
        </div>           
    </div>
    <?php if (!$domain->isVerified) { ?>
    <div class="box-footer">
        <div class="pull-right">
            <a href="<?php echo $this->createUrl('sending_domains/verify', array('id' => $domain->domain_id));?>" class="btn btn-primary btn-flat"><?php echo IconHelper::make('next') . '&nbsp;' . Yii::t('sending_domains', 'Verify DNS records');?></a>
        </div>
        <div class="clearfix"><!-- --></div>
    </div>
    <?php } ?>
</div>
<hr />