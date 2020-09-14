$(document).ready(function () {
	if (module.get_value('view') == 'edit') {
		rt_edit_functions();
	} else if (module.get_value('view') == 'index') {
		rt_list_functions();
	}
});

function rt_edit_functions()
{
	if ($('#record_id').val() == '-1') {
		$('#employee_id').val(user.get_value('user_id'));
	}

	$('.col-2-form')
		.append('<div class="clear"></div><div class="spacer"></div><small>* I understand that if I cannot attend this conference as scheduled, I may be held accountable for any non-refundable fees incurred by HDI.</small>');
}

function rt_list_functions()
{
	$('.approve-single').live('click', function () {
		change_training_request_status($(this).parents('tr').attr('id'), 1);
	});

	$('.reject-single').live('click', function () {
		change_training_request_status($(this).parents('tr').attr('id'), 0);
	});
}

function change_training_request_status(record_id, status)
{
	$.ajax({
		url: module.get_value('base_url') + module.get_value('module_link') + '/change_status',
		data: 'record_id=' + record_id + '&approved=' + status,
		type: 'post',
		dataType: 'json',
		success: function (data) {
			$('#message-container').html(message_growl(data.msg_type, data.msg));
			$('#jqgridcontainer').jqGrid().trigger("reloadGrid");
		}
	})
}