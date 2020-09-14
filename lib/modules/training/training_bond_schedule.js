$(document).ready(function () {

	if( module.get_value('view') == 'edit' ){

		window.onload = function(){

			$('label[for="training_cost_from"]').html('Training Cost From:<span class="red font-large">*</span>');
			$('label[for="training_cost_to"]').html('Training Cost To:<span class="red font-large">*</span>');
			$('label[for="rls_days"]').html('Required Length of Service (RLS) in Days:<span class="red font-large">*</span>');
			$('label[for="rls_months"]').html('Required Length of Service (RLS) in Months:');

			$('#rls_months').attr('disabled','disabled');
			$('#rls_months').attr('id','rls_months-temp');
			$('#rls_months-temp').attr('name','rls_months-temp');
			$('#rls_months-temp').parent().append('<input type="hidden" id="rls_months" name="rls_months" value="" />');
			$('#rls_months').val($('#rls_months-temp').val());

		}

		$('input[name="rls_days"]').live('change',function(){

			var url = module.get_value('base_url') + module.get_value('module_link') + '/convert_rls_days_to_months';
		    var data = 'rls_days=' + $('input[name="rls_days"]').val();

			$.ajax({
		        url: url,
		        dataType: 'json',
		        type:"POST",
		        data: data,
		        success: function (response) {

		        	$('#rls_months-temp').val(response.rls_months);
		        	$('input[name="rls_months"]').val(response.rls_months);

		        }

			});


		});

	}

	if( module.get_value('view') == 'detail' ){

		window.onload = function(){

			$('label[for="rls_days"]').parent().find('div.text-input-wrap').append('Days');
			$('label[for="rls_months"]').parent().find('div.text-input-wrap').append('Months');

		}

	}


});

function bond_ajax_save( on_success, is_wizard , callback ){

	var url = module.get_value('base_url') + module.get_value('module_link') + '/validate_existing_bond';
    var data = $('#record-form').serialize();

	$.ajax({
        url: url,
        dataType: 'json',
        type:"POST",
        data: data,
        success: function (response) {

        	if( response.msg_type == "error" ){
        		message_growl(response.msg_type,response.msg);
        	}
        	else{
        		ajax_save( on_success, is_wizard , callback );
        	}
        }
	});

	


}