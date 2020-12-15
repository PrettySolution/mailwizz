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
	
    $('a.header-account-stats').on('click', function(){
        var $this = $(this);
        if ($this.data('loaded')) {
            return true;
        }

        $this.data('loaded', true);

        var $dd   = $this.closest('li').find('ul:first'),
            $menu = $dd.find('ul.menu');

        $.get($this.data('url'), {}, function(json){
            if (json.html) {
                $menu.html(json.html);
            }
        }, 'json');
    });

    $('.header-account-stats-refresh').on('click', function(){
        $('a.header-account-stats').data('loaded', false).trigger('click').trigger('click');
        return false;
    });

	// since 1.3.5.9
	var loadCustomerMessagesInHeader = function(){
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
		loadCustomerMessagesInHeader();
		setInterval(loadCustomerMessagesInHeader, 60000);
	}
	//
});
