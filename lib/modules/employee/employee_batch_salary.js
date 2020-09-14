$(document).ready(function() {

	// $('#company_id').change(function() {
	// 	var company_id = $(this).val();
	// 	get_department(company_id);
	// });

	$('.new_salary').live('keyup', maskInteger);
	var record_id = $('#record_id').val();

	if (record_id !== '-1' && module.get_value('view') == 'edit')  {
		var department_id = $('#department_id').val();

		get_employees(department_id, record_id);
		get_employee_info($('#employee_id').val(), record_id);

	};

	if (record_id !== '-1' && module.get_value('view') == 'detail') {
		
		$.ajax({
	        url: module.get_value('base_url') + module.get_value('module_link') + '/get_status',
	        type: 'post',
	        dataType: 'json',
	        data: 'record_id='+ record_id,
	        beforeSend:function() {
	        },
	        success: function (response) {
	        	
	        	if (response.status === '1') {
	        		$(".icon-16-edit").parent().parent().remove();
	        		$(".or-cancel").find('.or').remove();
	        	};
	        }
	    });
	    
	    get_employee_info($('#employee_id').val(), record_id);
	};

	$('#department_id').change(function() {
		var department_id = $(this).val();
		get_employees(department_id, record_id);
	});

	$("#multiselect-employee_id").bind("multiselectclose", function(event, ui){
	    var selected = $(this).val();
	   	get_employee_info(selected, record_id);
	   		
	});	


});


function get_employees(department_id, record_id){
	$.ajax({
        url: module.get_value('base_url') + module.get_value('module_link') + '/get_employees',
        type: 'post',
        dataType: 'json',
        data: 'department=' + department_id +'&record_id='+ record_id,
        beforeSend:function() {
        },
        success: function (response) {
        	$('#multiselect-employee_id option').remove();
        	if (response !== null) {
				$('#multiselect-employee_id').append(response.result);
				if (record_id > 0) {
					$.each(response.employee_id, function(index, values){
						$('#multiselect-employee_id option[value="' + values + '"]').attr('selected','selected');
					});
				};
        	};
        	$('#multiselect-employee_id').multiselect("refresh");

        }
    });
}


function get_employee_info(employee, record_id){
		$.ajax({
	        url: module.get_value('base_url') + module.get_value('module_link') + '/get_employee_info',
	        type: 'post',
	        dataType: 'json',
	        data: 'employee=' + employee +'&record_id='+ record_id,
	        beforeSend: function(){
                $.blockUI({
                    message: '<div class="now-loading align-center"><img src="'+module.get_value('base_url')+user.get_value('user_theme')+'/images/loading.gif"><br />Loading, please wait...</div>'
                });                                    
            },
	        success: function (response) {
	        	$.unblockUI();
	        	$('#sal_adj').html(response.result);
	        }
		});	
}

function get_department(company_id){

     $.ajax({
        url: module.get_value('base_url') + module.get_value('module_link') + '/get_department',
        type: 'post',
        dataType: 'json',
        data: 'company_id=' + company_id,
        success: function (response) {
			$('#department_id').html(response.department);
			$('#department_id').chosen().trigger("liszt:updated");
        }
    });
}