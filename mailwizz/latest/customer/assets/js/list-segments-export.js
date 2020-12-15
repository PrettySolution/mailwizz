/**
 * This file is part of the MailWizz EMA application.
 * 
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com> 
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.3.4.8
 */
jQuery(document).ready(function($){
    
    // CSV EXPORT
    (function(){
        if (!$('#csv-export').length) {
            return;
        }
        
        var rowCount = 0,
            timeout = 10, 
            haltExecution = false, 
            exportSuccessCount = 0, 
            exportErrorCount = 0, 
            exportCount = 0, 
            recordsCount = -1,
            recordsIteration = 0,
            percentage = 0;
        
        var $csvExport = $('#csv-export'),
            $exportSuccessCount = $csvExport.find('.counters .success'), 
            $exportErrorCount = $csvExport.find('.counters .error'), 
            $exportTotalProcessed = $csvExport.find('.counters .total-processed'),
            $exportTotal = $csvExport.find('.counters .total'),
            $exportPercentage = $csvExport.find('.counters .percentage'),
            $logInfo = $csvExport.find('.log-info'),
            $logErrors = $csvExport.find('.log-errors'),
            $progress = $csvExport.find('.progress').eq(0), 
            $progressBar = $progress.find('.progress-bar'),
            $progressBarSr = $progressBar.find('.sr-only'),
            pause = $csvExport.data('pause') * 1000;
        
        function doQueueMessage(messageObject, counter, doHaltExecution) {
            setTimeout(function(){
                if (haltExecution) {
                    return;
                }
                
                if (messageObject.type == 'error') {
                    messageObject.type = 'danger';
                }
                
                $logInfo.html(messageObject.message);
                if (messageObject.type == 'danger') {
                    $logErrors.prepend('<div class="alert alert-'+messageObject.type+'">'+messageObject.message+'</div>');
                }
                // rowCount--;
                
                if (messageObject.counter && (messageObject.type == 'success' || messageObject.type == 'info')) {
                    exportSuccessCount++;
                } else if (messageObject.counter && messageObject.type == 'danger') {
                    exportErrorCount++;
                }

                exportCount = exportSuccessCount + exportErrorCount;
                $exportTotalProcessed.html(exportCount);
                $exportSuccessCount.html(exportSuccessCount);
                $exportErrorCount.html(exportErrorCount);
                
                if (messageObject.counter && recordsCount > 0) {
                    recordsIteration++;
                    percentage = Math.floor((recordsIteration / recordsCount) * 100);
                    $progressBar.width(percentage + '%');
                    $progressBarSr.html(percentage + '%');
                    $exportPercentage.html(percentage + '%');
                    $exportTotal.html(recordsCount);
                }
                
                haltExecution = (doHaltExecution === true ? true : false);
            }, counter * timeout);    
        }
        
        function sendRequest(attributes) {
            if (haltExecution) {
                return;
            }
            attributes = attributes || $('#csv-export').data('attributes');
            
            var sendData = {'ListSegmentCsvExport': {}};
            for (i in attributes) {
                sendData['ListSegmentCsvExport'][i] = attributes[i];
            }
            
            if ($('meta[name=csrf-token-name]').length && $('meta[name=csrf-token-value]').length){
                var csrfTokenName = $('meta[name=csrf-token-name]').attr('content'),
                    csrfTokenValue = $('meta[name=csrf-token-value]').attr('content');
                sendData[csrfTokenName] = csrfTokenValue;
            }
            
            $.ajax({
                url: '',
                data: sendData,
                type: 'POST',
                dataType: 'json'
            }).done(function(json){
                if (json.result == 'error') {
                    doQueueMessage({type:'error', message: json.message, counter: false}, 1, true);
                } else if (json.result == 'success'){
                    if (json.attributes) {
                        setTimeout(function(){
                            sendRequest(json.attributes);
                        }, pause);
                    }
                    
                    if (json.recordsCount && recordsCount == -1) {
                        recordsCount = json.recordsCount;
                    }
                    
                    if (json.export_log) {
                        for (i in json.export_log) {
                            rowCount++;
                            doQueueMessage(json.export_log[i], rowCount);
                        }
                    }

                    rowCount++;
                    doQueueMessage({type:'success', message: json.message, counter: false}, rowCount);
                    
                    if (json.download) {
                        setTimeout(function(){
                            window.location.href = json.download;
                        }, rowCount * timeout);
                    }
                }
            }).fail(function(jqXHR){
                if (jqXHR.statusText == 'error') {
                    jqXHR.statusText = 'Error, aborting the export process!'
                }
                doQueueMessage({type:'error', message: jqXHR.statusText, counter: false}, 1, true);
            });
        }
        sendRequest();
        
        // fake iframe to avoid cookie expiration.
        setInterval(function() {
            var iframe = $('<iframe/>', {
                src: $('#list-import-log-container').data('iframe'), 
                width: 1, 
                height: 1
            }).css({display:'none'});
            $('body').append(iframe);
            setTimeout(function(){
                iframe.remove();
            }, 1000 * 60 * 2);
        }, 1000 * 60 * 20);
    })();
    
    // ping page from within iframe
    (function(){
        if (!$('#ping').length || !window.opener) {
            return;
        }
        if ($('meta[name=csrf-token-name]').length && $('meta[name=csrf-token-value]').length){
            var csrfTokenName = $('meta[name=csrf-token-name]').attr('content'),
            csrfTokenValue = $('meta[name=csrf-token-value]').attr('content');
            
            window.opener.$('meta[name=csrf-token-name]').attr('content', csrfTokenName);
            window.opener.$('meta[name=csrf-token-value]').attr('content', csrfTokenValue);    
        }
    })();
});