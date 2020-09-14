$(document).ready(function () {

	window.onload = function(){

		if(module.get_value('view') == 'edit') {
			$('label[for="is_attach"]').parent().parent().parent().hide();
			$('label[for="hr_attach"]').parent().parent().parent().hide();
		}

		if( $('input[name="offense_datetime"]').val() != "" ) var real_val = $('input[name="offense_datetime"]').val();
		$('input[name="offense_datetime"]').datetimepicker( "option", "maxDate", new Date() );
		$('input[name="offense_datetime"]').val( real_val );

		//set default complainant

		if(module.get_value('view') == 'edit' && $('#record_id').val() == "-1"){

			$('select[name="complainants"]').val( user.get_value('user_id') );
			$('select[name="complainants"]').trigger("liszt:updated");

		}

		if( user.get_value('post_control') != 1 && module.get_value('view') == 'edit' ){

			$('label[for="content"]').parent().remove();

		}

		if(module.get_value('view') == 'edit' && ir_status == 2 && module.get_value('client_no') != 2){

			$('label[for="location"]').parent().remove();
			$('label[for="offence_id"]').parent().remove();
			$('label[for="complainants"]').parent().remove();
			$('label[for="details"]').parent().remove();
			$('label[for="offense_datetime"]').parent().remove();
			$('label[for="location"]').parent().remove();
			$('label[for="involved_employees"]').parent().remove();
			$('label[for="witnesses"]').parent().remove();
			$('label[for="damages"]').parent().remove();

		}

		if(module.get_value('view') == 'edit' && ir_status == 6 ){
			$("select").attr('disabled', true).trigger("liszt:updated");
			$("input, textarea").attr('readonly', true);
			$("#is_remarks").attr('readonly', false);
			$("#offense_datetime").datepicker("disable");
			$('label[for="is_attach"]').parent().parent().parent().show();

		}

		if(module.get_value('view') == 'edit' && ir_status == 2 ){
			$("select").attr('disabled', true).trigger("liszt:updated");
			$("input, textarea").attr('readonly', true);
			$("#hr_remarks").attr('readonly', false);
			$("#offense_datetime").datepicker("disable");
			$('label[for="hr_attach"]').parent().parent().parent().show();


		}


	}

});

function validate_form()
{	
	return true;
}

function ajax_save( on_success, is_wizard , callback ){


	if( is_wizard == 1 ){
		var current = $('.current-wizard');
		var fg_id = current.attr('fg_id');
		var ok_to_save = eval('validate_fg'+fg_id+'()')
	}
	else{

		//validate involve employee
		if($("#involved_employees_chzn ul.chzn-choices li:first").is("#involved_employees_chzn ul.chzn-choices li:last")){

			add_error('involved_employees', 'Involved Employee/s', "This field is mandatory, select at least 1.");
			
		}
		if($("#complainants_chzn ul.chzn-choices li:first").is("#complainants_chzn ul.chzn-choices li:last")){

			add_error('complainants', 'Complainant/s', "This field is mandatory, select at least 1.");
			
		}
		if($("#offence_id_chzn a.chzn-single").hasClass('chzn-default')){

			add_error('offence_id', 'Offense', "This field is mandatory.");
			
		}

		ok_to_save = validate_form();

	}
	
	if( ok_to_save ) {		
		$('#record-form').find('.chzn-done').each(function (index, elem) {
			if (elem.multiple) {
				if ($(elem).attr('name') != $(elem).attr('id') + '[]') {
					$(elem).attr('name', $(elem).attr('name') + '[]');
				}
				
				var values = new Array();
				for(var i=0; i< elem.options.length; i++) {
					if(elem.options[i].selected == true) {
						values[values.length] = elem.options[i].value;
					}
				}
				$(elem).val(values);
			}
		});

		var data = $('#record-form').serialize();
		var saveUrl = module.get_value('base_url')+module.get_value('module_link')+"/ajax_save"		



		$.ajax({
			url: saveUrl,
			type:"POST",
			data: data,
			dataType: "json",
			/**async: false, // Removed because loading box is not displayed when set to false **/
			beforeSend: function(){
					show_saving_blockui();
			},
			success: function(data){

				if( data.msg_type != "error" && data.record_id != null ){
					switch( on_success ){
						case 'go_da':
							$.ajax({
								url: module.get_value('base_url')+module.get_value('module_link')+'/get_da',
								type: 'post',
								data: 'record_id='+data.record_id,
								dataType: 'json',
								success: function(response) {
									window.location = module.get_value('base_url')+'employee/disciplinary_action/edit/'+response.da_id;
								}
							});
							break;
						case 'back':
							go_to_previous_page( data.msg );
							break;
						case 'email':
							// Ajax request to send email.                    
							$.ajax({
								url: module.get_value('base_url') + module.get_value('module_link') + '/send_email',
								data: 'record_id=' + data.record_id,
								dataType: 'json',
								type: 'post',
								async: false,
								beforeSend: function(){
										show_saving_blockui();
									},								
								success: function () {

									go_to_previous_page( data.msg );

								}
							}); 
						default:
							if (typeof data.page_refresh != 'undefined' && data.page_refresh == "true"){
									window.location = module.get_value('base_url') + module.get_value('module_link') + '/edit/' + data.record_id;
							}
							else{
								//check if new record, update record_id
								if($('#record_id').val() == -1 && data.record_id != ""){
									$('#record_id').val(data.record_id);
									$('#record_id').trigger('change');
									if( is_wizard == 1 ) window.location = module.get_value('base_url') + module.get_value('module_link') + '/edit/' + data.record_id;
								}
								else{
									$('#record_id').val( data.record_id );
								}
								//generic ajax save callback
								if(typeof window.ajax_save_callback == 'function') ajax_save_callback();
								//custom ajax save callback
								if (typeof(callback) == typeof(Function)) callback();
								$.unblockUI({ onUnblock: function() { message_growl(data.msg_type, data.msg) } });
							}
							break;
					}	
				}
				else{
					$.unblockUI({ onUnblock: function() { message_growl(data.msg_type, data.msg) } });
				}
			}
		});
	}
	else{
		return false;
	}
	return true;

	
}

function callBoxy(record_id = 0)
{
	Boxy.ask("<div class='select-input-wrap' style='margin-top:5px'><select style='margin-top:20px' name='employee_export' class='chzn-select' id='employee_export' style='width:75%'><option value='ir'>Incident Report</option><option value='nte'>Notice To Explain Form</option></select></div>", ["Print", "Cancel"],function( choice ) {
	if(choice == "Print") {
			var data = $('#employee_export').serialize();
			data += "&record_id="+record_id;

			if($('#employee_export').val() == 'ir')
				loc = "print_record";
			else
				loc = "print_record_nte";

			$.ajax({
				url: module.get_value('base_url') +'employee/incident_report/'+loc,
		        data: data,
		        dataType: 'json',
		        type: 'post',
		        success: function (response) {
		        	file_path = response.data;
	                window.location = module.get_value('base_url')+response.data;
		        }
			});
			
	    }
	},
	{
	    title: "Printing"
	});
}

function init_filter_tabs(){
    $('ul#grid-filter li').click(function(){
        $('ul#grid-filter li').each(function(){ $(this).removeClass('active') });
        $(this).addClass('active');
        $('#filter').val( $(this).attr('filter') );
        filter_grid( 'jqgridcontainer', $(this).attr('filter') );
    });
}

function filter_grid( jqgridcontainer, filter )
{
    var searchfield;
    var searchop;
    var searchstring = $('.search-'+ jqgridcontainer ).val() != "Search..." ? $('.search-'+ jqgridcontainer ).val() : "";
    
    if( $("form.search-options-"+ jqgridcontainer).hasClass("hidden") ){
        searchfield = "all";
        searchop = "";
    }else{
        searchfield = $('#searchfield-'+jqgridcontainer).val();
        searchop = $('#searchop-'+jqgridcontainer).val()    
    }

    //search history
    $('#prev_search_str').val(searchstring);
    $('#prev_search_field').val(searchfield);
    $('#prev_search_option').val(searchop);
    $('#prev_search_page').val( $("#"+jqgridcontainer).jqGrid("getGridParam", "page") );

    $("#"+jqgridcontainer).jqGrid('setGridParam', 
    {
        search: true,
        postData: {
            searchField: searchfield, 
            searchOper: searchop, 
            searchString: searchstring,
            filter: filter
        },  
    }).trigger("reloadGrid");   
}

// function init_filter_tabs(){
//     $('ul#grid-filter li').click(function(){
//         $('ul#grid-filter li').each(function(){ $(this).removeClass('active') });
//         $(this).addClass('active');
//         $('#filter').val( $(this).attr('filter') );

//         if( $(this).attr('filter') == 'all' ){
//             $('.status-buttons').parent().show();
//         }
//         else{
//             $('.status-buttons').parent().hide();
//         }

//         filter_grid( 'jqgridcontainer', $(this).attr('filter') );
//     });
// }