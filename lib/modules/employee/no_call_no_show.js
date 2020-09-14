$(document).ready(function(){
	if(module.get_value('view') == 'edit')
	{
		$('#employee_id option').remove();
		$.ajax({
			url: module.get_value('base_url')+module.get_value('module_link')+'/get_dropdown',
			dataType: 'html',
			success: function(response) {
				$('#employee_id').append(response);
				$('#employee_id').trigger("liszt:updated");			
			}
		});
	}
});