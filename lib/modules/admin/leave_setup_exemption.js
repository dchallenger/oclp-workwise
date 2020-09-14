$( document ).ready( function() {
	$('#multiselect-employee_id option').remove();  

	$('#leave_setup_id').bind('change',function(){
		var leave_setup_id = $(this).val();
		$.ajax({
			url: module.get_value('base_url') + module.get_value('module_link') + '/get_employee_by_leave_setup',
			data: 'leave_setup_id=' + leave_setup_id + '&record_id=' + $('#record_id').val(),
			type: 'post',
			dataType: 'html',
			success: function(response) {
				$('#multiselect-employee_id option').remove();
				$('#multiselect-employee_id').append(response);
				$('#multiselect-employee_id').multiselect('refresh');
			}
		});			
	});  

	if ($('#record_id').val() != '-1'){	
 		$('#leave_setup_id').trigger('change');	
  	}
	
});