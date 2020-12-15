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

    if ($('#CustomerGroupOptionSending_action_quota_reached').length) {
        $('#CustomerGroupOptionSending_action_quota_reached').on('change', function(){
            var val = $(this).val();
            if (val == 'move-in-group') {
                $('#CustomerGroupOptionSending_move_to_group_id').closest('.move-to-group-id').show();
            } else {
                $('#CustomerGroupOptionSending_move_to_group_id').closest('.move-to-group-id').hide();
            }
        });
    }
    
    $(document).on('click', 'a.copy-group', function() {
		$.post($(this).attr('href'), ajaxData, function(){
			window.location.reload();
		});
		return false;
	});
    
    $(document).on('click', 'a.reset-sending-quota', function() {
        if (!confirm($(this).data('message'))) {
            return false;
        }
		$.post($(this).attr('href'), ajaxData, function(){
			window.location.reload();
		});
		return false;
	});
    
});