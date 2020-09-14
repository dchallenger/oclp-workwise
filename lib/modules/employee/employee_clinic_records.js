$(document).ready(function(){
	// $('#other_med_cost').css('width','35%');
	// var combined_html = '<div class="form-item even "><label class="label-desc gray" for="other_med_cost"> Other Med Cost: &nbsp;&nbsp; Medication Qty: </label><div class="text-input-wrap"><input id="other_med_cost" class="input-text" type="text" value="" name="other_med_cost" style="width: 35%;"><input id="medication_qty" class="input-text" type="text" value="" name="medication_qty"></div>';
	// $('label[for="medication_qty"]').parent().remove();

	$('label[for="medication"]').prepend('Medication: &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Quantity')

	$('select[name="dept_id"]').prop('disabled',true);
	$('#user_id').live('change', function(){
		var data = "user_id="+$('#user_id').val();
		$.ajax({
			url: module.get_value('base_url')+"employee/employee_clinic_records/get_department",
			type: 'post',
			data: data,
			success: function(response)
			{
				$('select[name="dept_id"]').val(response.department_id);
			}
		});
	});

});