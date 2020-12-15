jQuery(document).ready(function($) {

    $('.state-field').each(function() {
        (function(el){
            var $this = $(el), $zone = $this.find('select'), $country = $this.prev('div.country-field').find('select');
            if (!$country.length || $country.val() || !$zone.data('selected')) {
                return false;
            }
            
            $.get($zone.data('url'), {zone: $zone.data('selected')}, function(data) {
                if (typeof data.country === 'object') {
                    $country.val(data.country.name).trigger('change');
                }
            }, 'json');
        })(this);
    });
});