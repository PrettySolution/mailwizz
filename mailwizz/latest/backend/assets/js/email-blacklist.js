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
	
	$('.delete-all').on('click', function() {
		var $this = $(this);
		if (!confirm($this.data('message'))) {
			return false;
		}
		$.post($this.attr('href'), ajaxData, function(){
			window.location.reload();
		});
		return false;
	});
	
	// 1.3.7.1
	$(document).on('click', '.toggle-filters-form', function(){
		$('#filters-form').toggle();
		return false;
	});
    $(document).on('submit', '#filters-form', function() {
        var action = $('#action', this).val();
        if (action == 'delete' && !confirm($(this).data('confirm'))) {
            return false;
        }
        return true;
    });
});