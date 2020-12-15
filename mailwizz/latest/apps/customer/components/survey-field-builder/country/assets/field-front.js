jQuery(document).ready(function($){
    $('.country-field select').on('change', function(){
        var $this = $(this), $states = $this.closest('.country-field').next('div.state-field').find('select');
        if (!$states.length || !$this.val()) {
            return;
        }
        var selectedState = $states.data('selected');
        $.get($this.data('states-url'), {country: $this.val()}, function(states){
            $('option', $states).remove();
            if (!states) {
                return;
            }
            var options = [];
            for (var i in states) {
                var opt = '<option value="'+states[i]+'"';
                if (states[i] == selectedState) {
                    opt += ' selected="selected"';
                }
                opt += '>'+states[i]+'</option>';
                
                options.push(opt);
            }
            $states.append(options.join(""));
        }, 'json');
    }).trigger('change');
});