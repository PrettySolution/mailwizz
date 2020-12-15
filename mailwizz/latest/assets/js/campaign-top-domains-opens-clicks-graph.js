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
jQuery(document).ready(function($){
    
    $.plot("#opens-clicks-by-domain", $('#opens-clicks-by-domain').data('chartdata'), {
        series: {
            bars: {
                show: true,
                barWidth: 0.5,
                align: "center",
                lineWidth: 0,
                fill:.60
            }
        },
        xaxis: {
            mode: "categories",
            tickLength: 0
        }
    });
    
});