$(document).ready(function() {
	
	$('#multiselect-position_id').css('width', '507px');
	var record = $('#record_id').val();

	if (record == -1) {
		// $('label[for="employee_id"]').parent().hide();
		$('label[for="employment_status_id"]').parent().hide();	
	};

	$("#planning_period_id").live("change", function(){
		 var planning_period_id = $(this).val();

       	 get_employees(planning_period_id, record);
    }); 

    $("#multiselect-employment_status_id").bind("multiselectclose", function(event, ui){
        var selected = $(this).val();
        
        var position_id = $("#multiselect-position_id").val();
        get_employees(position_id, selected, record);
            
    }); 
});


function get_employees (planning_period_id, record_id) {
	$.ajax({
		url: module.get_value('base_url') + 'appraisal/appraisal_planning_reminders' + '/get_employees',
		data: 'planning_period_id='+planning_period_id+'&record_id='+record_id,
		dataType: 'json',
		type:'POST',
		beforeSend: function() {
			$.blockUI({ message: '<div class="now-loading align-center"><img src="'+module.get_value('base_url')+user.get_value('user_theme')+'/images/loading.gif"><br />Loading, please wait...</div>' });
		},
		success: function(response) {
			$.unblockUI();
			if (response !== null) {
				$('#multiselect-employee_id option').remove();
 				$('#multiselect-employee_id').append(response.result);
				$('label[for="employee_id"]').parent().show();
                if (response.employees !== '') {
                    $.each(response.employees, function(index, values){
                        $('#multiselect-employee_id option[value="' + values + '"]').attr('selected','selected');
                    });
                };

                 $('#company').val(response.employee_company)
        	};
			$("#multiselect-employee_id").multiselect("refresh");
		}

	});
}