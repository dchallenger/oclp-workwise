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
                url: module.get_value('base_url') + module.get_value('module_link') + '/get_biometrics_report_filter',
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
            $('#category_selected').html('');
            $('#multi-select-container').html('');          
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

	$("#"+jqgridcontainer).jqGrid('setGridParam', 
	{
		//search: true,
		postData: {
			employee : $('#employee').val(),
			company : $('#company').val(),
			department : $('#department').val(),
			division : $('#division').val(),
			//dateStart : $('#date_start').val(),
			//dateEnd : $('#date_end').val(),
		}, 	
	}).trigger("reloadGrid");

}