$(document).ready(function(){ 
	if(module.get_value('view')=='edit')
	{

		 $('.icon-16-disk-back').attr('onclick','form_validate()');
		 $('.icon-16-disk').parent().remove();
		 $('.icon-16-disk-back').children('span').text('Save & Send');
	}
});

function form_validate(){

	if( Date.parse($('#date_from').val())  > Date.parse($('#date_to').val()) ){

		message_growl('error', 'Date From field must at least less than or equal to Date To field')

	}
	else{

		if( user.get_value('post_control') != 1 ){
		 	save_and_email(false, "Send request to HR?");
		 }
		 else{
		 	skip_notification();
		 }

	}

}

function skip_notification(){

	ajax_save('email', false, function () { window.location = module.get_value('base_url') + module.get_value('module_link'); });

}

