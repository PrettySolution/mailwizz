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

	// company start
	$('select#CustomerCompany_country_id').on('change', function() {
		var url = $(this).data('zones-by-country-url'), 
			countryId = $(this).val(),
			$zones = $('select#CustomerCompany_zone_id');
		
		if (url) {
			var formData = {
				country_id: countryId
			}

			$.get(url, formData, function(json){
				$zones.html('');
				if (typeof json.zones == 'object' && json.zones.length > 0) {
					for (var i in json.zones) {
						$zones.append($('<option/>').val(json.zones[i].zone_id).html(json.zones[i].name));
					}	
				}
			}, 'json');
			
		}
	});
	// company end
	
});