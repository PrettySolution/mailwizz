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
	
	$('.icon-item a i').on('click', function(){
        $iconWrap.closest('.input-group-addon').show();
	    $('#StartPage_icon').closest('div').find('span a.icon-wrap i').removeAttr('class').addClass($(this).attr('class'));
        $('#StartPage_icon').val($(this).closest('a').data('icon'));
        $('#StartPage_search_icon').val('');
        $('.icon-item').show();
        return false;
    });
	
	$('#StartPage_search_icon').on('keyup', function(){
	    var val = $(this).val();
	    if (!val) {
            $('.icon-item').show();
            return;
        }

        $('.icon-item').each(function(){
            if ($('a', this).data('icon').indexOf(val) == -1) {
                $(this).hide();
            }
        });
    });

	var $iconWrap   = $('#StartPage_icon').closest('div').find('a.icon-wrap');
	var $selectIcon = $('#StartPage_icon').closest('div').find('a.btn-select-color');
    var $resetIcon  = $('#StartPage_icon').closest('div').find('a.btn-reset-color');
    var $removeIcon = $('#StartPage_icon').closest('div').find('a.btn-remove-icon');

    $selectIcon.colorpicker({
        format: 'hex'
    }).on('changeColor', function(e) {
        $iconWrap.css({
            color: e.color.toString('hex')
        });
        $('#StartPage_icon_color').val( e.color.toString('hex').replace('#', '') );
    });

    $resetIcon.on('click', function(){
        $('#StartPage_icon_color').val('');
        $iconWrap.removeAttr('style');
        return false;
    });

    $removeIcon.on('click', function(){
        $iconWrap.closest('.input-group-addon').hide();
        $resetIcon.trigger('click');
        $('#StartPage_icon').val('');
    });
});