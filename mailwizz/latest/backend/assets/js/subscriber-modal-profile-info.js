/**
 * This file is part of the MailWizz EMA application.
 *
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.5.3
 */
jQuery(document).ready(function($){

    var ajaxData = {};
    if ($('meta[name=csrf-token-name]').length && $('meta[name=csrf-token-value]').length) {
        var csrfTokenName = $('meta[name=csrf-token-name]').attr('content');
        var csrfTokenValue = $('meta[name=csrf-token-value]').attr('content');
        ajaxData[csrfTokenName] = csrfTokenValue;
    }
    
    var $modal = $('#subscriber-modal-profile-info');
    $modal.on('hide.bs.modal', function(){
        $modal.find('.modal-body-loader').show();
        $modal.find('.modal-body-content').html('').hide();
        $modal.data('url', '');
    }).on('shown.bs.modal', function(){
        var url = $modal.data('url');
        if (!url) {
            return false;
        }
        $.get(url, {}, function(html){
            $modal.find('.modal-body-loader').hide();
            $modal.find('.modal-body-content').html(html).show();
        }, 'html');
    });
    
    $(document).on('click', 'a.btn-subscriber-profile-info', function(){
        $modal.data('url', $(this).attr('href'));
        $modal.modal('show');
        return false;
    });
});