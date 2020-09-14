$( document ).ready( function() {


	window.onload = function(){
		
		 $(".multi-select").multiselect({
            show:['blind',250],
            hide:['blind',250],
            selectedList: 1
        });
 

		$('#searchfield-jqgridcontainer').find('option').each(function(){

			if( $(this).text() == 'First Name' ){

				$(this).val('u.firstname');

			}
			else if( $(this).text() == 'Last Name' ){

				$(this).val('u.lastname');

			}
			else if( $(this).text() == 'Department' ){

				$(this).val('ucd.department');

			}
			else if( $(this).text() == 'Division' ){

				$(this).val('ucdv.division');

			}
			else if( $(this).text() == 'Company' ){

				$(this).val('uc.company');

			}

		});

	}

	$('#company_list').live('change',function(){
        var items = {0 : "Select", 1 : "Company",2 : "Division",3 : "Department",4 : "Employee"};
        var category_id = $(this).val();
        var category = items[category_id];

        var eleid = category.toLowerCase()

        if (category_id > 0){
            $.ajax({
                url: module.get_value('base_url') + module.get_value('module_link') + '/get_retireable_employee_filter',
                data: 'category_id=' + category_id,
                dataType: 'html',
                type: 'post',
                async: false,
                beforeSend: function(){
                
                },                              
                success: function ( response ) {
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
        }
        else{
            $('#multi-select-main-container').hide();
            $('#category_selected').empty();
            $('#multi-select-container').empty();          
        }   

         var company_id = $(this).val();

        if (company_id > 0){
            $.ajax({
                url: module.get_value('base_url') + module.get_value('module_link') + '/get_department_list',
                data: 'company_id=' + company_id,
                dataType: 'html',
                type: 'post',
                async: false,
                beforeSend: function(){
                
                },                              
                success: function ( response ) {
                    $('#multi-select-main-container').show();
                    $('#department_selected').html('Department');
                    $('#multi-select-container').html(response);
                    $('#department').multiselect().multiselectfilter({
                        show:['blind',250],
                        hide:['blind',250],
                        selectedList: 1
                    });
                }
            });
        }
        else{
            $('#multi-select-main-container').hide();
            $('#category_selected').empty();
            $('#multi-select-container').empty();          
        }   
    }); 
	
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

	$('#export-form').attr('action', $('#export_link').val());
	$('#export-form').submit();
	$('#export-form').attr('action', '');
	
	return false;
}

function generate_list(){
	
	ok_to_save = validate();

	if( ok_to_save ){
		$('#export-form').hasClass('export-search');
		list_search_grid( 'jqgridcontainer' );
		$('#export-form').removeClass('export-search');
		return false;
	}

	
}

function validate(){

	return true;

}

function list_search_grid( jqgridcontainer ){

	$("#jqgridcontainer").jqGrid('destroyGroupHeader');
	$("#jqgridcontainer").jqGrid('GridUnload');

	recreate_manpower_count_grid();

}

function recreate_manpower_count_grid(){

var colname = ["Department", "Regular", "Probationary", "Consultant", "Project Based",
            "Contractual (Direct Hired)","Contractual (Agency Hired)","OJT","Regular", "Probationary", "Consultant", "Project Based",
            "Contractual (Direct Hired)","Contractual (Agency Hired)","OJT","Regular", "Probationary", "Consultant", "Project Based",
            "Contractual (Direct Hired)","Contractual (Agency Hired)","OJT","Total"];

var colmodel = [{name : 'department', sortable : false},
				{name : 'off_regular', sortable : false},
				{name : 'off_probationary', sortable : false},
				{name : 'off_consultant', sortable : false},
				{name : 'off_project', sortable : false},
				{name : 'off_contractual_direct', sortable : false},
				{name : 'off_contractual_agency', sortable : false},
                {name : 'off_ojt', sortable : false},
				{name : 'sup_regular', sortable : false},
				{name : 'sup_probationary', sortable : false},
				{name : 'sup_consultant', sortable : false},
				{name : 'sup_project', sortable : false},
				{name : 'sup_contractual_direct', sortable : false},
				{name : 'sup_contractual_agency', sortable : false},
                {name : 'sup_ojt', sortable : false},
				{name : 'rank_regular', sortable : false},
				{name : 'rank_probationary', sortable : false},
				{name : 'rank_consultant', sortable : false},
				{name : 'rank_project', sortable : false},
				{name : 'rank_contractual_direct', sortable : false},
				{name : 'rank_contractual_agency', sortable : false},
                {name : 'rank_ojt', sortable : false},
				{name : 'total', sortable : false}] ;


var force_to_fit = true;

$("#jqgridcontainer").jqGrid({
        url: module.get_value('base_url') + module.get_value('module_link') + '/listview',
        loadtext: '<img src="'+ module.get_value('base_url') + user.get_value('user_theme') + '/images/loading.gif"><br />Loading...',
        datatype: "json",
        mtype: "POST",
        postData: {
			company : $('#company_list').val(),
			department : $('#department').val(),
			date_asof : $('#date_asof').val()
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
        forceFit: false,
        shrinkToFit: false,
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


	$("#jqgridcontainer").jqGrid('setGroupHeaders', {
              useColSpanStyle: false, 
              groupHeaders:[
                            {startColumnName: 'off_regular', numberOfColumns: 7, titleText: '<span style="font-weight:bold;">OFFICER</span>'},
                            {startColumnName: 'sup_regular', numberOfColumns: 7, titleText: '<span style="font-weight:bold;">SUPERVISOR</span>'},
                            {startColumnName: 'rank_regular', numberOfColumns: 7, titleText: '<span style="font-weight:bold;">RANK AND FILE</span>'}
                          ]
     }).trigger("reloadGrid");

}

