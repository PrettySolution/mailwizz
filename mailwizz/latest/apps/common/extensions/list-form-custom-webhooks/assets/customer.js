jQuery(document).ready(function($){
    
    // delete
	$(document).on('click', 'a.btn-list-custom-webhook-remove', function(){
		if ($(this).data('webhook-id') > 0 && !confirm($(this).data('message'))) {
			return false;
		}
		$(this).closest('.list-custom-webhooks-row').fadeOut('slow', function() {
			$(this).remove();
		});
		return false;
	});
	
	// add
    var customWebhooksRowTpl = $('#list-custom-webhooks-row-template').html();
    $('#list-custom-webhooks-row-template').remove();
	$('a.btn-list-custom-webhook-add').on('click', function(){
		var currentIndex = -1;
		$('.list-custom-webhooks-row').each(function(){
			if ($(this).data('start-index') > currentIndex) {
				currentIndex = $(this).data('start-index');
			}
		});
		
		currentIndex++;
		var tpl = customWebhooksRowTpl.replace(/\{index\}/g, currentIndex);
		var $tpl = $(tpl);
		$('.list-custom-webhooks-list').append($tpl);
		
		$tpl.find('.has-help-text').popover();
		
		return false;	
	});
});