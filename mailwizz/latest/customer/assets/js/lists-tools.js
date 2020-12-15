/**
 * This file is part of the MailWizz EMA application.
 * 
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com> 
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.3.4.3
 */
jQuery(document).ready(function($){
	
	var ajaxData = {};
	if ($('meta[name=csrf-token-name]').length && $('meta[name=csrf-token-value]').length) {
			var csrfTokenName = $('meta[name=csrf-token-name]').attr('content');
			var csrfTokenValue = $('meta[name=csrf-token-value]').attr('content');
			ajaxData[csrfTokenName] = csrfTokenValue;
	}
    
    if ($('#sync-lists-box').length) {
        var syncListsTool = function() {
            var self = this;  
            
            self.count              = ko.observable(0);
            self.processedTotal     = ko.observable(0);
            self.processedSuccess   = ko.observable(0);
            self.processedError     = ko.observable(0);
            self.percentage         = ko.observable(0);
            self.progressText       = ko.observable(''); 
            self.primary_list_id    = 0;
            self.secondary_list_id  = 0;

            self.widthPercentage = function() {
                return self.percentage() + '%';
            };
            
            var setAttributes = function(attributes) {
                self.count(attributes.count || self.count());
                self.processedTotal(attributes.processed_total || self.processedTotal());
                self.processedSuccess(attributes.processed_success || self.processedSuccess());
                self.processedError(attributes.processed_error || self.processedError());
                self.percentage(attributes.percentage || self.percentage());
                self.progressText(attributes.progress_text || self.progressText()); 
                
                self.primary_list_id    = attributes.primary_list_id || self.primary_list_id;
                self.secondary_list_id  = attributes.secondary_list_id || self.secondary_list_id;
            };
            
            var process = function(attributes) {
                var postData = $.extend({}, ajaxData, attributes);
                $.post('', postData, null, 'json')
                .done(function(json){
                    if (json && typeof json == 'object') {
                        setAttributes(json.attributes);
                        if (json.attributes.finished != 1) {
                            process(json.formatted_attributes);
                        }
                    }
                })
                .fail(function(jqXHR, textStatus, errorThrown){
                    self.progressText(errorThrown);
                });
            };
            setAttributes($('#sync-lists-box').data('attrs').attributes);
            process($('#sync-lists-box').data('attrs').formatted_attributes);
        };    
        ko.applyBindings(new syncListsTool(), $('#sync-lists-box').get(0));
    }
    
    if ($('#split-list-box').length) {
        var splitListTool = function() {
            var self = this;  

            self.percentage   = ko.observable(0);
            self.progressText = ko.observable(''); 
            
            self.widthPercentage = function() {
                return self.percentage() + '%';
            };
            
            var setAttributes = function(attributes) {
                self.percentage(attributes.percentage || self.percentage());
                self.progressText(attributes.progress_text || self.progressText()); 
            };
            
            var process = function(attributes) {
                var postData = $.extend({}, ajaxData, attributes);
                $.post('', postData, null, 'json')
                .done(function(json){
                    if (json && typeof json == 'object') {
                        setAttributes(json.attributes);
                        if (json.attributes.finished != 1) {
                            process(json.formatted_attributes);
                        }
                    }
                })
                .fail(function(jqXHR, textStatus, errorThrown){
                    self.progressText(errorThrown);
                });
            };
            setAttributes($('#split-list-box').data('attrs').attributes);
            process($('#split-list-box').data('attrs').formatted_attributes);
        };    
        ko.applyBindings(new splitListTool(), $('#split-list-box').get(0));
    }

});