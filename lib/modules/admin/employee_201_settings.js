$( document ).ready( function() {
	
	init_datepick();

	$("#date_start").change(function(){
		$('#date_from').val($('#date_start').val());
	});

	$("#date_end").change(function(){
		$('#date_to').val($('#date_end').val());
	});

});

function validate_form()
{	
	return true;
}