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
	
	$('#Article_title').on('blur', function() {
		var $this = $(this);
		if ($this.val() != '' && $('#Article_slug').val() == '') {
			var formData = {
				string: $this.val(), 
				article_id: $this.data('article-id')
			};
			formData = $.extend({}, formData, ajaxData);
			$.post($this.data('slug-url'), formData, function(json){
				if (json.result == 'success') {
					$('#Article_slug').val(json.slug).closest('.slug-wrapper').fadeIn();
				}
			}, 'json');
		}
	});
	
	$('#Article_slug').on('blur', function() {
		var $this = $(this), 
		formData = {
			string: ($this.val() != '' ? $this.val() : $('#Article_title').val()), 
			article_id: $('#Article_title').data('article-id')
		};
		formData = $.extend({}, formData, ajaxData);
		$.post($('#Article_title').data('slug-url'), formData, function(json){
			if (json.result == 'success') {
				$this.val(json.slug).closest('.slug-wrapper').fadeIn();
			}
		}, 'json');
	});
	
	$('#ArticleCategory_name').on('blur', function() {
		var $this = $(this);
		if ($this.val() != '' && $('#ArticleCategory_slug').val() == '') {
			var formData = {
				string: $this.val(), 
				category_id: $this.data('category-id')
			};
			formData = $.extend({}, formData, ajaxData);
			$.post($this.data('slug-url'), formData, function(json){
				if (json.result == 'success') {
					$('#ArticleCategory_slug').val(json.slug).closest('.slug-wrapper').fadeIn();
				}
			}, 'json');
		}
	});
	
	$('#ArticleCategory_slug').on('blur', function() {
		var $this = $(this), 
		formData = {
			string: ($this.val() != '' ? $this.val() : $('#ArticleCategory_name').val()), 
			category_id: $('#ArticleCategory_name').data('category-id')
		};
		formData = $.extend({}, formData, ajaxData);
		$.post($('#ArticleCategory_name').data('slug-url'), formData, function(json){
			if (json.result == 'success') {
				$this.val(json.slug).closest('.slug-wrapper').fadeIn();
			}
		}, 'json');
	});
	
});