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

	get_period_date	();
	
	$('#category').live('change',function(){
		var category_id = $(this).val();
		var category = '';
		if (category_id == 1){
			category = 'Company';	
		}
		else if (category_id == 2){
			category = 'Division';
		}
		else if (category_id == 3){
			category = 'Department';
		}
		else if (category_id == 4){
			category = 'Employee';
		}	
		var eleid = category.toLowerCase()

		if (category_id > 0){
			$.ajax({
				url: module.get_value('base_url') + module.get_value('module_link') + '/get_employee_time_record',
				data: 'category_id=' + category_id,
				dataType: 'html',
				type: 'post',
				async: false,
				beforeSend: function(){
					$('#multi-select-main-container').hide();
					$('#multi-select-loader').html('<div><img src="'+module.get_value('base_url')+user.get_value('user_theme')+'/images/loading.gif" style="vertical-align:middle"><span style="padding-left:10px">Loading, please wait...</span></div>');				
				},								
				success: function ( response ) {
					$('#multi-select-loader').html('');
					$('#multi-select-main-container').show();
					$('#category_selected').html(category + ':');
					$('#multi-select-container').html(response);
					$('#'+eleid).multiselect().multiselectfilter({
						show:['blind',250],
						hide:['blind',250],
						selectedList: 1
					});
				}
			});

			$('#employment_status_container').show();
			$('#employee_type_container').show();
		}
		else{
			$('#multi-select-main-container').hide();
			$('#category_selected').html('');
			$('#multi-select-container').html('');			
		}	
	});

	$('select[id="period_year"]').live('change',function(){
		get_period_date	();
	});
});

function get_period_date(){
	$.ajax({
		url: module.get_value('base_url') + module.get_value('module_link') + '/get_period_date',
		data: 'period_year=' + $('#period_year').val(),
		dataType: 'html',
		type: 'post',
		async: false,
		beforeSend: function(){
		
		},								
		success: function ( response ) {
			$('#period_date_container').show();
			$('#period_date').html(response);
		}
	});		
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

function export_list(){
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
	$("#"+jqgridcontainer).jqGrid('setGridParam', { postData: null });

	$("#"+jqgridcontainer).jqGrid('setGridParam', 
	{
		search: true,
		postData: {
			category : $('#category').val(),
			department : $('#department').val(),
			company : $('#company').val(),
			division : $('#division').val(),
			employee : $('#employee').val(),
			employment_status : $('#employment_status').val(),
			employee_type : $('#employee_type').val(),
			period_year : $('#period_year').val(),
			period_date : $('#period_date').val(),
		}, 	
	}).trigger("reloadGrid");

}