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
    
    // IMPORTER
    function importer($elem) {
        if (!$elem.length) {
            return;
        }
    
        var rowCount = 0,
            timeout = 1, 
            haltExecution = false, 
            importSuccessCount = 0, 
            importErrorCount = 0, 
            importCount = 0, 
            recordsCount = -1,
            recordsIteration = 0,
            percentage = 0;
        
        var $importSuccessCount = $elem.find('.counters .success'), 
            $importErrorCount = $elem.find('.counters .error'), 
            $importTotalProcessed = $elem.find('.counters .total-processed'),
            $importTotal = $elem.find('.counters .total'),
            $importPercentage = $elem.find('.counters .percentage'),
            $logInfo = $elem.find('.log-info'),
            $logErrors = $elem.find('.log-errors'),
            $progress = $elem.find('.progress').eq(0), 
            $progressBar = $progress.find('.progress-bar'),
            $progressBarSr = $progressBar.find('.sr-only'),
            pause = $elem.data('pause') * 1000;
        
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
                rowCount--;
                
                if (messageObject.counter) {
                    
                    if (messageObject.type == 'success' || messageObject.type == 'info') {
                        importSuccessCount++;
                    } else if (messageObject.counter && messageObject.type == 'danger') {
                        importErrorCount++;
                    }
                    
                    importCount = importSuccessCount + importErrorCount;
                    $importTotalProcessed.html(importCount);
                    $importSuccessCount.html(importSuccessCount);
                    $importErrorCount.html(importErrorCount);
                    
                    recordsIteration++;
                    percentage = Math.floor((recordsIteration / recordsCount) * 100);
                    $progressBar.width(percentage + '%');
                    $progressBarSr.html(percentage + '%');
                    $importPercentage.html(percentage + '%');
                    $importTotal.html(recordsCount);    
                }

                haltExecution = (doHaltExecution === true ? true : false);
            }, counter * timeout);    
        }
        
        function sendRequest(attributes) {
            if (haltExecution) {
                return;
            }
            attributes = attributes || $elem.data('attributes');
            
            var modelName = $elem.data('model');
            var sendData = {};
            sendData[modelName] = {};
            
            for (i in attributes) {
                sendData[modelName][i] = attributes[i];
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
                    
                    if (json.import_log) {
                        for (i in json.import_log) {
                            rowCount++;
                            doQueueMessage(json.import_log[i], rowCount);
                        }
                    }

                    rowCount++;
                    doQueueMessage({type:'success', message: json.message, counter: false}, rowCount);
                }
            }).fail(function(jqXHR){
                if (jqXHR.statusText == 'error') {
                    jqXHR.statusText = 'Error, aborting the import process!'
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
    };
    
    // START IT
    importer($('#csv-import'));
    importer($('#database-import'));
    importer($('#text-import'));

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
    
    if ($('#database-import-modal .has-help-text').length) {
        $('#database-import-modal .has-help-text').on('shown.bs.popover', function(){
            $('.popover').css({zIndex: 99999});
        });
    }
});