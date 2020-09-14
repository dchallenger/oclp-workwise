var error = new Array();
var error_ctr = 0;
var colNames = ["Project", "Date Created", "Division Head", "Cost Code", "Date Changes","Version"];
var colModel = [
    {name: 'Project', sortable: false, width: 100, align: 'left'},
    {name: 'Date Created', sortable: false, width: 100},
    {name: 'Division Head', sortable: false, width: 100, align: 'left'},
    {name: 'Cost Code', sortable: false, width: 100},
    {name: 'Date Changes', sortable: false, width: 100},
    {name: 'Version', sortable: false, width: 100}
];


$(document).ready(function(){

});

function export_list(){

	if( $('select[name="control_code"]').val() == "" ){
		add_error('control_code', 'Control Code', "This field is mandatory.");
	}
	
	ok_to_save = validate_form();
	if( ok_to_save ){
		$('#export-form').attr('action', $('#export_link').val());
		$('#export-form').submit();
		$('#export-form').attr('action', '');
		
		return false;
	}
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
	if( $('select[name="control_code"]').val() == "" ){
		add_error('control_code', 'Control Code', "This field is mandatory.");
	}

	ok_to_save = validate_form();

	if( ok_to_save ){
		 var control_code = $('select[name="control_code"]').val();

		$("#jqgridcontainer").jqGrid('clearGridData', {	clearfooter: true});
		$("#jqgridcontainer").GridUnload();
		$("#jqgridcontainer").jqGrid({
	        url: module.get_value('base_url') + module.get_value('module_link') + '/listview',
	        loadtext: '<img src="'+ module.get_value('base_url') + user.get_value('user_theme') + '/images/loading.gif"><br />Loading...',
	        datatype: "json",
	        mtype: "POST",
	        height: 'auto',
	        autowidth: true,
	        altRows: true,
	        loadonce: true,
	        forceFit: true,
	        shrinkToFit: true,
	        treeGrid: true,
	        gridview: true,
	        treeGridModel: 'adjacency',
	        colNames:colNames,
	        colModel:colModel,
	        ExpandColumn : 'month',
	        pager: "#jqgridpager",
	        pagerpos: 'right',
	        viewrecords: true,
	       	toppager: true,
	       	postData: {
	            control_code: control_code,
	        }, 
	        rowNum: 9999,
	        //loadComplete: expand_all,
	        gridComplete: function(){ },
	    });
		return false;
	}
}

/*grid_resize('jqgridcontainer');

function gridResize_jqgridcontainer() {
    $("#jqgridcontainer").jqGrid("setGridWidth", $("#body-content-wrap").width() );
}

$(window).resize(gridResize_jqgridcontainer);*/