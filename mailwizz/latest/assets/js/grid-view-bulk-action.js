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
	
	ajaxData = {};
	if ($('meta[name=csrf-token-name]').length && $('meta[name=csrf-token-value]').length) {
			var csrfTokenName = $('meta[name=csrf-token-name]').attr('content');
			var csrfTokenValue = $('meta[name=csrf-token-value]').attr('content');
			ajaxData[csrfTokenName] = csrfTokenValue;
	}
	
    $(document).on('change', '.checkbox-column input[type=checkbox]', function(){
        var $this = $(this);
        setTimeout(function(){
            if ($('.checkbox-column input[type=checkbox]:checked').length) {
                $('#bulk-actions-wrapper').slideDown();
            } else {
                $('#bulk-actions-wrapper').slideUp();
                $('#bulk_action').val('');
                $('#btn-run-bulk-action').hide();
            }
        }, 50);
    }).on('change', '#bulk_action', function(){
        var $this = $(this);
        if ($this.val()) {
            $('#btn-run-bulk-action').show();
        } else {
            $('#btn-run-bulk-action').hide();
        }
    }).on('click', '#btn-run-bulk-action', function(){
        if ($('#bulk_action').val() == 'delete' && !confirm($('#bulk_action').data('delete-msg'))) {
            $('#bulk_action').val('');
            return false;
        }

        if ($('#bulk_action').val() == 'send-test-email') {
            return false;
        }
        
        $('#bulk-action-form')
            .append($('<input/>').attr({name: 'bulk_action'}).val($('#bulk_action').val()))
            .append($('.checkbox-column input[type=checkbox]:checked').clone())
            .submit();
        
        return false;
    });
});