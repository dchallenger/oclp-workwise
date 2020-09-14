$(document).ready(function () {

	window.onload = function(){

        /*
        if( $('#evaluation_type_id').val() == 2 ){
            $('label[for="position_id"]').parent().show();
        }
        else{
            $('label[for="position_id"]').parent().hide();
        }
        */

        //get training competencies
        get_training_competencies();

    }

    if( module.get_value('view') == 'index' ){


		$('.go_back_to_main').live('click',function(){

			window.location = module.get_value('base_url') + 'training/training_revalida';


		});

	}

    $('.revalida_average').live('click',function(){
		get_total_average($(this));
	});



});


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



function get_total_average(obj){

	var sub_total = 0;
	var sub_average = 0;
	var calculate_average = 0;
	var category_weight = 0;
	var revalida_category_id = obj.attr('revalida-category-id');
	var item_count = $('.subtotal_count_'+revalida_category_id).val();

	obj.parents('#module-competencies').find('.revalida_average').each(function(){

		if( $(this).attr('revalida-category-id') == revalida_category_id ){

			if( $(this).attr('checked') ){

				var item_weight = $(this).parents('tr').find('.revalida_item_weight').val();

				sub_total += parseInt($(this).val());

				calculate_average = ( parseFloat($(this).val()) / 5 );
				calculate_average = ( calculate_average * ( parseFloat(item_weight) / 100 ) );
				category_weight = parseFloat($('.item_weight_'+revalida_category_id).val()) / 100;
				calculate_average = ( parseFloat(calculate_average) * parseFloat(category_weight) );
				sub_average +=  calculate_average;
			}

		}


	});
	
	sub_average = sub_average * 100 ;
	obj.parents('#module-competencies').find('.subtotal_score_'+revalida_category_id).val(sub_total);
	obj.parents('#module-competencies').find('.sub_average_'+revalida_category_id).val(sub_average.toFixed(2));

	var total = 0;
	var average = 0;

	$('.subtotal_score').each(function(){
		total += parseInt($(this).val());
	});

	$('.sub_average').each(function(){
		average += parseFloat($(this).val());
	});

	$('.total_score').val(total);
	$('.average').val(average.toFixed(2));


}


function save_revalida_participants(on_success, is_wizard , callback){

	ajax_save_revalida_participants( on_success, is_wizard , callback );

}

function go_back(){

	var calendar_id = $('#calendar_id').val();
	var employee_direct = $('#employee_direct').val();

	if(employee_direct == 0){
		window.location = module.get_value('base_url') + module.get_value('module_link') + '/index/' + calendar_id;
	}
	else{
		window.location = module.get_value('base_url') + '/training/training_revalida/';
	}

}

function ajax_save_revalida_participants( on_success, is_wizard , callback ){
	if( is_wizard == 1 ){
		var current = $('.current-wizard');
		var fg_id = current.attr('fg_id');
		var ok_to_save = eval('validate_fg'+fg_id+'()')
	}
	else{
		ok_to_save = validate_form();
	}
	
	if( ok_to_save ) {		
		$('#record-form').find('.chzn-done').each(function (index, elem) {
			if (elem.multiple) {
				if ($(elem).attr('name') != $(elem).attr('id') + '[]') {
					$(elem).attr('name', $(elem).attr('name') + '[]');
				}
				
				var values = new Array();
				for(var i=0; i< elem.options.length; i++) {
					if(elem.options[i].selected == true) {
						values[values.length] = elem.options[i].value;
					}
				}
				$(elem).val(values);
			}
		});

		var data = $('#record-form').serialize();
		var saveUrl = module.get_value('base_url')+module.get_value('module_link')+"/ajax_save"		

		$.ajax({
			url: saveUrl,
			type:"POST",
			data: data,
			dataType: "json",
			/**async: false, // Removed because loading box is not displayed when set to false **/
			beforeSend: function(){
					show_saving_blockui();
			},
			success: function(data){
				if(  data.record_id != null ){
					//check if new record, update record_id
					if($('#record_id').val() == -1 && data.record_id != ""){
						$('#record_id').val(data.record_id);
						$('#record_id').trigger('change');
						if( is_wizard == 1 ) window.location = module.get_value('base_url') + module.get_value('module_link') + '/edit/' + data.record_id;
					}
					else{
						$('#record_id').val( data.record_id );
					}
				}

				if( data.msg_type != "error"){					
					switch( on_success ){
						case 'back':
							var calendar_id = $('#calendar_id').val();
							var employee_direct = $('#employee_direct').val();

							if(employee_direct == 0){
								window.location = module.get_value('base_url') + module.get_value('module_link') + '/index/' + calendar_id;
							}
							else{
								window.location = module.get_value('base_url') + '/training/training_revalida/';
							}

							//go_to_previous_page( data.msg );
							break;
						case 'email':
							if (data.record_id > 0 && data.record_id != '') {

								// Ajax request to send email.                    
								$.ajax({
									url: module.get_value('base_url') + module.get_value('module_link') + '/send_email',
									data: 'record_id=' + data.record_id,
									dataType: 'json',
									type: 'post',
									async: false,
									beforeSend: function(){
											//show_saving_blockui();
										},								
									success: function () {
									}
								});
							}							
						default:
							if (typeof data.page_refresh != 'undefined' && data.page_refresh == "true"){
									window.location = module.get_value('base_url') + module.get_value('module_link') + '/edit/' + data.record_id;
							}
							else{
								//generic ajax save callback
								if(typeof window.ajax_save_callback == 'function') ajax_save_callback();
								//custom ajax save callback
								if (typeof(callback) == typeof(Function)) callback( data );
								$.unblockUI({ onUnblock: function() { message_growl(data.msg_type, data.msg) } });
							}
							break;
					}	
				}
				else{
					$.unblockUI({ onUnblock: function() { message_growl(data.msg_type, data.msg) } });
				}
				
			}
		});
	}
	else{
		return false;
	}
	return true;
}

