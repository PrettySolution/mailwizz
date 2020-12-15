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
	
    $('.sidebar').on('mouseenter', function(){
    	if ($('.sidebar-collapse').length == 0) {
			$('.timeinfo').stop().fadeIn();
		}
    }).on('mouseleave', function(){
        $('.timeinfo').stop().fadeOut();
    });

    // since 1.3.7.3
    var loadUserMessagesInHeader = function(){
        var url = $('.messages-menu .header-messages').data('url');
        if (!url) {
            return;
        }
        $.get(url, {}, function(json){
            if (json.counter) {
                $('.messages-menu .header-messages span.label').text(json.counter);
            }
            if (json.header) {
                $('.messages-menu ul.dropdown-menu li.header').html(json.header);
            }
            if (json.html) {
                $('.messages-menu ul.dropdown-menu ul.menu').html(json.html);
            }
        }, 'json');
    };
    // don't run on guest.
    if (!$('body').hasClass('ctrl-guest')) {
        loadUserMessagesInHeader();
        setInterval(loadUserMessagesInHeader, 60000);
    }
    //
});