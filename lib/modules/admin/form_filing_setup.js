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

	if (module.get_value('view') == "edit"){
		// $('label[for="filing_before_days_or_month"]').next().append('<input id="filing_before_days_or_month_not_allowed" class="input-radio" type="radio" value="2" name="filing_before_days_or_month"><label class="check-radio-label gray" for="filing_before_days_or_month-no">Not allowed</label>');
		// $('label[for="filing_after_days_or_month"]').next().append('<input id="filing_after_days_or_month_not_allowed" class="input-radio" type="radio" value="2" name="filing_after_days_or_month"><label class="check-radio-label gray" for="filing_after_days_or_month-no">Not allowed</label>');
		// $('label[for="approval_before_days_or_month"]').next().append('<input id="approval_before_days_or_month_not_allowed" class="input-radio" type="radio" value="2" name="approval_before_days_or_month"><label class="check-radio-label gray" for="approval_before_days_or_month-no">Not allowed</label>');
		// $('label[for="approval_after_days_or_month"]').next().append('<input id="approval_after_days_or_month_not_allowed" class="input-radio" type="radio" value="2" name="approval_after_days_or_month"><label class="check-radio-label gray" for="approval_after_days_or_month-no">Not allowed</label>');
		// $('label[for="cancelation_before_days_or_month"]').next().append('<input id="cancelation_before_days_or_month_not_allowed" class="input-radio" type="radio" value="2" name="cancelation_before_days_or_month"><label class="check-radio-label gray" for="cancelation_before_days_or_month-no">Not allowed</label>');
		// $('label[for="cancelation_after_days_or_month"]').next().append('<input id="cancelation_after_days_or_month_not_allowed" class="input-radio" type="radio" value="2" name="cancelation_after_days_or_month"><label class="check-radio-label gray" for="cancelation_after_days_or_month-no">Not allowed</label>');

		$('input[name="with_attachment"]').live('change',function(){
			if ($(this).val() == 1){
				$('label[for="no_days_required_attachment"]').parent().show();
			}
			else{
				$('#no_days_required_attachment').val('');
				$('label[for="no_days_required_attachment"]').parent().hide();
			}
		})

		$('#filing_before_days_or_month').live('change',function(){

			$('#filing_before_no_days_or_cutoff').attr('readonly',false);
			if ($(this).val() == 1){
				$('label[for="filing_before_no_days_or_cutoff"]').html('No. of Day(s):');
			}		
			else if($(this).val() == 2){
				$('label[for="filing_before_no_days_or_cutoff"]').html('No. of Cut-off:');
			}
			else{
				$('label[for="filing_before_no_days_or_cutoff"]').html('&nbsp;');
				$('#filing_before_no_days_or_cutoff').val('');
				$('#filing_before_no_days_or_cutoff').attr('readonly',true);
			}
		})	

		$('#filing_after_days_or_month').live('change',function(){
			$('#filing_after_no_days_or_cutoff').attr('readonly',false);
			if ($(this).val() == 1){
				$('label[for="filing_after_no_days_or_cutoff"]').html('No. of Day(s):');
			}
			else if ($(this).val() == 2){
				$('label[for="filing_after_no_days_or_cutoff"]').html('No. of Cut-off:');		
			}			
			else{
				$('label[for="filing_after_no_days_or_cutoff"]').html('&nbsp;');
				$('#filing_after_no_days_or_cutoff').val('');
				$('#filing_after_no_days_or_cutoff').attr('readonly',true);		
			}
		})

		$('#approval_before_days_or_month').live('change',function(){
			$('#approval_before_no_days_or_cutoff').attr('readonly',false);
			if ($(this).val() == 1){
				$('label[for="approval_before_no_days_or_cutoff"]').html('No. of Day(s):');
			}
			else if ($(this).val() == 2){
				$('label[for="approval_before_no_days_or_cutoff"]').html('No. of Cut-off:');			
			}			
			else{
				$('label[for="approval_before_no_days_or_cutoff"]').html('&nbsp;');
				$('#approval_before_no_days_or_cutoff').val('');
				$('#approval_before_no_days_or_cutoff').attr('readonly',true);
			}
		})	

		$('#approval_after_days_or_month').live('change',function(){
			$('#approval_after_no_days_or_cutoff').attr('readonly',false);
			if ($(this).val() == 1){
				$('label[for="approval_after_no_days_or_cutoff"]').html('No. of Day(s):');
			}
			else if ($(this).val() == 2){
				$('label[for="approval_after_no_days_or_cutoff"]').html('No. of Cut-off:');				
			}				
			else{
				$('label[for="approval_after_no_days_or_cutoff"]').html('&nbsp;');
				$('#approval_after_no_days_or_cutoff').val('');
				$('#approval_after_no_days_or_cutoff').attr('readonly',true);	
			}
		})		

		$('#cancelation_before_days_or_month').live('change',function(){
			$('#cancelation_before_no_days_or_cutoff').attr('readonly',false);
			if ($(this).val() == 1){
				$('label[for="cancelation_before_no_days_or_cutoff"]').html('No. of Day(s):');
			}
			else if ($(this).val() == 2){
				$('label[for="cancelation_before_no_days_or_cutoff"]').html('No. of Cut-off:');			
			}			
			else{
				$('label[for="cancelation_before_no_days_or_cutoff"]').html('&nbsp;');
				$('#cancelation_before_no_days_or_cutoff').val('');
				$('#cancelation_before_no_days_or_cutoff').attr('readonly',true);
			}
		})	

		$('#cancelation_after_days_or_month').live('change',function(){
			$('#cancelation_after_no_days_or_cutoff').attr('readonly',false);
			if ($(this).val() == 1){
				$('label[for="cancelation_after_no_days_or_cutoff"]').html('No. of Day(s):');
			}
			else if ($(this).val() == 2){
				$('label[for="cancelation_after_no_days_or_cutoff"]').html('No. of Cut-off:');			
			}				
			else{
				$('label[for="cancelation_after_no_days_or_cutoff"]').html('&nbsp;');
				$('#cancelation_after_no_days_or_cutoff').val('');
				$('#cancelation_after_no_days_or_cutoff').attr('readonly',true);	
			}
		})	

		if (module.get_value('record_id') != -1){
			$.ajax({
		        url: module.get_value('base_url') + module.get_value('module_link') + '/get_record',
		        type: 'post',
		        dataType: 'json',
		        data: 'record_id='+ $('#record_id').val(),
		        beforeSend:function() {
		        },
		        success: function (response) {
		        	switch (response.filing_before_days_or_month) {
		        		case '2':
		        			$('label[for="filing_before_no_days_or_cutoff"]').html('No. of Cut-off:');
		        			break;		        		
		        		case '1':
		        			$('label[for="filing_before_no_days_or_cutoff"]').html('No. of Day(s):');
		        			break;
		        		case '3':
		        			$('label[for="filing_before_no_days_or_cutoff"]').html('');
		        			break;		        			
		        	}
		        	switch (response.filing_after_days_or_month) {
		        		case '2':
		        			$('label[for="filing_after_no_days_or_cutoff"]').html('No. of Cut-off:');
		        			break;		        		
		        		case '1':
		        			$('label[for="filing_after_no_days_or_cutoff"]').html('No. of Day(s):');
		        			break;
		        		case '3':
		        			$('label[for="filing_after_no_days_or_cutoff"]').html('');
		        			break;		        			
		        	}

		        	switch (response.approval_before_days_or_month) {
		        		case '2':
		        			$('label[for="approval_before_no_days_or_cutoff"]').html('No. of Cut-off:');
		        			break;		        		
		        		case '1':
		        			$('label[for="approval_before_no_days_or_cutoff"]').html('No. of Day(s):');
		        			break;
		        		case '3':
		        			$('label[for="approval_before_no_days_or_cutoff"]').html('');
		        			break;		        			
		        	}
		        	switch (response.approval_after_days_or_month) {
		        		case '2':
		        			$('label[for="approval_after_no_days_or_cutoff"]').html('No. of Cut-off:');
		        			break;		        		
		        		case '1':
		        			$('label[for="approval_after_no_days_or_cutoff"]').html('No. of Day(s):');
		        			break;
		        		case '3':
		        			$('label[for="approval_after_no_days_or_cutoff"]').html('');
		        			break;		        			
		        	}

		        	switch (response.cancelation_before_days_or_month) {
		        		case '2':
		        			$('label[for="cancelation_before_no_days_or_cutoff"]').html('No. of Cut-off:');
		        			break;		        		
		        		case '1':
		        			$('label[for="cancelation_before_no_days_or_cutoff"]').html('No. of Day(s):');
		        			break;
		        		case '3':
		        			$('label[for="cancelation_before_no_days_or_cutoff"]').html('');
		        			break;		        			
		        	}	
		        	switch (response.cancelation_after_days_or_month) {
		        		case '2':
		        			$('label[for="cancelation_after_no_days_or_cutoff"]').html('No. of Cut-off:');
		        			break;		        		
		        		case '1':
		        			$('label[for="cancelation_after_no_days_or_cutoff"]').html('No. of Day(s):');
		        			break;
		        		case '3':
		        			$('label[for="cancelation_after_no_days_or_cutoff"]').html('');
		        			break;		        			
		        	}			        		        	
/*		        	if (response.filing_before_days_or_month == 2) 
		        		$('#filing_before_days_or_month').prop("checked", true)
		        	if (response.filing_after_days_or_month == 2) 
		        		$('#filing_after_days_or_month').prop("checked", true)		        	
		        	if (response.approval_before_days_or_month == 2) 
		        		$('#approval_before_days_or_month').prop("checked", true)
		        	if (response.approval_after_days_or_month == 2) 
		        		$('#approval_after_days_or_month').prop("checked", true)		        	
		        	if (response.cancelation_before_days_or_month == 2) 
		        		$('#cancelation_before_days_or_month').prop("checked", true)
		        	if (response.cancelation_after_days_or_month == 2) 
		        		$('#cancelation_after_days_or_month').prop("checked", true)	*/	        	
		        }
		    });
		}						
	}
})