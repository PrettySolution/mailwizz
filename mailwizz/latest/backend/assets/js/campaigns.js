/**
 * This file is part of the MailWizz EMA application.
 *
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.3.5.5
 */
jQuery(document).ready(function($){

	var ajaxData = {};
	if ($('meta[name=csrf-token-name]').length && $('meta[name=csrf-token-value]').length) {
			var csrfTokenName = $('meta[name=csrf-token-name]').attr('content');
			var csrfTokenValue = $('meta[name=csrf-token-value]').attr('content');
			ajaxData[csrfTokenName] = csrfTokenValue;
	}

	$(document).on('click', 'a.pause-sending, a.unpause-sending', function() {
		if (!confirm($(this).data('message'))) {
			return false;
		}
		$.post($(this).attr('href'), ajaxData, function(){
			window.location.reload();
		});
		return false;
	});

    $(document).on('click', 'a.approve', function() {
        if (!confirm($(this).data('message'))) {
            return false;
        }
        $.post($(this).attr('href'), ajaxData, function(){
            window.location.reload();
        });
        return false;
    });
    
	$(document).on('click', 'a.block-sending, a.unblock-sending', function() {
		if (!confirm($(this).data('message'))) {
			return false;
		}
		$.post($(this).attr('href'), ajaxData, function(){
			window.location.reload();
		});
		return false;
	});

    $(document).on('click', 'a.resume-campaign-sending', function() {
        if (!confirm($(this).data('message'))) {
			return false;
		}
		$.post($(this).attr('href'), ajaxData, function(){
			window.location.reload();
		});
		return false;
	});

    $(document).on('click', 'a.mark-campaign-as-sent', function() {
        if (!confirm($(this).data('message'))) {
			return false;
		}
		$.post($(this).attr('href'), ajaxData, function(){
			window.location.reload();
		});
		return false;
	});

	$(document).on('click', 'a.resend-campaign-giveups', function() {
		var $this = $(this);
		if (!confirm($this.data('message'))) {
			return false;
		}
		$.post($(this).attr('href'), ajaxData, function(json){
			if (json.result === 'success') {
				notify.addSuccess(json.message);
				$this.remove();
			} else {
				notify.addError(json.message);
			}
			$('html, body').animate({scrollTop: 0}, 500);
			notify.show();
		});
		return false;
	});
	
    $(document).on('click', '.toggle-filters-form', function(){
        $('#filters-form').toggle();
        return false;
    });
});
