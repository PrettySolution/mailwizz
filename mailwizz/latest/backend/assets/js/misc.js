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
	
	$('.delete-app-log').on('click', function() {
		var $this = $(this);
		if (!confirm($this.data('message'))) {
			return false;
		}
	});
    
    $('.remove-sending-pid, .remove-bounce-pid, .remove-fbl-pid, .reset-campaigns, .reset-bounce-servers, .reset-fbl-servers, .reset-email-box-monitors').on('click', function(){
        if (!confirm($(this).data('confirm'))) {
            return false;
        }
        $.getJSON($(this).attr('href'), {}, function(json){
            notify.addSuccess($('#ea-box-wrapper').data('success')).show();
        });
        return false;
    });
    
    $('a.btn-delete-delivery-temporary-errors').on('click', function(){
        var $this = $(this);
        if (!confirm($this.data('confirm'))) {
            return false;
        }
    });

});