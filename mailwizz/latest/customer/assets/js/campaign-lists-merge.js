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

    if ($('#merge-lists-box').length) {
        var copySubscribers = function() {
            var self = this;  
            
            self.total              = ko.observable(0);
            self.processedTotal     = ko.observable(0);
            self.processedSuccess   = ko.observable(0);
            self.processedError     = ko.observable(0);
            self.percentage         = ko.observable(0);
            self.progressText       = ko.observable(''); 
            
            self.postUrl    = '';
            self.listId     = 0;
            self.segmentId  = 0;
            self.sourceId   = 0;
            self.clid       = 0;
            self.page       = 0;  
            
            self.widthPercentage = function() {
                return self.percentage() + '%';
            };
            
            var setAttributes = function(attributes) {
                var oldSourceId = self.sourceId;
                self.total(attributes.total && attributes.total >= 0 ? attributes.total : self.total());
                self.processedTotal(attributes.processed_total && attributes.processed_total >= 0 ? attributes.processed_total : self.processedTotal());
                self.processedSuccess(attributes.processed_success && attributes.processed_success >= 0 ? attributes.processed_success : self.processedSuccess());
                self.processedError(attributes.processed_error && attributes.processed_error >= 0 ? attributes.processed_error : self.processedError());
                self.percentage(attributes.percentage && attributes.percentage >= 0 ? attributes.percentage : self.percentage());
                self.progressText(attributes.progress_text || self.progressText()); 
                
                self.postUrl    = attributes.post_url || self.postUrl;
                self.listId     = attributes.list_id    >= 0 ? attributes.list_id    : self.listId;
                self.segmentId  = attributes.segment_id >= 0 ? attributes.segment_id : self.segmentId;
                self.sourceId   = attributes.source_id  >= 0 ? attributes.source_id  : self.sourceId;
                self.clid       = attributes.clid       >= 0 ? attributes.clid       : self.clid;
                self.page       = attributes.page       >= 1 ? attributes.page       : self.page; 

                var newSourceId = self.sourceId;
                
                if (oldSourceId != newSourceId) {
                    $('.source-' + oldSourceId).removeClass('bg-green').addClass('bg-blue');
                    $('.source-' + newSourceId).removeClass('bg-red').addClass('bg-green');
                }
                $('.source.bg-green .percentage').html(self.percentage() + '%');
                
                if (attributes.reset_counters) {
                    self.total(0);
                    self.processedTotal(0);
                    self.processedSuccess(0);
                    self.processedError(0);
                    self.percentage(0);
                    self.page = 0;
                }
            };
            
            var process = function() {
                var postData = $.extend({}, ajaxData, {
                    list_id             : self.listId,
                    segment_id          : self.segmentId,
                    source_id           : self.sourceId,
                    clid                : self.clid,
                    processed_total     : self.processedTotal(),
                    processed_success   : self.processedSuccess(),
                    processed_error     : self.processedError(),
                    page                : self.page
                });
                $.post(self.postUrl, postData, null, 'json')
                .done(function(json){
                    if (json && typeof json == 'object') {
                        setAttributes(json);
                        if (!json.finished) {
                            process();
                        }
                        if (json.finished && json.redirect) {
                            if (!json.timeout || typeof json.timeout != 'number') {
                                window.location.href = json.redirect;
                            } else {
                                setTimeout(function(){
                                    window.location.href = json.redirect;
                                }, json.timeout);
                            }
                        }
                    }
                })
                .fail(function(jqXHR, textStatus, errorThrown){
                    self.progressText(errorThrown);
                });
            };
            setAttributes($('#merge-lists-box').data('attributes'));
            process();
        };    
        ko.applyBindings(new copySubscribers(), $('#merge-lists-box').get(0));
    }

});