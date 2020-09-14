$(window).ready(function(){
	
});

function edit()
{
	if( user.get_value('edit_control') == 1 ){
		$('#record-form').attr("action", module.get_value('base_url') + module.get_value('module_link') +"/edit");
		$('#record-form').submit();	
	}
	else{
		$('#message-container').html(message_growl('attention', 'Insufficient Access grant!\nPlease contact the system administrator.'))
	}	
}

function seeDetail(record_id, url)
{
	$('#record_id').val(record_id);
	$('#record-form').attr("action", module.get_value('base_url')+url+"/detail");
	$('#record-form').submit();	
}