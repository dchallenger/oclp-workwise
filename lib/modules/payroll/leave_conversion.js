$(document).ready(function(){
	if( module.get_value('view') == 'edit' ){
		if( module.get_value('record_id') == '-1' ){
			//set year to current year
			var year = new Date().getFullYear();
			$('#year').val(year);
			update_employee_dropdown();
		}

		$('#multiselect-employee_id').multiselect({
			close: function(){
				var temp = $.map($('#multiselect-employee_id').multiselect("getChecked"),function( input ){
					return input.value;
				});
				$('#employee_id').val(temp);
				get_leave_converts();
			}
		});	

		$('#application_form_id').change(function(){
			$('tr.employee-row').remove();
			get_leave_converts();	
		});

		$('#year').change(function(){
			$('tr.employee-row').remove();
			get_leave_converts();	
		})

	}
});

function update_employee_dropdown(){
	var year = $('#year').val();
	$.ajax({
		url: module.get_value('base_url') + module.get_value('module_link') + '/get_employee_list',
		data: 'year='+year,
		type: 'post',
		dataType: 'json',
		success: function (data) {
			$('#multiselect-employee_id option').remove();
			$('#multiselect-employee_id').append( data.option );
			$('#multiselect-employee_id').multiselect('refresh');				
		}
	});
}

function get_leave_converts(){
	var employee_id = $('#employee_id').val();
	employee_id = employee_id.split(',');
	for(var i in employee_id ){
		if( $('#year').val() !='' && $('#application_form_id').val() != '' ){
			if( employee_id[i] != '' && $('tr#employee-'+employee_id[i]).length == 0 ) {
				$.ajax({
					url: module.get_value('base_url') + module.get_value('module_link') + '/get_convert_form',
					data: 'year='+$('#year').val()+'&application_form_id='+$('#application_form_id').val()+'&employee_id='+employee_id[i],
					type: 'post',
					dataType: 'json',
					success: function (data) {
						$('#listview-list').append( data.convert_form )				
					}
				});
			}
		}
	}		
}

function delete_employee_row(employee_id){
	$("#multiselect-employee_id option[value="+employee_id+"]").attr('selected', false);
	$('#multiselect-employee_id').multiselect('refresh');
	$('tr#employee-'+employee_id).remove();
}