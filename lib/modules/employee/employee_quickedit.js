$(document).ready(function(){
	$('#rank_id').live('change',function(){
		get_employee_type($(this).val(), 'edit', module.get_value('record_id'));
	});

	$('#birth_date-temp').val($('#birth_date').val())
})         