<?php defined('MW_PATH') || exit('No direct script access allowed');

/**
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.0
 */
?>

<link href='https://fonts.googleapis.com/css?family=Raleway' rel='stylesheet' type='text/css'>
<script src="<?php echo $extension->getAssetsUrl();?>/js/jquery.flexslider-min.js"></script>
<link rel="stylesheet" href="<?php echo $extension->getAssetsUrl();?>/css/flexslider.css"/>
<script src="<?php echo $extension->getAssetsUrl();?>/js/tour.js"></script>
<link rel="stylesheet" href="<?php echo $extension->getAssetsUrl();?>/css/tour.css"/>


<div id="tour">
    <div class="modal fade" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">

                <div class="modal-body">
                    <?php
                    // at least one valid image
                    $loadImages = false;
                    foreach ($slides as $slide) {
                        if (!empty($slide->image)) {
                            $loadImages = true;
                            break;
                        }
                    }
                    ?>
                    
                    <?php if (!$loadImages) { ?>
                    <img src="<?php echo $extension->getAssetsUrl();?>/images/banner.jpg" style="width:100%" />
                    <?php } ?>
                    
                    <div class="flexslider">
                        <ul class="slides">
                            <?php foreach ($slides as $index => $slide) { ?>
                                <li>
                                    <?php
                                    if ($loadImages) { 
                                        $image = empty($slide->image) ? $extension->getAssetsUrl() . '/images/banner.jpg' : $slide->getImageUrl(1600, 400);
                                        echo CHtml::image($image, $extension->replaceContentTags($slide->title), array('style' => 'width:100%'));
                                    } 
                                    ?>
                                    <div class="flex-caption">
                                        <div class="heading"><?php echo $extension->replaceContentTags($slide->title);?></div>
                                        <div class="caption-content">
                                            <?php echo $extension->replaceContentTags($slide->content);?>
                                        </div>
                                    </div>
                                </li>
                            <?php } ?>
                        </ul>
                    </div>

                </div>
                <div class="modal-footer">
                    <div class="pull-left">
                        <button id="skip-the-tour" 
                                type="button" 
                                class="btn btn-primary btn-flat" 
                                data-message="<?php echo $extension->t('Are you sure? The tour contains valuable information to help you get started. You will not see the tour again if you end it!');?>" 
                                data-url="<?php echo $this->createUrl('ext_tour_slideshow_skip/index');?>" 
                                data-slideshow="<?php echo $slideshow->slideshow_id;?>"
                        >
                            <?php echo $extension->t('End the tour');?>
                        </button>
                    </div>
                    <div class="pull-right">
                        <button type="button" class="btn btn-default btn-flat" data-dismiss="modal"><?php echo $extension->t('Close for now');?></button>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>
