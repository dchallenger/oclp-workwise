$(document).ready(function () {
	if (module.get_value('view') == 'edit') {
		if ($('#record_id').val() == '-1'){
			$('label[for="subordinates_division_id"]').parent().parent().parent().hide();
			$('label[for="subordinates_project_id"]').parent().parent().parent().hide();
			$('label[for="subordinates_group_id"]').parent().parent().parent().hide();
			$('label[for="subordinates_department_id"]').parent().parent().parent().hide();
			$('label[for="subordinates_position_id"]').parent().parent().parent().hide();

			$('#multiselect-organization_category_value_id option').remove();
			$('#employee_id_reporting_to option').remove();
			$('#multiselect-subordinates_division_pos_employee_id option').remove();
			$('#multiselect-subordinates_division_position_id option').remove();
			$('#multiselect-subordinates_project_position_id option').remove();
			$('#multiselect-subordinates_project_pos_employee_id option').remove();
			$('#multiselect-subordinates_group_position_id option').remove();
			$('#multiselect-subordinates_group_pos_employee_id option').remove();
			$('#multiselect-subordinates_department_position_id option').remove();
			$('#multiselect-subordinates_department_pos_employee_id option').remove();
		}

		$('#organization_category_id').bind('change',function(){
			var organization_category_id = $(this).val();
			$.ajax({
				url: module.get_value('base_url') + module.get_value('module_link') + '/get_category_value',
				data: 'organization_category_id=' + organization_category_id + '&record_id=' + $('#record_id').val(),
				type: 'post',
				dataType: 'html',
				success: function(response) {
					$('#multiselect-organization_category_value_id option').remove();				
					$('#multiselect-organization_category_value_id').append(response);				
					$('#multiselect-organization_category_value_id').multiselect('refresh');   									
				}
			});
			if (organization_category_id == 1 || organization_category_id == 3 || organization_category_id == 4){
				$('label[for="subordinates_division_id"]').parent().parent().parent().hide();
				$('label[for="subordinates_project_id"]').parent().parent().parent().hide();
				$('label[for="subordinates_group_id"]').parent().parent().parent().hide();
				$('label[for="subordinates_department_id"]').parent().parent().parent().hide();				
		        $("#multiselect-organization_category_value_id").multiselect({
		            close: function(event, ui){
					    var temp = $.map($('select[name="multiselect-organization_category_value_id"]').multiselect("getChecked"),function( input ){
					    	return input.value;
					    });
						$.ajax({
							url: module.get_value('base_url') + module.get_value('module_link') + '/get_position_by_category',
							data: 'organization_category_value_id=' + temp + '&organization_category_id=' + $('#organization_category_id').val() + '&record_id=' + $('#record_id').val(),
							type: 'post',
							dataType: 'html',
							success: function(response) {
								$('#multiselect-subordinates_position_id option').remove();				
								$('#multiselect-subordinates_position_id').append(response);				
								$('#multiselect-subordinates_position_id').multiselect('refresh');
								$('#multiselect-subordinates_employee_id option').remove();
								$('#multiselect-subordinates_employee_id').multiselect('refresh');
								$('label[for="subordinates_position_id"]').parent().parent().parent().show();
							}
						});				        
		            }
		        });				
			}
			else{
				$('label[for="subordinates_division_id"]').parent().parent().parent().show();
				$('label[for="subordinates_project_id"]').parent().parent().parent().show();
				$('label[for="subordinates_group_id"]').parent().parent().parent().show();
				$('label[for="subordinates_department_id"]').parent().parent().parent().show();
				$('label[for="subordinates_position_id"]').parent().parent().parent().hide();							
			}
		});		

		$('#position_id').bind('change',function(){
			var position_id = $(this).val();
			$.ajax({
				url: module.get_value('base_url') + module.get_value('module_link') + '/get_employee_by_position',
				data: 'position_id=' + position_id + '&record_id=' + $('#record_id').val(),
				type: 'post',
				dataType: 'html',
				success: function(response) {
					$('#employee_id_reporting_to option').remove();				
					$('#employee_id_reporting_to').append(response);				
					$('#employee_id_reporting_to').trigger("liszt:updated");
				}
			});			
		});

		if ($('#record_id').val() != '-1'){
			$('#position_id').trigger('change');
			$('#organization_category_id').trigger('change');

		    // var organization_category_value_id = $.map($('select[name="multiselect-organization_category_value_id"]').multiselect("getChecked"),function( input ){
		    // 	return input.value;
		    // });

			var organization_category_id = $('#organization_category_id').val();
			var organization_category_value_id = $('#organization_category_value_id').val();
			var position_id = $.map($("#multiselect-subordinates_position_id option:selected"),function(a){return a.value;}).join(',');
			var employee_ids = $.map($('select[name="multiselect-subordinates_employee_id"]').multiselect("getChecked"),function( input ){
			    	return input.value;
			    });

			if (organization_category_id == 1 || organization_category_id == 4){			
				$.ajax({
					url: module.get_value('base_url') + module.get_value('module_link') + '/get_position_by_category',
					data: 'record_id=' + $('#record_id').val() + '&organization_category_value_id=' + organization_category_value_id + '&organization_category_id=' + organization_category_id,
					type: 'post',
					dataType: 'html',
					success: function(response) {
						$('#multiselect-subordinates_position_id option').remove();				
						$('#multiselect-subordinates_position_id').append(response);				
						$('#multiselect-subordinates_position_id').multiselect('refresh');
						$('label[for="subordinates_position_id"]').parent().parent().parent().show();
					}
				});

				$.ajax({
					url: module.get_value('base_url') + module.get_value('module_link') + '/get_user_by_position',
					data: 'record_id=' + $('#record_id').val() + '&position_id=' + position_id + '&organization_category_id=' + organization_category_id + '&organization_category_value_id=' + organization_category_value_id + '&employee_id=' + employee_ids,
					type: 'post',
					dataType: 'html',
					success: function(response) {
						$('#multiselect-subordinates_employee_id option').remove();				
						$('#multiselect-subordinates_employee_id').append(response);				
						$('#multiselect-subordinates_employee_id').multiselect('refresh');
					}
				});									
			}	
			else{
				$.each([1,2,3,4],function(index, value){
					switch (value){
						case 1:					
						var organization_category_value_id = $.map($("#multiselect-subordinates_division_id option:selected"),function(a){return a.value;}).join(',');
						$.ajax({
							url: module.get_value('base_url') + module.get_value('module_link') + '/get_position_by_category_compli',
							data: 'record_id=' + $('#record_id').val() + '&organization_category_id=' + value + '&organization_category_value_id=' + organization_category_value_id,
							type: 'post',
							dataType: 'html',
							success: function(response) {
								$('#multiselect-subordinates_division_position_id option').remove();				
								if (response && response != ''){
									setTimeout(function () {
										$('#multiselect-subordinates_division_position_id').append(response);				
										$('#multiselect-subordinates_division_position_id').multiselect('refresh');	
										$('label[for="subordinates_position_id"]').parent().parent().parent().show();										
									}, 100)
								}
							}
						});

		            	var organization_category_id = $('#organization_category_id').val();
		            	var organization_category_value_id =$.map($("select[name='multiselect-organization_category_value_id'] option:selected"),function(a){return a.value;}).join(',');
		            	var position_id =$.map($("select[name='multiselect-subordinates_division_position_id'] option:selected"),function(a){return a.value;}).join(',');

						$.ajax({
							url: module.get_value('base_url') + module.get_value('module_link') + '/get_user_by_position_compli',
							data: 'record_id=' + $('#record_id').val() + '&position_id=' + position_id + '&organization_category_id=1&organization_category_value_id=' + organization_category_value_id,
							type: 'post',
							dataType: 'html',
							success: function(response) {
								$('#multiselect-subordinates_division_pos_employee_id option').remove();				
								if (response && response != ''){
									setTimeout(function () {
										$('#multiselect-subordinates_division_pos_employee_id').append(response);				
										$('#multiselect-subordinates_division_pos_employee_id').multiselect('refresh');										
									}, 100)									
								}
							}
						});	
						break;
						case 2:					
						var organization_category_value_id = $.map($("#multiselect-subordinates_group_id option:selected"),function(a){return a.value;}).join(',');
						$.ajax({
							url: module.get_value('base_url') + module.get_value('module_link') + '/get_position_by_category_compli',
							data: 'record_id=' + $('#record_id').val() + '&organization_category_id=' + value + '&organization_category_value_id=' + organization_category_value_id,
							type: 'post',
							dataType: 'html',
							success: function(response) {
								$('#multiselect-subordinates_group_position_id option').remove();				
								if (response && response != ''){
									setTimeout(function () {
										$('#multiselect-subordinates_group_position_id').append(response);				
										$('#multiselect-subordinates_group_position_id').multiselect('refresh');	
										$('label[for="subordinates_position_id"]').parent().parent().parent().show();										
									}, 100)									
								}
							}
						});

		            	var organization_category_id = $('#organization_category_id').val();
		            	var organization_category_value_id =$.map($("select[name='multiselect-organization_category_value_id'] option:selected"),function(a){return a.value;}).join(',');
		            	var position_id =$.map($("select[name='multiselect-subordinates_group_position_id'] option:selected"),function(a){return a.value;}).join(',');

						$.ajax({
							url: module.get_value('base_url') + module.get_value('module_link') + '/get_user_by_position_compli',
							data: 'record_id=' + $('#record_id').val() + '&position_id=' + position_id + '&organization_category_id=2&organization_category_value_id=' + organization_category_value_id,
							type: 'post',
							dataType: 'html',
							success: function(response) {
								$('#multiselect-subordinates_group_pos_employee_id option').remove();				
								if (response && response != ''){
									setTimeout(function () {
										$('#multiselect-subordinates_group_pos_employee_id').append(response);				
										$('#multiselect-subordinates_group_pos_employee_id').multiselect('refresh');										
									}, 100)									
								}
							}
						});	
						break;	
						case 3:					
						var organization_category_value_id = $.map($("#multiselect-subordinates_department_id option:selected"),function(a){return a.value;}).join(',');
						$.ajax({
							url: module.get_value('base_url') + module.get_value('module_link') + '/get_position_by_category_compli',
							data: 'record_id=' + $('#record_id').val() + '&organization_category_id=' + value + '&organization_category_value_id=' + organization_category_value_id,
							type: 'post',
							dataType: 'html',
							success: function(response) {
								$('#multiselect-subordinates_department_position_id option').remove();				
								if (response && response != ''){
									setTimeout(function () {
										$('#multiselect-subordinates_department_position_id').append(response);				
										$('#multiselect-subordinates_department_position_id').multiselect('refresh');
										$('label[for="subordinates_position_id"]').parent().parent().parent().show();											
									}, 100)									
								}
							}
						});

		            	var organization_category_id = $('#organization_category_id').val();
		            	var organization_category_value_id =$.map($("select[name='multiselect-organization_category_value_id'] option:selected"),function(a){return a.value;}).join(',');
		            	var position_id =$.map($("select[name='multiselect-subordinates_department_position_id'] option:selected"),function(a){return a.value;}).join(',');

						$.ajax({
							url: module.get_value('base_url') + module.get_value('module_link') + '/get_user_by_position_compli',
							data: 'record_id=' + $('#record_id').val() + '&position_id=' + position_id + '&organization_category_id=3&organization_category_value_id=' + organization_category_value_id,
							type: 'post',
							dataType: 'html',
							success: function(response) {
								$('#multiselect-subordinates_department_pos_employee_id option').remove();				
								if (response && response != ''){
									$('#multiselect-subordinates_department_pos_employee_id').append(response);				
									$('#multiselect-subordinates_department_pos_employee_id').multiselect('refresh');
								}
							}
						});	
						break;
						case 4:					
						var organization_category_value_id = $.map($("#multiselect-subordinates_project_id option:selected"),function(a){return a.value;}).join(',');
						$.ajax({
							url: module.get_value('base_url') + module.get_value('module_link') + '/get_position_by_category_compli',
							data: 'record_id=' + $('#record_id').val() + '&organization_category_id=' + value + '&organization_category_value_id=' + organization_category_value_id,
							type: 'post',
							dataType: 'html',
							success: function(response) {
								$('#multiselect-subordinates_department_project_id option').remove();				
								if (response && response != ''){
									$('#multiselect-subordinates_department_project_id').append(response);				
									$('#multiselect-subordinates_department_project_id').multiselect('refresh');
										$('label[for="subordinates_position_id"]').parent().parent().parent().show();	
								}
							}
						});

		            	var organization_category_id = $('#organization_category_id').val();
		            	var organization_category_value_id =$.map($("select[name='multiselect-organization_category_value_id'] option:selected"),function(a){return a.value;}).join(',');
		            	var position_id =$.map($("select[name='multiselect-subordinates_project_position_id'] option:selected"),function(a){return a.value;}).join(',');

						$.ajax({
							url: module.get_value('base_url') + module.get_value('module_link') + '/get_user_by_position_compli',
							data: 'record_id=' + $('#record_id').val() + '&position_id=' + position_id + '&organization_category_id=4&organization_category_value_id=' + organization_category_value_id,
							type: 'post',
							dataType: 'html',
							success: function(response) {
								$('#multiselect-subordinates_project_pos_employee_id option').remove();				
								if (response && response != ''){
									$('#multiselect-subordinates_project_pos_employee_id').append(response);				
									$('#multiselect-subordinates_project_pos_employee_id').multiselect('refresh');
								}
							}
						});	
						break;																	
					}				
				});	
			}	
		}    

		//for division and project
        $("#multiselect-subordinates_position_id").multiselect({
            close: function(event, ui){
            	var organization_category_id = $('#organization_category_id').val();
			    var organization_category_value_id = $.map($('select[name="multiselect-organization_category_value_id"]').multiselect("getChecked"),function( input ){
			    	return input.value;
			    });
			    var position_id = $.map($('select[name="multiselect-subordinates_position_id"]').multiselect("getChecked"),function( input ){
			    	return input.value;
			    });

				var employee_ids = $.map($('select[name="multiselect-subordinates_employee_id"]').multiselect("getChecked"),function( input ){
			    	return input.value;
			    });

				$.ajax({
					url: module.get_value('base_url') + module.get_value('module_link') + '/get_user_by_position',
					data: 'position_id=' + position_id + '&organization_category_id=' + organization_category_id + '&organization_category_value_id=' + organization_category_value_id + '&employee_id=' + employee_ids,
					type: 'post',
					dataType: 'html',
					success: function(response) {

						$('#multiselect-subordinates_employee_id option').remove();				
						$('#multiselect-subordinates_employee_id').append(response);				
						$('#multiselect-subordinates_employee_id').multiselect('refresh');
					}
				});				        
            }
        });	

		// division
        $("#multiselect-subordinates_division_id").multiselect({
            close: function(event, ui){
			    var temp = $.map($('select[name="multiselect-subordinates_division_id"]').multiselect("getChecked"),function( input ){
			    	return input.value;
			    });

			    var position_id = $.map($('select[name="multiselect-subordinates_division_position_id"]').multiselect("getChecked"),function( input ){
			    	return input.value;
			    });

				$.ajax({
					url: module.get_value('base_url') + module.get_value('module_link') + '/get_position_by_category',
					data: 'organization_category_value_id=' + temp + '&organization_category_id=1' + '&record_id=' + $('#record_id').val() + '&position_id=' + position_id,
					type: 'post',
					dataType: 'html',
					success: function(response) {
						$('#multiselect-subordinates_division_position_id option').remove();				
						$('#multiselect-subordinates_division_position_id').append(response);				
						$('#multiselect-subordinates_division_position_id').multiselect('refresh');
					}
				});				        
            }
        });

        $("#multiselect-subordinates_division_position_id").multiselect({
            close: function(event, ui){
            	var organization_category_id = $('#organization_category_id').val();
			    var organization_category_value_id = $.map($('select[name="multiselect-organization_category_value_id"]').multiselect("getChecked"),function( input ){
			    	return input.value;
			    });            	
			    var position_id = $.map($('select[name="multiselect-subordinates_division_position_id"]').multiselect("getChecked"),function( input ){
			    	return input.value;
			    });

			    var employee_ids = $.map($('select[name="multiselect-subordinates_division_pos_employee_id"]').multiselect("getChecked"),function( input ){
			    	return input.value;
			    });

				$.ajax({
					url: module.get_value('base_url') + module.get_value('module_link') + '/get_user_by_position',
					data: 'position_id=' + position_id + '&organization_category_id=' + organization_category_id + '&organization_category_value_id=' + organization_category_value_id + '&employee_id=' + employee_ids,
					type: 'post',
					dataType: 'html',
					success: function(response) {
						$('#multiselect-subordinates_division_pos_employee_id option').remove();				
						if (response && response != ''){
							$('#multiselect-subordinates_division_pos_employee_id').append(response);				
							$('#multiselect-subordinates_division_pos_employee_id').multiselect('refresh');
						}
					}
				});				        
            }
        });	

		// project
        $("#multiselect-subordinates_project_id").multiselect({
            close: function(event, ui){
			    var temp = $.map($('select[name="multiselect-subordinates_project_id"]').multiselect("getChecked"),function( input ){
			    	return input.value;
			    });

			    var position_id = $.map($('select[name="multiselect-subordinates_project_position_id"]').multiselect("getChecked"),function( input ){
			    	return input.value;
			    });

				$.ajax({
					url: module.get_value('base_url') + module.get_value('module_link') + '/get_position_by_category',
					data: 'organization_category_value_id=' + temp + '&organization_category_id=4' + '&record_id=' + $('#record_id').val() + '&position_id=' + position_id,
					type: 'post',
					dataType: 'html',
					success: function(response) {
						$('#multiselect-subordinates_project_position_id option').remove();				
						$('#multiselect-subordinates_project_position_id').append(response);				
						$('#multiselect-subordinates_project_position_id').multiselect('refresh');
					}
				});				        
            }
        });

        $("#multiselect-subordinates_project_position_id").multiselect({
            close: function(event, ui){
            	var organization_category_id = $('#organization_category_id').val();
			    var organization_category_value_id = $.map($('select[name="multiselect-organization_category_value_id"]').multiselect("getChecked"),function( input ){
			    	return input.value;
			    });            	
			    var position_id = $.map($('select[name="multiselect-subordinates_project_position_id"]').multiselect("getChecked"),function( input ){
			    	return input.value;
			    });

			    var employee_ids = $.map($('select[name="multiselect-subordinates_project_pos_employee_id"]').multiselect("getChecked"),function( input ){
			    	return input.value;
			    });


				$.ajax({
					url: module.get_value('base_url') + module.get_value('module_link') + '/get_user_by_position',
					data: 'position_id=' + position_id + '&organization_category_id=' + organization_category_id + '&organization_category_value_id=' + organization_category_value_id + '&employee_id=' + employee_ids,
					type: 'post',
					dataType: 'html',
					success: function(response) {
						$('#multiselect-subordinates_project_pos_employee_id option').remove();	
						if (response && response != ''){			
							$('#multiselect-subordinates_project_pos_employee_id').append(response);				
							$('#multiselect-subordinates_project_pos_employee_id').multiselect('refresh');
						}
					}
				});				        
            }
        });   

		// group
        $("#multiselect-subordinates_group_id").multiselect({
            close: function(event, ui){
			    var temp = $.map($('select[name="multiselect-subordinates_group_id"]').multiselect("getChecked"),function( input ){
			    	return input.value;
			    });

			    var position_id = $.map($('select[name="multiselect-subordinates_group_position_id"]').multiselect("getChecked"),function( input ){
			    	return input.value;
			    });

				$.ajax({
					url: module.get_value('base_url') + module.get_value('module_link') + '/get_position_by_category',
					data: 'organization_category_value_id=' + temp + '&organization_category_id=2' + '&record_id=' + $('#record_id').val() + '&position_id=' + position_id,
					type: 'post',
					dataType: 'html',
					success: function(response) {
						$('#multiselect-subordinates_group_position_id option').remove();				
						$('#multiselect-subordinates_group_position_id').append(response);				
						$('#multiselect-subordinates_group_position_id').multiselect('refresh');
					}
				});				        
            }
        });

        $("#multiselect-subordinates_group_position_id").multiselect({
            close: function(event, ui){
            	var organization_category_id = $('#organization_category_id').val();
            	
			    var organization_category_value_id = $.map($('select[name="multiselect-organization_category_value_id"]').multiselect("getChecked"),function( input ){
			    	return input.value;
			    });            	
			    var position_id = $.map($('select[name="multiselect-subordinates_group_position_id"]').multiselect("getChecked"),function( input ){
			    	return input.value;
			    });

			    var employee_ids = $.map($('select[name="multiselect-subordinates_group_pos_employee_id"]').multiselect("getChecked"),function( input ){
			    	return input.value;
			    });


				$.ajax({
					url: module.get_value('base_url') + module.get_value('module_link') + '/get_user_by_position',
					data: 'position_id=' + position_id + '&organization_category_id=' + organization_category_id + '&organization_category_value_id=' + organization_category_value_id + '&employee_id=' + employee_ids,
					type: 'post',
					dataType: 'html',
					success: function(response) {
						$('#multiselect-subordinates_group_pos_employee_id option').remove();		
						if (response && response != ''){								
							$('#multiselect-subordinates_group_pos_employee_id').append(response);				
							$('#multiselect-subordinates_group_pos_employee_id').multiselect('refresh');
						}
					}
				});				        
            }
        });     

		// department
        $("#multiselect-subordinates_department_id").multiselect({
            close: function(event, ui){
			    var temp = $.map($('select[name="multiselect-subordinates_department_id"]').multiselect("getChecked"),function( input ){
			    	return input.value;
			    });
			    
			    var position_id = $.map($('select[name="multiselect-subordinates_group_position_id"]').multiselect("getChecked"),function( input ){
			    	return input.value;
			    });

				$.ajax({
					url: module.get_value('base_url') + module.get_value('module_link') + '/get_position_by_category',
					data: 'organization_category_value_id=' + temp + '&organization_category_id=3' + '&record_id=' + $('#record_id').val() + '&position_id=' + position_id,
					type: 'post',
					dataType: 'html',
					success: function(response) {
						$('#multiselect-subordinates_department_position_id option').remove();				
						$('#multiselect-subordinates_department_position_id').append(response);				
						$('#multiselect-subordinates_department_position_id').multiselect('refresh');
					}
				});				        
            }
        });

        $("#multiselect-subordinates_department_position_id").multiselect({
            close: function(event, ui){
            	var organization_category_id = $('#organization_category_id').val();
			    var organization_category_value_id = $.map($('select[name="multiselect-organization_category_value_id"]').multiselect("getChecked"),function( input ){
			    	return input.value;
			    });            	
			    var position_id = $.map($('select[name="multiselect-subordinates_department_position_id"]').multiselect("getChecked"),function( input ){
			    	return input.value;
			    });

			    var employee_ids = $.map($('select[name="multiselect-subordinates_department_pos_employee_id"]').multiselect("getChecked"),function( input ){
			    	return input.value;
			    });

				$.ajax({
					url: module.get_value('base_url') + module.get_value('module_link') + '/get_user_by_position',
					data: 'position_id=' + position_id + '&organization_category_id=' + organization_category_id + '&organization_category_value_id=' + organization_category_value_id + '&employee_id=' + employee_ids,
					type: 'post',
					dataType: 'html',
					success: function(response) {
						$('#multiselect-subordinates_department_pos_employee_id option').remove();				
						if (response && response != ''){						
							$('#multiselect-subordinates_department_pos_employee_id').append(response);				
							$('#multiselect-subordinates_department_pos_employee_id').multiselect('refresh');
						}
					}
				});				        
            }
        });                   	
	}

	if (module.get_value('view') == 'detail') {
		$.ajax({
			url: module.get_value('base_url') + module.get_value('module_link') + '/get_reporting_to_position',
			data: 'record_id=' + $('#record_id').val(),
			type: 'post',
			dataType: 'json',
			success: function(response) {
				var organization_category_id = response.category_id.organization_category_id

				if (organization_category_id == 1 || organization_category_id == 4){
					$('label[for="subordinates_division_id"]').parent().parent().hide().prev().hide();
					$('label[for="subordinates_project_id"]').parent().parent().hide().prev().hide();
					$('label[for="subordinates_group_id"]').parent().parent().hide().prev().hide();
					$('label[for="subordinates_department_id"]').parent().parent().hide().prev().hide();
				}
				else{
					$('label[for="subordinates_position_id"]').parent().parent().hide().prev().hide();
					$('label[for="subordinates_employee_id"]').parent().parent().hide().prev().hide();
					$('label[for="subordinates_division_id"]').parent().parent().show().prev().show();
					$('label[for="subordinates_project_id"]').parent().parent().show().prev().show();
					$('label[for="subordinates_group_id"]').parent().parent().show().prev().show();
					$('label[for="subordinates_department_id"]').parent().parent().show().prev().show();							
				}

				$('label[for="organization_category_value_id"]').next().html(response.category_value);
			}			
		});				
	}
});