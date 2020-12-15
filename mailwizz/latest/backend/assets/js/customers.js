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

    $(document).on('click', 'a.reset-sending-quota', function() {
        if (!confirm($(this).data('message'))) {
            return false;
        }
		$.post($(this).attr('href'), ajaxData, function(){
			window.location.reload();
		});
		return false;
	});

	var $displayInactiveAt = $('#Customer_inactiveAt'),
		$fakeInactiveAt = $('#fake_inactive_at');
	if ($displayInactiveAt.length && $fakeInactiveAt.length) {

		$fakeInactiveAt.datetimepicker({
			format: $fakeInactiveAt.data('date-format') || 'yyyy-mm-dd hh:ii:ss',
			autoclose: true,
			language: $fakeInactiveAt.data('language') || 'en',
			showMeridian: true
		}).on('changeDate', function(e) {
			syncDateTime();
		}).on('blur', function(){
			syncDateTime();
		});

		$displayInactiveAt.on('focus', function(){
			$('#fake_inactive_at').datetimepicker('show');
		});

		function syncDateTime() {
			var date = $fakeInactiveAt.val();
			if (!date) {
				return;
			}
			$displayInactiveAt.val(date);
		}
		syncDateTime();
	}
});