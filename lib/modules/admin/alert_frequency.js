$( document ).ready( function() {
    
	window.onload = function(){

		if( module.get_value('view') == 'edit' ){

			if( $('#crontask_function_id').val() > 0 ){
				get_function_variables();
			}

			if( module.get_value('record_id') == '-1' ){

				$('input[name="minute"]').val('');
				$('input[name="hour"]').val('');
				$('input[name="time"]').val('');

				$('label[for="time"]').parent().hide();
				$('label[for="hour"]').parent().hide();
				$('label[for="minute"]').parent().hide();

			}
			else{

				if( $('#hour_implement_type_id').val() == 1 ){

					$('label[for="time"]').parent().show();
					$('input[name="hour"]').val('');
					$('label[for="hour"]').parent().hide();
					$('input[name="minute"]').val('');
					$('label[for="minute"]').parent().hide();

				}
				else if( $('#hour_implement_type_id').val() == 2 ){

					$('input[name="time"]').val('');
					$('label[for="time"]').parent().hide();
					$('label[for="hour"]').parent().show();
					$('label[for="minute"]').parent().show();

				}
				else{

					$('input[name="minute"]').val('');
					$('input[name="hour"]').val('');
					$('input[name="time"]').val('');

					$('label[for="time"]').parent().hide();
					$('label[for="hour"]').parent().hide();
					$('label[for="minute"]').parent().hide();

				}


			}

		}

		if( module.get_value('view') == 'detail' ){

			get_function_variables();
			get_record_info();

		}

	}

	$('#hour_implement_type_id').live('change',function(){

		if( $('#hour_implement_type_id').val() == 1 ){

			$('label[for="time"]').parent().show();
			$('input[name="hour"]').val('');
			$('label[for="hour"]').parent().hide();
			$('input[name="minute"]').val('');
			$('label[for="minute"]').parent().hide();

		}
		else if( $('#hour_implement_type_id').val() == 2 ){

			$('input[name="time"]').val('');
			$('label[for="time"]').parent().hide();
			$('label[for="hour"]').parent().show();
			$('label[for="minute"]').parent().show();

		}
		else{

			$('input[name="minute"]').val('');
			$('input[name="hour"]').val('');
			$('input[name="time"]').val('');

			$('label[for="time"]').parent().hide();
			$('label[for="hour"]').parent().hide();
			$('label[for="minute"]').parent().hide();

		}

	});

	$('#crontask_function_id').live('change',function(){

		get_function_variables();

	});

});

function get_record_info(){

	$.ajax({
	        url: module.get_value('base_url') + module.get_value('module_link') + '/get_record_info',
	        data: 'record_id=' + $('#record_id').val(),
	        type: 'post',
	        dataType: 'json',
	        success: function(data) {

	        	if( data.implement_type == 1 ){

	        		$('label[for="time"]').parent().show();
					$('label[for="hour"]').parent().hide();
					$('label[for="minute"]').parent().hide();

	        	}
	        	else if( data.implement_type == 2 ){

	        		$('label[for="time"]').parent().hide();
					$('label[for="hour"]').parent().show();
					$('label[for="minute"]').parent().show();

	        	}
	        	else{

	        		$('label[for="time"]').parent().hide();
					$('label[for="hour"]').parent().hide();
					$('label[for="minute"]').parent().hide();

	        	}

	        }
	});


}

function get_function_variables(){

	$.ajax({
        url: module.get_value('base_url') + module.get_value('module_link') + '/get_function_variables',
        dataType: 'html',
        type:"POST",
        data: 'function=' + $('#crontask_function_id').val() + '&record_id=' + $('#record_id').val() + '&view=' + module.get_value('view'),
        success: function (response) {

        	$('#alert_frequency_variables fieldset').html(response);

        }
    });

}