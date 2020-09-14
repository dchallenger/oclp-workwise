$( document ).ready( function() {

	if( module.get_value('view') == 'index' ){


		$('.go_back_to_main').live('click',function(){

			window.location = module.get_value('base_url') + 'training/training_feedback';


		});

	}

	if( module.get_value('view') == 'edit' ){

		window.onload = function(){

			$('label[for="participant_name"]').parents('.col-2-form').addClass('view');

		}

		$('.feedback_average').live('click',function(){
			get_total_average();
		});

	}

});


function get_total_average(){

	var url = module.get_value('base_url') + module.get_value('module_link') + '/get_total_average';
    var data = $('#record-form').serialize();

	$.ajax({
        url: url,
        dataType: 'json',
        type:"POST",
        data: data,
        success: function (response) {

        	$('input[name="total_score"]').val(response.total_score);
        	$('input[name="average_score"]').val(response.average_score);
        }

	});

}

function ajax_save_feedback_participants( status, on_success, is_wizard , callback ){
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

		var data = $('#record-form').serialize()+'&status='+status;
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
							var calendar_id = $('#calendar_id').val();
							var employee_direct = $('#employee_direct').val();

							if(employee_direct == 0){
								window.location = module.get_value('base_url') + module.get_value('module_link') + '/index/' + calendar_id;
							}
							else{
								window.location = module.get_value('base_url') + '/training/training_feedback/';
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

function go_back(){

	var calendar_id = $('#calendar_id').val();
	var employee_direct = $('#employee_direct').val();

	if(employee_direct == 0){
		window.location = module.get_value('base_url') + module.get_value('module_link') + '/index/' + calendar_id;
	}
	else{
		window.location = module.get_value('base_url') + '/training/training_feedback/';
	}

}

function save_feedback_participants( status, on_success, is_wizard , callback){



	ajax_save_feedback_participants( status, on_success, is_wizard , callback );

}