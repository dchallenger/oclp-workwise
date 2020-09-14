$( document ).ready( function() {


	window.onload = function(){
		
		 $(".multi-select").multiselect({
            show:['blind',250],
            hide:['blind',250],
            selectedList: 1
        });

		 for (i = new Date().getFullYear(); i > 1900; i--)
		{
		    $('#yearpicker').append($('<option />').val(i).html(i));
		}
 

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

		$('#gender_male').click(function(){

			$('#gender_hidden').val($('#gender_male').val());

		});

		$('#gender_female').click(function(){

			$('#gender_hidden').val($('#gender_female').val());

		});

	}

	 

	$("#company").live('change',function(){

		var selectedOptions = $.map($('#company :selected'),
        function(e) { return $(e).val(); } );
        var div_id_delimited1 = selectedOptions.join(',');
        if (div_id_delimited1)
        {
	        		$.ajax({
	                    url: module.get_value('base_url') + module.get_value('module_link') + '/get_division',
	                    data: 'div_id_delimited=' + div_id_delimited1,
	                    dataType: 'html',
	                    type: 'post',
	                    async: false,
	                    beforeSend: function(){
	                    
	                    },                              
	                    success: function ( response ) 
	                    {
	                    	$('#company_srch').val(div_id_delimited1);
	                        $('#multi-select-main-container1').show();
	                        $('#multi-select-main-container2').hide();
	                        $('#multi-select-container1').html(response);                               
	                    }
	                }); 
	    }
	    else{
	    	$('#multi-select-main-container1').hide();
	    	$('#multi-select-main-container2').hide();
	    }
    });

    $("#division").live('change',function(){
    	var selectedOptions = $.map($('#division :selected'),
        function(e) { return $(e).val(); } );
        var div_id_delimited2 = selectedOptions.join(',');
        if (div_id_delimited2){
	        $.ajax({
	                    url: module.get_value('base_url') + module.get_value('module_link') + '/get_department',
	                    data: 'div_id_delimited=' + div_id_delimited2,
	                    dataType: 'html',
	                    type: 'post',
	                    async: false,
	                    beforeSend: function(){
	                    
	                    },                              
	                    success: function ( response ) 
	                    {
	                    	$('#division_srch').val(div_id_delimited2);
	                        $('#multi-select-main-container2').show();
	                        $('#multi-select-container2').html(response);  
	                    }
	                });
   		}
    });

	$('#category').live('change',function(){
        var items = {0 : "Select", 1 : "Company",2 : "Division",3 : "Department", 4 : "Employee"};
        var category_id = $(this).val();
        var company_id = $('#company_list').val();
        var category = items[category_id];

        var eleid = category.toLowerCase()

        if (category_id > 0){
            $.ajax({
                url: module.get_value('base_url') + module.get_value('module_link') + '/get_uniform_order_filter',
                data: 'category_id=' + category_id + '&company_id=' + company_id,
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

	/*

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

	*/

	return true;


}

function list_search_grid( jqgridcontainer ){

	$("#jqgridcontainer").jqGrid('destroyGroupHeader');
	$("#jqgridcontainer").jqGrid('GridUnload');

	recreate_uniform_order_grid();

}

function recreate_uniform_order_grid( years ){

var colname = ["Name","Gender","Date","Remarks"];
var colmodel = [{name : 'name'},{name : 'gender'},{name : 'date'},{name : 'remarks'}] ;
var years = $('#years').val();

var group_ctr = 2;
var force_to_fit = true;


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
			year : $('#yearpicker').val(),
			gender : $('#gender_hidden').val()
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

}

