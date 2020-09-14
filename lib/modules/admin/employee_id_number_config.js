$( document ).ready( function() {
	$('.icon-delete').live('click',function(){
		var elem = $(this);
		Boxy.ask("Delete this item?", ["Yes", "Cancel"],
		function( choice ) {
			if(choice == "Yes"){
				$(elem).closest('.header-container').remove();
			}
		},
		{
			title: "Delete Item"
		});		
	});
});

function validate_form()
{	
	return true;
}

function add_id_number_type(){
	$('#id_number_config_container').append($('#employee_id_number_config_container').html());
}