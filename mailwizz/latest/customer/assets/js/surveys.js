/**
 * This file is part of the MailWizz EMA application.
 * 
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com> 
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.7.8
 */
jQuery(document).ready(function($){

	var ajaxData = {};
	if ($('meta[name=csrf-token-name]').length && $('meta[name=csrf-token-value]').length) {
		var csrfTokenName = $('meta[name=csrf-token-name]').attr('content');
		var csrfTokenValue = $('meta[name=csrf-token-value]').attr('content');
		ajaxData[csrfTokenName] = csrfTokenValue;
	}
	
    $(document).on('click', 'a.copy-list', function() {
		$.post($(this).attr('href'), ajaxData, function(){
			window.location.reload();
		});
		return false;
	});

	var $startAt = $('#Survey_start_at'),
		$displayStartAt = $('#Survey_startAt'),
		$fakeStartAt = $('#fake_start_at');

	if ($startAt.length && $displayStartAt.length && $fakeStartAt.length) {
		console.log($fakeStartAt.data('date-format'));
		$fakeStartAt.datetimepicker({
			format: $fakeStartAt.data('date-format') || 'yyyy-mm-dd hh:ii:ss',
			autoclose: true,
			language: $fakeStartAt.data('language') || 'en',
			showMeridian: true
		}).on('changeDate', function(e) {
			syncDateTimeStart();
		}).on('blur', function(){
			syncDateTimeStart();
		});

		$displayStartAt.on('focus', function(){
			$('#fake_start_at').datetimepicker('show');
		}).on('keyup', function(){
			alert($displayStartAt.data('keyup'));
		});

		function syncDateTimeStart() {
			var date = $fakeStartAt.val();
			if (!date) {
				return;
			}
			$displayStartAt.val('').addClass('spinner');
			$.get($fakeStartAt.data('syncurl'), {date: date}, function(json){
				$displayStartAt.removeClass('spinner');
				$displayStartAt.val(json.localeDateTime);
				$startAt.val(json.utcDateTime);
			}, 'json');
		}
	}

	$displayStartAt.on('change', function() {
		if (!$(this).val() || $(this).val() === '0000-00-00 00:00:00') {
			$fakeStartAt.val('');
			$startAt.val('');
		}
	});

	var $endAt = $('#Survey_end_at'),
		$displayEndAt = $('#Survey_endAt'),
		$fakeEndAt = $('#fake_end_at');

	if ($endAt.length && $displayEndAt.length && $fakeEndAt.length) {

		$fakeEndAt.datetimepicker({
			format: $fakeEndAt.data('date-format') || 'yyyy-mm-dd hh:ii:ss',
			autoclose: true,
			language: $fakeEndAt.data('language') || 'en',
			showMeridian: true
		}).on('changeDate', function(e) {
			syncDateTimeEnd();
		}).on('blur', function(){
			syncDateTimeEnd();
		});

		$displayEndAt.on('focus', function(){
			$('#fake_end_at').datetimepicker('show');
		}).on('keyup', function(){
			alert($displayEndAt.data('keyup'));
		});

		function syncDateTimeEnd() {
			var date = $fakeEndAt.val();
			if (!date) {
				return;
			}
			$displayEndAt.val('').addClass('spinner');
			$.get($fakeEndAt.data('syncurl'), {date: date}, function(json){
				$displayEndAt.removeClass('spinner');
				$displayEndAt.val(json.localeDateTime);
				$endAt.val(json.utcDateTime);
			}, 'json');
		}
	}

	$displayEndAt.on('change', function() {
		if (!$(this).val() || $(this).val() === '0000-00-00 00:00:00') {
			$fakeEndAt.val('');
			$endAt.val('');
		}
	});
});