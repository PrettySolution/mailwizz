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
	
	// company start
	$('select#ListCompany_country_id').on('change', function() {
		var url = $(this).data('zones-by-country-url'), 
			countryId = $(this).val(),
			$zones = $('select#ListCompany_zone_id');
		
		if (url) {
			var formData = {
				country_id: countryId
			};

			$.get(url, formData, function(json){
				$zones.html('');
				if (typeof json.zones == 'object' && json.zones.length > 0) {
					for (var i in json.zones) {
						$zones.append($('<option/>').val(json.zones[i].zone_id).html(json.zones[i].name));
					}	
				}
			}, 'json');
			
		}
	});
	// company end
	
    $(document).on('click', 'a.copy-list', function() {
		$.post($(this).attr('href'), ajaxData, function(){
			window.location.reload();
		});
		return false;
	});
	
	$('.list-subscriber-actions-scrollbox input[type=checkbox]').on('click', function(){
		var $this = $(this), $wrapper = $this.closest('.list-subscriber-actions-scrollbox');
		if ($this.val() == 0) {
            if ($this.is(':checked')) {
                $('input[type=checkbox]', $wrapper).not($this).each(function() {
                    if (!$(this).is(':checked')) {
                        $(this).click();
                    }
                });
            } else {
                $('input[type=checkbox]', $wrapper).not($this).each(function() {
                    if ($(this).is(':checked')) {
                        $(this).click();
                    }
                });
            }
        } else {
            if (!$(this).is(':checked')) {
                $wrapper.find('input[type=checkbox]:first').removeAttr('checked').get(0).checked = false;
            }
		}
	});
	
	if ($('#campaigns-overview-wrapper').length) {
		(function(){
			var handle = function(){
				var $el = $('#campaigns-overview-wrapper');
                $el.css({opacity: .5});
                
                var cid = $el.find('#campaign_id').length ? $el.find('#campaign_id').val() : 0, 
                    lid = $el.data('list'), 
                    data = 'campaign_id='+cid+'&list_id=' + lid;
				
                for (var i in ajaxData) {
					data += '&' + i + '=' + ajaxData[i];
				}
				
				$.post($el.data('url'), data, function(json){
					$el.html(json.html);
                    $el.css({opacity: 1});
				}, 'json');
			};
			$(document).on('change', '#campaigns-overview-wrapper #campaign_id', function(){
				$(this).attr('disabled', true);
				handle();
			});
			handle();
		})()
	}
    
});