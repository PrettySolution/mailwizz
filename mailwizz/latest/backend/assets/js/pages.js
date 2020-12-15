/**
 * This file is part of the MailWizz EMA application.
 *
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.5.5
 */
jQuery(document).ready(function($){
	
	var ajaxData = {};
	if ($('meta[name=csrf-token-name]').length && $('meta[name=csrf-token-value]').length) {
			var csrfTokenName = $('meta[name=csrf-token-name]').attr('content');
			var csrfTokenValue = $('meta[name=csrf-token-value]').attr('content');
			ajaxData[csrfTokenName] = csrfTokenValue;
	}
	
	$('#Page_title').on('blur', function() {
		var $this = $(this);
		if ($this.val() != '' && $('#Page_slug').val() == '') {
			var formData = {
				string: $this.val(), 
				article_id: $this.data('article-id')
			};
			formData = $.extend({}, formData, ajaxData);
			$.post($this.data('slug-url'), formData, function(json){
				if (json.result == 'success') {
					$('#Page_slug').val(json.slug).closest('.slug-wrapper').fadeIn();
				}
			}, 'json');
		}
	});
	
	$('#Page_slug').on('blur', function() {
		var $this = $(this), 
		formData = {
			string: ($this.val() != '' ? $this.val() : $('#Page_title').val()), 
			article_id: $('#Page_title').data('article-id')
		};
		formData = $.extend({}, formData, ajaxData);
		$.post($('#Page_title').data('slug-url'), formData, function(json){
			if (json.result == 'success') {
				$this.val(json.slug).closest('.slug-wrapper').fadeIn();
			}
		}, 'json');
	});
	
});