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

    ajaxData = {};
    if ($('meta[name=csrf-token-name]').length && $('meta[name=csrf-token-value]').length) {
        var csrfTokenName = $('meta[name=csrf-token-name]').attr('content');
        var csrfTokenValue = $('meta[name=csrf-token-value]').attr('content');
        ajaxData[csrfTokenName] = csrfTokenValue;
    }

    // input/select/textarea fields help text
    $('.has-help-text').popover();
    $(document).on('blur', '.has-help-text', function(e) {
        if ($(this).data('bs.popover')) {
            // this really doesn't want to behave correct unless forced this way!
            $(this).data('bs.popover').destroy();
            $('.popover').remove();
            $(this).popover();
        }
    });
    
    (function(){
        var $lastClickedBtn = false;
        $(document).on('click', 'a, button, input[type="submit"], input[type="button"]', function(){
            $lastClickedBtn = $(this);
            if (!$lastClickedBtn.hasClass('btn') || $lastClickedBtn.hasClass('no-spin') || $lastClickedBtn.attr('target') === '_blank') {
                $lastClickedBtn = false;
                return true;
            }
            if (!$('i', $lastClickedBtn).length) {
                return true;
            }
        });
        $(window).on('beforeunload', function(){
            if ($lastClickedBtn) {
                $('i', $lastClickedBtn).removeAttr('class').addClass('fa fa-spinner fa-spin');
            }
        });
    })();

    $('a, span, i').tooltip();
    setTimeout(function(){
        $('.content-wrapper, .right-side').off();
        $('.content-wrapper, .right-side').css('min-height', '1000px');
    }, 50);

    if (typeof Cookies == 'function') {
        $(document).on('click', '.sidebar-toggle', function(){
            var sidebarStatus = $('body').hasClass('sidebar-collapse') ? 'closed' : 'open';
            Cookies.set('sidebar_status', sidebarStatus, { expires: 365 });
        });
    }
});
