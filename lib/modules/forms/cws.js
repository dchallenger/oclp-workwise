$(document).ready(function(){
	if( module.get_value('module_link') == 'employee/dtr' ){
		//delay a little for boxy to load everything before binding events
		var x = setTimeout('bindCWSEvents();', 100);
	}
	else{
		bindCWSEvents();
	}

	
});

function bindCWSEvents(){
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

	if( module.get_value('view') == "edit" || module.get_value('module_link') == 'employee/dtr' ){
		if (module.get_value('module_link') == 'employee/dtr') {
			 $('input[name="employee_id"]').siblings('span.icon-group').remove();
		};
	       
		$( 'input[name="date-temp-from"],input[name="date-temp-to"]' ).change(function(){
			var date_from = $('#date_from').val();
			var date_to = $('#date_to').val();				
			if (date_from != '' && date_to != ''){								
				$.ajax({
					url: module.get_value('base_url') + module.get_value('module_link') + '/validation_check',
					data: 'date_from=' + $('#date_from').val() + '&date_to=' + $('#date_to').val() + '&employee_id=' + $('#employee_id').val(),
					dataType: 'json',
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
		})		
		disable_field( $('#current_shift_calendar_id') );

		$('#employee_id').change(function(){
			if( $('#employee_id').val() != '' ){
				$.ajax({
				 	url: module.get_value('base_url') + module.get_value('module_link') + '/get_employee_sched',
				 	type: 'POST',
				 	dataType: "json",
				 	data: 'employee_id='+$('#employee_id').val(),
	           		success: function (data) {
						$('#current_shift_calendar_id').val(data.emp.shift_calendar_id);
					}
				});
			}
		});
		$('#employee_id').trigger('change');
	}
}