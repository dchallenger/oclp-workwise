$(document).ready(function(){
	if( module.get_value('view') == "edit" ){
		$('#division_manager_id').chosen({allow_single_deselect: true }).change(function(){
			update_position_ddlb( $(this).val() );
		});

		$('#dm_position_id').chosen({allow_single_deselect: true }).change(function(){
			update_user_ddlb( $(this).val() );
		});
	}
});

function update_position_ddlb( user_id ){
	$.ajax({
		url: module.get_value('base_url') + module.get_value('module_link') + '/get_position',
		type:"POST",
		data: 'user_id='+user_id,
		dataType: "json",
		beforeSend: function(){
		},
		success: function(data){
			$('#dm_position_id').val(data.position_id).trigger("liszt:updated");
		}
	});
}

function update_user_ddlb( position_id ){
	$.ajax({
		url: module.get_value('base_url') + module.get_value('module_link') + '/get_user_via_position',
		type:"POST",
		data: 'position_id='+position_id,
		dataType: "json",
		beforeSend: function(){
		},
		success: function(data){
			$('#division_manager_id').val(data.user_id).trigger("liszt:updated");
		}
	});
}