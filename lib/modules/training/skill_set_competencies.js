$( document ).ready( function() {
	
	init_datepick();

	if( module.get_value('view') == 'index' ){

		$('.set_weight').live('click',function(){

			var position_id = $('#position_id').val();

			var url = module.get_value('base_url') + module.get_value('module_link') + '/count_skillset';
	        var module_link = $(this).attr('module_link');

	        $.ajax({
	            url: url,
	            dataType: 'json',
	            type:"POST",
	            data: 'position_id=' + position_id,
	            success: function (response) {

	                if( response.skill_count != 0 ){
	                    

	                	$.ajax({
						        url: module.get_value('base_url') + 'training/skill_set_competencies/get_set_weight_template_form',
						        data: 'position_id=' + position_id,
						        type: 'post',
						        dataType: 'json',
						        beforeSend: function(){
									$.blockUI({
										message: '<div class="now-loading align-center"><img src="'+module.get_value('base_url')+user.get_value('user_theme')+'/images/loading.gif"><br />Loading, please wait...</div>'
									});  		
								},	
						        success: function(response) {

						        	if(response.msg_type == 'error'){
						        	
						        		$.unblockUI();	
						        		message_growl(response.msg_type, response.msg);

						      		}
									else{

						        	$.unblockUI();	

										template_form = new Boxy('<div id="boxyhtml" style="">'+ response.form +'</div>',
										{
												title: 'Set Weight',
												draggable: false,
												modal: true,
												center: true,
												unloadOnHide: true,
												beforeUnload: function (){
													template_form = false;
												}
											});
											boxyHeight(template_form, '#boxyhtml');			

									}

						        }
						});




	                }
	                else{
	                	message_growl('error', 'There are no available Skills to be Set');
	                }
	            }

	        });

		});

	}

	if(module.get_value('view') == 'edit'){

		init_ui();

		$('a.add-more').click(function(event) {

        	event.preventDefault();
        	var url = module.get_value('base_url') + module.get_value('module_link') + '/get_form/' + $(this).attr('rel');
        	var type = $(this).attr('rel');


        	if( $(this).attr('rel') == 'item' ){
        		var data = 'item_count=' + ( parseInt($('.item_count').val()) + 1 );
        	}

        	 $.ajax({
	            url: url,
	            dataType: 'html',
	            type:"POST",
	            data: data,
	            beforeSend: function(){
					$.blockUI({
						message: '<div class="now-loading align-center"><img src="'+module.get_value('base_url')+user.get_value('user_theme')+'/images/loading.gif"><br />Loading, please wait...</div>'
					});  		
				},	
	            success: function (response) {

	            	$.unblockUI();	
					if( type == 'item' ){

						var item_count = parseInt($('.item_count').val());
		            	$('.item_count').val( item_count + 1 );
		            	$('.form-multiple-add-item fieldset').append(response);
		            	init_ui();

		            }
	            }

	        });

    	});

    	$('a.delete-detail').live('click', function () {

    		var type = $(this).attr('rel');

    		if( type == 'item' ){

	    		var remove_item_no = parseInt($(this).parents('div.form-multiple-add').find('.item_no').val());
		        $(this).parents('div.form-multiple-add').remove();

		        var session_count = parseInt($('.item_count').val());
		        $('.item_count').val( session_count - 1 );

		        $('.item_no').each(function(){

		        	if( parseInt($(this).val()) > remove_item_no ){
		        		var item_no = parseInt($(this).val());
		        		$(this).val( item_no - 1 );
		        	}

		        });


	    	}


	    });

	    $('.item_inactive').live('click',function(){

	    	var value = $(this).val();
	    	$(this).parent().find('.item_inactive_hidden').val(value);

	    });

	    $('.score_type').live('change',function(){

	    	if( $(this).val() == 6 ){

	    		$(this).parent().parent().parent().find('.multiple_type').show();
	    		$(this).parent().parent().parent().find('.item_weight').val('0');
	    		$(this).parent().parent().parent().find('.item_weight').parent().parent().hide();
	    	}
	    	else if( $(this).val() == 3 ){

	    		$(this).parent().parent().parent().find('.item_weight').val('0');
	    		$(this).parent().parent().parent().find('.item_weight').parent().parent().hide();
	    		$(this).parent().parent().parent().find('.multiple_type').hide();

	    	}
	    	else{

	    		$(this).parent().parent().parent().find('.multiple_type').find('.sub_criteria').each(function(){
	    			$(this).val('');
	    		});

	    		$(this).parent().parent().parent().find('.multiple_type').hide();
	    		$(this).parent().parent().parent().find('.item_weight').parent().parent().show();

	    	}

	    });

	}

});


function ajax_save_items( on_success, is_wizard , callback ){
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
							var position_id = $('#position_id').val();
							window.location = module.get_value('base_url') + module.get_value('module_link') + '/index/' + position_id;
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

function go_back(){
	var position_id = $('#position_id').val();
	window.location = module.get_value('base_url') + module.get_value('module_link') + '/index/' + position_id;
}

function save_skillset_category(on_success, is_wizard , callback){

	


	$('.skills_item').each(function(){

		if( $(this).val() == '' ){

			add_error('skills_item', 'Skills Item', "This field is mandatory.");

		}

	});

	var sub_criteria_error = 0;

	

	$('.score_type').each(function(){

		if( $(this).val() == '' ){

			add_error('score_type', 'Score Type', "This field is mandatory.");

		}

		if( $('.score_type').val() == 6 ){

			$(this).parents('.form-multiple-add').find('.sub_criteria').each(function(){

				if( $(this).val() == '' ){

					sub_criteria_error++;

				}

			});

		}

	});


	if( sub_criteria_error > 0 ){

		add_error('skills_item', 'Sub Criteria', "This field is mandatory.");

	}

	var item_weight = 0;
	var decimal = 0;
	var non_weight_score_type = 0;

	$('.score_type').each(function(){

		if( $(this).val() == 6 || $(this).val() == 3 ){

			non_weight_score_type++;

		}

	});


	if( non_weight_score_type != $('.score_type').length ){


		$('.item_weight').each(function(){

			item_weight += parseFloat($(this).val());

			if( ( parseFloat($(this).val()) % 1 ) != 0 ){
				decimal ++;
			}

		});

		if( decimal > 0 ){

			add_error('item_weight', 'Item Weight', "Item weight must be a whole number.");

		}


		if( item_weight != 100 ){

			add_error('item_weight', 'Item Weight', "Item weight must be a total of 100.");

		}

	}


	ajax_save_items( on_success, is_wizard , callback );

}


function init_ui(){

	$('.score_type').chosen();


}