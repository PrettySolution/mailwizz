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
	var segmentConditionsIndex = $('.conditions-container .item').length, 
		$segmentConditionsTemplate = $('#condition-template');
	
	$('.btn-add-condition').on('click', function(){
		var html = $segmentConditionsTemplate.html();
		html = html.replace(/\{index\}/g, segmentConditionsIndex);
		$('.conditions-container').append(html);
		$('.btn-show-segment-responders').hide();
		++segmentConditionsIndex;
		return false;
	});
	
	$(document).on('click', '.btn-remove-condition', function(){
		$(this).closest('.item').remove();
		$('.btn-show-segment-responders').hide();
		return false;
	});
	
	$('.btn-show-segment-responders').on('click', function(){
		var $this = $(this);
		$.get($this.attr('href'), {}, function(html){
			$('#responders-wrapper').html(html);
			$('.responders-wrapper').show();
			$this.hide();
		});
		return false;
	});
	
	$(document).on('click', 'ul#responders-pagination li a', function(){
		$.get($(this).attr('href'), {}, function(html){
			$('#responders-wrapper').html(html);
			$('.responders-wrapper').show();
		});
		return false;
	});
	
    $(document).on('click', 'a.copy-segment', function() {
		$.post($(this).attr('href'), ajaxData, function(){
			window.location.reload();
		});
		return false;
	});
});