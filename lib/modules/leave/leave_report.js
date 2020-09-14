$( document ).ready( function() {


	window.onload = function(){
		$(".multi-select").multiselect({
			show:['blind',250],
			hide:['blind',250],
			selectedList: 1
		});
	}
	
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

function export_list(){

	if( ( $('#date_start').val() == "" && $('#date_end').val() != "" ) ||  ( $('#date_start').val() != "" && $('#date_end').val() == "" )  ){
		$('#message-container').html(message_growl('error', 'Invalid Date Period'));
		return false;
	}

	var sortColumnName = $("#jqgridcontainer").jqGrid('getGridParam','sortname');
    var sortOrder = $("#jqgridcontainer").jqGrid('getGridParam','sortorder');
    if (sortColumnName != ''){
        $('#previous_page').append('<input id="sidx" type="hidden" value="'+ sortColumnName +'" name="sidx"><input id="sord" type="hidden" value="'+ sortOrder +'" name="sord">');
    }

	$('#export-form').attr('action', $('#export_link').val());
	$('#export-form').submit();
	$('#export-form').attr('action', '');
	return false;
}

function generate_list(){

	if( ( $('#date_start').val() == "" && $('#date_end').val() != "" ) ||  ( $('#date_start').val() != "" && $('#date_end').val() == "" )  ){

		$('#message-container').html(message_growl('error', 'Invalid Date Period'));
		return false;

	}

	$('#export-form').hasClass('export-search');
	list_search_grid( 'jqgridcontainer' );
	$('#export-form').removeClass('export-search');
	return false;

}

function list_search_grid( jqgridcontainer ){
	$("#jqgridcontainer").jqGrid('clearGridData', {	clearfooter: true});
	$("#"+jqgridcontainer).jqGrid('setGridParam', 
	{
		postData: null
	});

	$("#"+jqgridcontainer).jqGrid('setGridParam', 
	{
		search: true,
		postData: {
			leaveType : $('#leave_type').val(),
			leaveStatus : $('#leave_status').val(),
			dateStart : $('#date_start').val(),
			dateEnd : $('#date_end').val(),
		}, 	
	}).trigger("reloadGrid");
}