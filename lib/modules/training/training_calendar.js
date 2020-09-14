$( document ).ready( function() {
	
	init_datepick();

	if(module.get_value('view') == 'index'){

		$('.icon-16-document-stack').live('click',function(){

			var training_calendar_id = $(this).parents('tr').attr('id');
			duplicate_calendar(training_calendar_id);

		});

		$('.icon-16-close').live('click',function(){

			var training_calendar_id = $(this).parents('tr').attr('id');
			close_calendar(training_calendar_id,'index');

		});

		$('.icon-16-cancel').live('click',function(){

			var training_calendar_id = $(this).parents('tr').attr('id');
			cancel_calendar(training_calendar_id,'index');

		});


	}

	if(module.get_value('view') == 'edit'){

		window.onload = function(){

			get_training_subject_info();
			$('.advance_search_container').hide();
			$('.hide_advance_search').parent().hide();

			$('.previous_training_subject').val($('#training_subject_id').val());
			$('.previous_training_type').val($('#calendar_type_id').val());

		}

		init_ui();

		$('.hide_advance_search').live('click',function(){

			$('.advance_search_container').hide();
			$('.hide_advance_search').parent().hide();
			$('.show_advance_search').parent().show();

			return false;

		});

		$('.show_advance_search').live('click',function(){

			$('.advance_search_container').show();
			$('.hide_advance_search').parent().show();
			$('.show_advance_search').parent().hide();

			return false;

		});

		$('#training_subject_id').live('change',function(){

			var no_participants = $('#module-participant tbody tr').length;
			var current_training_subject = $(this).val();
			var previous_training_subject = $('.previous_training_subject').val();

			if( no_participants > 0 ){

				Boxy.ask("Are you sure to change the training course? This may cause all current participants to be deleted.", ["Yes", "No"],function( choice ) {
			    	if(choice == "Yes"){

			    		clear_all_participants();
			    		$('.previous_training_subject').val(current_training_subject);
						get_training_subject_info();
			        }
			        else{

			        	$('#training_subject_id').val(previous_training_subject);
			        	$('#training_subject_id').trigger('liszt:updated');

						get_training_subject_info();

			        }
			    },
			    {
			        title: "Change Training Course"
			    });

			}
			else{

				$('.previous_training_subject').val(current_training_subject);
				get_training_subject_info();

			}

		});

		$('#calendar_type_id').live('change',function(){

			var no_participants = $('#module-participant tbody tr').length;
			var current_training_type = $(this).val();
			var previous_training_type = $('.previous_training_type').val();

			if( no_participants > 0 ){

				Boxy.ask("Are you sure to change the training type? This may cause all current participants to be deleted.", ["Yes", "No"],function( choice ) {
			    	if(choice == "Yes"){

			    		clear_all_participants();
			    		$('.previous_training_type').val(current_training_type);
			        }
			        else{

			        	$('#calendar_type_id').val(previous_training_type);

			        }
			    },
			    {
			        title: "Change Training Type"
			    });

			}
			else{

				$('.previous_training_type').val(current_training_type);

			}

		});

		$('a.add-more').click(function(event) {

        	event.preventDefault();
        	var url = module.get_value('base_url') + module.get_value('module_link') + '/get_form/' + $(this).attr('rel');
        	var type = $(this).attr('rel');

        	if( $(this).attr('rel') == 'session' ){
        		var data = 'session_count=' + ( parseInt($('.session_count').val()) + 1 );
        	}

        	 $.ajax({
	            url: url,
	            dataType: 'html',
	            type:"POST",
	            data: data,
	            success: function (response) {

	            	if( type == 'session' ){

		            	var session_count = parseInt($('.session_count').val());

		            	$('.form-multiple-add-session fieldset').append(response);
		            	$('.session_count').val( session_count + 1 );
		            	init_ui();
		            	calculate_total_hours();

		            }
		            else if( type == 'budget' ){

		            	$('.form-multiple-add-budget fieldset').append(response);

		            }
	            }

	        });

    	});

    	$('a.delete-detail').live('click', function () {

    		var type = $(this).attr('rel');

    		if( type == 'session' ){

	    		var remove_session_no = parseInt($(this).parents('div.form-multiple-add').find('.session_no').val());
		        $(this).parents('div.form-multiple-add').remove();

		        var session_count = parseInt($('.session_count').val());
		        $('.session_count').val( session_count - 1 );

		        $('.session_no').each(function(){

		        	if( parseInt($(this).val()) > remove_session_no ){
		        		var session_no = parseInt($(this).val());
		        		$(this).val( session_no - 1 );
		        	}

		        });

		        calculate_total_hours();

	    	}
	    	else if( type == 'budget' ){

	    		$(this).parents('div.form-multiple-add').remove();

	    		calculate_total_cost_pax();

	    	}

	    });

	    $('.breaktime_from, .breaktime_to, .sessiontime_from, .sessiontime_to').live('change',function(){

	    	calculate_total_hours();

	    });

	    $('.cost, .pax').live('change',function(){

	    	var sub_cost = $(this).parents('div.form-multiple-add').find('.cost').val();
	    	var sub_pax = $(this).parents('div.form-multiple-add').find('.pax').val();

	    	$(this).parents('div.form-multiple-add').find('.total').val( ( sub_cost * sub_pax ).toFixed(2) );

	    	calculate_total_cost_pax();

	    });

	    $('#category').live('change',function(){
	        var items = {0 : "Select", 1 : "Company",2 : "Division",3 : "Department",5 : "Rank"};
	        var category_id = $(this).val();
	        var category = items[category_id];

	        var eleid = category.toLowerCase()

	        $('#employee').multiselect("disable");

	        if (category_id > 0){
	            $.ajax({
	                url: module.get_value('base_url') + module.get_value('module_link') + '/get_category_filter',
	                data: 'category_id=' + category_id,
	                dataType: 'html',
	                type: 'post',
	                async: false,
	                 beforeSend: function(){
	                    $.blockUI({ message: '<div class="now-loading align-center"><img src="'+module.get_value('base_url')+user.get_value('user_theme')+'/images/loading.gif"><br />Loading, please wait...</div>'});                            
	                },                              
	                success: function ( response ) {
	                	$.unblockUI();
	                    $('#multi-select-main-container').show();
	                    $('#category_selected').html(category + ':');
	                    $('#multi-select-container').html(response);
	                    
	                    $('#'+eleid).multiselect().multiselectfilter({
	                        show:['blind',250],
	                        hide:['blind',250],
	                        selectedList: 1
	                    });
						
	                    $('#company, #division, #department, #rank').bind("multiselectclose", function(event, ui){

							var type = $(this).attr('id');
							var id = $(this).multiselect("getChecked").map(function(){
							   return this.value;	
							}).get();
							
							var nid = "";

							var course_id = $('#training_subject_id').val();
							var training_type_id = $('#calendar_type_id').val();

							$('.participants').each(function(){
								nid = nid + $(this).val() + ',';
							});

							$.ajax({
				                url: module.get_value('base_url') + module.get_value('module_link') + '/get_employee_filter',
				                data: 'id=' + id + '&type=' + type + '&nid=' + nid + '&course_id=' + course_id + '&training_type_id=' + training_type_id,
				                dataType: 'html',
				                type: 'post',
				                async: false,
				                beforeSend: function(){
				                    $.blockUI({ message: '<div class="now-loading align-center"><img src="'+module.get_value('base_url')+user.get_value('user_theme')+'/images/loading.gif"><br />Loading, please wait...</div>'});                            
				                },                             
				                success: function ( response ) {
				                	$.unblockUI(); 
				                	 $('#multi-select-employee-main-container').show();
					                 $('#multi-select-employee-container').html(response);
					                 $('#employee').multiselect().multiselectfilter({
					                        show:['blind',250],
					                        hide:['blind',250],
					                        selectedList: 1
					                 });
				                }
				            });
						});

	                }
	            });
	        }
	        else{
	            $('#multi-select-main-container').hide();
	            $('#category_selected').empty();
	            $('#multi-select-container').empty();          
	        }   
	    }); 

		$('.add_course_participants').live('click',function(){

			var participants = "";

			$('.participants').each(function(){
				participants += $(this).val()+",";
			});


			$.ajax({
	            url: module.get_value('base_url') + module.get_value('module_link') + '/add_course_participants',
	            dataType: 'html',
	            type:"POST",
	            data: 'training_subject_id='+$('#training_subject_id').val()+'&training_type_id='+$('#calendar_type_id').val()+'&participant_list='+participants,
	            success: function (response) {

	            	if( response != "" ){

	            		$('#module-participant tbody').append(response);

	            	}

	            }
	          });

		});

		$('.send_status_email').live('click',function(){

			var url = module.get_value('base_url') + module.get_value('module_link') + '/send_status_email';
		    var data = $('#record-form').serialize();

			$.ajax({
			    url: url,
			    dataType: 'json',
			    type:"POST",
			    data: data,
			    success: function (response) {



			    }
			});


		});

		
		$('.add_new_participants').live('click',function(){

			if( $('#category').val() == 0 ){

				message_growl('error', 'Please select a category');
				return false;
			}
			else{

				var items = {0 : "Select", 1 : "Company",2 : "Division",3 : "Department",5 : "Rank"};
				var category_id = $('#category').val();
	        	var category = items[category_id];
	        	var eleid = category.toLowerCase()

				if( ( $('#'+eleid).val() == null ) ){
					message_growl('error', 'Please select a ' + category);
					return false;
				}
				else{
					
					if( ( $('#employee').val() == null ) ){
						message_growl('error', 'Please select an employee');
						return false;
					}
					else{

						var training_type_id = $('#calendar_type_id').val();
						var course_id = $('#training_subject_id').val();

						$.ajax({
				            url: module.get_value('base_url') + module.get_value('module_link') + '/add_participants',
				            dataType: 'html',
				            type:"POST",
				            data: 'employee_id='+$('#employee').val()+' &training_type_id='+training_type_id+'&course_id='+course_id,
				            success: function (response) {

				            	$('#module-participant tbody').append(response);

				            	var type = $('#'+eleid).attr('id');
								var id = $('#'+eleid).val();
								var nid = "";

								$('.participants').each(function(){
									nid = nid + $(this).val() + ',';
								});

								var course_id = $('#training_subject_id').val();
								var training_type_id = $('#calendar_type_id').val();
								

								$.ajax({
					                url: module.get_value('base_url') + module.get_value('module_link') + '/get_employee_filter',
					                data: 'id=' + id + '&type=' + type + '&nid=' + nid + '&course_id=' + course_id + '&training_type_id=' + training_type_id,
					                dataType: 'html',
					                type: 'post',
					                async: false,
					                beforeSend: function(){
					                    $.blockUI({ message: '<div class="now-loading align-center"><img src="'+module.get_value('base_url')+user.get_value('user_theme')+'/images/loading.gif"><br />Loading, please wait...</div>'});                            
					                },                              
					                success: function ( response ) {
					                	$.unblockUI(); 
					                	 $('#multi-select-employee-main-container').show();
						                 $('#multi-select-employee-container').html(response);
						                 $('#employee').multiselect().multiselectfilter({
						                        show:['blind',250],
						                        hide:['blind',250],
						                        selectedList: 1
						                 });
					                }
					            });


				            	$("#employee").multiselect("uncheckAll");
				            	
				            }

				        });
					}
				}
			}

			return false;

		});

		$('.delete-participant').live('click',function(){

			var element = $(this);

			Boxy.confirm(
				'<div id="boxyhtml" height="50px">Are you sure to delete this participant?</div>',
				function () {

					var participant_id = element.parent().find('.participants').val();

					$.ajax({
		                url: module.get_value('base_url') + module.get_value('module_link') + '/delete_participant',
		                data: 'participant_id=' + participant_id + '&training_calendar_id=' + $('#record_id').val() ,
		                dataType: 'json',
		                type: 'post',
		                async: false,
		                beforeSend: function(){
		                    $.blockUI({ message: '<div class="now-loading align-center"><img src="'+module.get_value('base_url')+user.get_value('user_theme')+'/images/loading.gif"><br />Loading, please wait...</div>'});                            
		                },                                 
		                success: function ( response ) {
		                	
							element.parent().parent().remove();

							count_confirmed();

							var items = {0 : "Select", 1 : "Company",2 : "Division",3 : "Department",5 : "Rank"};
							var category_id = $('#category').val();
				        	var category = items[category_id];
				        	var eleid = category.toLowerCase()
							var type = $('#'+eleid).attr('id');
							var id = $('#'+eleid).val();
							var nid = "";
							var disabled = false;

							if( $('#employee').attr('disabled') == 'disabled' ){
								disabled = true;
							}

							$('.participants').each(function(){
								nid = nid + $(this).val() + ',';
							});

							var course_id = $('#training_subject_id').val();
							var training_type_id = $('#calendar_type_id').val();

							$.ajax({
				                url: module.get_value('base_url') + module.get_value('module_link') + '/get_employee_filter',
				                data: 'id=' + id + '&type=' + type + '&nid=' + nid + '&course_id=' + course_id + '&training_type_id=' + training_type_id,
				                dataType: 'html',
				                type: 'post',
				                async: false,
				                beforeSend: function(){
				                    $.blockUI({ message: '<div class="now-loading align-center"><img src="'+module.get_value('base_url')+user.get_value('user_theme')+'/images/loading.gif"><br />Loading, please wait...</div>'});                            
				                },                                 
				                success: function ( response ) {
				                	$.unblockUI(); 
				                	 $('#multi-select-employee-main-container').show();
					                 $('#multi-select-employee-container').html(response);
					                 $('#employee').multiselect().multiselectfilter({
					                        show:['blind',250],
					                        hide:['blind',250],
					                        selectedList: 1
					                 });

					                 if( disabled == true ){
					                 	$('#employee').multiselect("disable");
					                 }
				                }
				            });

					    }
		            });

			});

		});

		$('.clear_all_participants').live('click',function(){

			Boxy.confirm(
				'<div id="boxyhtml" height="50px">Are you sure to delete all participant?</div>',
				function () {

					clear_all_participants();

			});

		});


		$('.participant_status').live('change',function(){

			count_confirmed();
			
		});


	}


	if( module.get_value('view') == 'detail' ){

		window.onload = function(){

			get_training_subject_info();

		}


		$('.icon-16-close').live('click',function(){

			var training_calendar_id = $('#record_id').val();
			close_calendar(training_calendar_id,"detail");

		});

		$('.icon-16-cancel').live('click',function(){

			var training_calendar_id = $('#record_id').val();
			cancel_calendar(training_calendar_id,"detail");

		});

	}

});

function clear_all_participants(){

	$.ajax({
        url: module.get_value('base_url') + module.get_value('module_link') + '/delete_all_participants',
        data: 'training_calendar_id=' + $('#record_id').val() ,
        dataType: 'json',
        type: 'post',
        async: false,
        beforeSend: function(){
            $.blockUI({ message: '<div class="now-loading align-center"><img src="'+module.get_value('base_url')+user.get_value('user_theme')+'/images/loading.gif"><br />Loading, please wait...</div>'});                            
        },                                 
        success: function ( response ) {

			$('#module-participant tbody').empty();

			count_confirmed();

			var items = {0 : "Select", 1 : "Company",2 : "Division",3 : "Department",5 : "Rank"};
			var category_id = $('#category').val();
        	var category = items[category_id];
        	var eleid = category.toLowerCase()
			var type = $('#'+eleid).attr('id');
			var id = $('#'+eleid).val();
			var nid = "";
			var disabled = false;

			if( $('#employee').attr('disabled') == 'disabled' ){
				disabled = true;
			}

			$('.participants').each(function(){
				nid = nid + $(this).val() + ',';
			});

			var course_id = $('#training_subject_id').val();
			var training_type_id = $('#calendar_type_id').val();

			$.ajax({
                url: module.get_value('base_url') + module.get_value('module_link') + '/get_employee_filter',
                data: 'id=' + id + '&type=' + type + '&nid=' + nid + '&course_id=' + course_id + '&training_type_id=' + training_type_id,
                dataType: 'html',
                type: 'post',
                async: false,
                beforeSend: function(){
                    $.blockUI({ message: '<div class="now-loading align-center"><img src="'+module.get_value('base_url')+user.get_value('user_theme')+'/images/loading.gif"><br />Loading, please wait...</div>'});                            
                },                                 
                success: function ( response ) {
                	$.unblockUI();
                	 $('#multi-select-employee-main-container').show();
	                 $('#multi-select-employee-container').html(response);
	                 $('#employee').multiselect().multiselectfilter({
	                        show:['blind',250],
	                        hide:['blind',250],
	                        selectedList: 1
	                 });

	                 if( disabled == true ){
	                 	$('#employee').multiselect("disable");
	                 }


                }
            });
		}
	});

}

function get_training_subject_info(){

	var url = module.get_value('base_url') + module.get_value('module_link') + '/get_training_subject_info';
    var data = $('#record-form').serialize()+'&view='+module.get_value('view');

	$.ajax({
	    url: url,
	    dataType: 'json',
	    type:"POST",
	    data: data,
	    success: function (response) {

	    	if( module.get_value('view') == 'edit' ){

	    		$('input[name="training_provider"]').val(response.training_provider);
	    		$('input[name="training_category_id"]').val(response.training_category);

	    	}
	    	else if( module.get_value('view') == 'detail' ){

	    		$('label[for="training_provider"]').parent().find('div').html(response.training_provider);
	    		$('label[for="training_category_id"]').parent().find('div').html(response.training_category);

	    	}

	    }
	});


}


function save_training_calendar(on_success, is_wizard , callback, publish, confirm){


	var min_capacity = parseInt($('#min_training_capacity').val().replace(/,/g,'') );
	var max_capacity = parseInt($('#training_capacity').val().replace(/,/g,'') );
	var no_participants = $('.participants').length;

	var registration_date = new Date($('#registration_date').val());
	var last_registration_date = new Date($('#last_registration_date').val());

	
	if( max_capacity < min_capacity ){

		add_error('max_capacity', 'Maximum Trainee Capacity', "Maximum Trainee Capacity field must be greater than Minimum Trainee Capacity field");

	}

	if ( registration_date > last_registration_date ){
		add_error('registration_date', 'Registration Date', "Registration date must be less than the Last registration date.");
	}
	

	/*
	if( ( no_participants > max_capacity ) ){

		add_error('max_capacity', 'Maximum Trainee Capacity', "Total no. of trainees must not be greater than "+max_capacity+" participants ");

	}
	*/


	if( $('.session_date').length == 0 ){

		add_error('planned_date', 'Training Date', "This field is mandatory.");

	}


	$('.session_date').each(function(){

		if( $(this).val() == '' ){

			add_error('planned_date', 'Training Date', "This field is mandatory.");

		}

	});

	$('.training_cost_name').each(function(){

		if( $(this).val() == '' ){

			add_error('training_cost_name', 'Training Cost Name', "This field is mandatory.");

		}

	});

	$('.cost').each(function(){

		if( $(this).val() == '' ){

			add_error('cost', 'Cost', "This field is mandatory.");

		}

		var fieldval = $(this).val();
		if( fieldval != "" ){
			var float_val = parseFloat(fieldval.replace(",", ""));
			
			//test if float
			var valid = /^[-+]?\d+(\.\d+)?$/.test( float_val );
			if( !valid ){
				add_error('cost', 'Cost', "This field only accept floats or integers.");
				return false;
			}
		}

	});

	$('.pax').each(function(){

		if( $(this).val() == '' ){

			add_error('pax', 'Pax', "This field is mandatory.");

		}

		var fieldval = $(this).val();
		if( fieldval != "" ){
			// remove comma separations
			var integer_val = parseFloat(fieldval.replace(",", ""));
			
			//test if integer
			var valid = /^-?\d+$/.test( integer_val );
			if( !valid ){
				add_error('pax', 'Pax', "This field only accept integers.");
				return false;
			}
		}

	});

	if( publish == true ){
		if( $('#publish').length == 0 ){
			$('#record-form').prepend('<input type="hidden" value="1" id="publish" name="publish">');
		}
		else{
			$('#publish').val(1);
		}
	}
	else{
		if( $('#publish').length == 0 ){
			$('#record-form').prepend('<input type="hidden" value="0" id="publish" name="publish">');
		}
		else{
			$('#publish').val(0);
		}
	}

	if( confirm == true ){
		if( $('#confirm').length == 0 ){
			$('#record-form').prepend('<input type="hidden" value="1" id="confirm" name="confirm">');
		}
		else{
			$('#confirm').val(1);
		}
	}
	else{
		if( $('#confirm').length == 0 ){
			$('#record-form').prepend('<input type="hidden" value="0" id="confirm" name="confirm">');
		}
		else{
			$('#confirm').val(0);
		}
	}

	calendar_ajax_save( on_success, is_wizard , callback );

}

function calendar_ajax_save( on_success, is_wizard , callback ){

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
							go_to_previous_page( data.msg );
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
											show_saving_blockui();
										},								
									success: function () {
									}
								});
							}							
							//custom ajax save callback
							if (typeof(callback) == typeof(Function)) callback( data );
							$.unblockUI({ onUnblock: function() { message_growl(data.msg_type, data.msg) } });
						break;
						case 'email_confirm':							
							if (data.record_id > 0 && data.record_id != '') {
								// Ajax request to send email.                    
								$.ajax({
									url: module.get_value('base_url') + module.get_value('module_link') + '/send_confirm_email',
									data: 'record_id=' + data.record_id,
									dataType: 'json',
									type: 'post',
									async: false,
									beforeSend: function(){
											show_saving_blockui();
										},								
									success: function () {
									}
								});
							}							

							//custom ajax save callback
							if (typeof(callback) == typeof(Function)) callback( data );
							$.unblockUI({ onUnblock: function() { message_growl(data.msg_type, data.msg) } });
						break;
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

function calculate_total_cost_pax(){

	var url = module.get_value('base_url') + module.get_value('module_link') + '/calculate_total_cost_pax';
    var data = $('#record-form').serialize();

	$.ajax({
    url: url,
    dataType: 'json',
    type:"POST",
    data: data,
    success: function (response) {

    	$('.total_cost').val( response.total_cost );
		$('.total_pax').val( response.total_pax );
    }
});

	

}

function calculate_total_hours(){

	var url = module.get_value('base_url') + module.get_value('module_link') + '/calculate_total_hours';
    var data = $('#record-form').serialize();

	$.ajax({
        url: url,
        dataType: 'json',
        type:"POST",
        data: data,
        success: function (response) {

        	$('input[name="total_session_hours"]').val(response.total_session_hours);
        	$('input[name="total_session_breaks"]').val(response.total_break_hours);
        }

	});

}

function init_ui(){

	$('#employee').multiselect().multiselectfilter({
            show:['blind',250],
            hide:['blind',250],
            selectedList: 1
     });

	$('#employee').multiselect("disable");

	count_confirmed();

	$('.instructor').chosen();

		$('.session_date').datepicker({
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
			yearRange: 'c-90:c+10',
        });

		$('.session_date_from, .session_date_to').datetimepicker({
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
			onClose: function(dateText, inst){
				
			}
		}); 

		$('.breaktime_from, .breaktime_to, .sessiontime_from, .sessiontime_to').timepicker({
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
			}
		});

		
	if( user.get_value('post_control') == 0 && user.get_value('approve_control') == 1 ){

		
		$('#topic').attr('disabled','disabled');
		$('#training_subject_id').attr('disabled','disabled');
		$('#training_subject_id').chosen();
		$('#training_category_id').attr('disabled','disabled');
		$('#training_provider').attr('disabled','disabled');
		$('#calendar_type_id').attr('disabled','disabled');
		$('#min_training_capacity').attr('disabled','disabled');
		$('#training_capacity').attr('disabled','disabled');
		$('#room').attr('disabled','disabled');
		$('#venue').attr('disabled','disabled');
		$('#equipment').attr('disabled','disabled');
		$('#tba-yes').attr('disabled','disabled');
		$('#tba-no').attr('disabled','disabled');
		$('#registration_date-temp').datepicker({disabled: true});
		$("#registration_date-temp").datepicker('disable');
		$('#last_registration_date-temp').datepicker({disabled: true});
		$("#last_registration_date-temp").datepicker('disable');
		$('#revalida_date-temp').datepicker({disabled: true});
		$("#revalida_date-temp").datepicker('disable');
		$('#publish_date-temp').datepicker({disabled: true});
		$("#publish_date-temp").datepicker('disable');
		$('#with_certification-yes').attr('disabled','disabled');
		$('#with_certification-no').attr('disabled','disabled');
		$('#cost_per_pax').attr('disabled','disabled');
		$('#training_revalida_master_id').attr('disabled','disabled').trigger("liszt:updated");
 		$("#multiselect-feedback_category_id").multiselect({disabled: true});
 		$("#multiselect-feedback_category_id").multiselect('disable');

 		$('.add-more-div').parent().remove();
 		$('.delete-detail').parent().parent().remove();

 		$('.session_date').datepicker({disabled: true});
		$(".session_date").datepicker('disable');

		$('.instructor').attr('disabled','disabled').trigger("liszt:updated");

		$('.sessiontime_from').datepicker({disabled: true});
		$(".sessiontime_from").datepicker('disable');

		$('.sessiontime_to').datepicker({disabled: true});
		$(".sessiontime_to").datepicker('disable');

		$('.breaktime_from').datepicker({disabled: true});
		$(".breaktime_from").datepicker('disable');

		$('.breaktime_to').datepicker({disabled: true});
		$(".breaktime_to").datepicker('disable');

		$('.cost_name').attr('disabled','disabled');
		$('.cost').attr('disabled','disabled');
		$('.remarks').attr('disabled','disabled');
		$('.pax').attr('disabled','disabled');

 		

	}


}

function duplicate_calendar(training_calendar_id){


	Boxy.ask("Duplicate selected record?", ["Yes", "Cancel"],
	function( choice ) {
		if(choice == "Yes"){

			var url = module.get_value('base_url') + module.get_value('module_link') + '/duplicate_calendar';
		    var data = 'training_calendar_id='+training_calendar_id;

			$.ajax({
		        url: url,
		        dataType: 'json',
		        type:"POST",
		        data: data,
		        success: function (response) {

		        	message_growl(response.msg_type, response.msg);
		        	$('#jqgridcontainer').jqGrid().trigger("reloadGrid");

		        }

			});

		}
	},
	{
		title: "Duplicate Training Calendar"
	});

}

function cancel_calendar(training_calendar_id, view){

	if( view == "" ){
		view == "index";
	}

	Boxy.ask("Cancel selected record?", ["Yes", "Cancel"],
	function( choice ) {
		if(choice == "Yes"){
			
			var url = module.get_value('base_url') + module.get_value('module_link') + '/cancel_calendar';
		    var data = 'training_calendar_id='+training_calendar_id;

			$.ajax({
		        url: url,
		        dataType: 'json',
		        type:"POST",
		        data: data,
		        success: function (response) {

		        	message_growl(response.msg_type, response.msg);

		        	if( response.msg_type != "error" ){

			        	if( view == "index" ){
			        		$('#jqgridcontainer').jqGrid().trigger("reloadGrid");
			        	}

			        	if( view == "detail" ){
			        		window.location = module.get_value('base_url') + module.get_value('module_link');
			        	}

		        	}

		        }

			});
			

		}
	},
	{
		title: "Cancel Training Calendar"
	});



}

function close_calendar(training_calendar_id, view){

	if( view == "" ){
		view == "index";
	}

	Boxy.ask("Close selected record?", ["Yes", "Cancel"],
	function( choice ) {
		if(choice == "Yes"){

			
			var url = module.get_value('base_url') + module.get_value('module_link') + '/close_calendar';
		    var data = 'training_calendar_id='+training_calendar_id;

			$.ajax({
		        url: url,
		        dataType: 'json',
		        type:"POST",
		        data: data,
		        success: function (response) {

		        	message_growl(response.msg_type, response.msg);

		        	if( response.msg_type != "error" ){

			        	if( view == "index" ){
			        		$('#jqgridcontainer').jqGrid().trigger("reloadGrid");
			        	}

			        	if( view == "detail" ){
			        		window.location = module.get_value('base_url') + module.get_value('module_link');
			        	}

		        	}

		        }

			});
			


		}
	},
	{
		title: "Close Training Calendar"
	});

	



}

function count_confirmed(){

	if( $('#module-participant tbody tr').length > 0 ){

		var confirmed_count = 0;

		$('.participant_status').each(function(){

			if( $(this).val() == "2" ){

				confirmed_count++;

			}

		});

		$('.total_confirmed').val(confirmed_count);

	}
	else{

		$('.total_confirmed').val("0");


	}

}