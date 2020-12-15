/**
 * This file is part of the MailWizz EMA application.
 * 
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com> 
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.4.5
 */
jQuery(document).ready(function($){

    $(document).on('click', 'a.copy-server, a.enable-server, a.disable-server', function() {
		$.post($(this).attr('href'), ajaxData, function(){
			window.location.reload();
		});
		return false;
	});
    
	(function(){
        var segmentConditionsIndex = 10000 + 1 * $('.conditions-container .item').length,
            $segmentConditionsTemplate = $('#condition-template');

        $('.btn-add-condition').on('click', function(){
            var html = $segmentConditionsTemplate.html();
            html = html.replace(/\{index\}/g, segmentConditionsIndex);
            $('.conditions-container').append(html);
            ++segmentConditionsIndex;
            return false;
        });

        $(document).on('click', '.btn-remove-condition', function(){
            $(this).closest('.item').remove();
            return false;
        });
        
        $(document).on('change', '.select-action-wrapper select', function(){
            var $item = $(this).closest('.item');
            if ($(this).val() == 'move to list' || $(this).val() == 'copy to list') {
                $('.select-email-list-wrapper', $item).show();
                $('.select-campaign-group-wrapper', $item).hide();
            } else if ($(this).val() == 'stop campaign group') {
                $('.select-campaign-group-wrapper', $item).show();
                $('.select-email-list-wrapper', $item).hide();
            } else {
                $('.select-email-list-wrapper', $item).hide();
                $('.select-campaign-group-wrapper', $item).hide();
            }
        });
	})();
	
	$('form').on('submit', function(){
	    $('#condition-template').remove();
	    return true;
    });
});