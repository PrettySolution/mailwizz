/**
 * This file is part of the MailWizz EMA application.
 * 
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com> 
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.3.4.7
 */
jQuery(document).ready(function($){
	
	var ajaxData = {};
	if ($('meta[name=csrf-token-name]').length && $('meta[name=csrf-token-value]').length) {
			var csrfTokenName = $('meta[name=csrf-token-name]').attr('content');
			var csrfTokenValue = $('meta[name=csrf-token-value]').attr('content');
			ajaxData[csrfTokenName] = csrfTokenValue;
	}
    
    if ($('#customers-mass-email-box').length) {
        var customersMassEmailBox = function() {
            var self = this;  
            
            self.total          = ko.observable(0);
            self.processed      = ko.observable(0);
            self.percentage     = ko.observable(0);
            self.progressText   = ko.observable(''); 

            self.widthPercentage = function() {
                return self.percentage() + '%';
            };
            
            var setAttributes = function(attributes) {
                self.total(attributes.total || self.total());
                self.processed(attributes.processed || self.processed());
                self.percentage(attributes.percentage || self.percentage());
                self.progressText(attributes.progress_text || self.progressText()); 
            };
            
            var process = function(attributes) {
                var postData = $.extend({}, ajaxData, attributes);
                $.post('', postData, null, 'json')
                .done(function(json){
                    if (json && typeof json == 'object') {
                        if (json.result != 'success') {
                            self.progressText(json.message);
                            return;
                        }
                        setAttributes(json.attributes);
                        if (!json.attributes.finished) {
                            process(json.formatted_attributes);
                        }
                    }
                })
                .fail(function(jqXHR, textStatus, errorThrown){
                    self.progressText(errorThrown);
                });
            };
            setAttributes($('#customers-mass-email-box').data('attrs').attributes);
            process($('#customers-mass-email-box').data('attrs').formatted_attributes);
        };    
        ko.applyBindings(new customersMassEmailBox(), $('#customers-mass-email-box').get(0));
    }

});