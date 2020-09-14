$(document).ready(function(){

	window.onload = function(){

		$('#date-temp').datepicker("option", "maxDate", new Date());
		$('#time').datepicker("option", "maxDate", new Date());

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

		$('#date_created-temp').datepicker().datepicker('disable');

			var curr_date = $.datepicker.formatDate('mm/dd/yy', new Date());
			$("#date_created").val(curr_date);
			$("#date_created-temp").val(curr_date);


		$('#date-temp').change(function(){
			$.ajax({
				url: module.get_value('base_url') + module.get_value('module_link') + '/validation_check',
				data: 'date=' + $('#date').val() + '&time=' + $('#time').val() + '&time_set_id=' + $('#time_set_id').val() + '&employee_id=' + $('#employee_id').val(),
				dataType: 'json',
				type: 'post',
				async: false,
				beforeSend: function(){
				
				},								
				success: function ( response ) {
					var actualDate = new Date($('#date').val()); // convert to actual date
					var newDate = new Date(actualDate.getFullYear(), actualDate.getMonth(), actualDate.getDate()+1); // create new increased date
					$('#time').datepicker("option", "maxDate", newDate);
					if (response.err){
						$('#message-container').html(message_growl('attention', response.msg_type))					
						$('#date-temp').val("");
						$('#date').val("");						
					}
				}
			});
			$('input[name="time"]').datetimepicker( "setDate", $('#date-temp').val());
		});

		$('input[name="time"]').datetimepicker({
			changeMonth: true,
			changeYear: true,
			showOtherMonths: true,
			showButtonPanel: true,
			showAnim: 'slideDown',
			selectOtherMonths: true,
			showOn: "both",
			buttonImage: module.get_value('base_url') + user.get_value('user_theme') + "/icons/calendar-month.png",
			buttonImageOnly: true,
			buttonText: '',
			hourGrid: 4,
			minuteGrid: 10,
			timeFormat: 'hh:mm tt',
			ampm: true,
			yearRange: 'c-90:c+10',
			onClose: function(dateText) {
				$.ajax({
					url: module.get_value('base_url') + module.get_value('module_link') + '/validation_check',
					data: 'date=' + $('#date').val() + '&time=' + $('#time').val() + '&time_set_id=' + $('#time_set_id').val() + '&employee_id=' + $('#employee_id').val(),
					dataType: 'json',
					type: 'post',
					async: false,
					beforeSend: function(){
					
					},								
					success: function ( response ) {
						if (response.err){
							$('#message-container').html(message_growl('error', response.msg_type))					
							$('#date-temp').val("");
							$('#date').val("");		
							$('input[name="time"]').val("");				
						}
					}
				});
			} 		
		}); 

	}
});