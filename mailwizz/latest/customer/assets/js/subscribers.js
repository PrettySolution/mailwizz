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
	
	var ajaxData = {};
	if ($('meta[name=csrf-token-name]').length && $('meta[name=csrf-token-value]').length) {
			var csrfTokenName = $('meta[name=csrf-token-name]').attr('content');
			var csrfTokenValue = $('meta[name=csrf-token-value]').attr('content');
			ajaxData[csrfTokenName] = csrfTokenValue;
	}
	
	$(document).on('click', 'ul#subscribers-pagination li a', function(){
		$('form#subscribers-form').attr('action', $(this).attr('href'));
		$('form#subscribers-form').submit();
		return false;
	});
	
	$(document).on('keydown', 'form#subscribers-form input', function(e){
		if (e.keyCode == 13 || e.which == 13) {
			$('#submit-subscribe-form').click();
		}
	});
    
    $(document).on('change', 'form#subscribers-form select', function(e){
		$('#submit-subscribe-form').click();
	});
	
	$(document).on('submit', 'form#subscribers-form', function(){
		$('.empty-options-header').addClass('loading');
		$.post($(this).attr('action'), $.param(ajaxData) + '&' + $(this).serialize(), function(html){
			$('#subscribers-wrapper').html(html);
			$('.empty-options-header').removeClass('loading');
		});
		return false;
	});
	
	$(document).on('click', 'form#subscribers-form a.delete', function(){
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
    
    $(document).on('click', 'form#subscribers-form a.unsubscribe, form#subscribers-form a.subscribe', function(){
		if (!confirm($(this).data('message'))) {
			return false;
		}
		var $this = $(this);
		$('.empty-options-header').addClass('loading');
		$.post($(this).attr('href'), $.param(ajaxData) + '&' + $(this).serialize(), function(){
			window.location.reload();
			/*
			$('.empty-options-header').removeClass('loading');
            if ($('ul#subscribers-pagination li.selected').length) {
                $('ul#subscribers-pagination li.selected a').click();
            } else {
                $('#submit-subscribe-form').click();
            }
			*/
		});
		
		return false;
	});
    
    $(document).on('click', '#filter_bulk_select', function(){
        if ($(this).is(':checked')) {
            $('.bulk-select').attr('checked', true);
            $('.bulk-selected-options').slideDown();
        } else {
            $('.bulk-select').removeAttr('checked');
            $('.bulk-selected-options').slideUp();
        }
    });
    
    $(document).on('click', '.bulk-select', function(){
        if (!$(this).is(':checked')) {
            $('#filter_bulk_select').removeAttr('checked');
        } else {
            $('.bulk-selected-options').slideDown();
        }
    });
    
    $(document).on('change', '.bulk-action', function(){
        var $this = $(this);
        var val = $this.val();
        var serializedCheckboxData = $('.bulk-select:checked').serialize();
        
        $('#filter_bulk_select, .bulk-select').removeAttr('checked');
        $('.bulk-selected-options').slideUp();
        $('.bulk-action option').removeAttr('selected');
        
        if (val) {
            var proceed = true;
            
            if (val == 'delete' && !confirm($this.data('delete'))) {
                proceed = false;
            }
            
            if (proceed) {
                var formData = $.param(ajaxData) + '&action=' + val + '&' + serializedCheckboxData;
        		$('.empty-options-header').addClass('loading');
        		$.post($this.data('bulkurl'), formData, function(){
					window.location.reload();
					/*
					$('.empty-options-header').removeClass('loading');
                    if ($('ul#subscribers-pagination li.selected').length) {
                        $('ul#subscribers-pagination li.selected a').click();
                    } else {
                        $('#submit-subscribe-form').click();
                    }
                    */
        		});    
            }
        }
    });
    
    $(document).on('click', '.btn-next-action', function(){
        $('#next_action').val($(this).data('next_action'));
        $(this).closest('form').submit();
        return false;
    });
	
	// index
	if ($('body.ctrl-list_subscribers.act-index').length) {
		$(window).trigger('resize');
	}
	
	// 
	$(document).on('click', '.toggle-campaigns-filters-form', function(){
		$('#campaigns-filters-form').toggle();
		return false;
	});

	$('.btn-delete-subscriber-from-update').on('click', function(){
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