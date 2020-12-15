<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * This file is part of the MailWizz EMA application.
 *
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.3.5.9
 */

?>
<?php foreach ($messages as $message) { ?>
<li>
    <a href="<?php echo $this->createUrl('messages/view', array('message_uid' => $message->message_uid));?>">
        <h4>
            <small><i class="fa fa-clock-o"></i> <?php echo $message->dateAdded;?></small>
            <div class="clearfix"><!-- --></div>
            <span><?php echo $message->getShortTitle(20);?></span>
        </h4>
        <p><?php echo wordwrap($message->getShortMessage(120), 45, '<br />', true);?></p>
    </a>
</li>
<?php } ?>
