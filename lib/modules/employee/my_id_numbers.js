$(document).ready(function(){
    $( 'input[name="sss_balance_date-temp"],input[name="pagibig_balance_date-temp"]' ).datepicker({
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
		yearRange: 'c-90:c+10',
		beforeShow: function(input, inst) {						
			
		},
		onClose: function(dateText) {

		}
	});

	$('.detail').click(function(event){
		event.preventDefault();
		window.location = module.get_value('base_url') + module.get_value('module_link') + '/detail';		
	});
});

function ajax_save( on_success, is_wizard , callback ){
	ok_to_save = true;
	if( ok_to_save ){
		var data = $('#record-form').serialize();
        data += "&on_success=" + on_success;
		var saveUrl = module.get_value('base_url')+module.get_value('module_link')+"/ajax_save"
				
		$.ajax({
			url: saveUrl,
			type:"POST",
			data: data,
			dataType: "json",
			async: false,
			beforeSend: function(){
			    if( $('.now-loading').length == 0) $.blockUI({ message: '<div class="now-loading align-center"><img src="'+module.get_value('base_url')+user.get_value('user_theme')+'/images/loading.gif"><br />Saving, please wait...</div>' });
			},
			success: function(data){  
				window.location = module.get_value('base_url') + module.get_value('module_link') + '/detail/' + data.record_id;
				$.unblockUI({ onUnblock: function() { message_growl(data.msg_type, data.msg) } });				
/*				if(on_success == "back") {
					go_to_previous_page( data.msg );
				} else if (on_success == "email" && data.msg_type == "success") {
                                    // Ajax request to send email.
                                    $.ajax({
                                        url: module.get_value('base_url') + module.get_value('module_link') + '/send_email',
                                        data: 'record_id=' + data.record_id,
                                        type: 'post',
                                        success: function () {
                                          window.location = module.get_value('base_url') + module.get_value('module_link') + '/detail/' + data.record_id;
                                          $.unblockUI({ onUnblock: function() { message_growl(data.msg_type, data.msg) } });
                                        }
                                    });                                    
                                } else{
					//check if new record, update record_id
					if($('#record_id').val() == -1 && data.record_id != ""){
						$('#record_id').val(data.record_id);
						$('#record_id').trigger('change');
						if( is_wizard == 1 ) window.location = module.get_value('base_url') + module.get_value('module_link') + '/edit/' + data.record_id;
					}                                        
					$.unblockUI({ onUnblock: function() { message_growl(data.msg_type, data.msg) } });
				}
*/			}
		}); 
	}
	else{
		return false;
	}
	return true;
}