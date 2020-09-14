$(document).ready(function(){
	$('#record_id').live('change', quickedit_boxy_callback);
});

function edit_leave_setup( leave_setup_id, employee_type_id ){
	var data = "record_id="+leave_setup_id+"&employee_type_id="+employee_type_id;
	showQuickEditForm( module.get_value('base_url') + "admin/leave_setup/quick_edit", data)
}

function delete_leave_setup( leave_setup_id )
{
	Boxy.ask("Delete selected leave setup?", ["Yes", "Cancel"],
	function( choice ) {
		if(choice == "Yes"){
			$.ajax({
				url: module.get_value('base_url')+"admin/leave_setup/delete",
				type:"POST",
				dataType: "json",
				data: 'record_id='+leave_setup_id,
				success: function(data){
					$('#message-container').html(message_growl(data.msg_type, data.msg));
					$('#leavesetup-'+leave_setup_id).remove();
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
		url: module.get_value('base_url') + module.get_value('module_link') + "/get_leave_setup",
		type:"POST",
		dataType: "json",
		data: 'record_id='+module.get_value('record_id'),
		success: function(data){
			$('#leavesetup-container').html(data.leave_setup);
		}
	}); 	
}