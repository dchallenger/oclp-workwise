var error = new Array();
var error_ctr = 0;
var colNames = ["PISC","January", "February", "March", "April","May","June","July","August","September","October","November","December"];
var colModel = [
    {name: 'PISC', sortable: false, width: 350, align: 'left'},
    {name: 'January', sortable: false, width: 100},
    {name: 'February', sortable: false, width: 100},
    {name: 'March', sortable: false, width: 100},
    {name: 'April', sortable: false, width: 100},
    {name: 'May', sortable: false, width: 100},
    {name: 'June', sortable: false, width: 100},
    {name: 'July', sortable: false, width: 100},
    {name: 'August', sortable: false, width: 100},
    {name: 'September', sortable: false, width: 100},
    {name: 'October', sortable: false, width: 100},
    {name: 'November', sortable: false, width: 100},
    {name: 'December', sortable: false, width: 100}
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
	            year: year,
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