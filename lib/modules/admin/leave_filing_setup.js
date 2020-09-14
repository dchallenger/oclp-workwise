$(document).ready(function(){
	$('label[for="filing_before_days_or_month-yes"]').html('No. of Days');
	$('label[for="filing_before_days_or_month-no"]').html('No. of Cut-off');
	$('label[for="filing_after_days_or_month-yes"]').html('No. of Days');
	$('label[for="filing_after_days_or_month-no"]').html('No. of Cut-off');

	$('label[for="approval_before_days_or_month-yes"]').html('No. of Days');
	$('label[for="approval_before_days_or_month-no"]').html('No. of Cut-off');
	$('label[for="approval_after_days_or_month-yes"]').html('No. of Days');
	$('label[for="approval_after_days_or_month-no"]').html('No. of Cut-off');

	$('label[for="cancelation_before_days_or_month-yes"]').html('No. of Days');
	$('label[for="cancelation_before_days_or_month-no"]').html('No. of Cut-off');
	$('label[for="cancelation_after_days_or_month-yes"]').html('No. of Days');
	$('label[for="cancelation_after_days_or_month-no"]').html('No. of Cut-off');

	$('label[for="calendar_days"]').parent().hide();
	if (module.get_value('view') == "edit"){
		$('input[name="with_attachment"]').live('change',function(){
			if ($(this).val() == 1){
				// $('label[for="no_days_required_attachment"]').parent().show();
			}
			else{
				$('#no_days_required_attachment').val('');
				// $('label[for="no_days_required_attachment"]').parent().hide();
			}
		})

		$('input[name="for_hr_validation"]').live('change',function(){
			if ($(this).val() == 1){
				// $('label[for="no_days_required_attachment"]').parent().show();
			}
			else{
				$('#no_days_hr_validate').val('');
				// $('label[for="no_days_required_attachment"]').parent().hide();
			}
		})
		
		$('#filing_before_days_or_month').live('change',function(){
			$('#filing_before_no_days_or_cutoff').attr('readonly',false);
			$('label[for="filing_before_no_days_or_cutoff"]').html($(this).find('option:selected').text() + ':');
			if ($(this).val() == 3 || $(this).val() == 4){
				$('#filing_before_no_days_or_cutoff').val('').attr('readonly',true);
			}
		})	
		$('#filing_after_days_or_month').live('change',function(){
			$('#filing_after_no_days_or_cutoff').attr('readonly',false);
			$('label[for="filing_after_no_days_or_cutoff"]').html($(this).find('option:selected').text() + ':');
			if ($(this).val() == 3 || $(this).val() == 4){
				$('#filing_after_no_days_or_cutoff').val('').attr('readonly',true);
			}
		})	

		$('#approval_before_days_or_month').live('change',function(){
			$('#approval_before_no_days_or_cutoff').attr('readonly',false);
			$('label[for="approval_before_no_days_or_cutoff"]').html($(this).find('option:selected').text() + ':');
			if ($(this).val() == 3 || $(this).val() == 4){
				$('#approval_before_no_days_or_cutoff').val('').attr('readonly',true);
			}
		})	
		$('#approval_after_days_or_month').live('change',function(){
			$('#approval_after_no_days_or_cutoff').attr('readonly',false);
			$('label[for="approval_after_no_days_or_cutoff"]').html($(this).find('option:selected').text() + ':');
			if ($(this).val() == 3 || $(this).val() == 4){
				$('#approval_after_no_days_or_cutoff').val('').attr('readonly',true);
			}
		})

		$('#cancelation_before_days_or_month').live('change',function(){
			$('#cancelation_before_no_days_or_cutoff').attr('readonly',false);
			$('label[for="cancelation_before_no_days_or_cutoff"]').html($(this).find('option:selected').text() + ':');
			if ($(this).val() == 3 || $(this).val() == 4){
				$('#cancelation_before_no_days_or_cutoff').val('').attr('readonly',true);
			}
		})	
		$('#cancelation_after_days_or_month').live('change',function(){
			$('#cancelation_after_no_days_or_cutoff').attr('readonly',false);
			$('label[for="cancelation_after_no_days_or_cutoff"]').html($(this).find('option:selected').text() + ':');
			if ($(this).val() == 3){
				$('#cancelation_after_no_days_or_cutoff').val('').attr('readonly',true);
			}
		})

		$('#application_form_id').live('change',function(){
			if ($(this).val() == 5 || $(this).val() == 6){
				$('label[for="maternity_max_no_children"]').parent().show();
			}
			else{
				$('label[for="maternity_max_no_children"]').parent().hide();	
			}

			if ($(this).val() == 13){
				$('label[for="bday_no_of_days_alowed_before_filing"]').parent().show();
				$('label[for="bday_no_of_days_alowed_after_filing"]').parent().show();
			}
			else{
				$('label[for="bday_no_of_days_alowed_before_filing"]').parent().hide();	
				$('label[for="bday_no_of_days_alowed_after_filing"]').parent().hide();
			}			
		})

		if (module.get_value('record_id') != -1){
			$('#filing_before_days_or_month').trigger('change');
			$('#filing_after_days_or_month').trigger('change');
			$('#approval_before_days_or_month').trigger('change');
			$('#approval_after_days_or_month').trigger('change');
			$('#cancelation_before_days_or_month').trigger('change');
			$('#cancelation_after_days_or_month').trigger('change');
			$('#application_form_id').trigger('change');
		}						
	}

	if (module.get_value('view') == "detail"){
		$.ajax({
	        url: module.get_value('base_url') + module.get_value('module_link') + '/get_record',
	        type: 'post',
	        dataType: 'json',
	        data: 'record_id='+ $('#record_id').val(),
	        beforeSend:function() {
	        },
	        success: function (response) {
	        	if (response.application_form_id == 5 || response.application_form_id == 6){
	        		$('label[for="maternity_max_no_children"]').parent().show()
	        	}
	        	else{
	        		$('label[for="maternity_max_no_children"]').parent().hide()	
	        	}

	        	if (response.application_form_id == 13){
	        		$('label[for="bday_no_of_days_alowed_before_filing"]').parent().show()
	        		$('label[for="bday_no_of_days_alowed_after_filing"]').parent().show()
	        	}
	        	else{
	        		$('label[for="bday_no_of_days_alowed_before_filing"]').parent().hide()	
	        		$('label[for="bday_no_of_days_alowed_after_filing"]').parent().hide()
	        	}

	        	switch (response.filing_before_days_or_month) {
	        		case '1':
	        			$('label[for="filing_before_no_days_or_cutoff"]').html('No. of Days');
	        			break;		        		
	        		case '2':
	        			$('label[for="filing_before_no_days_or_cutoff"]').html('No. of Cut-off');
	        			break;
	        		case '3':
						$('label[for="filing_before_no_days_or_cutoff"]').html('Not allowed').next().html('');
	        			break;	
	        		case '4':
						$('label[for="filing_before_no_days_or_cutoff"]').html('Within the Cut-off').next().html('');
	        			break;	        				        			
	        	}
	        	switch (response.filing_after_days_or_month) {
	        		case '1':
	        			$('label[for="filing_after_no_days_or_cutoff"]').html('No. of Days');
	        			break;		        		
	        		case '2':
	        			$('label[for="filing_after_no_days_or_cutoff"]').html('No. of Cut-off');
	        			break;
	        		case '3':
						$('label[for="filing_after_no_days_or_cutoff"]').html('Not allowed').next().html('');
	        			break;	
	        		case '4':
						$('label[for="filing_after_no_days_or_cutoff"]').html('Within the Cut-off').next().html('');
	        			break;		        				        			
	        	}

	        	switch (response.approval_before_days_or_month) {
	        		case '1':
	        			$('label[for="approval_before_no_days_or_cutoff"]').html('No. of Days');
	        			break;		        		
	        		case '2':
	        			$('label[for="approval_before_no_days_or_cutoff"]').html('No. of Cut-off');
	        			break;
	        		case '3':
						$('label[for="approval_before_no_days_or_cutoff"]').html('Not allowed').next().html('');
	        			break;	
	        		case '4':
						$('label[for="approval_before_no_days_or_cutoff"]').html('Within the Cut-off').next().html('');
	        			break;		        				        			
	        	}
	        	switch (response.approval_after_days_or_month) {
	        		case '1':
	        			$('label[for="approval_after_no_days_or_cutoff"]').html('No. of Days');
	        			break;		        		
	        		case '2':
	        			$('label[for="approval_after_no_days_or_cutoff"]').html('No. of Cut-off');
	        			break;
	        		case '3':
						$('label[for="approval_after_no_days_or_cutoff"]').html('Not allowed').next().html('');
	        			break;	
	        		case '4':
						$('label[for="approval_after_no_days_or_cutoff"]').html('Within the Cut-off').next().html('');
	        			break;	        					        			
	        	}

	        	switch (response.cancelation_before_days_or_month) {
	        		case '1':
	        			$('label[for="cancelation_before_no_days_or_cutoff"]').html('No. of Days');
	        			break;		        		
	        		case '2':
	        			$('label[for="cancelation_before_no_days_or_cutoff"]').html('No. of Cut-off');
	        			break;
	        		case '3':
						$('label[for="cancelation_before_no_days_or_cutoff"]').html('Not allowed').next().html('');
	        			break;	
	        		case '4':
						$('label[for="cancelation_before_no_days_or_cutoff"]').html('Within the Cut-off').next().html('');
	        			break;		        				        			
	        	}	
	        	switch (response.cancelation_after_days_or_month) {
	        		case '1':
	        			$('label[for="cancelation_after_no_days_or_cutoff"]').html('No. of Days');
	        			break;		        		
	        		case '2':
	        			$('label[for="cancelation_after_no_days_or_cutoff"]').html('No. of Cut-off');
	        			break;
	        		case '3':
						$('label[for="cancelation_after_no_days_or_cutoff"]').html('Not allowed').next().html('');
	        			break;	
	        		case '4':
						$('label[for="cancelation_after_no_days_or_cutoff"]').html('Within the Cut-off').next().html('');
	        			break;	        			        			
	        	}			        		        	        	
	        }
	    });
	}
})