<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * This file is part of the MailWizz EMA application.
 * 
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com> 
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.7.8
 */

$htmlOptions = array();
if (!empty($attributes) && !empty($attributes['target']) && in_array($attributes['target'], array('_blank'))) {
    $htmlOptions['target'] = $attributes['target'];
} 
?>

<div class="row">
    <div class="<?php echo $this->layout != 'embed' ? 'col-lg-6 col-lg-push-3 col-md-6 col-md-push-3 col-sm-12' : '';?>">
        <div class="callout callout-info">
            <?php echo Yii::t('surveys', 'Thank you!'); ?>
        </div>
    </div>
</div>