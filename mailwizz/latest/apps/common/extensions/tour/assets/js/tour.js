jQuery(document).ready(function($){

    ajaxData = {};
    if ($('meta[name=csrf-token-name]').length && $('meta[name=csrf-token-value]').length) {
        var csrfTokenName = $('meta[name=csrf-token-name]').attr('content');
        var csrfTokenValue = $('meta[name=csrf-token-value]').attr('content');
        ajaxData[csrfTokenName] = csrfTokenValue;
    }
    
    $('.tour-clear-image').on('click', function(){
        var def = $(this).data('default');
        if (!def) {
            return false;
        }
        $(this).closest('div').find('img:first').attr('src', def);
        $(this).closest('div').parent('div').find('input[type=hidden]').val('');
        return false;
    });

    $(window).load(function(){
        if ($('#tour .flexslider').length) {
            $('#tour .modal').on('shown.bs.modal', function(){
                $('.flexslider').flexslider({
                    animation: "slide",
                    smoothHeight: true,
                    slideshow: false,
                    start: function(slider){},
                    after: function(slider){}
                });
            });
            $('#tour .modal').modal('show');
            
            $('#skip-the-tour').on('click', function(){
                if (!confirm($(this).data('message'))) {
                    return false;
                }
                var data = $.extend({}, ajaxData, $(this).data());
                $.post($(this).data('url'), data);
                $('#tour .modal').modal('hide');
                return false; 
            });
        }
    });
    
});
