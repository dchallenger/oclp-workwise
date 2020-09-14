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

	$('.export-billable').live('click', function(){
		url = module.get_value('base_url') + $(this).attr('module_link') + '/export/' + $(this).parent().parent().parent().attr("id");
		window.location = url;
	});

	$('.icon-16-disk-back').parent().remove();

	
	// $('.icon-16-tick-button').live('click', function(){

	// 	// $.ajax({
	// 	// 	url: module.get_value('base_url') +'employee/billable_hours_calls/close_form',
	// 	// 	data: $(this).parent().parent().parent().attr("id"),
	// 	// 	dataType: 'json'
	// 	// 	type: 'post',
	// 	// 	success: function(response)	{
				
	// 	// 	}
	// 	// });
	// });
});

function closeform(id)
{
	// var id = 'id='+$(this).parent().parent().parent().attr("id");
	$.post(module.get_value('base_url') + 'dtr/billable_hours_calls/close_form', {id: id}).done( function (){ $('#jqgridcontainer').trigger('reloadGrid') } );
}

// function export_list(){

// 	$('#export-form').attr('action', $('#export_link').val());
// 	$('#export-form').submit();
// 	$('#export-form').attr('action', '');
	
// 	return false;
// }

// function export_list() {
// 	$('#export-form').attr('action', $('#export_link').val());
// 	$('#export-form').submit();
// 	console.log($(this).val());
// 	var option = "";

// 	$.ajax({
// 		url: module.get_value('base_url') +'employee/billable_hours_calls/export',
// 		type: 'post',
// 		beforeSend: function(){
// 			$.blockUI({ message: '<div class="now-loading align-center"><img src="'+module.get_value('base_url')+user.get_value('user_theme')+'/images/loading.gif"><br />Loading Employees, please wait...</div>'});
// 		},		
// 		success: function(response)	{
// 			$.unblockUI();

// 			Boxy.ask("<label class='label-desc gray' for='employee_id'>Employee:</label><div class='select-input-wrap' style='margin-top:5px'><select style='margin-top:20px' name='employee_export' class='chzn-select' id='employee_export' style='width:75%'>"+response.data+"</select></div>", ["Export", "Cancel"],function( choice ) {
// 			if(choice == "Export"){
// 					var data = $('#employee_export').serialize();
// 					$.ajax({
// 						url: module.get_value('base_url') +'employee/movement/export_list',
// 				        data: data,
// 				        dataType: 'json',
// 				        type: 'post',
// 				        success: function (response) {
// 				            var path = "/"+response.data;
// 			                window.location = module.get_value('base_url')+path;
// 				        }
// 					});
					
// 			    }
// 			},
// 			{
// 			    title: "Individual Movement History"
// 			});
// 			$(".chzn-select").chosen();
// 		}
// 	});

// 	$('#export-form').attr('action', '');
// 	return false;
// }