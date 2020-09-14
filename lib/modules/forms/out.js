$(document).ready(function(){

	window.onload = function(){

		if( module.get_value('view') == 'index' ){

			if( $('ul#grid-filter li').length > 0 ){

	            $('ul#grid-filter li').each(function(){ 

	                if( $(this).hasClass('active') ){

	                    if($(this).attr('filter') == 'for_approval'){
	                       $('.status-buttons').parent().show();
	                    }
	                    else{
	                       $('.status-buttons').parent().hide();
	                    }

	                }
	            });

	        }
	        else{
	            $('.status-buttons').parent().hide();
	        }

		}

	}
	
	
	$('input[name="time_start"],input[name="time_end"]').timepicker({
		showAnim: 'slideDown',
		showOn: "both",
		timeFormat: 'hh:mm tt',
		buttonImage: module.get_value('base_url') + user.get_value('user_theme') + "/icons/calendar-month.png",
		buttonImageOnly: true,
		buttonText: '',
		hourGrid: 4,
		minuteGrid: 10,
		ampm: true,
		onClose: function(){
			var element_id = $(this).attr('id');
			var parts = element_id.split('_');
			parts.pop();
			var identifier = parts.join('_');
			var start_time = $(this).val();
			var end_time = $('#' + identifier + '_end').val();
			var start_time_parts = start_time.split(' ');
			var end_time_parts = end_time.split(' ');
			var start_hh_mm = start_time_parts[0].split(':');
			var end_hh_mm = end_time_parts[0].split(':');
			var start;
			var end;
			if (end_time != '')
			{
				if (start_time_parts[1] == 'am' && start_hh_mm[0] == '12') {
					start_hh_mm[0] = '00';
				}
				else if (start_time_parts[1] == 'pm') {
					start_hh_mm[0] = parseInt(start_hh_mm[0], 10) + 12;
				}
				if (end_time_parts[1] == 'am' && end_hh_mm[0] == '12') {
					end_hh_mm[0] = '00';
				}
				else if (end_time_parts[1] == 'pm') {
					end_hh_mm[0] = parseInt(end_hh_mm[0], 10) + 12;
				}
			}

			if ($('#employee_id').val() == "" || $('#date').val() == "" || $('#time_start').val() == "" || $('#time_end').val() == ""){
				return;
			}

			$.ajax({
				url: module.get_value('base_url') + module.get_value('module_link') + '/validation_check',
				data: 'date=' + $('#date').val() + '&employee_id=' + $('#employee_id').val() + '&time_start=' + $('#time_start').val() + '&time_end=' + $('#time_end').val(),
				type: 'post',
				async: false,
				beforeSend: function(){
				
				},								
				success: function ( response ) {
					if (response.err){
						$('#message-container').html(message_growl('error', response.msg_type))										
					}
				}
			});			
		}
	}); 	


	if(module.get_value('view') == "edit"){
		$('#date-temp').live("change", function(){
			$.ajax({
				url: module.get_value('base_url') + module.get_value('module_link') + '/get_worksched',
				data: 'date=' + $('#date').val() + '&employee_id=' + $('#employee_id').val(),
				dataType: 'json',
				type: 'post',
				async: false,
				beforeSend: function(){
				
				},								
				success: function ( data ) {
					$('#time_end').val(data.shift.shifttime_end);
					$('#time_start').val(data.shift.shifttime_start);

					$('#date-temp').change(function(){
						$.ajax({
							url: module.get_value('base_url') + module.get_value('module_link') + '/validation_check',
							data: 'date=' + $('#date').val() + '&employee_id=' + $('#employee_id').val() + '&time_start=' + $('#time_start').val() + '&time_end=' + $('#time_end').val(),
							type: 'post',
							async: false,
							beforeSend: function(){
							
							},								
							success: function ( response ) {
								if (response.err){
									$('#message-container').html(message_growl('error', response.msg_type))										
								}
							}
						});
					});					
				}
			});
		});				
	}
});