function ajax_save(){
	var data = $('form#record-form').serialize();
	$.ajax({
		url: module.get_value('base_url') + module.get_value('module_link') + "/save",
		type:"POST",	
		data: data,
		dataType: "json",
		beforeSend: function(){
			$.blockUI({ message: '<div class="now-loading align-center"><img src="'+module.get_value('base_url')+user.get_value('user_theme')+'/images/loading.gif"><br />Loading, please wait...</div>' });  		
		},
		success: function( data ){
			if( data.msg != "" ) $('#message-container').html(message_growl(data.msg_type, data.msg));
		}
	});
}