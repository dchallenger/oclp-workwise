$(document).ready(function () {
	if (module.get_value('view') == "edit"){
		$('#total_manhour').attr('readonly', true);

		$('#employee_id,#date_incident-temp').live('change',function(){
			var employee_id = $('#employee_id').val();
			var date_incident = $('#date_incident').val();
			$.ajax({
				url: module.get_value('base_url') + module.get_value('module_link') + "/get_manhour",
				type:"POST",
				dataType: "json",
				data: 'employee_id='+employee_id+'&date_incident='+date_incident,
				success: function(data){
					$('#total_manhour').val(data.total_manhour);
				}				
			});
		});
	}
});