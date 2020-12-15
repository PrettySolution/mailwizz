<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * This file is part of the MailWizz EMA application.
 * 
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com> 
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.5.5
 */
 
?>

<div class="row">
    <div class="col-lg-12">
        <h1 class="page-heading">
            <?php echo $page->title;?>
        </h1>
        <hr />
        <?php echo $page->content;?>
        <hr />
    </div>
</div>