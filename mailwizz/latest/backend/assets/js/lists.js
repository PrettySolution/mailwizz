/**
 * This file is part of the MailWizz EMA application.
 * 
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com> 
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.3.8.7
 */
jQuery(document).ready(function($){

	var ajaxData = {};
	if ($('meta[name=csrf-token-name]').length && $('meta[name=csrf-token-value]').length) {
		var csrfTokenName = $('meta[name=csrf-token-name]').attr('content');
		var csrfTokenValue = $('meta[name=csrf-token-value]').attr('content');
		ajaxData[csrfTokenName] = csrfTokenValue;
	}
	
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