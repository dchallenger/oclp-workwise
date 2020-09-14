$(document).ready(function(){

	if(module.get_value('view')=='edit')
	{
		$('h2').text('Survey');
		if(module.get_value('record_id')!='-1')
		{
			var rec_id="record_id="+module.get_value('record_id');
			$.ajax({
				url: module.get_value('base_url') + 'employee/employee_ufs_main/get_prev_info',
				data: rec_id,
				dataType: 'json',
				type: 'post',
				success: function (response) {
					if(response.msg_type=='no_record'){
						//alert('exam not yet taken');
					} else {
						period_date=response.data
						$('input[name="date_period_from"]').val(period_date.date_period_from);
						$('input[name="date_period_to"]').val(period_date.date_period_to);
					}
				}
			});
		}
		if(user.get_value('post_control') == 1) {
			$('#is_hr').val('1');
		}
		else
		{
			$('#fg-311').remove();
		}

		$('.icon-16-listback').parent().hide();
		$('.icon-16-disk-back').parent().hide();

		var send='employee_id='+user.get_value('user_id')+"&record_id="+module.get_value('record_id')+"&is_hr="+$('#is_hr').val();
		$.ajax({
			url: module.get_value('base_url') + 'employee/employee_ufs_main/is_done_with_survey',
			data: send,
			dataType: 'json',
			type: 'post',
			success: function (response) {
				if(response.msg_type=='no_record'){
					//alert('exam not yet taken');
				} 
				else if(response.msg_type=='not_yet_allowed')
				{
					if(user.get_value('post_control') != 1) 
						$('.is_taken').replaceWith('<span>You are not yet allowed to answer the survey</span>');
				}
				else {
					if(user.get_value('post_control') == 1) {
						//$('#fg-307').remove();
						$('.is_taken').replaceWith('<span>Survey Already Taken</span>');
					}
					else
					{
						$('.form-submit-btn').remove();
						$('.is_taken').replaceWith('<span>Survey Already Taken</span>');	
					}
				}
			}
		});
	}

	if(module.get_value('view')=="detail")
		$('.icon-16-edit').parent().remove();
	
});

function get_report()
{
	//$('#company').val()+' '+
	var send='record_id='+$('#record_id').val()+'&company_id='+$('#company').val()+'&department_id='+$('#department').val()+'&segment_1_id='+$('#segment_1').val()+'&segment_2_id='+$('#segment_2').val()+'&division_id='+$('#division').val();	
	$.ajax({
		url: module.get_value('base_url') + 'employee/employee_ufs_main/get_report',
		data: send,
		dataType: 'json',
		type: 'post',
		success: function (response) {
			// alert(response.html);
			if(response.msg_type=='error'){
				$('#message-container').html(message_growl(response.msg_type, response.msg));
			} else {
				$('.put_me_here').children().remove();
				$('.prev').remove();
				$('.put_me_here').append(response.html);

			}
		}
	});
}

function showboxy(msg)
{
	Boxy.alert(msg);
}

