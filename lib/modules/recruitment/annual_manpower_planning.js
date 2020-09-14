$(document).ready(function () {
	if( module.get_value('view') == "edit" )
	{

		var annual_manpower_planning_id = $('#record_id').val();

		$.ajax({
			url: module.get_value('base_url') + module.get_value('module_link') + '/get_year',
			data: 'annual_manpower_planning_id=' + annual_manpower_planning_id,
			dataType: 'json',
			type: 'post',
			async: false,
			beforeSend: function(){
			
			},								
			success: function ( response ) {

				$('label[for="year"]').next().removeClass('text-input-wrap').addClass('select-input-wrap');
				$('label[for="year"]').next().html(response.year);
				//$('#record_id').parent().prepend(response.employee);
				disable_all();
			}
		});	
		
		if (annual_manpower_planning_id == -1)
		{

			$.ajax({
				url: module.get_value('base_url') + module.get_value('module_link') + '/get_amp_user_type',
				data: '',
				dataType: 'json',
				type: 'post',
				async: false,
				beforeSend: function(){
				
				},								
				success: function ( response ) {

					var amp_user_type = response.amp_user_type;

					if( amp_user_type == "division_head" ){

						$.ajax({
							url: module.get_value('base_url') + module.get_value('module_link') + '/get_user_info',
							data: 'amp_user_type=department_head',
							dataType: 'json',
							type: 'post',
							async: false,
							beforeSend: function(){
							
							},								
							success: function ( response ) {

								var company = response.company_name;
								var company_id = response.company_id;

								$('#company_id').parent().remove();
								$('label[for="company_id"]').parent().append('<input id="company_id" name="company_id" type="hidden" value="'+company_id+'" /><input id="company_id-name" class="input-text disabled" type="text" disabled="disabled" style="width:80%" value="'+company+'" name="company_id-name"></div>');

							}
						});

						$.ajax({
							url: module.get_value('base_url') + module.get_value('module_link') + '/get_department_list',
							data: '',
							dataType: 'html',
							type: 'post',
							async: false,
							beforeSend: function(){
							
							},								
							success: function ( response ) {
								
								$('#department_id').parent().empty();
								$('label[for="department_id"]').parent().find('.select-input-wrap').html(response);

							}
						});	

					}
					else if( amp_user_type == "department_head" ){

						$.ajax({
							url: module.get_value('base_url') + module.get_value('module_link') + '/get_user_info',
							data: 'amp_user_type=department_head',
							dataType: 'json',
							type: 'post',
							async: false,
							beforeSend: function(){
							
							},								
							success: function ( response ) {


								var department_id = response.department_id;
								var department_name = response.department_name;
								var name_dept = response.lastname + " " + response.firstname;
								var division_id = response.division_id;
								var division_name = response.division_name;
								var user_id = response.user_id
								var company = response.company_name;
								var company_id = response.company_id;

								$('#employee_id').parent().remove();
								$('#department_id').parent().remove();
								$('#company_id').parent().remove();
								$('label[for="department_id"]').parent().append('<input id="department_id" name="department_id" type="hidden" value="'+department_id+'" /><input id="department_id-name" class="input-text disabled" type="text" disabled="disabled" style="width:80%" value="'+department_name+'" name="department_id-name"></div>');
								$('label[for="employee_id"]').parent().append('<input id="employee_id" name="employee_id" type="hidden" value="'+user_id+'" /><input id="employee_id-name" class="input-text disabled" type="text" disabled="disabled" style="width:80%" value="'+name_dept+'" name="employee_id-name"></div>');
								$('label[for="company_id"]').parent().append('<input id="company_id" name="company_id" type="hidden" value="'+company_id+'" /><input id="company_id-name" class="input-text disabled" type="text" disabled="disabled" style="width:80%" value="'+company+'" name="company_id-name"></div>');
								$('#annual_user_division_id').parent().remove();
								$('label[for="annual_user_division_id"]').parent().append('<div class="text-input-wrap"><input id="annual_user_division_id" class="input-text" type="hidden" value="'+division_id+'" name="annual_user_division_id"><input id="annual_user_division_id-name" class="input-text disabled" type="text" disabled="disabled" style="width:80%" value="'+division_name+'" name="annual_user_division_id-name"></div>');
							

								$.ajax({
									url: module.get_value('base_url') + module.get_value('module_link') + '/get_position_per_dept',
									data: 'department_id=' + department_id + '&user_id=' + user_id,
									dataType: 'html',
									type: 'post',
									async: false,
									beforeSend: function(){
									
									},								
									success: function ( response ) {
										$('#module-access-container').html(response);

									    $( "#module-access" ).delegate( 'td, th','mouseover mouseleave', function(e) {
									        if ( e.type == 'mouseover' ) {
									          $( this ).parent().addClass( "hover" );
									        }
									        else {
									          $( this ).parent().removeClass( "hover" );
									        }
									    });				
									}
								});	

								/*

								$.ajax({
									url: module.get_value('base_url') + module.get_value('module_link') + '/get_headcount',
									data: 'department_id=' + department_id + '&user_id=' + user_id + '&record_id=' + annual_manpower_planning_id,
									dataType: 'html',
									type: 'post',
									async: false,
									beforeSend: function(){
									
									},								
									success: function ( response ) {
										$('#headcount-container').html(response);
						
									}
								});

								*/

								$.ajax({
									url: module.get_value('base_url') + module.get_value('module_link') + '/get_existing_headcount',
									data: 'department_id=' + department_id + '&user_id=' + user_id + '&record_id=' + annual_manpower_planning_id + '&year=' + $('#year').val(),
									dataType: 'html',
									type: 'post',
									async: false,
									beforeSend: function(){
									
									},								
									success: function ( response ) {
										$('#headcount-existing-container').html(response);
						
									}
								});

								
								$.ajax({
									url: module.get_value('base_url') + module.get_value('module_link') + '/get_new_headcount',
									data: 'department_id=' + department_id + '&user_id=' + user_id + '&record_id=' + annual_manpower_planning_id,
									dataType: 'html',
									type: 'post',
									async: false,
									beforeSend: function(){
									
									},								
									success: function ( response ) {
										$('#headcount-new-container').html(response);
						
									}
								});

							}
						});	

					}

				}
			});

			if( user.get_value('post_control') == 1 ){

				$('#department_id').parent().parent().hide();

			}

		}	
		else
		{


			$.ajax({
				url: module.get_value('base_url') + module.get_value('module_link') + '/get_amp_user_type',
				data: '',
				dataType: 'json',
				type: 'post',
				async: false,
				beforeSend: function(){
				
				},								
				success: function ( response ) {

					var amp_user_type = response.amp_user_type;

					if( amp_user_type == "division_head" ){

						$.ajax({
							url: module.get_value('base_url') + module.get_value('module_link') + '/get_department_list',
							data: '',
							dataType: 'html',
							type: 'post',
							async: false,
							beforeSend: function(){
							
							},								
							success: function ( response ) {
								
								$('#department_id').parent().empty();
								$('label[for="department_id"]').parent().find('.select-input-wrap').html(response);

							}
						});	

					}

			
					$.ajax({
						url: module.get_value('base_url') + module.get_value('module_link') + '/get_user_info_edit',
						data: 'annual_manpower_planning_id=' + annual_manpower_planning_id,
						dataType: 'json',
						type: 'post',
						async: false,			
						success: function ( response ) {

							var department_id = response.department_id;
							var department_name = response.department_name;
							var name_dept = response.lastname + " " + response.firstname;
							var division_id = response.division_id;
							var division_name = response.division_name;
							var user_id = response.user_id

							if( amp_user_type == "department_head" ){

								$('#employee_id').parent().remove();
								$('#department_id').parent().remove();
								$('label[for="department_id"]').parent().append('<input id="department_id" name="department_id" type="hidden" value="'+department_id+'" /><input id="department_id-name" class="input-text disabled" type="text" disabled="disabled" style="width:80%" value="'+department_name+'" name="department_id-name"></div>');
								$('label[for="employee_id"]').parent().append('<input id="employee_id" name="employee_id" type="hidden" value="'+user_id+'" /><input id="employee_id-name" class="input-text disabled" type="text" disabled="disabled" style="width:80%" value="'+name_dept+'" name="employee_id-name"></div>');
								$('#annual_user_division_id').parent().remove();
								$('label[for="annual_user_division_id"]').parent().append('<div class="text-input-wrap"><input id="annual_user_division_id" class="input-text" type="hidden" value="'+division_id+'" name="annual_user_division_id"><input id="annual_user_division_id-name" class="input-text disabled" type="text" disabled="disabled" style="width:80%" value="'+division_name+'" name="annual_user_division_id-name"></div>');
						
							}
							else{

								$('#department_id').val(department_id);
								$('#department_id').trigger("liszt:updated");

								$('#annual_user_division_id').parent().remove();
								$('label[for="annual_user_division_id"]').parent().append('<div class="text-input-wrap"><input id="annual_user_division_id" class="input-text" type="hidden" value="'+division_id+'" name="annual_user_division_id"><input id="annual_user_division_id-name" class="input-text disabled" type="text" disabled="disabled" style="width:80%" value="'+division_name+'" name="annual_user_division_id-name"></div>');					
								$('#employee_id').parent().empty();
								$('label[for="employee_id"]').parent().find('.text-input-wrap').append('<input id="employee_id" name="employee_id" type="hidden" value="'+user_id+'" /><input id="employee_id-name" class="input-text disabled" type="text" disabled="disabled" style="width:80%" value="'+name_dept+'" name="employee_id-name"></div>');

							}


							$.ajax({
								url: module.get_value('base_url') + module.get_value('module_link') + '/get_position_per_dept_edit',
								data: 'annual_manpower_planning_id=' + annual_manpower_planning_id,
								dataType: 'html',
								type: 'post',
								async: false,
								success: function ( response ) {
									$('#module-access-container').html(response);

								    $( "#module-access" ).delegate( 'td, th','mouseover mouseleave', function(e) {
								        if ( e.type == 'mouseover' ) {
								          $( this ).parent().addClass( "hover" );
								        }
								        else {
								          $( this ).parent().removeClass( "hover" );
								        }
								    });						
								    disable_all();
								}
							});	

							/*

							$.ajax({
								url: module.get_value('base_url') + module.get_value('module_link') + '/get_headcount',
								data: 'department_id=' + department_id + '&user_id=' + user_id + '&record_id=' + annual_manpower_planning_id,
								dataType: 'html',
								type: 'post',
								async: false,
								beforeSend: function(){
								
								},								
								success: function ( response ) {
									$('#headcount-container').html(response);
					
								}
							});

							*/

							$.ajax({
								url: module.get_value('base_url') + module.get_value('module_link') + '/get_existing_headcount',
								data: 'department_id=' + department_id + '&user_id=' + user_id + '&record_id=' + annual_manpower_planning_id + '&year=' + $('#year').val(),
								dataType: 'html',
								type: 'post',
								async: false,
								beforeSend: function(){
								
								},								
								success: function ( response ) {
									$('#headcount-existing-container').html(response);
					
								}
							});

							
							$.ajax({
								url: module.get_value('base_url') + module.get_value('module_link') + '/get_new_headcount',
								data: 'department_id=' + department_id + '&user_id=' + user_id + '&record_id=' + annual_manpower_planning_id,
								dataType: 'html',
								type: 'post',
								async: false,
								beforeSend: function(){
								
								},								
								success: function ( response ) {
									$('#headcount-new-container').html(response);
					
								}
							});


							$('.position_with_incumbent').each(function(){

								var parent = $(this);
								update_remarks_list(parent);


							});

						}
					});	
					
				}
			});
		} 

		$('#company_id').live('change',function(){

			if( $('#company_id').val() > 0 ){

				$.ajax({
					url: module.get_value('base_url') + module.get_value('module_link') + '/get_department_list',
					data: 'company_id=' + $('#company_id').val(),
					dataType: 'html',
					type: 'post',
					async: false,
					beforeSend: function(){
					
					},								
					success: function ( response ) {

						$('#department_id').parent().parent().show();
						$('#department_id').parent().empty();
						$('label[for="department_id"]').parent().find('.select-input-wrap').html(response);
						$('#department_id').chosen();

					}
				});	

			}
			else{

				$('#department_id').parent().parent().hide();

				var department_id = 0;
				var user_id = 0;

				$.ajax({
					url: module.get_value('base_url') + module.get_value('module_link') + '/get_position_per_dept',
					data: 'department_id=' + department_id + '&user_id=' + user_id,
					dataType: 'html',
					type: 'post',
					async: false,
					beforeSend: function(){
					
					},								
					success: function ( response ) {
						$('#module-access-container').html(response);

					    $( "#module-access" ).delegate( 'td, th','mouseover mouseleave', function(e) {
					        if ( e.type == 'mouseover' ) {
					          $( this ).parent().addClass( "hover" );
					        }
					        else {
					          $( this ).parent().removeClass( "hover" );
					        }
					    });				
					}
				});	

				$.ajax({
					url: module.get_value('base_url') + module.get_value('module_link') + '/get_existing_headcount',
					data: 'department_id=' + department_id + '&user_id=' + user_id + '&record_id=-1&year=' + $('#year').val(),
					dataType: 'html',
					type: 'post',
					async: false,
					beforeSend: function(){
					
					},								
					success: function ( response ) {
						$('#headcount-existing-container').html(response);
		
					}
				});

				$('.add_new_job_container').show();

				$.ajax({
					url: module.get_value('base_url') + module.get_value('module_link') + '/get_new_headcount',
					data: 'department_id=' + department_id + '&user_id=' + user_id + '&record_id=-1',
					dataType: 'html',
					type: 'post',
					async: false,
					beforeSend: function(){
					
					},								
					success: function ( response ) {
						$('#headcount-new-container').html(response);
		
					}
				});


			}

		})

		$('#department_id').live('change',function(){


				$.ajax({
					url: module.get_value('base_url') + module.get_value('module_link') + '/get_user_info',
					data: 'amp_user_type=division_head&department_id='+$('#department_id').val(),
					dataType: 'json',
					type: 'post',
					async: false,
					beforeSend: function(){
					
					},								
					success: function ( response ) {

						var department_id = response.department_id;
						var department_name = response.department_name;
						var name_dept = response.lastname + " " + response.firstname;
						var division_id = response.division_id;
						var division_name = response.division_name;
						var user_id = response.user_id

						$('#employee_id').parent().empty();
						$('#annual_user_division_id').parent().remove();

						if( response.lastname != null && response.firstname != null ){
							$('label[for="employee_id"]').parent().find('.text-input-wrap').append('<input id="employee_id" name="employee_id" type="hidden" value="'+user_id+'" /><input id="employee_id-name" class="input-text disabled" type="text" disabled="disabled" style="width:80%" value="'+name_dept+'" name="employee_id-name"></div>');
						}
						else{
							$('label[for="employee_id"]').parent().find('.text-input-wrap').append('<input id="employee_id" name="employee_id" type="hidden" value="" /><input id="employee_id-name" class="input-text disabled" type="text" disabled="disabled" style="width:80%" value="" name="employee_id-name"></div>');
						}

						if( response.division_name != null ){
							$('label[for="annual_user_division_id"]').parent().append('<div class="text-input-wrap"><input id="annual_user_division_id" class="input-text" type="hidden" value="'+division_id+'" name="annual_user_division_id"><input id="annual_user_division_id-name" class="input-text disabled" type="text" disabled="disabled" style="width:80%" value="'+division_name+'" name="annual_user_division_id-name"></div>');
						}
						else{
							$('label[for="annual_user_division_id"]').parent().append('<div class="text-input-wrap"><input id="annual_user_division_id" class="input-text" type="hidden" value="" name="annual_user_division_id"><input id="annual_user_division_id-name" class="input-text disabled" type="text" disabled="disabled" style="width:80%" value="" name="annual_user_division_id-name"></div>');
						}

						

						$.ajax({
							url: module.get_value('base_url') + module.get_value('module_link') + '/get_position_per_dept',
							data: 'department_id=' + department_id + '&user_id=' + user_id,
							dataType: 'html',
							type: 'post',
							async: false,
							beforeSend: function(){
							
							},								
							success: function ( response ) {
								$('#module-access-container').html(response);

							    $( "#module-access" ).delegate( 'td, th','mouseover mouseleave', function(e) {
							        if ( e.type == 'mouseover' ) {
							          $( this ).parent().addClass( "hover" );
							        }
							        else {
							          $( this ).parent().removeClass( "hover" );
							        }
							    });				
							}
						});	

						/*
						$.ajax({
							url: module.get_value('base_url') + module.get_value('module_link') + '/get_headcount',
							data: 'department_id=' + department_id + '&user_id=' + user_id + '&record_id=' + annual_manpower_planning_id,
							dataType: 'html',
							type: 'post',
							async: false,
							beforeSend: function(){
							
							},								
							success: function ( response ) {
								$('#headcount-container').html(response);
				
							}
						});
						*/

						$.ajax({
							url: module.get_value('base_url') + module.get_value('module_link') + '/get_existing_headcount',
							data: 'department_id=' + department_id + '&user_id=' + user_id + '&record_id=-1&year=' + $('#year').val(),
							dataType: 'html',
							type: 'post',
							async: false,
							beforeSend: function(){
							
							},								
							success: function ( response ) {
								$('#headcount-existing-container').html(response);
				
							}
						});

						$('.add_new_job_container').show();

						$.ajax({
							url: module.get_value('base_url') + module.get_value('module_link') + '/get_new_headcount',
							data: 'department_id=' + department_id + '&user_id=' + user_id + '&record_id=-1',
							dataType: 'html',
							type: 'post',
							async: false,
							beforeSend: function(){
							
							},								
							success: function ( response ) {
								$('#headcount-new-container').html(response);
				
							}
						});

						

					}
				});	

		});

							

		$('#year').live('change', function(){
			if ($('#record_id') != -1){
				var department_id = $('#department_id').val();
				var year = $(this).val();
				$.ajax({
					url: module.get_value('base_url') + module.get_value('module_link') + '/validation',
					data: 'department_id=' + department_id + '&year=' + year + '&record_id=' + module.get_value('record_id'),
					dataType: 'json',
					type: 'post',
					async: false,
					beforeSend: function(){
					
					},								
					success: function ( response ) {
						if (response.err == 1){
							message_growl(response.type, response.message);			
						}
						else{

							$.ajax({
								url: module.get_value('base_url') + module.get_value('module_link') + '/validation',
								data: 'department_id=' + department_id + '&year=' + year + '&record_id=' + module.get_value('record_id'),
								dataType: 'json',
								type: 'post',
								async: false,
								beforeSend: function(){
								
								},								
								success: function ( response ) {
									if (response.err == 1){
										message_growl(response.type, response.message);			
									}
									else{

										$.ajax({
											url: module.get_value('base_url') + module.get_value('module_link') + '/get_previous_headcount',
											data: 'department_id=' + department_id + '&year=' + year + '&record_id=' + module.get_value('record_id'),
											dataType: 'json',
											type: 'post',
											async: false,
											beforeSend: function(){
											
											},								
											success: function ( response ) {
												
												sum = 0;
												$.each(response, function(i, data) {

													$('.existing_position_id').each(function(){

														if( $(this).val() == data.position_id ){

															var parent = $(this).parents('tbody');

															parent.find('.existing_job_headcount_previous').val(data.previous_amp);

														}

													});

												});
								
											}
										});
										
									}
								}
							});	



						}
					}
				});					
			}
		});

		$('.add_new_headcount_job').live('click',function(){				

			$('.new_headcount_position_empty').hide();

			var department_id = $('#department_id').val();

			$.ajax({
				url: module.get_value('base_url') + module.get_value('module_link') + '/get_form_new_headcount_position',
				data: 'department_id=' + department_id,
				dataType: 'html',
				type: 'post',
				async: false,
				beforeSend: function(){
				
				},								
				success: function ( response ) {
					$('#module-new-headcount').append(response);
				}
			});	

			return false;

		});

		$('.add_new_job').live('click',function(){				

			$('#module-headcount').show();
			$('.new_job_headcount').show();

			var department_id = $('#department_id').val();

			$.ajax({
				url: module.get_value('base_url') + module.get_value('module_link') + '/get_form_new_position',
				data: 'department_id=' + department_id,
				dataType: 'html',
				type: 'post',
				async: false,
				beforeSend: function(){
				
				},								
				success: function ( response ) {
					$('.new_job_headcount').append(response);
				}
			});	

			return false;

		});

		$('.add_existing_job').live('click',function(){

			$('#module-headcount').show();
			$('.existing_job_headcount').show();			

			var department_id = $('#department_id').val();

			$.ajax({
				url: module.get_value('base_url') + module.get_value('module_link') + '/get_form_existing_position',
				data: 'department_id=' + department_id,
				dataType: 'html',
				type: 'post',
				async: false,
				beforeSend: function(){
				
				},								
				success: function ( response ) {
					$('.existing_job_headcount').append(response);
				}
			});	

			return false;

		});


		$('.delete-single').live('click',function(){
			var obj = $(this);
			Boxy.ask("Do you want to delete this row?",["Yes","No"],
				function(choice){
					if (choice == "Yes"){

						if( obj.hasClass('delete_new_position') ){

							$(obj).parent().parent().remove();
							zebra_structure_list();

							var form_count_new = $('.new_job_form').length;
							var form_count_existing = $('.existing_job_form').length;
											 
							if( form_count_new == 0 ){

								if( form_count_existing == 0 ){

									$('#module-headcount').hide();

								}

								$('.new_job_headcount').hide();

							}

						}
						else if( obj.hasClass('delete_existing_position') ){

							$(obj).parent().parent().remove();
							zebra_structure_list();

							var form_count_new = $('.new_job_form').length;
							var form_count_existing = $('.existing_job_form').length;

							if( form_count_existing == 0 ){

								if( form_count_new == 0 ){

									$('#module-headcount').hide();

								}

								$('.existing_job_headcount').hide();

							}

						}
						else if( obj.hasClass('delete_new_headcount_position') ){

							$(obj).parent().parent().parent().remove();
							zebra_structure_list();

							var new_headcount_position_row = $('.new_headcount_position_row').length;

							if( new_headcount_position_row == 0 ){

								$('.new_headcount_position_empty').show();

							}

						}

					}
				}
			);

		});

		$('.existing_headcount_month_value').live('change',function(){

			var parent_node = $(this).parents('tbody');
			var total_headcount = 0;
			var count = 0;

			parent_node.find('.existing_headcount_month_value').each(function(){

				var value = $.trim($(this).val());
				var parse = parseInt(value);

				if( value != '' ){
					if( parse || value == 0 ){
						$(this).val(parse);
						total_headcount = total_headcount + parse;
					}
					else{
						$(this).val(0);
						count++;
					}
				}
				else{
					$(this).val(0);
				}
			});

			if( count > 0 ){
				message_growl('error','Headcount must be a number');
			}

			parent_node.find('.existing_headcount_month_total').val(total_headcount);

		});

		$('.new_headcount_month_value').live('change',function(){

			var parent_node = $(this).parents('tbody');
			var total_headcount = 0;
			var count = 0;

			parent_node.find('.new_headcount_month_value').each(function(){

				var parse = parseInt($(this).val());

				if( $(this).val() != '' ){
					if( parse || $(this).val() == 0 ){
						$(this).val(parse);
						total_headcount = total_headcount + parse;
					}
					else{
						$(this).val(0);
						count++;
					}
				}
			});

			if( count > 0 ){
				message_growl('error','Headcount must be a number');
			}

			parent_node.find('.new_headcount_month_total').val(total_headcount);

		});

		
		$('.manpower_setup').live('change',function(){

			var parent = $(this).parents('tr');
			update_remarks_list(parent);

		});


		

	};

	if( module.get_value('view') == "detail" )
	{
		$.ajax({
			url: module.get_value('base_url') + module.get_value('module_link') + '/get_user_info_detail',
			data: 'annual_manpower_planning_id=' + $('#record_id').val(),
			dataType: 'json',
			type: 'post',
			async: false,			
			success: function ( response ) {
				var department_id = response.department_id;
				var name_dept = response.department_name;
				var division_id = response.division_id;
				var division_name = response.division_name;
				$('label[for="employee_id"]').next().remove();
				$('label[for="annual_user_division_id"]').next().remove();
				$('label[for="employee_id"]').parent().append('<div class="text-input-wrap">'+name_dept+'</div>');
				$('label[for="annual_user_division_id"]').parent().append('<div class="text-input-wrap">'+division_name+'</div>');									
			}
		});	

		$('.reevaluate_incumbent_all').live('click',function(){
			var checked = $('.reevaluate_incumbent_all:checked').length;

			if( checked == 1 ){
				$('.reevaluate_incumbent').attr('checked',checked);
			}
			else{
				$('.reevaluate_incumbent').removeAttr('checked');
			}
		});

		$('.reevaluate_existing_headcount_all').live('click',function(){
			var checked = $('.reevaluate_existing_headcount_all:checked').length;

			if( checked == 1 ){
				$('.reevaluate_existing_headcount').attr('checked',checked);
			}
			else{
				$('.reevaluate_existing_headcount').removeAttr('checked');
			}
		});

		$('.reevaluate_new_headcount_all').live('click',function(){
			var checked = $('.reevaluate_new_headcount_all:checked').length;

			if( checked == 1 ){
				$('.reevaluate_new_headcount').attr('checked',checked);
			}
			else{
				$('.reevaluate_new_headcount').removeAttr('checked');
			}
		});

	};

    $('a.approve-class_list').live('click', function () {

        change_status($(this).parent().parent().parent().attr("id"),3);
    });	

    $('a.disapprove-class_list').live('click', function () {    


        var record_id = $(this).parent().parent().parent().attr("id");        
        Boxy.ask("Are you sure you want to decline this request?", ["Yes", "No"],function( choice ) {
        if(choice == "Yes"){
              change_status(record_id,4);
            }
        },
        {
            title: "Decline Annual Manpower Request"
        });

              
    }); 
    
	// $('.icon-label a').hide();
	// $('.icon-label').append('<a class="icon-16-export" href="javascript:void(0)" onclick="export_list();"><span>Export to excel</span></a>');
})


function update_remarks_list( parent ){

	$.ajax({
		url: module.get_value('base_url') + module.get_value('module_link') + '/get_annual_manpower_remarks',
		dataType: 'json',
		type: 'post',
		async: false,
		beforeSend: function(){
		
		},								
		success: function ( response ) {

			$.each(response, function() {

				var remarks = this.remarks;
				var remarks_id = this.annual_manpower_planning_remarks_id;
				var remark_selected = 0;

				parent.find('.manpower_setup').each(function(){

				 	if( ( $(this).val() == remarks_id ) ){

				 		$(this).addClass('main_selected');
				 		remark_selected++;
				 	}

				});

				if( remark_selected == 0 ){

					parent.find('.manpower_setup').each(function(){
						$(this).find('option[value="'+remarks_id+'"]').css('display','block');
					});

				}
				else{

					parent.find('.manpower_setup').each(function(){

						if( !( $(this).hasClass('main_selected') ) ){
							$(this).find('option[value="'+remarks_id+'"]').css('display','none');
						}

					});

					parent.find('.main_selected').removeClass('main_selected');

				}


			});

		}
	});

}

function export_list()
{
	var record_id = $('#record_id').val();
	$('#record-form').attr('action', module.get_value('base_url') + module.get_value('module_link') + '/excel_export/'+ record_id +'');
	$('#record-form').submit();
	$('#record-form').attr('action', '');
	return false;
}

function change_status(record_id, form_status_id,from_detail,remarks) 
{

	if( module.get_value('view') == 'index' ){
		var data = 'record_id='+record_id;
	}
	else{
		var data = $('#record-form').serialize();
	}

    $.ajax({
        url: module.get_value('base_url') + module.get_value('module_link') + '/change_status',
        data: data + '&form_status_id=' + form_status_id + '&remarks=' + remarks,
        type: 'post',
        dataType: 'json',
        success: function(response) 
        {
  
    		if( from_detail == true ){
    			window.location.href = module.get_value('base_url') + module.get_value('module_link');    
    		}
    		else{
    			message_growl(response.type, response.message);
	            //window.location.reload( false );
	    		$('#jqgridcontainer').trigger('reloadGrid'); 
    		}
    		
        }
    });
    
}

function validate_ajax_save( on_success, is_wizard , callback ){

	var count = 0;

	$('.new_headcount_position').each(function(){
		if( $(this).val() == '' ){
			count++;
		}
	});

	if( count > 0 ){
		add_error('new_headcount_position', 'Position', "This field is mandatory.");
	}

	if(error.length > 0){
		var error_str = "Please correct the following errors:<br/><br/>";
		for(var i in error){
			if(i == 0) $('#'+error[i][0]).focus(); //set focus on the first error
			error_str = error_str + (parseFloat(i)+1) +'. '+error[i][1]+" - "+error[i][2]+"<br/>";
		}
		$('#message-container').html(message_growl('error', error_str));
		
		//reset errors
		error = new Array();
		error_ctr = 0
		return false;
	}

	$('.manpower_setup').each(function(){
		$(this).removeAttr('disabled');
	});

	ajax_save( on_success, is_wizard , callback );

}


function ajax_save_custom(record_id, form_status_id) 
{	

   	var reevaluate_count = 0;

	$('.reevaluate_incumbent').each(function(){
		if( $(this).attr('checked') ){
			reevaluate_count++;
		}
	});

	$('.reevaluate_existing_headcount').each(function(){
		if( $(this).attr('checked') ){
			reevaluate_count++;
		}
	});

	$('.reevaluate_new_headcount').each(function(){
		if( $(this).attr('checked') ){
			reevaluate_count++;
		}
	});

	if(form_status_id == 'approve')
	{
		if( reevaluate_count == 0 ){
	   		change_status(record_id,3,true);
	   	}
	   	else{
	   		message_growl('error', 'Please ensure that all the information are no need for re-evaluation');
	   	}
	}
	else
	{	

		if( reevaluate_count != 0 ){

			/*

			Boxy.ask("Are you sure you want to decline this request?", ["Yes", "No"],function( choice ) {
	        if(choice == "Yes"){
	              change_status(record_id,4,true);
	            }
	        },
	        {
	            title: "Decline Annual Manpower Request"
	        });

	        */

	        $.ajax({
			        url: module.get_value('base_url') + module.get_value('module_link') + '/get_remarks_form',
			        data: 'record_id=' + record_id,
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
									title: 'Annual Manpower Planning',
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
			message_growl('error', 'Please check all the information that needs to be evaluated');
		}
	}
}

function goto_detail( data )
{
    if (data.record_id > 0 && data.record_id != '') 
    {
        module.set_value('record_id', data.record_id);    
        window.location.href = module.get_value('base_url') + module.get_value('module_link') + '/detail/' + module.get_value('record_id');         
    }
}

function backtolistview()
{
	window.location.href = module.get_value('base_url') + module.get_value('module_link');	
}

function zebra_structure_list()
{
	var ctr = 0;
	$('.structure_list tr').each(function(){
		$(this).removeClass();
		if ((ctr % 2) == 1){
			$(this).addClass('odd');
		}
		else{
			$(this).addClass('even');
		}
		ctr++;
	})
}

function disable_all()
{
	if($('#record_id').val() != '-1')
	{
		if($('#annual_status_id').val() != 1 && $('#annual_status_id').val() != 4)
		{
			$('input').attr('disabled', true);
			$('select').attr('disabled', true);
			$('textarea').attr('disabled', true);
		}
	}
}