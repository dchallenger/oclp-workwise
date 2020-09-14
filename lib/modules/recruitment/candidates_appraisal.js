$(document).ready(function () {
	$('#interviewer_id_post').val($('select[name="interviewer_id"]').val());	
	$('#final_interviewer_id_post').val($('select[name="final_interviewer_id"]').val());
	
	$('#screening_datetime').change(function () {
		$('#screening_datetime_post').val($(this).val());
	});		

	$('select[name="interviewer_id"]').change(function () {
		$('#interviewer_id_post').val($(this).val());
	});

	$('select[name="final_interviewer_id"]').change(function () {
		$('#final_interviewer_id_post').val($(this).val());
	});

	$('#final_datetime').change(function () {
		$('#final_datetime_post').val($(this).val());
	});	

	$('#final_datetime,#screening_datetime').datetimepicker({                            
                changeMonth: true,
                changeYear: true,
                showOtherMonths: true,
                showButtonPanel: true,
                showAnim: 'slideDown',
                selectOtherMonths: true,
                showOn: "both",
                buttonImage: module.get_value('base_url') + user.get_value('user_theme') + "/icons/calendar-month.png",
                buttonImageOnly: true,  
                buttonText: '',                    
                hourGrid: 4,
                minuteGrid: 10,
                timeFormat: 'hh:mm tt',
                ampm: true,
                yearRange: 'c-90:c+10'                        
            });		
		//$('select[name="final_interviewer_id"],  #final_datetime').attr('disabled', 'disabled');	
});

function save(param1, param2) {
	$('.required').each(function (i,obj){
		var label_elem = $(obj).parent().parent().children('label');
		var elem_id = $(label_elem).attr("for");
		var elem_message = $(label_elem).attr("title");
		if ($(obj).val() == ""){
			add_error(elem_id, elem_message, "This field is mandatory.");
		}
		else{
			if ($(obj).hasClass('numeric')){
				if (!$.isNumeric($(obj).val())){
					add_error(elem_id, elem_message, "This field should in numeric value.");
				}				
			}
		}
	});

	ok_to_save = validate_form();

	if( ok_to_save ){
		ajax_save(param1, param2);
	}
}

function validate_form()
{
	
	//errors
	if(error.length > 0){
		var error_str = "Please correct the following errors:<br/><br/>";
		for(var i in error){
			if(i == 0) $('#'+error[i][0]).focus(); //set focus on the first error
			error_str = error_str + (parseFloat(i)+1) +'. '+error[i][1]+" - "+error[i][2]+"<br/>";
		}
		$('#message-container').html(message_growl('error', error_str));
		
		//reset errors
		error = new Array();
		error_ctr = 0
		return false;
	}
	
	//no error occurred
	return true;
}