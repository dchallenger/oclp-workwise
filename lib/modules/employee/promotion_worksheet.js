$(document).ready(function(){
	init_datepick();
});


function export_list(){
	if($('#date_year').val() == ""){
        add_error('date', 'Date Period', "This field is mandatory.");
    }

    ok_to_save = validate_form();

    if(ok_to_save){
		$('#export-form').attr('action', $('#export_link').val());
		// $('#export-form').submit();
		var data = $('#date_year, #company, #employee_type').serialize();
		var url = module.get_value('base_url')+module.get_value('module_link')+"/promotion_export";

		$.ajax({
			url: url,
	        data: data,
	        dataType: 'json',
	        type: 'post',
	        beforeSend: function(){
				$.blockUI({ message: '<div class="now-loading align-center"><img src="'+module.get_value('base_url')+user.get_value('user_theme')+'/images/loading.gif"><br />Loading, please wait...</div>'});
			},		
	        success: function (response) {
	            var path = "/"+response.data;
                window.location = module.get_value('base_url')+path;
                $.unblockUI();
	        }
		});

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
	if($('#date_year').val() == ""){
		add_error('date', 'Date Year', "This field is mandatory.");
	}
	ok_to_save = validate_form();

	if( ok_to_save ){
		$('#export-form').hasClass('export-search');
		list_search_grid('jqgridcontainer');
		$('#export-form').removeClass('export-search');
		return false;
	}
}

function list_search_grid( jqgridcontainer )
{
	var colname = ['Incumbent','Position Title As of December 31, '+$('#date_year').val(),'Rank Code','Rank','Range of Ranks','Date Hired','Date of Last Promotion'];
	var colmodel = [{name : 'Incumbent'},{name : 'Position Title As of December 31, '+$('#date_year').val(), width: 250},{name : 'Rank Code'},{name : 'Rank'},{name : 'Range of Ranks'},{name : 'Date Hired'},{name : 'Date of Last Promotion'}];

	$("#jqgridcontainer").jqGrid('GridUnload');

	$("#jqgridcontainer").jqGrid({
        url: module.get_value('base_url') + module.get_value('module_link') + '/listview',
        loadtext: '<img src="'+ module.get_value('base_url') + user.get_value('user_theme') + '/images/loading.gif"><br />Loading...',
        datatype: "json",
        mtype: "POST",
        postData: {
			date_year : $('#date_year').val(),
			company : $('#company').val(),
			employee_type : $('#employee_type').val()
		},
        rowNum: 30,
        rowList: [10,15,25, 30, 60, 85, 100],
        toolbar: [true,"top"],
        height: 'auto',
        autowidth: true,
        pager: "#jqgridpager",
        pagerpos: 'right',
        toppager: true,
        viewrecords: true,
        altRows: true,
        loadonce: false,
        colNames:colname,
        colModel:colmodel,
        loadComplete: function(data){
            if (data.msg_type != 'success') {
                $('#message-container').html(message_growl(data.msg_type, data.msg));
            }
        },
        gridComplete:function(){
        },
        caption: " List"    
    });

	// $("#jqgridcontainer").jqGrid("setLabel", 'newLabel', 0).trigger("reloadGrid");

	// $("#"+jqgridcontainer).jqGrid('setGridParam', 
	// {
	// 	//search: true,
	// 	postData: {
	// 		company : $('#company').val(),
	// 		date_year : $('#date_year').val(),
	// 		employee_type : $('#employee_type').val(),
	// 	}, 	
	// }).trigger("reloadGrid");
}