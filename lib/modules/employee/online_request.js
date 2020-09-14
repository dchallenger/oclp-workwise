$(document).ready(function () {

	window.onload = function(){

		if( user.get_value('post_control') != 1 ){
			$('#employee_id').find('option').each(function(){
				if( $(this).val() != user.get_value('user_id') ){
					$(this).remove();
				}
			});
			$("#employee_id").trigger("liszt:updated");
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