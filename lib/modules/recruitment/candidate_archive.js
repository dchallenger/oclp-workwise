$(document).ready(function(){
	$('input:radio[name="is_internal"]').change(
	    function(){
	        if ($(this).val() == 0) {
	        	$("#employee_id").attr('disabled', true).trigger("liszt:updated");
	        	$("#applicant_id").attr('disabled', false).trigger("liszt:updated");
	        }
	        else {
	        	$("#applicant_id").attr('disabled', true).trigger("liszt:updated");	        	
	        	$("#employee_id").attr('disabled', false).trigger("liszt:updated");
	        }
	    }
	);

	if (module.get_value('view') == 'detail'){
		if($.trim($('label[for="is_internal"]').next().html()) == "Yes"){
			$('label[for=applicant_id]').parent().hide();
			$('label[for=employee_id]').parent().show();
		}
		else{
			$('label[for=applicant_id]').parent().show();
			$('label[for=employee_id]').parent().hide();
		}
	}

	if (module.get_value('view') == 'edit'){
		var is_internal = $('input:radio[name="is_internal"]').val();
        if (is_internal == 0) {
        	$("#employee_id").attr('disabled', true).trigger("liszt:updated");
        	$("#applicant_id").attr('disabled', false).trigger("liszt:updated");
        }
        else {
        	$("#applicant_id").attr('disabled', true).trigger("liszt:updated");	        	
        	$("#employee_id").attr('disabled', false).trigger("liszt:updated");
        }		
	}
});