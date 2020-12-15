jQuery(document).ready(function($){
    
    var searchXHR = false;
    $('form#search-modal-form').on('submit', function(){
        var term = $('#search-term').val();
        if (term.length < 3 || term.length > 100) {
            return false;
        }
        
        if (searchXHR && (searchXHR.readyState > 0 && searchXHR.readyState < 4)) {
            searchXHR.abort();
            searchXHR = false;
        }

        var $this = $(this);
        $('.search-input').addClass('loading');

        searchXHR = $.get($this.attr('action'), $this.serialize(), function(html){
            $('.search-input').removeClass('loading');
            $('#search-results-wrapper').html(html);
        }, 'html');
        
        return false;
        
    });
    
    $('#search-modal').on('shown.bs.modal', function(){
       setTimeout(function(){
           $('#search-term').focus();
       }, 100);
    });

    $('#search-term').on('keyup', function(){
        if ($(this).val().length == 0) {
            $('#search-results-wrapper').empty();
        }
    });
});
