<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * This file is part of the MailWizz EMA application.
 *
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.3.8.8
 */
?>

<a href="#" class="btn btn-primary btn-flat dropdown-toggle" data-toggle="dropdown"><?php echo $heading;?> <span class="caret"></span></a>
<ul class="dropdown-menu">
	<?php foreach ($links as $link) { ?>
		<li><?php echo $link; ?></li>
	<?php } ?>
</ul>
