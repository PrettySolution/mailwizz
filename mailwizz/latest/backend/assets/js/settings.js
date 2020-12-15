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

	$('select#OptionCommon_clean_urls').on('change', function(){
		var $this = $(this);
		if ($this.val() == 1) {
			$('.clean-urls-action').show();
		} else {
            $('.clean-urls-action').hide();
		}
	});
	
	$(document).on('click', 'button.btn-write-htaccess', function(){
		var $this = $(this);
		$this.button('loading');
		$.get($this.data('remote'), {}, function(json){
			$this.button('reset');
			var notifyContainer = notify.getOption('container');
			notify.remove();
			notify.setOption('container', '.modal-message');
			if (json.result === 'success') {
				notify.addSuccess(json.message);
			} else {
				notify.addError(json.message);	
			}
			notify.show();
			notify.setOption('container', notifyContainer);
		}, 'json');
	});
    
    $('a.add-campaign-allowed-extension').on('click', function(){
        $('#campaign-allowed-ext-list').append($('#campaign-allowed-ext-item').html());
        return false;
    });
    
    $(document).on('click', 'a.remove-campaign-allowed-ext', function() {
        $(this).closest('.item').fadeOut('slow', function() {
            $(this).remove();
        });
        return false;
    });
    
    $('a.add-campaign-allowed-mime').on('click', function(){
        $('#campaign-allowed-mime-list').append($('#campaign-allowed-mime-item').html());
        return false;
    });
    
    $(document).on('click', 'a.remove-campaign-allowed-mime', function() {
        $(this).closest('.item').fadeOut('slow', function() {
            $(this).remove();
        });
        return false;
    });
    
    if ($('#OptionCustomerSending_action_quota_reached').length) {
        $('#OptionCustomerSending_action_quota_reached').on('change', function(){
            var val = $(this).val();
            if (val == 'move-in-group') {
                $('#OptionCustomerSending_move_to_group_id').closest('.move-to-group-id').show();
            } else {
                $('#OptionCustomerSending_move_to_group_id').closest('.move-to-group-id').hide();
            }
        });
    }
    
    $('.customization-clear-logo').on('click', function(){
        var def = $(this).data('default');
        if (!def) {
            return false;
        }
        $(this).closest('div').find('img:first').attr('src', def);
        $(this).closest('div').parent('div').find('input[type=hidden]').val('');
        return false;
    });
    
    $('#OptionCommon_auto_update').on('focus', function(){
        $(this).closest('div').find('.callout').fadeIn();
    });
});