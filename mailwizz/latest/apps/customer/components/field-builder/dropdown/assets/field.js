jQuery(document).ready(function($){

	// delete field button.
	$(document).on('click', 'a.btn-remove-dropdown-field', function(){
		if ($(this).data('field-id') > 0 && !confirm($(this).data('message'))) {
			return false;
		}
		$(this).closest('.field-row').fadeOut('slow', function() {
			$(this).remove();
		});
		return false;
	});
	
	// add
	$('a.btn-add-dropdown-field').on('click', function(){
		var currentIndex = -1;
		$('.field-row').each(function(){
			if ($(this).data('start-index') > currentIndex) {
				currentIndex = $(this).data('start-index');
			}
		});
		
		currentIndex++;
		var tpl = $('#field-dropdown-javascript-template').html();
		tpl = tpl.replace(/\{index\}/g, currentIndex);
		$tpl = $(tpl);
		$('.list-fields').append($tpl);
		
		$tpl.find('.has-help-text').popover();
		
		return false;	
	});
	
    // add option
    $(document).on('click', '.btn-dropdown-add-option', function(){
        var $fieldRow = $(this).closest('.field-row');
        var currentIndex = -1;
		$('.dropdown-option-row', $fieldRow).each(function(){
			if ($(this).data('start-index') > currentIndex) {
				currentIndex = $(this).data('start-index');
			}
		});
		currentIndex++;
        
        var parentIndex = parseInt($(this).closest('.field-row').data('start-index'));
		var tpl = $('#field-dropdown-option-javascript-template').html();
		tpl = tpl.replace(/\{optionIndex\}/g, currentIndex);
        tpl = tpl.replace(/\{parentIndex\}/g, parentIndex);
		$tpl = $(tpl);
		$('.dropdown-options-list', $fieldRow).append($tpl);
		
		$tpl.find('.has-help-text').popover();
		
		return false;	
    });
    
    // remove option
    $(document).on('click', 'a.btn-remove-dropdown-option-field', function(){
		if ($(this).data('option-id') > 0 && !confirm($(this).data('message'))) {
			return false;
		}
		$(this).closest('.dropdown-option-row').fadeOut('slow', function() {
			$(this).remove();
		});
		return false;
	});
});