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

	if(module.get_value('view') == "edit"){
		$('#datelate-temp').change(function(){
			$.ajax({
				url: module.get_value('base_url') + module.get_value('module_link') + '/validation_check',
				data: 'datelate=' + $('#datelate').val() + '&employee_id=' + $('#employee_id').val() + '&timelate=' + $('#time').val(),
				dataType: 'json',
				type: 'post',
				async: false,
				beforeSend: function(){
				
				},								
				success: function ( response ) {
					if (response.err){
						$('#message-container').html(message_growl('error', response.msg_type))					
						$('#datelate-temp').val("");
						$('#datelate').val("");						
					}
				}
			});
		});

		$('#employee_id,#datelate-temp').live('change',function(){
			var employee_id = $('#employee_id').val();
			var date = $('#datelate').val();
			if (employee_id != "" && date != ""){
				$.ajax({
					url: module.get_value('base_url') + module.get_value('module_link') + '/get_worksched',
					data: 'employee_id=' + employee_id + '&date=' + $('#datelate').val(),
					dataType: 'json',
					type: 'post',
					async: false,
					beforeSend: function(){
					
					},								
					success: function (response) {

						$('label[for="time_difference"]').parent().parent().find('.shift_schedule').remove();
						$('label[for="time_difference"]').parent().after('<div class="form-item even shift_schedule"><label>Shift Schedule:</label><br /><span style="color: #B5121B">'+ response.shift +'</span></div>');								
					}
				});	
			}	
		})

		$('input[name="time"]').timepicker({
			showAnim: 'slideDown',
			selectOtherMonths: true,
			showOn: "both",
			buttonImage: module.get_value('base_url') + user.get_value('user_theme') + "/icons/calendar-month.png",
			buttonImageOnly: true,
			buttonText: '',
			hourGrid: 4,
			minuteGrid: 10,
			ampm: true,
			onClose: function(){
				var time = $(this).val();
				var employee_id = $('#employee_id').val();
				var date = $('#datelate').val();
				if (employee_id != "" && date != "" && time != ""){
					$.ajax({
						url: module.get_value('base_url') + module.get_value('module_link') + '/get_worksched',
						data: 'employee_id=' + employee_id + '&date=' + $('#datelate').val() + '&time=' + time,
						dataType: 'json',
						type: 'post',
						async: false,
						beforeSend: function(){
						
						},								
						success: function (response) {
							$('#time_difference').val(response.time_diff);
						}
					});	
				}					
			}
		}); 	
	}

	if(module.get_value('view') == "detail"){
		$.ajax({
			url: module.get_value('base_url') + module.get_value('module_link') + '/get_worksched_detail',
			data: 'record_id=' + $('#record_id').val(),
			dataType: 'json',
			type: 'post',
			async: false,
			beforeSend: function(){
			
			},								
			success: function (response) {
				var shift_html = '<div class="form-item view odd">';
				shift_html = shift_html +	'<label class="label-desc view gray" for="shift_html">';
				shift_html = shift_html +	'Shift :';
				shift_html = shift_html +	'</label>';		
				shift_html = shift_html +	'<div class="text-input-wrap">';
				shift_html = shift_html +	response.shift;
				shift_html = shift_html +	'</div></div>';
				$('label[for="form_status_id"]').parent().after(shift_html);								
			}
		});	
	}	
	//$('<div class="form-item even"><label>Shift Schedule</label></div>').insertAfter('label[for="time"]').parent();
});