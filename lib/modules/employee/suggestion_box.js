$(document).ready(function() {
	if (module.get_value('view') == 'edit'){
		$('#employee_id').val(user.get_value('user_id'));	
		$('#employee_id').hide();
		$('<label>'+user.get_value('nicename')+'</label>').insertAfter($('label[for="employee_id"]'));
	}

	if (module.get_value('view') == 'detail'){
		$('<div class="text-input-wrap">'+user.get_value('nicename')+'</div>').insertAfter($('label[for="employee_id"]'));
		$('label[for="employee_id"]').next().next().hide();
	}	
})