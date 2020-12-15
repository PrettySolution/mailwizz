jQuery(document).ready(function($){
    
    // delete
	$(document).on('click', 'a.btn-list-custom-asset-remove', function(){
		if ($(this).data('asset-id') > 0 && !confirm($(this).data('message'))) {
			return false;
		}
		$(this).closest('.list-custom-assets-row').fadeOut('slow', function() {
			$(this).remove();
		});
		return false;
	});
	
	// add
    var customAssetsRowTpl = $('#list-custom-assets-row-template').html();
    $('#list-custom-assets-row-template').remove();
	$('a.btn-list-custom-asset-add').on('click', function(){
		var currentIndex = -1;
		$('.list-custom-assets-row').each(function(){
			if ($(this).data('start-index') > currentIndex) {
				currentIndex = $(this).data('start-index');
			}
		});
		
		currentIndex++;
		var tpl = customAssetsRowTpl.replace(/\{index\}/g, currentIndex);
		var $tpl = $(tpl);
		$('.list-custom-assets-list').append($tpl);
		
		$tpl.find('.has-help-text').popover();
		
		return false;	
	});
});