$(document).ready(function(){
	$( '#health_type' ).change(function(){
		if( $( '#health_type' ).val() != '' ){
			var health_type = $( '#health_type' ).val();
			$.ajax({
				url: module.get_value('base_url') + module.get_value('module_link') + '/get_statUs_ddlb',
				type:"POST",
				data: 'health_type='+health_type,
				dataType: "json",
				success: function(data){
					var health_type_status_id = $('#health_type_status_id').val();
					$('#health_type_status_id').html('');
					$('#health_type_status_id').html(data.ddlb);
					$('#health_type_status_id').val(health_type_status_id);
				}
			});
		}
	});
	$( '#health_type' ).trigger('change');
});