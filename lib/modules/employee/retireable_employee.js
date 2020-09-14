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

	$('#category').live('change',function(){
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

	var years = $("#years").val();

	if( years.toString().search(/^-?[0-9]+$/) != 0 ){

		add_error('years', 'No. of Years', "No. of years must be an integer");

	}

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

	return true;


}

function list_search_grid( jqgridcontainer ){

	$("#jqgridcontainer").jqGrid('destroyGroupHeader');
	$("#jqgridcontainer").jqGrid('GridUnload');

	recreate_retire_employee_grid();

}

function recreate_retire_employee_grid( years ){

var colname = ["Full Name","Birthdate","Date Hired","Age","Tenure"];
var colmodel = [{name : 'fullname'},{name : 'birthdate'},{name : 'datehired'},{name : 'age'},{name : 'tenure'}] ;
var years = $('#years').val();

var group_ctr = 2;
var force_to_fit = true;


if( years > 1 ){

	var year_ctr = 2;
	var a_ctr = 5;
	var i_ctr = 1;

	force_to_fit = false;

	while( year_ctr <= years ){

		colname[a_ctr] = "Age";
		colname[a_ctr+1] = "Tenure";

		colmodel[a_ctr] = { name : 'age'+year_ctr }
		colmodel[a_ctr+1] = { name : 'tenure'+year_ctr }

		year_ctr ++;
		a_ctr +=2;
		i_ctr++;

	}

}


$("#jqgridcontainer").jqGrid({
        url: module.get_value('base_url') + module.get_value('module_link') + '/listview',
        loadtext: '<img src="'+ module.get_value('base_url') + user.get_value('user_theme') + '/images/loading.gif"><br />Loading...',
        datatype: "json",
        mtype: "POST",
        postData: {
			employee : $('#employee').val(),
			company : $('#company').val(),
			department : $('#department').val(),
			division : $('#division').val(),
			years : $('#years').val(),
			retire_type : $('#retire_type').val(),
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
        forceFit: force_to_fit,
        shrinkToFit: force_to_fit,
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

	if( !($('#date_asof').val()) ){
		var today = new Date();
		var current_year = today.getFullYear();
		var current_date = today.getDate();
		var current_month = today.getMonth();
		var this_year = new Date(current_year,current_month,current_date);
		var this_year_date = this_year.getDate();
		var this_year_month = this_year.getMonth()+1;
		var this_year_year = this_year.getFullYear();
	}
	else{
		var this_year = new Date($('#date_asof').val());
		var this_year_date = this_year.getDate();
		var this_year_month = this_year.getMonth()+1;
		var this_year_year = this_year.getFullYear();
	}

	var i_ctr = 1;
	var group_header = [{startColumnName: 'age', numberOfColumns: 2, titleText: '<span style="font-weight:bold;">As of '+this_year_month+" - "+this_year_date+" - "+this_year_year+'</span>'}]

	while( group_ctr <= years ){

        if(!($('#date_asof').val())){

             var today = new Date();
		     var current_year = today.getFullYear();
		     var current_date = today.getDate();
		     var current_month = today.getMonth();
		     var this_year = new Date(current_year+i_ctr,current_month,current_date);

        }
        else{

             var chosen_date = new Date($('#date_asof').val());
		     var chosen_date_date = chosen_date.getDate();
		     var chosen_date_month = chosen_date.getMonth();
		     var chosen_date_year = chosen_date.getFullYear();

		     var this_year = new Date(chosen_date_year+i_ctr,chosen_date_month,chosen_date_date);

        }

		

		var this_year_date = this_year.getDate();
		var this_year_month = this_year.getMonth()+1;
		var this_year_year = this_year.getFullYear();

		group_header[i_ctr] =  {startColumnName: 'age'+group_ctr, numberOfColumns: 2, titleText: '<span style="font-weight:bold;">As of '+this_year_month+" - "+this_year_date+" - "+this_year_year+'</span>'}

		group_ctr++;
		i_ctr++;

	}

	$("#jqgridcontainer").jqGrid('setGroupHeaders', {
              useColSpanStyle: false, 
              groupHeaders:group_header
     }).trigger("reloadGrid");

}

