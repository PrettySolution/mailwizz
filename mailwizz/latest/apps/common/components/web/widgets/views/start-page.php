<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * This file is part of the MailWizz EMA application.
 * 
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com> 
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.3.9.2
 */
?>

<div id="start-page">
    <div class="col-lg-12 text-center start-page-wrapper">
        
        <?php if (!empty($page->icon)) { ?>
        <span class="icon" style="<?php echo !empty($page->icon_color) ? sprintf('color:#%s', $page->icon_color) : '';?>">
            <?php echo IconHelper::make($page->icon);?>
        </span>
        <?php } ?>
        
        <?php if (!empty($pageHeading)) { ?>
        <h3><?php echo $pageHeading;?></h3>
        <?php } ?>
        
        <?php if (!empty($pageContent)) { ?>
        <p><?php echo $pageContent;?></p>
        <?php } ?>
        
    </div>
</div>
