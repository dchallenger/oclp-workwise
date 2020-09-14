$(document).ready(function () {
	$('#multiselect-city_id, #multiselect-location_id').bind('multiselectclose', function () {
		$('#city_id').val($("#multiselect-city_id").multiselect("getChecked").map(function(){
			   		return this.value;	
				}).get());

		$('#location_id').val($("#multiselect-location_id").multiselect("getChecked").map(function(){
				   return this.value;	
				}).get());

		var data = $('#city_id, #location_id, #record_id').serialize();

		$.ajax({
			url: module.get_value('base_url') + module.get_value('module_link') + '/filter_employees',
			data: data,
			type: 'post',
			dataType: 'html',
			success: function(response) {				
				$('#multiselect-employee_id option').remove();				
				$('#multiselect-employee_id').append(response);				
				$('#multiselect-employee_id').multiselect('refresh');
			}
		});
	});

	if (module.get_value('view') == 'edit') {
		$('#multiselect-city_id, #multiselect-location_id').trigger('multiselectclose');
	}

	$('.process-credits').live('click', function () {
		$.ajax({
			url: module.get_value('base_url') + module.get_value('module_link') + '/update_leave_credits',
			data: 'record_id=' + $(this).parents('tr').attr('id'),
			dataType: 'json',
			type: 'post',
			beforeSend: function(){
				$.blockUI({
					message: '<div class="now-loading align-center"><img src="'+module.get_value('base_url')+user.get_value('user_theme')+'/images/loading.gif"><br />Processing, please wait...</div>'
				});
			},
			success: function(response){
				$.unblockUI();
				if(response.msg != "") $('#message-container').html(message_growl(response.msg_type, response.msg));

				$('#jqgridcontainer').jqGrid().trigger("reloadGrid");
			}
		});
	});
});