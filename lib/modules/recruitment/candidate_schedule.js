$(document).ready(function(){

	window.onload = function(){

		if( module.get_value('view') == "index" ){

			if( ( $('#mrf_id_posted').length > 0 ) && ( $('#mrf_id_posted').val() > 0 ) ){


				$.ajax({
			        url: module.get_value('base_url') + 'recruitment/candidate_schedule/get_mrf_details',
			        data: 'mrf_id='+ $('#mrf_id_posted').val(),
					dataType: 'html',
					type: 'post',
					async: false,
					beforeSend: function(){
					
					},								
					success: function ( response ) {
						$('#record-form').append(response);
			        }
				});

			}

		}

		if( module.get_value('view') == "edit" ){

			check_applicant_status();

		}


	}


	$('.datetimepicker').datetimepicker(
		{                            
			changeMonth: true,
			changeYear: true,
			showOtherMonths: true,
			showButtonPanel: true,
			showAnim: 'slideDown',
			selectOtherMonths: true,
			showOn: "both",
			buttonImage: module.get_value('base_url') + user.get_value('user_theme') + "/icons/calendar-month.png",
			buttonImageOnly: true,
			buttonText: '',
			hourGrid: 4,
			minuteGrid: 10,
			timeFormat: 'hh:mm tt',
			ampm: true,
			yearRange: 'c-90:c+10'
        }
    );
    	
	$("#employee_id").attr('disabled', true).trigger("liszt:updated");

	$('input:radio[name="is_internal"]').change(
	    function(){
	        if ($(this).val() == 0) {
	        	$("#employee_id").attr('disabled', true).trigger("liszt:updated");
	        	$("#employee_id").find('option').each(function(){
	        		$(this).prop('selected', false);
	        	});     	
	        	$("#employee_id").trigger("liszt:updated");
	        	$("#applicant_id").attr('disabled', false).trigger("liszt:updated");
	        	$("#applicant_name").val("");
	        }
	        else {
	        	$("#applicant_id").attr('disabled', true).trigger("liszt:updated");	  
	        	$("#applicant_id").find('option').each(function(){
	        		$(this).prop('selected', false);
	        	});  
	        	$("#applicant_id").trigger("liszt:updated");
	        	$("#employee_id").attr('disabled', false).trigger("liszt:updated");
	        	$("#applicant_name").val("");
	        	$('.applicant_application_status').remove();
	        }
	    	//$('#mrf_id').attr('disabled',true);	        
	    }
	);

	if (module.get_value('view') == 'detail'){
		if($.trim($('label[for="is_internal"]').next().html()) == "Yes"){
			$('label[for=applicant_id]').parent().hide();
			$('label[for=employee_id]').parent().show();
		}
		else{
			$('label[for=applicant_id]').parent().show();
			$('label[for=employee_id]').parent().hide();
		}

		$('label[for="applicant_name"]').parent().hide();
	}

	if (module.get_value('view') == 'edit'){
		// to replace list of applicants filter application_status_id into 1 = candidate
		var applicant_id = $('#applicant_id').val();
		$.ajax({
			url: module.get_value('base_url') + module.get_value('module_link') + '/get_applicant',
			data: 'applicant_id=' + applicant_id,
			dataType: 'html',
			type: 'POST',
			success: function(data){
				$('#applicant_id').html(data);
				$("#applicant_id").trigger("liszt:updated");
			}
		});

		$('#employee_id').live('change',function(){
			var applicant_name = $('#employee_id option:selected').text();
			$('#applicant_name').val(applicant_name);
		});

		$('#applicant_id').live('change',function(){
			var applicant_name = $('#applicant_id option:selected').text();
			$('#applicant_name').val(applicant_name);

			check_applicant_status();
		});

		var is_internal = $('input:radio[name="is_internal"]:checked').val();
        if (is_internal == 0) {
        	$("#employee_id").attr('disabled', true).trigger("liszt:updated");
        	$("#applicant_id").attr('disabled', false).trigger("liszt:updated");
        }
        else {
        	$("#applicant_id").attr('disabled', true).trigger("liszt:updated");	        	
        	$("#employee_id").attr('disabled', false).trigger("liszt:updated");
        }		
/*		$('#mrf_id').attr('disabled',true);	
		$('#applicant_id,#employee_id').live('change',function(){
			var applicant_id = $(this).val();
			$.ajax({
				url: module.get_value('base_url') + module.get_value('module_link') + '/get_mrf_by_position',
				data: 'applicant_id=' + applicant_id,
				dataType: 'html',
				type: 'POST',
				beforeSend: function(){
					$.blockUI({ message: '<div class="now-loading align-center"><img src="'+module.get_value('base_url')+user.get_value('user_theme')+'/images/loading.gif"><br />Loading, please wait...</div>' });
				},
				success: function(data){
					$.unblockUI();
					$('#mrf_id').html(data);
					$('#mrf_id').attr('disabled',false);
				}
			});
		});*/
	}

	$('.external').live('click',function(){
		var action_link = $(this).attr('module_link');
		var mrf_id_posted = $('#mrf_id_posted').val();
		$('#record-form').attr("action", module.get_value('base_url') + action_link);
		$('#record-form').append('<input type="hidden" name="from_cs" value="1">');
		$('#record-form').append('<input type="hidden" name="mrf_from_posted_jobs" value="'+ mrf_id_posted +'">');
		$('#record-form').submit();
	});

	$('.quick-add').live('click',function(){
	    $.ajax({
            url: module.get_value('base_url') + 'recruitment/applicants/simple_applicant_form',
            type: 'post',
            dataType: 'json',
            beforeSend: function(){
                $.blockUI({
                    message: '<div class="now-loading align-center"><img src="'+module.get_value('base_url')+user.get_value('user_theme')+'/images/loading.gif"><br />Loading, please wait...</div>'
                });         
            },  
            success: function(response) {
                if(response.msg_type == 'error'){
                    $.unblockUI();  
                    message_growl(response.msg_type, response.msg);
                }
                else{            

	                $.unblockUI();  
	                template_form = new Boxy('<div id="boxyhtml">'+ response.form +'</div>',
	                {
	                    title: 'Add Applicant',
	                    draggable: false,
	                    modal: true,
	                    center: true,
	                    unloadOnHide: true,
	                    beforeUnload: function (){
	                        template_form = false;
	                    }
	                });
	                boxyHeight(template_form, '#boxyhtml'); 

	                $('#date_schedule').datetimepicker({
                                        changeMonth: true,
                                        changeYear: true,
                                        showOtherMonths: true,
                                        showButtonPanel: true,
                                        showAnim: 'slideDown',
                                        selectOtherMonths: true,
                                        showOn: "both",
                                        buttonImage: module.get_value('base_url') + user.get_value('user_theme') + "/icons/calendar-month.png",
                                        buttonImageOnly: true,
                                        buttonText: '',
                                        hourGrid: 4,
                                        minuteGrid: 10,
                                        timeFormat: 'hh:mm tt',
                                        ampm: true,
                                        yearRange: 'c-90:c+10',
                                        onClose: function(dateText, inst){
                                            if ($('#datetime_from').val() == "" || $('#datetime_to').val() == ""){
                                                return;
                                            }
                                        }
                                    });
	            }      
            }
	    }); 	
	});

	$('#save_applicant').live('click',function(){
		if ($('#applicant_name').val() == ""){
			$('#applicant_name').focus();
			message_growl('error', 'Applicant name is required');
			return false;
		}

		var mrf_id = 0;

		if( $('#mrf_id_posted').length > 0 ){
			mrf_id = $('#mrf_id_posted').val()
		}

		$.ajax({
	        url: module.get_value('base_url') + 'recruitment/applicants/simple_save_applicant',
	        data: 'mrf_id=' + mrf_id  + '&applicant_name=' + $('#applicant_name').val() + '&date_schedule=' + $('#date_schedule').val(),
	        type: 'post',
	        dataType: 'json',
	        success: function(data) {
	        	$.unblockUI();	
	        	Boxy.get($('#boxyhtml')).hide();
	        	message_growl(data.msg_type, data.msg);
	        	$("#jqgridcontainer").jqGrid().trigger("reloadGrid");
	        	//window.location = module.get_value('base_url') + 'recruitment/candidate_schedule';
	        }
		});

	});

	$('.icon-send').live('click',function(){
		var employee_id = $(this).closest('div.parent_container').find('#interviewer_id').val();
		var date_time = $(this).closest('div.parent_container').find('input[name="interview_date[]"]').val();

		if(! employee_id){
			add_error_mine('interviewer', 'Interviewer', "This field is mandatory.");
		}
		if(! date_time){
			add_error_mine('date_time', 'Date', "This field is mandatory.");
		}

		ok_to_save = validate_form_mine();

		if (ok_to_save){
			$.ajax({
		        url: module.get_value('base_url') + 'recruitment/candidate_schedule/send_email',
		        data: 'employee_id=' + employee_id + '&candidate_id=' + $('#record_id').val() + '&date_time=' + date_time,
		        type: 'post',
		        dataType: 'json',
	            beforeSend: function(){
	                $.blockUI({
	                    message: '<div class="now-loading align-center"><img src="'+module.get_value('base_url')+user.get_value('user_theme')+'/images/loading.gif"><br />Sending, please wait...</div>'
	                });
	            },	        
		        success: function(data) {
					$.unblockUI();
		        	message_growl(data.msg_type, data.msg);
		        }
			});	
		}	
	});

	$('.back_to_schedule_list').live('click',function(){

		if( $('#current_mrf_id').val() != "-1" ){
			window.location = module.get_value('base_url') + module.get_value('module_link') + '/index/' + $('#mrf_id').val();
		}
		else{
			window.location = module.get_value('base_url') + module.get_value('module_link');
		}

	});



});

var error_mine = new Array;

function add_error_mine(fieldname, fieldlabel, msg)
{
	error_mine[error_ctr] = new Array(fieldname, fieldlabel, msg);
	error_ctr++;
}

function validate_form_mine()
{
	//errors
	if(error_mine.length > 0){
		var error_str = "Please correct the following errors:<br/><br/>";
		for(var i in error_mine){
			if(i == 0) $('#'+error_mine[i][0]).focus(); //set focus on the first error
			error_str = error_str + (parseFloat(i)+1) +'. '+error_mine[i][1]+" - "+error_mine[i][2]+"<br/>";
		}
		$('#message-container').html(message_growl('error', error_str));
		
		//reset errors
		error_mine = new Array();
		error_ctr = 0
		return false;
	}
	
	//no error occurred
	return true;
}

function validate_save(on_success, is_wizard , callback){

	if( $('#is_internal-yes').attr('checked') == "checked" ){

		if( ! ( parseInt( $('#employee_id').val() ) > 0 ) ){

			add_error('employee_id', 'Employee', 'Employee is required');
		}

	}else{

		if( ! ( parseInt( $('#applicant_id').val() ) > 0 ) ){

			add_error('applicant_id', 'Applicant', 'Applicant is required');
		}

	}

	if( $('#applicant_id').val() > 0 ){

		 $.ajax({
            url: module.get_value('base_url') + 'recruitment/candidate_schedule/check_applicant_availability',
            data: $('#record-form').serialize(),
            type: 'post',
            dataType: 'json',
            beforeSend: function(){       
            },  
            success: function(response) {

            	if( response.msg_type == "error" ){
            		add_error('applicant_id', 'Applicant', response.msg);
            	}

            }
        });

	}


	ajax_save_candidate( on_success, is_wizard , callback );

}


function ajax_save_candidate( on_success, is_wizard , callback ){
	if( is_wizard == 1 ){
		var current = $('.current-wizard');
		var fg_id = current.attr('fg_id');
		var ok_to_save = eval('validate_fg'+fg_id+'()')
	}
	else{
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
				if(  data.record_id != null ){
					//check if new record, update record_id
					if($('#record_id').val() == -1 && data.record_id != ""){
						$('#record_id').val(data.record_id);
						$('#record_id').trigger('change');
						if( is_wizard == 1 ) window.location = module.get_value('base_url') + module.get_value('module_link') + '/edit/' + data.record_id;
					}
					else{
						$('#record_id').val( data.record_id );
					}
				}

				if( data.msg_type != "error"){					
					switch( on_success ){
						case 'back':

							if( $('#current_mrf_id').val() != "-1" ){
								window.location = module.get_value('base_url') + module.get_value('module_link') + '/index/' + $('#mrf_id').val();
							}
							else{
								window.location = module.get_value('base_url') + module.get_value('module_link');
							}
							//go_to_previous_page( data.msg );
							break;
						case 'email':							
							if (data.record_id > 0 && data.record_id != '') {

								// Ajax request to send email.                    
								$.ajax({
									url: module.get_value('base_url') + module.get_value('module_link') + '/send_email',
									data: 'record_id=' + data.record_id,
									dataType: 'json',
									type: 'post',
									async: false,
									beforeSend: function(){
											//show_saving_blockui();
										},								
									success: function () {
									}
								});
							}							
							//custom ajax save callback
							//if (typeof(callback) == typeof(Function)) callback( data );
						default:
							if (typeof data.page_refresh != 'undefined' && data.page_refresh == "true"){
									window.location = module.get_value('base_url') + module.get_value('module_link') + '/edit/' + data.record_id;
							}
							else{
								//generic ajax save callback
								if(typeof window.ajax_save_callback == 'function') ajax_save_callback();
								//custom ajax save callback
								if (typeof(callback) == typeof(Function)) callback( data );
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



function check_applicant_status(){

	if( $('#applicant_id').val() > 0 ){

		$.ajax({
			url: module.get_value('base_url') +  'recruitment/candidate_schedule/check_applicant_status',
			data: 'applicant_id='+$('#applicant_id').val(),
			dataType: 'html',
			type: 'post',
			async: false,
			beforeSend: function(){
			
			},								
			success: function ( response ) {

				if( response != "" ){

					$('.applicant_application_status').remove();
					$('label[for="applicant_id"]').parent().append(response);

				}
				else{

					$('.applicant_application_status').remove();

				}

			}
		});

	}
	else{

		$('.applicant_application_status').remove();

	}

}

function add_interviewer(){

	$.ajax({
		url: module.get_value('base_url') +  'recruitment/candidate_schedule/get_interviewer_form',
		data: '',
		dataType: 'html',
		type: 'post',
		async: false,
		beforeSend: function(){
		
		},								
		success: function ( response ) {
			
			var ctrval = parseFloat($('#ctr_handler').val());
			var cls = "datetimepicker"+ctrval+"";
			$('#interviewer-container').append(response).find('input').addClass(cls);
			$('#ctr_handler').val(ctrval + 1);

			$('.'+cls+'').datetimepicker(
				{                            
					changeMonth: true,
					changeYear: true,
					showOtherMonths: true,
					showButtonPanel: true,
					showAnim: 'slideDown',
					selectOtherMonths: true,
					showOn: "both",
					buttonImage: module.get_value('base_url') + user.get_value('user_theme') + "/icons/calendar-month.png",
					buttonImageOnly: true,
					buttonText: '',
					hourGrid: 4,
					minuteGrid: 10,
					timeFormat: 'hh:mm tt',
					ampm: true,
					yearRange: 'c-90:c+10'
		        }
		    );


		}
	});		
}

function delete_benefit( field ){
	Boxy.ask("Are you sure you want to delete interviewer?", ["Yes", "Cancel"],
	function( choice ) {
		if(choice == "Yes"){
			field.parent().parent().parent().remove();
		}
	},
	{
		title: "Delete Interviewer"
	});
}