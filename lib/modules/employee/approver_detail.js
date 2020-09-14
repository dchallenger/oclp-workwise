$(document).ready(function(){
	setTimeout('hide_buttons();', 100);
});


function hide_buttons(){
	if(module.get_value('view') == "edit"){
		$('label[for="employee_id"]').next().find('span.icon-group').remove();
		$('label[for="module_id"]').next().find('span.icon-group').remove();
		$('label[for="position_id"]').next().find('span.icon-group').remove();
	}
}