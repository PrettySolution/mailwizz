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
    
    var plot = $.plot("#24hours-performance", $('#24hours-performance').data('chartdata'), {
        series: {
            lines: {
                show: true
            },
            points: {
                show: true
            }
        },
        grid: {
            hoverable: true,
            clickable: true,
            autoHighlight: true
        },
        
        xaxis: {
            // mode: "time",
            // timeformat: "%H:00%P"
            
            // since 1.4.4
            mode: "time",
            timeformat: "%H:00%P",
            tickSize: [1, "hour"],
            timezone: "browser"
        },
        crosshair: {
            mode: "x"
        }
    });

    $("<div id='tooltip'></div>").css({
        position: "absolute",
        display: "none",
        border: "1px solid #008ca9",
        color: '#000000',
        padding: "2px",
        "background-color": "#ebf6f8",
        opacity: 0.80
    }).appendTo("body");

    $("#24hours-performance").bind("plothover", function (event, pos, item) {

        if (item) {
            
            var y = item.datapoint[1].toFixed(0);
            $("#tooltip")
                .html(y + ' ' + item.series.label)
                .css({
                    top: item.pageY + 5, 
                    left: item.pageX + 5
                })
                .fadeIn(200);
            
        } else {
            
            $("#tooltip").hide();
        }

    });

    $("#24hours-performance").bind("plotclick", function (event, pos, item) {
        
        if (item) {
            plot.highlight(item.series, item.datapoint);
        }
        
    });
});