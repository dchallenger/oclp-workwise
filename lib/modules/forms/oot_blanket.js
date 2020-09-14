$(document).ready(function () {

		$('label[for="employee_id"]').parent().append("<div id='multi-select-employee-loader'></div>");
		$('label[for="position_id"]').parent().append("<div id='multi-select-position-loader'></div>");

	if (module.get_value('view') == 'edit') {
		if ($('#record_id').val() == '-1'){
			$('#multiselect-employee_id option').remove();
			$('#multiselect-oot_blanket_category_value_id option').remove();
			$('#multiselect-position_id option').remove();
		}

		$('#oot_blanket_category_id').bind('change',function(){
			var category_id = $(this).val();
			$.ajax({
				url: module.get_value('base_url') + module.get_value('module_link') + '/get_category_value',
				data: 'category_id=' + category_id + '&record_id=' + $('#record_id').val(),
				type: 'post',
				dataType: 'html',
				success: function(response) {
					if (category_id != 1){				
						$("#multiselect-oot_blanket_category_value_id").multiselect("enable");
						$('#multiselect-employee_id option').remove();
						$('#multiselect-employee_id').multiselect('refresh');
						$('#multiselect-position_id option').remove();
						$('#multiselect-position_id').multiselect('refresh');

						$('#multiselect-oot_blanket_category_value_id option').remove();				
						$('#multiselect-oot_blanket_category_value_id').append(response);				
						$('#multiselect-oot_blanket_category_value_id').multiselect('refresh');

						if ($('#record_id').val() != '-1'){
							$('#oot_blanket_category_value_id').val($("#multiselect-oot_blanket_category_value_id").multiselect("getChecked").map(function(){
								return this.value;	
							}).get());								
							
							$.ajax({
								url: module.get_value('base_url') + module.get_value('module_link') + '/get_employee_position_by_category',
								data: 'category_value=' + $('#oot_blanket_category_value_id').val() + '&category_id=' + category_id + '&record_id=' + $('#record_id').val() + '&position_id=' + $('#position_id').val(),
								type: 'post',
								dataType: 'html',
				                async: false,
				                beforeSend: function(){
				                	$('input[name="employee_id"]').parent().hide();
				                	$('#multi-select-employee-loader').html('<div><img src="'+module.get_value('base_url')+user.get_value('user_theme')+'/images/loading.gif" style="vertical-align:middle"><span style="padding-left:10px">Loading, please wait...</span></div>');                
				                
				                	$('input[name="position_id"]').parent().hide();
				                    $('#multi-select-position-loader').html('<div><img src="'+module.get_value('base_url')+user.get_value('user_theme')+'/images/loading.gif" style="vertical-align:middle"><span style="padding-left:10px">Loading, please wait...</span></div>');                
				                },   
								success: function(response) {
        							var data = $.parseJSON(response);
									$('#multi-select-employee-loader').html(''); 
									$('input[name="employee_id"]').parent().show();
									$('#multiselect-employee_id option').remove();				
									$('#multiselect-employee_id').append(data.emp);				
									$('#multiselect-employee_id').multiselect('refresh');		

			                    	$('#multi-select-position-loader').html(''); 
									$('input[name="position_id"]').parent().show();
									$('#multiselect-position_id option').remove();				
									$('#multiselect-position_id').append(data.pos);				
									$('#multiselect-position_id').multiselect('refresh');				
								}
							});
						}					
					}
					else{
						//$('label[for="oot_blanket_category_value_id"]').parent().hide();
						$('#multiselect-oot_blanket_category_value_id option').remove();
						$('#multiselect-oot_blanket_category_value_id').multiselect('refresh');
						$("#multiselect-oot_blanket_category_value_id").multiselect("disable");

						$('#multiselect-employee_id option').remove();				
						$('#multiselect-employee_id').append(response);				
						$('#multiselect-employee_id').multiselect('refresh');	

						$('#multiselect-position_id option').remove();				
						$('#multiselect-position_id').append(response);				
						$('#multiselect-position_id').multiselect('refresh');					
					}
				}
			});
		});

	if (user.get_value('project_hr_control') == 1) {
		$.ajax({
			url: module.get_value('base_url') + module.get_value('module_link') + '/is_projecthr',
			type: 'post',
			dataType: 'json',
			success: function(data) {
				if (data.project_hr == true) {
					$('#oot_blanket_category_id').val(6);
					$('#oot_blanket_category_id').trigger('change').attr('disabled', true);	
				};
				
			}
		});
	};

		$('#multiselect-oot_blanket_category_value_id').bind('multiselectclose',function(){				
			$('#oot_blanket_category_value_id').val($("#multiselect-oot_blanket_category_value_id").multiselect("getChecked").map(function(){
				return this.value;	
			}).get());			
			$('#position_id').val($("#multiselect-position_id").multiselect("getChecked").map(function(){
				return this.value;	
			}).get());
			
			var category_id = $('#oot_blanket_category_id').val();

			$.ajax({
				url: module.get_value('base_url') + module.get_value('module_link') + '/get_employee_position_by_category',
				data: 'category_value=' + $('#oot_blanket_category_value_id').val() + '&category_id=' + category_id + '&record_id=' + $('#record_id').val() + '&position_id=' + $('#position_id').val(),
				type: 'post',
				dataType: 'html',
                async: false,
                beforeSend: function(){
                	$('input[name="employee_id"]').parent().hide();
					$('#multi-select-employee-loader').html('<div><img src="'+module.get_value('base_url')+user.get_value('user_theme')+'/images/loading.gif" style="vertical-align:middle"><span style="padding-left:10px">Loading, please wait...</span></div>');                
                
					$('input[name="position_id"]').parent().hide();
                    $('#multi-select-position-loader').html('<div><img src="'+module.get_value('base_url')+user.get_value('user_theme')+'/images/loading.gif" style="vertical-align:middle"><span style="padding-left:10px">Loading, please wait...</span></div>');                
               },   
				success: function(response) {
        			var data = $.parseJSON(response);
                    $('#multi-select-employee-loader').html(''); 
					$('input[name="employee_id"]').parent().show();
					$('#multiselect-employee_id option').remove();				
					$('#multiselect-employee_id').append(data.emp);				
					$('#multiselect-employee_id').multiselect('refresh');		

                    $('#multi-select-position-loader').html(''); 
					$('input[name="position_id"]').parent().show();
					$('#multiselect-position_id option').remove();				
					$('#multiselect-position_id').append(data.pos);				
					$('#multiselect-position_id').multiselect('refresh');				
				}
			});
		});
		//added to limit employee by selected position
		$('#multiselect-position_id').bind('multiselectclose',function(){				
			$('#oot_blanket_category_value_id').val($("#multiselect-oot_blanket_category_value_id").multiselect("getChecked").map(function(){
				return this.value;	
			}).get());			
			$('#position_id').val($("#multiselect-position_id").multiselect("getChecked").map(function(){
				return this.value;	
			}).get());
			
			var category_id = $('#oot_blanket_category_id').val();

			$.ajax({
				url: module.get_value('base_url') + module.get_value('module_link') + '/get_employee_position_by_category',
				data: 'category_value=' + $('#oot_blanket_category_value_id').val() + '&category_id=' + category_id + '&record_id=' + $('#record_id').val() + '&position_id=' + $('#position_id').val(),
				type: 'post',
				dataType: 'html',
                async: false,
                beforeSend: function(){
					$('input[name="employee_id"]').parent().hide();
					$('#multi-select-employee-loader').html('<div><img src="'+module.get_value('base_url')+user.get_value('user_theme')+'/images/loading.gif" style="vertical-align:middle"><span style="padding-left:10px">Loading, please wait...</span></div>');                
                
					// $('input[name="position_id"]').parent().hide();
     //                $('#multi-select-position-loader').html('<div><img src="'+module.get_value('base_url')+user.get_value('user_theme')+'/images/loading.gif" style="vertical-align:middle"><span style="padding-left:10px">Loading, please wait...</span></div>');                
                },   
				success: function(response) {
        			var data = $.parseJSON(response);
                    $('#multi-select-employee-loader').html(''); 
					$('input[name="employee_id"]').parent().show();
					$('#multiselect-employee_id option').remove();				
					$('#multiselect-employee_id').append(data.emp);				
					$('#multiselect-employee_id').multiselect('refresh');


     //                $('#multi-select-position-loader').html(''); 
					// $('input[name="position_id"]').parent().show();
					$('#multiselect-position option').remove();				
					$('#multiselect-position').append(data.pos);				
					$('#multiselect-position').multiselect('refresh');							
				}
			});
		});
	}

	setTimeout(function () {
		if ($('#oot_blanket_category_id').val() == 1){
			$('#multiselect-oot_blanket_category_value_id option').remove();
			$('#multiselect-oot_blanket_category_value_id').multiselect('refresh');
			$("#multiselect-oot_blanket_category_value_id").multiselect("disable");
		}
	}, 100)
});