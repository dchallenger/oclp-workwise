$(document).ready(function () {

	if( module.get_value('view') == 'edit' ){

		window.onload = function(){

			if( $('#evaluation_type_id').val() == 2 ){
				$('label[for="position_id"]').parent().show();
			}
			else{
				$('label[for="position_id"]').parent().hide();
			}

			if($('#employee_id').val()){

				// get training list
				get_training_list();

		        //get training competencies
		        get_training_competencies();

			}
		}

		$('.skills_average').live('click',function(){
			get_total_average();
			get_sub_total_average($(this));


		});

		$('.multiple').live('click',function(){

			var skill_item_id = $(this).attr('skill-item-id');

				$('.multiple').each(function(){

					if( $(this).attr('skill-item-id') == skill_item_id ){
						$(this).parents('tr').find('textarea').val('');
						$(this).parents('tr').find('textarea').attr('disabled',true);
					}
				});

				$(this).parents('tr').find('textarea').removeAttr('disabled');

			
		});

		$('#evaluation_type_id').live('change',function(){

			if( $(this).val() == 2 ){
				$('label[for="position_id"]').parent().show();
			}
			else{
				$('label[for="position_id"]').parent().hide();
				$('#position_id').val('');
				$('#position_id').trigger("liszt:updated");
			}

		});

		$('#employee_id').live('change',function(){

			// get training list
			get_training_list();

	        //get training competencies
	        get_training_competencies();

		});


	}
	else if( module.get_value('view') == 'detail' ){

		window.onload = function(){

			if( $('#evaluation_type_id').val() == 2 ){
				$('label[for="position_id"]').parent().show();
			}
			else{
				$('label[for="position_id"]').parent().hide();
			}

			get_evaluation_employee_id();

		}

	}


});

function get_evaluation_employee_id(){

	var url = module.get_value('base_url') + module.get_value('module_link') + '/get_evaluation_employee_id';
    var data = 'record_id=' + $('#record_id').val();

	$.ajax({
        url: url,
        dataType: 'json',
        type:"POST",
        data: data,
        success: function (response) {

        	$('input[name="employee_id"]').val(response.employee_id);

        	// get training list
			get_training_list();

	        //get training competencies
	        get_training_competencies();
        }

	});


}

function get_training_list(){

	var url = module.get_value('base_url') + module.get_value('module_link') + '/get_training_list';
	var data = 'employee_id=' + $('#employee_id').val() + '&record_id=' + $('#record_id').val() + '&type='+module.get_value('view');

	$.ajax({
        url: url,
        dataType: 'html',
        type:"POST",
        data: data,
        success: function (response) {

        	if( response ){

        		$('.training_list tbody').empty();
        		$('.training_list tbody').html(response);

        	}

        }

    });


}

function get_training_competencies(){

	var url = module.get_value('base_url') + module.get_value('module_link') + '/get_training_competencies';
	var data = 'employee_id=' + $('#employee_id').val() + '&record_id=' + $('#record_id').val() + '&type='+module.get_value('view');

	$.ajax({
        url: url,
        dataType: 'html',
        type:"POST",
        data: data,
        success: function (response) {

        	if( response ){

        		$('#module-competencies tbody').empty();
        		$('#module-competencies tbody').html(response);

        	}

        }

    });

}

function get_total_average(){

	var url = module.get_value('base_url') + module.get_value('module_link') + '/get_total_average';
    var data = $('#record-form').serialize();

	$.ajax({
        url: url,
        dataType: 'json',
        type:"POST",
        data: data,
        success: function (response) {

        	$('input[name="total_score"]').val(response.total_score);
        	$('input[name="average_score"]').val(response.average_score);
        }

	});

}

function get_sub_total_average(obj){

	var sub_total = 0;
	var sub_average = 0;
	var calculate_average = 0;
	var category_weight = 0;
	var position_skill_id = obj.attr('position-skill-id');
	var item_count = $('.subtotal_count_'+position_skill_id).val();

	obj.parents('#module-competencies').find('.skills_average').each(function(){

		if( $(this).attr('position-skill-id') == position_skill_id ){

			if( $(this).attr('checked') ){

				var item_weight = $(this).parents('tr').find('.skills_item_weight').val();

				sub_total += parseFloat($(this).val());

				calculate_average = ( parseFloat($(this).val()) / 5 );
				calculate_average = ( calculate_average * ( parseFloat(item_weight) / 100 ) );

				category_weight = parseFloat($('.item_weight_'+position_skill_id).val()) / 100;
				calculate_average = ( parseFloat(calculate_average) * parseFloat(category_weight) );
				sub_average +=  calculate_average;
			}

		}


	});
	

	sub_average = sub_average * 100 ;

	obj.parents('#module-competencies').find('.subtotal_score_'+position_skill_id).val(sub_total);
	obj.parents('#module-competencies').find('.sub_average_'+position_skill_id).val(sub_average.toFixed(2));

}
