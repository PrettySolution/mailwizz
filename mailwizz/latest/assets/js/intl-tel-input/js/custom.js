/**
 * This file is part of the MailWizz EMA application.
 *
 * @package MailWizz EMA
 * @author Serban George Cristian <cristian.serban@mailwizz.com>
 * @link https://www.mailwizz.com/
 * @copyright MailWizz EMA (https://www.mailwizz.com)
 * @license https://www.mailwizz.com/license/
 * @since 1.7.8
 */
jQuery(document).ready(function($){

    $(".field-type-phonenumber").each(function() {

        var errorMap = window.fieldTypePhoneNumberErrorMap;
        var options  = $.extend({}, window.fieldTypePhoneNumberOptions);

        options.hiddenInput = $(this).attr("name");
        $(this).intlTelInput(options);

        var $input = $(this);
        var $error = $input.closest('.form-group').find('.js-error-message');
        var $label = $input.closest('.form-group').find('label');

        /* Library instance */
        var iti = $(this).data("plugin_intlTelInput");

        /* Reset function */
        var reset = function() {
            $input.removeClass("error");
            $label.removeClass("error");
            $error.html('');
            $error.hide();
        };

        $input.on('blur', reset);
        $input.on('change', reset);
        $input.on('keyup', reset);

        // on blur: validate
        $input.on('blur', function() {
            if (!$(this).val().trim()) {
                return;
            }

            if (iti.isValidNumber()) {
                reset();
            } else {
                $input.addClass('error');
                $label.addClass('error');
                var errorCode = iti.getValidationError();
                $error.html(errorMap[errorCode]);
                $error.show();
            }
        });
    });

    $('.field-type-phonenumber:first').closest('form').on('submit', function() {
        return $('.js-error-message:visible').length === 0;
    });
});
