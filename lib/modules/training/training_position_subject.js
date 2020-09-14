$( document ).ready( function() {

	if( module.get_value('view') == 'edit' ){

		window.onload = function(){

			$('label[for="position_title"]').parents('.col-2-form').addClass('view');

		}

	}

});