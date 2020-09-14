// function generate_list(){

// 	if( ( $('#date_start').val() == "" ) || ( $('#date_end').val() == "" ) ){
// 		add_error('date', 'Date Period', "This field is mandatory.");
// 	}
// 	ok_to_save = validate_form();

// 	if( ok_to_save ){
// 		$('#export-form').hasClass('export-search');
// 		list_search_grid( 'jqgridcontainer' );
// 		$('#export-form').removeClass('export-search');
// 		return false;
// 	}
// }
$(document).ready(function(){
	init_datepick();
});

function export_list(){

	$('#export-form').attr('action', $('#export_link').val());
	$('#export-form').submit();
	$('#export-form').attr('action', '');
	
	return false;
}

function validate_form()
{
		//errors
	if(error.length > 0){
		var error_str = "Please correct the following errors:<br/><br/>";
		for(var i in error){
			if(i == 0) $('#'+error[i][0]).focus(); //set focus on the first error
			error_str = error_str + (parseFloat(i)+1) +'. '+error[i][1]+" - "+error[i][2]+"<br/>";
		}
		$('#message-container').html(message_growl('error', error_str));
			
		//reset errors
		error = new Array();
		error_ctr = 0
		return false;
	}
	
	//no error occurred
	return true;
}

function generate_list()
{
	if( ( $('#date_start').val() == "" ) || ( $('#date_end').val() == "" ) ){
		add_error('date', 'Date Period', "This field is mandatory.");
	}
	ok_to_save = validate_form();

	if( ok_to_save ){
		$('#export-form').hasClass('export-search');
		list_search_grid( 'jqgridcontainer' );
		$('#export-form').removeClass('export-search');
		return false;
	}
}

function list_search_grid( jqgridcontainer ){

	$("#"+jqgridcontainer).jqGrid('setGridParam', 
	{
		//search: true,
		postData: {
			campaign : $('#campaign').val(),
			date_period_start : $('#date_start').val(),
			date_period_end : $('#date_end').val(),
		}, 	
	}).trigger("reloadGrid");

}