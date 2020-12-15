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
    
    $('.btn-show-copy-subs-ajax').on('click', function(){
        $('#copy_list_id option').remove();
        $('#copy_list_id').append('<option value="">-</option>');
        $.get($(this).data('ajax'), {}, function(json){
            if (json.data && json.data.lists && json.data.lists.length > 0) {
                for (var i in json.data.lists) {
                    $('#copy_list_id').append('<option value="'+json.data.lists[i].list_id+'">'+json.data.lists[i].name+'</option>');
                }
            }
        }, 'json');
    });
    
    $('#copy_list_id').on('change', function(){
        var list_id = $(this).val();
        $('#copy_segment_id option').remove();
        $('#copy_segment_id').append('<option value="">-</option>');
        if (list_id) {
            $.get($('.btn-show-copy-subs-ajax').data('ajax'), {list_id: list_id}, function(json){
                if (json.data.segments && json.data.segments.length) {
                    for (var i in json.data.segments) {
                        $('#copy_segment_id').append('<option value="'+json.data.segments[i].segment_id+'">'+json.data.segments[i].name+'</option>');
                    }
                }
            }, 'json');
        }
    });
    
    if ($('#copy-list-subscribers-box').length) {
        var copySubscribers = function() {
            var self = this;  
            
            self.total              = ko.observable(0);
            self.processedTotal     = ko.observable(0);
            self.processedSuccess   = ko.observable(0);
            self.processedError     = ko.observable(0);
            self.percentage         = ko.observable(0);
            self.progressText       = ko.observable(''); 
            
            self.postUrl        = '';
            self.listId         = 0;
            self.segmentId      = 0;
            self.status         = [];
            self.status_action  = 0;
            self.page           = 0;  
            
            self.widthPercentage = function() {
                return self.percentage() + '%';
            };
            
            var setAttributes = function(attributes) {
                self.total(attributes.total || self.total());
                self.processedTotal(attributes.processed_total || self.processedTotal());
                self.processedSuccess(attributes.processed_success || self.processedSuccess());
                self.processedError(attributes.processed_error || self.processedError());
                self.percentage(attributes.percentage || self.percentage());
                self.progressText(attributes.progress_text || self.progressText()); 
                
                self.postUrl        = attributes.post_url || self.postUrl;
                self.listId         = attributes.list_id || self.listId;
                self.segmentId      = attributes.segment_id || self.segmentId;
                self.status         = attributes.status || self.status;
                self.status_action  = attributes.status_action || self.status_action;
                self.page           = attributes.page || self.page; 
            };
            
            var process = function() {
                var postData = $.extend({}, ajaxData, {
                    copy_list_id        : self.listId,
                    copy_segment_id     : self.segmentId,
                    copy_status         : self.status,
                    copy_status_action  : self.status_action,
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
                    }
                })
                .fail(function(jqXHR, textStatus, errorThrown){
                    self.progressText(errorThrown);
                });
            };
            setAttributes($('#copy-list-subscribers-box').data('attributes'));
            process();
        };    
        ko.applyBindings(new copySubscribers(), $('#copy-list-subscribers-box').get(0));
    }

});