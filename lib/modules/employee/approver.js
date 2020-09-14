$(document).ready(function(){
	if(module.get_value('view') == "edit"){
		$('label[for="employee_id"]').next().find('span.icon-group').remove();
		$('label[for="company_id"]').next().find('span.icon-group').remove();
		$('label[for="position_id"]').next().find('span.icon-group').remove();
	}
});

function delete_approver( employee_approver_id )
{
	Boxy.ask("Delete selected approver?", ["Yes", "Cancel"],
	function( choice ) {
		if(choice == "Yes"){
			$.ajax({
				url: module.get_value('base_url')+"employee/approver_detail/delete",
				type:"POST",
				dataType: "json",
				data: 'record_id='+employee_approver_id,
				success: function(data){
					$('#message-container').html(message_growl(data.msg_type, data.msg));
					$('.approver-'+employee_approver_id).remove();
				}
			}); 
		}
	},
	{
		title: "Delete approver"
	});
}

function quickedit_boxy_callback(){
	$.ajax({
				url: module.get_value('base_url') + module.get_value('module_link') + "/get_approvers",
				type:"POST",
				dataType: "json",
				data: 'record_id='+module.get_value('record_id'),
				success: function(data){
					$('.approvers-container').html(data.approvers);
				}
			}); 	
}

function export_list()
{
	// $('#export-form').attr('action', $('#export_link').val());
	// $('#export-form').submit();
	// var options = get_employee_list();

	// var option = "";

	$.ajax({
		url: module.get_value('base_url') + module.get_value('module_link') + '/export',
		type: 'post',
		beforeSend: function(){
			$.blockUI({ message: '<div class="now-loading align-center"><img src="'+module.get_value('base_url')+user.get_value('user_theme')+'/images/loading.gif"><br />Loading Employees, please wait...</div>'});
		},		
		success: function(response)	{
			var path = "/"+response.data;
            window.location = module.get_value('base_url')+path;
            $.unblockUI();
		}
	});

	// $('#export-form').attr('action', '');
	// return false;
}