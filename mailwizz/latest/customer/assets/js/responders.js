/**
 * This file is part of the MailWizz EMA application.
 * 
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com> 
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.7.8
 */
jQuery(document).ready(function($){
	
	var ajaxData = {};
	if ($('meta[name=csrf-token-name]').length && $('meta[name=csrf-token-value]').length) {
			var csrfTokenName = $('meta[name=csrf-token-name]').attr('content');
			var csrfTokenValue = $('meta[name=csrf-token-value]').attr('content');
			ajaxData[csrfTokenName] = csrfTokenValue;
	}
	
	$(document).on('click', 'ul#responders-pagination li a', function(){
		$('form#responders-form').attr('action', $(this).attr('href'));
		$('form#responders-form').submit();
		return false;
	});
	
	$(document).on('keydown', 'form#responders-form input', function(e){
		if (e.keyCode == 13 || e.which == 13) {
			$('#submit-respond-form').click();
		}
	});
    
    $(document).on('change', 'form#responders-form select', function(e){
		$('#submit-respond-form').click();
	});
	
	$(document).on('submit', 'form#responders-form', function(){
		$('.empty-options-header').addClass('loading');
		$.post($(this).attr('action'), $.param(ajaxData) + '&' + $(this).serialize(), function(html){
			$('#responders-wrapper').html(html);
			$('.empty-options-header').removeClass('loading');
		});
		return false;
	});
	
	$(document).on('click', 'form#responders-form a.delete', function(){
		if (!confirm($(this).data('message'))) {
			return false;
		}
		var $this = $(this);
		$('.empty-options-header').addClass('loading');
		$.post($(this).attr('href'), $.param(ajaxData) + '&' + $(this).serialize(), function(){
			//$('.empty-options-header').removeClass('loading');
			//$this.closest('tr').remove();
			window.location.reload();
		});
		
		return false;
	});
    
    $(document).on('click', '.btn-next-action', function(){
        $('#next_action').val($(this).data('next_action'));
        $(this).closest('form').submit();
        return false;
    });
	
	// index
	if ($('body.ctrl-survey_responders.act-index').length) {
		$(window).trigger('resize');
	}

	$('.btn-delete-responder-from-update').on('click', function(){
		var $this = $(this);
		if (!$this.data('confirm') || !$this.data('redirect')) {
			return false;
		}

		if (!confirm($this.data('confirm'))) {
			return false;
		}

		$.post($this.attr('href'), ajaxData, function() {
			window.location.href = $this.data('redirect');
		});

		return false;
	});
});