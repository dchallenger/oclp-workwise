var error = new Array();
var error_ctr = 0;
var colNames = ["Division/Department","Employment Status","Position","MRF No.","Existing Job","Additional Headcount Existing job","Additional Headcount Newly Created job","Status","Date of Request Approved","Date of Placement","Contract Date","Target","Actual","Source"];
var colModel = [
    {name: 'division_department', sortable: false, width: 350, align: 'left'},
    {name: 'employment_status', sortable: false, width: 150},
    {name: 'position', sortable: false, width: 100},
    {name: 'mrf_no', sortable: false, width: 100},
    {name: 'existing_job', sortable: false, width: 100},
    {name: 'additional_headcount_ej', sortable: false, width: 200},
    {name: 'additional_headcount_ncj', sortable: false, width: 200},
    {name: 'Status', sortable: false, width: 100},
    {name: 'date_request_approved', sortable: false, width: 200},
    {name: 'date_placement', sortable: false, width: 200},
    {name: 'contract_date', sortable: false, width: 100},
    {name: 'tat_target', sortable: false, width: 100},
    {name: 'tat_actual', sortable: false, width: 100},
    {name: 'source', sortable: false, width: 100},
];


$(document).ready(function(){

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
	if( $('select[name="date_year"]').val() == "" ){
		add_error('date_year', 'Year', "This field is mandatory.");
	}

	ok_to_save = validate_form();

	if( ok_to_save ){
		 var year = $('select[name="date_year"]').val();
		 var month = $('select[name="date_month"]').val();

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
	        shrinkToFit: false,
	        treeGrid: false,
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
	            year: year,
	            month: month
	        }, 
	        rowNum: 9999,
	        //loadComplete: expand_all,
	        gridComplete: function(){ },
	    });
	}
    $("#jqgridcontainer").jqGrid('setGroupHeaders', {
          useColSpanStyle: false, 
          groupHeaders:[
            {startColumnName: 'existing_job', numberOfColumns: 3, titleText: '<span style="font-weight:bold;">Nature of Request</span>'},
            {startColumnName: 'tat_target', numberOfColumns: 2, titleText: '<span style="font-weight:bold;">Nature of Request</span>'}
          ]
    }).trigger("reloadGrid");	
}

/*grid_resize('jqgridcontainer');

function gridResize_jqgridcontainer() {
    $("#jqgridcontainer").jqGrid("setGridWidth", $("#body-content-wrap").width() );
}

$(window).resize(gridResize_jqgridcontainer);*/