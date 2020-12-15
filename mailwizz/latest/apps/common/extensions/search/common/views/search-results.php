<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * This file is part of the MailWizz EMA application.
 *
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 */

?>

<div class="search-input-results">
    <ul>
		<?php if (empty($results)) { ?>

            <li><?php echo Yii::t('ext_search', 'There are no results matching your search!');?></li>

		<?php } else { ?>

			<?php foreach ($results as $result) { ?>

				<?php if (!empty($result['children'])) { ?>

                    <li class="search-heading">
                        <span>
                            <a href="<?php echo $result['url']; ?>" title="<?php echo $result['title']; ?>" data-score="<?php echo $result['score'];?>"><?php echo $result['title']; ?></a>
                        </span>
                        <ul>
							<?php foreach ($result['children'] as $res) { ?>
                                <li>
                                    <div class="pull-left">
                                        <a href="<?php echo $res['url']; ?>" title="<?php echo $res['title']; ?>" data-score="<?php echo $res['score'];?>">
                                            <i class="fa fa-arrow-circle-o-right"></i> <?php echo $res['title']; ?>
                                        </a>
                                    </div>
                                    <div class="pull-right search-result-item-buttons">
                                        <?php foreach ($res['buttons'] as $button) {
                                            echo $button;
                                        } ?>
                                    </div>
                                    <div class="clearfix"><!-- --></div>
                                </li>
							<?php } ?>
                        </ul>
                    </li>

				<?php } else { ?>

                    <li>
                        <div class="pull-left">
                            <a href="<?php echo $result['url']; ?>" title="<?php echo $result['title']; ?>" data-score="<?php echo $result['score'];?>">
                                <i class="fa fa-arrow-circle-o-right"></i> <?php echo $result['title']; ?>
                            </a>
                        </div>
                        <div class="pull-right search-result-item-buttons">
		                    <?php foreach ($result['buttons'] as $button) {
			                    echo $button;
		                    } ?>
                        </div>
                        <div class="clearfix"><!-- --></div>
                    </li>

				<?php } ?>

			<?php } ?>

		<?php } ?>
    </ul>    
</div>

