/**
 * This file is part of the MailWizz EMA application.
 *
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.5.1
 */
jQuery(document).ready(function($){

    $('a[href^="#page-info"]').pulsate({
        // color: $(this).css("background-color"), // set the color of the pulse
        reach: 10,      // how far the pulse goes in px
        speed: 1000,    // how long one pulse takes in ms
        pause: 1000,    // how long the pause between pulses is in ms
        glow: true,     // if the glow should be shown too
        repeat: 3,      // will repeat forever if true, if given a number will repeat for that many times
        onHover: false  // if true only pulsate if user hovers over the element
    });
    
});
