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
	
    var $extraRecipientsTemplate    = $('#extra-recipients-template');
    if ($extraRecipientsTemplate.length) {
        var extraRecipientsCounter = $extraRecipientsTemplate.data('count');
        $('a.btn-add-extra-recipients').on('click', function(){
            var $html = $($extraRecipientsTemplate.html().replace(/__#__/g, extraRecipientsCounter));
            $('#extra-list-segment-container').append($html);
            $html.find('input, select').removeAttr('disabled');
            $('.col-segment select option', $html).each(function(index, value){
            	if (index > 0) {
            		$(this).remove();
				}
			});
            extraRecipientsCounter++;
            return false;
        });
        
        $(document).on('click', 'a.remove-extra-recipients', function(){
            $(this).closest('.item').remove();
            return false;
        });
        
        $(document).on('change', '#extra-list-segment-container .col-list select', function(){
            var list_id = $(this).val();

    		var $segments = $(this).closest('div.item').find('.col-segment select');
    		var url = $segments.data('url');
    		$segments.html('');
    		
    		if (!list_id) {
    			$segments.attr('disabled', true);
    			return;
    		}
    		
    		$.get(url, {list_id: list_id}, function(json){
    				
    			if (typeof json.segments == 'object' && json.segments.length > 0) {
    				for (var i in json.segments) {
    					$segments.append($('<option/>').val(json.segments[i].segment_id).html(json.segments[i].name));
    				}	
    			}
    			
    		}, 'json');
    		
    		$segments.removeAttr('disabled'); 
        });
    }

	$('#Campaign_list_id').on('change', function(){
		var list_id = $(this).val();

		var $segments = $('select#Campaign_segment_id');
		var url = $segments.data('url');
		$segments.html('');
		
		if (!list_id) {
			$('#Campaign_segment_id').attr('disabled', true);
			return;
		}
		
		$.get(url, {list_id: list_id}, function(json){
				
			if (typeof json.segments == 'object' && json.segments.length > 0) {
				for (var i in json.segments) {
					$segments.append($('<option/>').val(json.segments[i].segment_id).html(json.segments[i].name));
				}	
			}
			
		}, 'json');
		
		$('#Campaign_segment_id').removeAttr('disabled');
	});
	
	$('a.load-selected').on('click', function(){
		var $select = $('select#CustomerEmailTemplate_template_id');
		
		if ($select.val() == '') {
			alert('Please select a template first!');
			return false;
		}
		$('#selected_template_id').val($select.val());
		$(this).closest('form').submit();
		return false;
	});
	
    var $sendAt = $('#Campaign_send_at'), 
        $displaySendAt = $('#Campaign_sendAt'),
        $fakeSendAt = $('#fake_send_at');
	
    if ($sendAt.length && $displaySendAt.length && $fakeSendAt.length) {

        $fakeSendAt.datetimepicker({
			format: $fakeSendAt.data('date-format') || 'yyyy-mm-dd hh:ii:ss',
			autoclose: true,
            language: $fakeSendAt.data('language') || 'en',
            showMeridian: true
		}).on('changeDate', function(e) {
            syncDateTime();
		}).on('blur', function(){
            syncDateTime();
		});
        
        $displaySendAt.on('focus', function(){
            $('#fake_send_at').datetimepicker('show');
        }).on('keyup', function(){
            alert($displaySendAt.data('keyup'));
        });
        
        function syncDateTime() {
            var date = $fakeSendAt.val();
            if (!date) {
                return;
            }
            $displaySendAt.val('').addClass('spinner');
            $.get($fakeSendAt.data('syncurl'), {date: date}, function(json){
                $displaySendAt.removeClass('spinner');
                $displaySendAt.val(json.localeDateTime);
                $sendAt.val(json.utcDateTime);
            }, 'json');
        }
        syncDateTime();
	}

	$(document).on('click', 'a.pause-sending, a.unpause-sending', function() {
		if (!confirm($(this).data('message'))) {
			return false;
		}
		$.post($(this).attr('href'), ajaxData, function(){
			window.location.reload();
		});
		return false;
	});
    
    $(document).on('click', 'a.copy-campaign', function() {
		$.post($(this).attr('href'), ajaxData, function(json) {
            if (typeof json.next == 'string' && json.next) {
                window.location.href = json.next;
                return;
            }
			window.location.reload();
		}, 'json');
		return false;
	});
    
    $(document).on('click', 'a.resume-campaign-sending', function() {
        if (!confirm($(this).data('message'))) {
			return false;
		}
		$.post($(this).attr('href'), ajaxData, function(){
			window.location.reload();
		});
		return false;
	});
    
    $(document).on('click', 'a.mark-campaign-as-sent', function() {
        if (!confirm($(this).data('message'))) {
			return false;
		}
		$.post($(this).attr('href'), ajaxData, function(){
			window.location.reload();
		});
		return false;
	});

    $(document).on('click', 'a.resend-campaign-giveups', function() {
        var $this = $(this);
        if (!confirm($this.data('message'))) {
            return false;
        }
        $.post($(this).attr('href'), ajaxData, function(json){
            if (json.result === 'success') {
                notify.addSuccess(json.message);
                $this.remove();
            } else {
                notify.addError(json.message);
            }
            $('html, body').animate({scrollTop: 0}, 500);
            notify.show();
        });
        return false;
    });
    
    $('a.btn-remove-attachment').on('click', function(){
        var $this = $(this);
        if (!confirm($this.data('message'))) {
			return false;
		}
        
        $this.closest('div.col-lg-4').fadeOut('slow', function(){
            $(this).remove();
        });
        
        $.post($this.attr('href'), ajaxData, function(){
			
		});
        return false;
    });
    
    $('button.btn-plain-text').on('click', function(){
        var $this = $(this), 
            $container = $('.plain-text-version');
		
        if (!$container.is(':visible')){
            $container.slideDown('slow', function(){
                $this.text($this.data('hidetext'));
            });
            $container.find('textarea').eq(0).focus();
        } else {
            $container.slideUp('slow', function(){
                $this.text($this.data('showtext'));
            });
            $this.blur();
        }
        
        return false;
    });
    
    //
    $('button.btn-template-click-actions').on('click', function(){
        var $this = $(this), 
            $container = $('.template-click-actions-container');
        
        if ($('.plain-text-version').is(':visible')) {
            $('button.btn-plain-text').trigger('click');
        }
        
        if (!$container.is(':visible')){
            $container.slideDown('slow');
        } else {
            $container.slideUp('slow');
            $this.blur();
        }
        
        return false;
    });
    
	$(document).on('click', 'a.btn-template-click-actions-remove', function(){
		if ($(this).data('url-id') > 0 && !confirm($(this).data('message'))) {
			return false;
		}
		$(this).closest('.template-click-actions-row').fadeOut('slow', function() {
            $('button.btn-template-click-actions span.count').text(parseInt($('button.btn-template-click-actions span.count').text()) - 1);
			$(this).remove();
		});
		return false;
	});
	
    $('a.btn-template-click-actions-add').on('click', function(){
		var currentIndex = -1;
		$('.template-click-actions-row').each(function(){
			if ($(this).data('start-index') > currentIndex) {
				currentIndex = $(this).data('start-index');
			}
		});
		currentIndex++;
        var tpl = $('#template-click-actions-template').html();
		tpl = tpl.replace(/\{index\}/g, currentIndex);
		var $tpl = $(tpl);
		$('.template-click-actions-list').append($tpl);
		
		$tpl.find('.has-help-text').popover();
		$('button.btn-template-click-actions span.count').text(parseInt($('button.btn-template-click-actions span.count').text()) + 1);
		return false;	
	});
    //
    
    //
    $('button.btn-campaign-track-url-webhook').on('click', function(){
        var $this = $(this),
            $container = $('.campaign-track-url-webhook-container');

        if ($('.plain-text-version').is(':visible')) {
            $('button.btn-plain-text').trigger('click');
        }

        if (!$container.is(':visible')){
            $container.slideDown('slow');
        } else {
            $container.slideUp('slow');
            $this.blur();
        }

        return false;
    });

    $(document).on('click', 'a.btn-campaign-track-url-webhook-remove', function(){
        if ($(this).data('url-id') > 0 && !confirm($(this).data('message'))) {
            return false;
        }
        $(this).closest('.campaign-track-url-webhook-row').fadeOut('slow', function() {
            $('button.btn-campaign-track-url-webhook span.count').text(parseInt($('button.btn-campaign-track-url-webhook span.count').text()) - 1);
            $(this).remove();
        });
        return false;
    });

    $('a.btn-campaign-track-url-webhook-add').on('click', function(){
        var currentIndex = -1;
        $('.campaign-track-url-webhook-row').each(function(){
            if ($(this).data('start-index') > currentIndex) {
                currentIndex = $(this).data('start-index');
            }
        });
        currentIndex++;
        console.log(currentIndex);
        var tpl = $('#campaign-track-url-webhook-template').html();
        tpl = tpl.replace(/\{index\}/g, currentIndex);
        var $tpl = $(tpl);
        $('.campaign-track-url-webhook-list').append($tpl);

        $tpl.find('.has-help-text').popover();
        $('button.btn-campaign-track-url-webhook span.count').text(parseInt($('button.btn-campaign-track-url-webhook span.count').text()) + 1);
        return false;
    });
    //
    
    //
    $('button.btn-template-click-actions-list-fields').on('click', function(){
        var $this = $(this), 
            $container = $('.template-click-actions-list-fields-container');
        
        if ($('.plain-text-version').is(':visible')) {
            $('button.btn-plain-text').trigger('click');
        }
        
        if (!$container.is(':visible')){
            $container.slideDown('slow');
        } else {
            $container.slideUp('slow');
            $this.blur();
        }
        
        return false;
    });
    
	$(document).on('click', 'a.btn-template-click-actions-list-fields-remove', function(){
		if ($(this).data('url-id') > 0 && !confirm($(this).data('message'))) {
			return false;
		}
		$(this).closest('.template-click-actions-list-fields-row').fadeOut('slow', function() {
            $('button.btn-template-click-actions-list-fields span.count').text(parseInt($('button.btn-template-click-actions-list-fields span.count').text()) - 1);
			$(this).remove();
		});
		return false;
	});
	
    $('a.btn-template-click-actions-list-fields-add').on('click', function(){
		var currentIndex = -1;
		$('.template-click-actions-list-fields-row').each(function(){
			if ($(this).data('start-index') > currentIndex) {
				currentIndex = $(this).data('start-index');
			}
		});
		currentIndex++;
        var tpl = $('#template-click-actions-list-fields-template').html();
		tpl = tpl.replace(/\{index\}/g, currentIndex);
		var $tpl = $(tpl);
		$('.template-click-actions-list-fields-list').append($tpl);
		
		$tpl.find('.has-help-text').popover();
		$('button.btn-template-click-actions-list-fields span.count').text(parseInt($('button.btn-template-click-actions-list-fields span.count').text()) + 1);
		return false;	
	});
    //
    
	$(document).on('click', 'a.btn-campaign-open-actions-remove', function(){
		if ($(this).data('action-id') > 0 && !confirm($(this).data('message'))) {
			return false;
		}
		$(this).closest('.campaign-open-actions-row').fadeOut('slow', function() {
            $(this).remove();
		});
		return false;
	});
	
    $('a.btn-campaign-open-actions-add').on('click', function(){
		var currentIndex = -1;
		$('.campaign-open-actions-row').each(function(){
			if ($(this).data('start-index') > currentIndex) {
				currentIndex = $(this).data('start-index');
			}
		});
		currentIndex++;
        var tpl = $('#campaign-open-actions-template').html();
		tpl = tpl.replace(/\{index\}/g, currentIndex);
		var $tpl = $(tpl);
		$('.campaign-open-actions-list').append($tpl);
		$tpl.find('.has-help-text').popover();
		return false;	
	});
    
    //
    $(document).on('click', 'a.btn-campaign-sent-actions-remove', function(){
        if ($(this).data('action-id') > 0 && !confirm($(this).data('message'))) {
            return false;
        }
        $(this).closest('.campaign-sent-actions-row').fadeOut('slow', function() {
            $(this).remove();
        });
        return false;
    });

    $('a.btn-campaign-sent-actions-add').on('click', function(){
        var currentIndex = -1;
        $('.campaign-sent-actions-row').each(function(){
            if ($(this).data('start-index') > currentIndex) {
                currentIndex = $(this).data('start-index');
            }
        });
        currentIndex++;
        var tpl = $('#campaign-sent-actions-template').html();
        tpl = tpl.replace(/\{index\}/g, currentIndex);
        var $tpl = $(tpl);
        $('.campaign-sent-actions-list').append($tpl);
        $tpl.find('.has-help-text').popover();
        return false;
    });
    //

    //
    $(document).on('click', 'a.btn-campaign-track-open-webhook-remove', function(){
        if (!confirm($(this).data('message'))) {
            return false;
        }
        $(this).closest('.campaign-track-open-webhook-row').fadeOut('slow', function() {
            $(this).remove();
        });
        return false;
    });

    $('a.btn-campaign-track-open-webhook-add').on('click', function(){
        var currentIndex = -1;
        $('.campaign-track-open-webhook-row').each(function(){
            if ($(this).data('start-index') > currentIndex) {
                currentIndex = $(this).data('start-index');
            }
        });
        currentIndex++;
        var tpl = $('#campaign-track-open-webhook-template').html();
        tpl = tpl.replace(/\{index\}/g, currentIndex);
        var $tpl = $(tpl);
        $('.campaign-track-open-webhook-list').append($tpl);
        $tpl.find('.has-help-text').popover();
        return false;
    });
    //

    //
    $(document).on('click', 'a.btn-campaign-extra-tags-remove', function(){
        if ($(this).data('tag-id') > 0 && !confirm($(this).data('message'))) {
            return false;
        }
        $(this).closest('.campaign-extra-tags-row').fadeOut('slow', function() {
            $(this).remove();
        });
        return false;
    });

    $('a.btn-campaign-extra-tags-add').on('click', function(){
        var currentIndex = -1;
        $('.campaign-extra-tags-row').each(function(){
            if ($(this).data('start-index') > currentIndex) {
                currentIndex = $(this).data('start-index');
            }
        });
        currentIndex++;
        var tpl = $('#campaign-extra-tags-template').html();
        tpl = tpl.replace(/\{index\}/g, currentIndex);
        var $tpl = $(tpl);
        $('.campaign-extra-tags-list').append($tpl);
        $tpl.find('.has-help-text').popover();
        return false;
    });
    //
    
    //
    $(document).on('click', 'a.btn-campaign-open-list-fields-actions-remove', function(){
		if ($(this).data('action-id') > 0 && !confirm($(this).data('message'))) {
			return false;
		}
		$(this).closest('.campaign-open-list-fields-actions-row').fadeOut('slow', function() {
            $(this).remove();
		});
		return false;
	});
	
    $('a.btn-campaign-open-list-fields-actions-add').on('click', function(){
		var currentIndex = -1;
		$('.campaign-open-list-fields-actions-row').each(function(){
			if ($(this).data('start-index') > currentIndex) {
				currentIndex = $(this).data('start-index');
			}
		});
		currentIndex++;
        var tpl = $('#campaign-open-list-fields-actions-template').html();
		tpl = tpl.replace(/\{index\}/g, currentIndex);
		var $tpl = $(tpl);
		$('.campaign-open-list-fields-actions-list').append($tpl);
		$tpl.find('.has-help-text').popover();
		return false;	
	});
    //

    //
    $(document).on('click', 'a.btn-campaign-sent-list-fields-actions-remove', function(){
        if ($(this).data('action-id') > 0 && !confirm($(this).data('message'))) {
            return false;
        }
        $(this).closest('.campaign-sent-list-fields-actions-row').fadeOut('slow', function() {
            $(this).remove();
        });
        return false;
    });

    $('a.btn-campaign-sent-list-fields-actions-add').on('click', function(){
        var currentIndex = -1;
        $('.campaign-sent-list-fields-actions-row').each(function(){
            if ($(this).data('start-index') > currentIndex) {
                currentIndex = $(this).data('start-index');
            }
        });
        currentIndex++;
        var tpl = $('#campaign-sent-list-fields-actions-template').html();
        tpl = tpl.replace(/\{index\}/g, currentIndex);
        var $tpl = $(tpl);
        $('.campaign-sent-list-fields-actions-list').append($tpl);
        $tpl.find('.has-help-text').popover();
        return false;
    });
    //
    
    if ($('#CampaignOption_autoresponder_event').length) {
        $('#CampaignOption_autoresponder_event').on('change', function(){
            var $this = $(this);
            if ($this.val() == 'AFTER-CAMPAIGN-OPEN') {
                $('#CampaignOption_autoresponder_open_campaign_id').closest('.autoresponder-open-campaign-id-wrapper').show();
                $('#CampaignOption_autoresponder_sent_campaign_id').closest('.autoresponder-sent-campaign-id-wrapper').hide();
            } else if ($this.val() == 'AFTER-CAMPAIGN-SENT') {
                $('#CampaignOption_autoresponder_open_campaign_id').closest('.autoresponder-open-campaign-id-wrapper').hide();
                $('#CampaignOption_autoresponder_sent_campaign_id').closest('.autoresponder-sent-campaign-id-wrapper').show();
            } else {
                $('#CampaignOption_autoresponder_open_campaign_id').closest('.autoresponder-open-campaign-id-wrapper').hide();
                $('#CampaignOption_autoresponder_sent_campaign_id').closest('.autoresponder-sent-campaign-id-wrapper').hide();
            }
        });
    }
	
    $('#CampaignTemplate_only_plain_text').on('change', function(){
        var $this = $(this);
        if ($this.val() == 'yes') {
            $('#CampaignTemplate_auto_plain_text').val('yes').closest('.auto-plain-text-wrapper').hide();
            $('.btn-plain-text').hide();
            $('#CampaignTemplate_content').closest('.html-version').hide();
            $('#CampaignTemplate_plain_text').closest('.plain-text-version').show();
        } else {
            $('#CampaignTemplate_auto_plain_text').val('yes').closest('.auto-plain-text-wrapper').show();
            $('.btn-plain-text').show();
            $('#CampaignTemplate_plain_text').closest('.plain-text-version').hide();
            $('#CampaignTemplate_content').closest('.html-version').show();
        }
    });
	
    // since 1.3.5.3
    if ($('#CampaignOption_cronjob').length && $.fn.jqCron != undefined) {
        $('#CampaignOption_cronjob').jqCron({
            enabled_minute: true,
            enabled_hour: true,
            multiple_dom: true,
            multiple_month: true,
            multiple_mins: true,
            multiple_dow: true,
            multiple_time_hours: true,
            multiple_time_minutes: true,
            no_reset_button: false,
            lang: $('#CampaignOption_cronjob').data('lang')
        });
        $('#CampaignOption_cronjob_enabled').on('change', function(){
            var $this = $(this);
            if (!$this.is(':checked')) {
                $('#CampaignOption_cronjob').closest('.jqcron-holder').find('.jqCron').css({visibility:'hidden'});
                $('#CampaignOption_cronjob_max_runs').closest('.form-group').hide();
            } else {
                $('#CampaignOption_cronjob').closest('.jqcron-holder').find('.jqCron').css({visibility:'visible'});
                $('#CampaignOption_cronjob_max_runs').closest('.form-group').show();
            }
        }).trigger('change');
    }
    
    // since 1.3.7.3
	function randomString(length) {
		var text 	 = [], 
			possible = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789",
			length	 = typeof length == 'number' ? length : 12;

		for (var i = 0; i < length; i++) {
			text.push(possible.charAt(Math.floor(Math.random() * possible.length)));
		}
		
		return text.join("");
	}
	$('.btn-generate-share-password').on('click', function(){
		$('#CampaignOptionShareReports_share_reports_password').val(randomString());
		return false;
	});
    $('#campaign-share-reports-form').on('submit', function(){
        var $form = $(this), $message = $form.find('.message');
        $message.empty();
        $message.append('<div class="alert alert-info">' + $message.data('wait') + '</div>');
        $.post($form.attr('action'), $form.serialize(), function(json){
            $message.empty();
            if (json.result == 'success') {
                $message.append('<div class="alert alert-success">' + json.message + '</div>');
            } else {
                $message.append('<div class="alert alert-danger">' + json.message + '</div>');
            }
        }, 'json');
        return false;
    });
    //
    
    // since 1.4.4
    $('#CampaignOptionShareReports_share_reports_enabled').on('change', function(){
        if ($(this).val() != 'yes') {
            $('#CampaignOptionShareReports_share_reports_email').attr('disabled', true);
            $('.btn-send-share-stats-details').attr('disabled', true);
        } else {
            $('#CampaignOptionShareReports_share_reports_email').removeAttr('disabled');
            $('.btn-send-share-stats-details').removeAttr('disabled');
        }
    }).trigger('change');
    $('.btn-send-share-stats-details').on('click', function(){
        var $this = $(this), $form = $this.closest('form'), $message = $form.find('.message'), data = $form.serialize();
        $this.attr('disabled', true);
        $message.empty();
        $message.append('<div class="alert alert-info">' + $message.data('wait') + '</div>');
        $.post($this.data('action'), data, function(json){
            $message.empty();
            if (json.result == 'success') {
                $message.append('<div class="alert alert-success">' + json.message + '</div>');
            } else {
                $message.append('<div class="alert alert-danger">' + json.message + '</div>');
            }
            $this.removeAttr('disabled');
        }, 'json');
        return false;
    });
    //

    $(document).on('click', '.toggle-filters-form', function(){
        $('#filters-form').toggle();
        return false;
    });
    
    $(document).on('click', '#toggle-emoji-list', function(){
    	$('#emoji-list').slideToggle();
    	return false;
	});

    $(document).on('click', '#emoji-list span', function(){
        $('#Campaign_subject').val( $('#Campaign_subject').val() + ' ' + $(this).text() ).focus();
        return false;
    });
	
    $(document).on('click', '.btn-toggle-random-content', function(){
    	$('.random-content-container').slideToggle();
    	return false;
	});

    var randomContentCounter = 0;
    setTimeout(function(){
        $('.random-content-container-items .random-content-item').each(function(){
            $('.random-content-container-items').find('#CampaignRandomContent_content_' + randomContentCounter).ckeditor(wysiwygOptionsCampaignRandomContent_content);
            randomContentCounter++;
        });
	}, 100);
	
    $(document).on('click', '.btn-template-random-content-item-add', function(){
        var html = $('#random-content-template').html();
        html = html.replace(/\{counter\}/gi, randomContentCounter);
        var $tpl = $('<div/>').html(html);
    	$tpl.find('.random-content-item').data('counter', randomContentCounter);
    	
    	$('.random-content-container-items').append($tpl.html());
        $('.random-content-container-items').find('#CampaignRandomContent_content_' + randomContentCounter).ckeditor(wysiwygOptionsCampaignRandomContent_content);
        $('.btn-toggle-random-content span').text($('.btn-toggle-random-content span').text() * 1 + 1);
        randomContentCounter++;
        return false;
	});

    $(document).on('click', '.btn-template-random-content-item-delete', function(){
        var $item = $(this).closest('.random-content-item');
        if (CKEDITOR.instances['CampaignRandomContent_content_' + $item.data('counter')]) {
            CKEDITOR.instances['CampaignRandomContent_content_' + $item.data('counter')].destroy();
		}
        $item.remove();
        $('.btn-toggle-random-content span').text($('.btn-toggle-random-content span').text() - 1);
        return false;
    });

    $(document).on('click', 'a.preview-email-template', function(){
        window.open($(this).attr('href'), $(this).attr('title'), 'scrollbars=1, resizable=1, height=600, width=600');
        return false;
    });
    
    $('.btn-show-more-options').on('click', function(){
        $('.more-options-wrapper').show();
        $('.btn-show-more-options').hide();
        $('.btn-show-less-options').show();
        return false;
    });

    $('.btn-show-less-options').on('click', function(){
        $('.more-options-wrapper').hide();
        $('.btn-show-less-options').hide();
        $('.btn-show-more-options').show();
        return false;
    });
    
    $(document).on('click', '#btn-run-bulk-action', function(){
        
        if ($('#bulk_action').val() === 'send-test-email') {
            $('#bulk-send-test-email').modal('show');
            return false;
        }

        $('#bulk-action-form')
            .append($('<input/>').attr({name: 'bulk_action'}).val($('#bulk_action').val()))
            .append($('.checkbox-column input[type=checkbox]:checked').clone())
            .submit();

        return false;
    });
    
    $(document).on('submit', '#bulk-send-test-email-form', function(){

        $('#bulk-action-form')
            .append($('<input/>').attr({name: 'bulk_action'}).val($('#bulk_action').val()))
            .append($('.checkbox-column input[type=checkbox]:checked').clone())
            .append($('<input/>').attr({name: 'recipients_emails'}).val($('#recipients_emails').val()))
            .submit();
        
        return false;
    });
    
});