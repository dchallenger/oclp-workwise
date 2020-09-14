$( document ).ready( function() {


	window.onload = function(){
		$(".multi-select").multiselect().multiselectfilter({
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


	$("#jqgridcontainer").jqGrid({
		url: module.get_value('base_url') + module.get_value('module_link') + '/listview',
		loadtext: '<img src="'+ module.get_value('base_url') + user.get_value('user_theme') + '/images/loading.gif"><br />Loading...',
		datatype: "json",
		mtype: "POST",
		rowNum: 25,
		rowList: [10,15,25, 40, 60, 85, 100],
		toolbar: [true,"top"],
		height: 'auto',
		autowidth: true,
		pager: "#jqgridpager",
		pagerpos: 'right',
		toppager: true,
		viewrecords: true,
		altRows: true,
		forceFit: true,
		shrinkToFit: true,
		colNames:["Date and Time","Module","Field Name","Original Value","To What Value","Mode","User"],
		colModel:[{name : 'date_time',width : '180',align : 'center'},{name : 'module'},{name : 'field_name'},{name : 'original_value'},{name : 'to_what_value'},{name : 'mode'},{name : 'user'}],
		loadComplete: function(data){
			post_gridcomplete_function(data, '#jqgridcontainer');
		},
		gridComplete:function(){
		},
		caption: " List",
        ondblClickRow: function(rowid) {
        	return false;
        }  		
	}); 	
});


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

/*	if( ( $('#date_start').val() == "" ) || ( $('#date_end').val() == "" ) ){
		add_error('date', 'Date Period', "This field is mandatory.");
	}*/
	
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
		search: true,
		postData: {
			user : $('#user').val(),
			dateStart : $('#date_start').val(),
			dateEnd : $('#date_end').val(),
		}, 	
	}).trigger("reloadGrid");

}