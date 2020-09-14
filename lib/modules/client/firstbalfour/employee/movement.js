var benefit_id_global="";
var benefit_val_global="";
var global_movement_nature="";
$(document).ready(function () {

	$('input[name="blacklisted"]').parent().hide();

	$('#fg-403').hide();

	var action = module.get_value('view');		

	var x = new Movement(action);	
	$('#range_of_rank').parent().parent().hide(); 
	$('#employee_type').parent().parent().hide();

	if(module.get_value('view') == "index"){
        $('.export-icon').live('click', function(){
            var record_id = $(this).attr('record_id');
            window.location = module.get_value('base_url')+module.get_value('module_link')+'/export_muf/'+record_id;
        });
        $('.print-floating').live('click', function(){
            var record_id = $(this).attr('record_id');
            window.location = module.get_value('base_url')+module.get_value('module_link')+'/print_floating/'+record_id;
        });
        $('.print-action-movement').live('click', function(){
            var record_id = $(this).attr('record_id');
            window.location = module.get_value('base_url')+module.get_value('module_link')+'/print_action_movement/'+record_id;
        }); 
        $('.print-regularization').live('click', function(){
            var record_id = $(this).attr('record_id');
            window.location = module.get_value('base_url')+module.get_value('module_link')+'/print_regularization/'+record_id;
        });
        $('.print-extension').live('click', function(){
            var record_id = $(this).attr('record_id');
            window.location = module.get_value('base_url')+module.get_value('module_link')+'/print_extension/'+record_id;
        });                 
    }

    if(module.get_value('view') == 'edit')
    {	

    	$('.benefit-field').live('keyup', maskFloat);
    	//$("#show_pick option[value='current_employment_status_id, new_employment_status_id']").hide();
        $('#project_name_id').live('change',function(){
            $.ajax({
                url: module.get_value('base_url') + module.get_value('module_link') + '/get_cost_code_division',
                data: 'project_name_id=' + $(this).val(),
                type: 'post',
                dataType: 'json',
                beforeSend: function(){
                    //$('#appraisal-template-criteria-container').block({ message: '<div class="now-loading align-center"><img src="'+module.get_value('base_url')+user.get_value('user_theme')+'/images/loading.gif"><br />Loading, please wait...</div>' });          
                },      
                success: function (response) {
                    $('#division_id').val(response.division_id);
                    $('#division_id option:not(:selected)').attr('disabled', true);
                }
            });     
        }) 

        $('#transfer_to').live('change',function(){
            $.ajax({
                url: module.get_value('base_url') + module.get_value('module_link') + '/get_department_code_group',
                data: 'department_id=' + $(this).val(),
                type: 'post',
                dataType: 'json',
                beforeSend: function(){
                    //$('#appraisal-template-criteria-container').block({ message: '<div class="now-loading align-center"><img src="'+module.get_value('base_url')+user.get_value('user_theme')+'/images/loading.gif"><br />Loading, please wait...</div>' });          
                },      
                success: function (response) {
                    $('#group_name_id').val(response.group_name_id);
                    $('#group_name_id option:not(:selected)').attr('disabled', true);
                }
            });     
        })

        get_salary();

    	// for open access
    	$('#subordinates_id').parent().parent().hide();
    	$('#employee_reporting_to').parent().parent().hide();

    	$('#campaign_id').live('change', function() {
    		get_sub_repto_dropdown();
    	});

    	$('#employee_type_readonly').attr('disabled','disabled');
    	$('#sick_leave').attr('readonly','readonly');
    	$('#vacation_leave').attr('readonly','readonly');
    	$('#emergency_leave').attr('readonly','readonly');

		$('#transfer_effectivity_date-temp').live('change', function(){
			if($('#employee_id').val() != 0 && global_movement_nature == 6)
			{
				var data = $('#transfer_effectivity_date, #employee_id').serialize();
				movement_leaves(data, 'resignation');
			} else if($('#employee_id').val() != 0 && global_movement_nature == 1)
			{
				var data = $('#transfer_effectivity_date, #employee_id').serialize();
				movement_leaves(data, 'regularization');
			}
		});

		if(module.get_value('record_id')!=-1)
		{
			$.ajax({
				url: module.get_value('base_url')+module.get_value('module_link')+'/fix_name_edit',
				data: 'record_id='+module.get_value('record_id'),
				type: 'post',
				dataType: 'json',
				success: function(response)
				{
					$('#employee_id').replaceWith('<input id="employee_id" name="employee_id" type="textbox" style="display:none;border:0px" readonly=true value="'+response.emp_id+'"/>')
					$('#employee_id_chzn').replaceWith('<input id="employee_fix_name" type="textbox" style="border:0px" readonly=true value="'+response.emp_name+'"/>');
				}
			});
		}

		if($('.employee_movement_nature_movement:input[value=15]').is(':checked')){
			$('label[for="transfer_effectivity_date_to"]').parent().show();
		}else{
			$('label[for="transfer_effectivity_date_to"]').parent().hide();	
		}	
    }

    $('#sick_leave').prop('readonly', true);
	$('#vacation_leave').prop('readonly', true);
	$('#emergency_leave').prop('readonly', true);
    //$('#employee_reporting_to').prop('readonly', true);
    $("#reason_for_leaving").parent().parent().hide();
	$("#remarks_leaving").parent().parent().hide();
	$("#further_reason_leaving").parent().parent().hide();

	if(module.get_value('view') == 'detail') {
		show_hidden_detail();
		get_salary();
	}
});



function Movement(action) {	
	
	this.edit = function () {

/*		$('#new_position_id').live('change', function () {        
      	  populate_reporting_to($(this).val());
    	});*/

		$('select[name="company_id"]').live('change', function () {        
      	  populate_company_relations($(this).val());
    	});

		//segment division
	    $('#transfer_to').change(function () {
	        get_division_segment($('#transfer_to').val());
	    });
	    //segment division
	    $('#job_level').change(function () {
	        get_rank_range($('#job_level').val());
	    });
	    $('#rank_id').change(function () {
	        get_employee_type($('#rank_id').val());
	    });
	    

		if ($('#record_id').val() == '-1') {

			setTimeout(function () {
				$('#created_date').datepicker("setDate", new Date() );
			}, 100);
		}		

		$('#current_allowances, #current_commission, #current_total').attr('readonly', 'readonly');

		//$('#new_basic_salary, #new_allowances, #new_commission').change(update_total);

		
		//Initialize Nature of Movement Type Checkbox
		
		options = $('#employee_movement_nature_movement option');	
		var rows = new Array();	

		$(options).each(function (index, element) {
			option = $(element);
			field  = $('<input type="checkbox" class="employee_movement_nature_movement" name="employee_movement_nature_movement[]"/>').val(option.val());

			if (element.selected) {			
				field.attr('checked', 'checked');
				if ($(option).val() == 15){
					$('#extension_no_months').parent().parent().show();
				}else if($(option).val() == 6 || $(option).val() == 7 || $(option).val() == 10){
					$('input[name="blacklisted"]').parent().show();
				}
			}		

			rows[option.text().replace(' ', '')] = $(field)[0].outerHTML + option.text();		
		});
		


	
		var table = $('<table width="80%"></table>');
		var tr1   = $('<tr></tr>');
		var tr2   = $('<tr></tr>');

		tr1.append($('<td></td>').html(rows['Regularization']));
		tr1.append($('<td></td>').html(rows['EmploymentStatus']));			
		tr1.append($('<td></td>').html(rows['Promotion']));
		tr1.append($('<td></td>').html(rows['Transfer']));	
		tr1.append($('<td></td>').html(rows['Extension']));		
		tr1.append($('<td></td>').html(rows['EndContract']));
		tr1.append($('<td></td>').html(rows['Resignation']));
		tr1.append($('<td></td>').html(rows['Termination']));
		tr1.append($('<td></td>').html(rows['Retirement']));	
		if(module.get_value('client_no') != 2 && module.get_value('client_no') != 1)
			tr1.append($('<td></td>').html(rows['TemporaryAssignment']));
		if(module.get_value('client_no') == 2) {
			tr1.append($('<td></td>').html(rows['Floating']));
			tr1.append($('<td></td>').html(rows['Recall']));
		}
		
		table.append(tr1);
		table.append(tr2);
		
		$('label[for="employee_movement_nature_movement"]').after(table);
		$('label[for="employee_movement_nature_movement"]').parent().css('width', '100%');
		$('#employee_movement_nature_movement, label[for="employee_movement_nature_movement"]').remove();

		setTimeout(function () { 
			$('#employee_movement_nature_movement_chzn').remove();			
		} , 50);


		//Initialize Resignation Termination Nature Movement Type Checkbox
		var table = $('<table width="50%"></table>');
		var tr1   = $('<tr></tr>');
		var tr2   = $('<tr></tr>');

		tr1.append($('<td></td>').html(rows['Resignation']));
		tr1.append($('<td></td>').html(rows['Termination']));
		
		table.append(tr1);
		table.append(tr2);
		
		$('label[for="employee_movement_resignation_terminate"]').after(table);
		$('label[for="employee_movement_resignation_terminate"]').parent().css('width', '100%');
		$('#employee_movement_resignation_terminate, label[for="employee_movement_resignation_terminate"]').remove();

		setTimeout(function () { 
			$('#employee_movement_resignation_terminate_chzn').remove();			
		} , 50);


		var table = $('<table width="50%"></table>');
		var tr1   = $('<tr></tr>');
		var tr2   = $('<tr></tr>');

		tr1.append($('<td></td>').html(rows['SalaryIncrease']));
		tr1.append($('<td></td>').html(rows['WageOrder']));
		tr1.append($('<td></td>').html(rows['Merit/Performance']));	
		
		table.append(tr1);
		table.append(tr2);
		
		$('label[for="employee_movement_compensation_adjustment"]').after(table);
		$('label[for="employee_movement_compensation_adjustment"]').parent().css('width', '100%');
		$('#employee_movement_nature_movement, label[for="employee_movement_compensation_adjustment"]').remove();

		setTimeout(function () { 
			$('#employee_movement_compensation_adjustment_chzn').remove();			
		} , 50);

		

		$('.employee_movement_nature_movement').click(function(){
			$("#show_pick option[value='current_employment_status_id, new_employment_status_id']").hide();
			var nature_movement_val = $(this).val();
			global_movement_nature = ($(this).attr('checked') ? nature_movement_val : "");

			//If Regularization is checked
			if( nature_movement_val == 1 ){
				if($(this).attr('checked')){

					$('input[name="blacklisted"]').parent().hide();
					// leaves
					if($('#employee_id').val() != 0 && $('#transfer_effectivity_date').val() != "")
					{
						var data = $('#transfer_effectivity_date, #employee_id').serialize();
						movement_leaves(data, 'regularization');
					}
						// remove check
						$('.employee_movement_nature_movement').each(function(){

							if( $(this).val() == 6 || $(this).val() == 7 ){

								$(this).removeAttr('checked');

							}

						});
						// remove check
				} else {
					$('#sick_leave').val('');
					$('#vacation_leave').val('');
					$('#emergency_leave').val('');
				}
			}
			//If Promotion is checked
			else if( nature_movement_val == 3 ){

				if($(this).attr('checked')){

					$('input[name="blacklisted"]').parent().hide();

					$('.employee_movement_nature_movement').each(function(){

						if( $(this).val() == 6 || $(this).val() == 7 ){

							$(this).removeAttr('checked');

						}

					});

				}


			}
			//If Re-assignment is checked
			else if( nature_movement_val == 8){

				if($(this).attr('checked')){
					
					$('input[name="blacklisted"]').parent().hide();

					$('.employee_movement_nature_movement').each(function(){

						if( $(this).val() == 6 || $(this).val() == 7 ){

							$(this).removeAttr('checked');

						}

					});
				}

			}
			//If Resignation is checked
			else if( nature_movement_val == 6 ){
				
				if($(this).attr('checked')){
					// leave
					$("#reason_for_leaving").parent().parent().show();
					$("#remarks_leaving").parent().parent().show();
					$("#further_reason_leaving").parent().parent().show();
					$("#show_pick").parent().hide();
					$("#employee_reporting_to").parent().parent().hide();
					$(".nom_pick").hide();
					$("#employee_approver").parent().parent().hide();

					$('input[name="blacklisted"]').parent().show();

					if($('#employee_id').val() != 0 && $('#transfer_effectivity_date').val() != "")
					{
						var data = $('#transfer_effectivity_date, #employee_id').serialize();
						movement_leaves(data, 'resignation');
					}
						// remove check
						$('.employee_movement_nature_movement').each(function(){

							if( $(this).val() != nature_movement_val ){

								$(this).removeAttr('checked');

							}

						});
						// remove check

					$.ajax({
						url: module.get_value('base_url') + 'employee/appraisal/get_pending_pa',
						data: 'employee_id='+$('#employee_id').val()+'&date='+$('#transfer_effectivity_date-temp').val(),
						type: 'post',
						dataType: 'html',
						success: function(response)
						{
							if (response != ""){
								$.unblockUI();
								new Boxy('<div id="boxyhtml" style="width:500px;max-height:400px;overflow-y:auto;">' + response +'</div>', 
									{
										title:"LIST OF PENDING PA",
										modal: true
									}
								);
							}
						}
					});
				} else {
					$('#sick_leave').val('');
					$('#vacation_leave').val('');
					$('#emergency_leave').val('');
					$("#reason_for_leaving").parent().parent().hide();
					$("#remarks_leaving").parent().parent().hide();
					$("#further_reason_leaving").parent().parent().hide();
					$("#show_pick").parent().show();
					$("#employee_reporting_to").parent().parent().show();
					$(".nom_pick").show();
					$("#employee_approver").parent().parent().show();
					
					$('input[name="blacklisted"]').parent().hide();
				}
			}

			else if( nature_movement_val == 11 ){
				if($(this).attr('checked'))
				{
					
					$('input[name="blacklisted"]').parent().hide();
					if($('#employee_id').val() != 0 && $('#transfer_effectivity_date').val() != "")
					{
						var data = $('#transfer_effectivity_date, #employee_id').serialize();
						movement_leaves(data, 'resignation');
					} 

					$('.employee_movement_nature_movement').each(function(){

						if( $(this).val() != nature_movement_val ){

							$(this).removeAttr('checked');

						}

					});
				} else {
					$('#sick_leave').val('');
					$('#vacation_leave').val('');
					$('#emergency_leave').val('');
				}
			}

			//If Termination is checked
			else if( nature_movement_val == 7 ){

				if($(this).attr('checked'))
				{
					$('input[name="blacklisted"]').parent().show();

					if($('#employee_id').val() != 0 && $('#transfer_effectivity_date').val() != "")
					{
						var data = $('#transfer_effectivity_date, #employee_id').serialize();
						movement_leaves(data, 'resignation');
					} 

					$('.employee_movement_nature_movement').each(function(){

						if( $(this).val() != nature_movement_val ){

							$(this).removeAttr('checked');

						}

					});
				} else {
					$('#sick_leave').val('');
					$('#vacation_leave').val('');
					$('#emergency_leave').val('');
					
					$('input[name="blacklisted"]').parent().hide();
				}
			}

			//If End of Contract is checked
			else if( nature_movement_val == 10 ){
				if($(this).attr('checked'))
				{
					$('input[name="blacklisted"]').parent().show();
				}else{
					$('input[name="blacklisted"]').parent().hide();
				}
			}
			// for temporary assignment fb
			else if( nature_movement_val == 14 ){

				if($(this).attr('checked')){
					
					$('input[name="blacklisted"]').parent().hide();

					$('.employee_movement_nature_movement').each(function(){

						if( $(this).val() == 6 || $(this).val() == 7 ){

							$(this).removeAttr('checked');

						}

					});

				}
			}
			// // If floating is checked
			else if(nature_movement_val == 12) {
				if($(this).attr('checked'))
				{
					
					$('input[name="blacklisted"]').parent().hide();

					// Show/hide field group
					$('#fg-267').hide();
					$('#fg-269').hide();
					$('#fg-403').show();

					// hide recall date
					$('#floating_date_to-temp').parent().parent().hide();

					// change single/multiple employee dropdown
					$('#employee_id_chzn').parent().parent().hide();
					$('#multiselect-employee_floating_id').parent().parent().show();

					// change multiple select dropndown list
					change_employee_dropdown(12);

					$('.employee_movement_nature_movement').each(function(){

						if( $(this).val() != nature_movement_val ){

							$(this).removeAttr('checked');

						}

					});
				} else {

					// Show/hide field group
					$('#fg-267').show();
					$('#fg-269').show();
					$('#fg-403').hide();

					// show recall date
					$('#floating_date_to-temp').parent().parent().show();

					// change single/multiple employee dropdown
					$('#employee_id_chzn').parent().parent().show();
					$('#multiselect-employee_floating_id').parent().parent().hide();

					change_employee_dropdown();
				}
			}
			// if extension is clicked
			else if(nature_movement_val == 15) {
				if($(this).attr('checked'))
				{
					
					$('input[name="blacklisted"]').parent().hide();
					$('#extension_no_months').parent().parent().show();
				}
				else{
					$('#extension_no_months').parent().parent().hide();	
				}
			}
			//appointment for probationary
			else if(nature_movement_val == 9) {
				if($(this).attr('checked'))
				{
					
					$('input[name="blacklisted"]').parent().hide();
					$("#show_pick option[value='current_employment_status_id, new_employment_status_id']").show();
				}
			}

			if($('.employee_movement_nature_movement:input[value=14]').is(':checked')){
				$('label[for="transfer_effectivity_date_to"]').parent().show();
			}
			else{
				$('label[for="transfer_effectivity_date_to"]').parent().hide();	
			}

		});

		// alert(module.get_value('record_id'));
	
		$('select[name="employee_id"]').chosen().change(function () {

			if(module.get_value('record_id')==-1)
			{
				clear_benefit();
				$.ajax({
					url: module.get_value('base_url') + 'employee/movement/get_current_benefits',
					data: 'employee_id='+$(this).val()+'&record_id='+module.get_value('record_id'),
					type: 'post',
					dataType: 'json',
					success: function(response)
					{
					// 	alert('hey');
						employee = response.array_data;
						var total = new Array();
						var benefit_val = new Array();
						for(var i in employee)
						{
							//$("#benefitddlb option[value=" + employee[i].benefit_id+"]").hide();
							total[i] = employee[i].benefit_id;
							benefit_val[i] = employee[i].value;
							//employee[i].benefit_id;
						}
						var total = total.join();
						var benefit_val = benefit_val.join();

						if (response.num_rows > 0){
							show_benefit(total, benefit_val);		
						}
						//$('input[name=selected-benefits]').val(total);
						// if(response.num_rows > 0)
						// {
						// 	for(var i in response )
						// 	response.array_data
						// }
					}
				});
			} 
			else 
			{
				var testval=$('#employee_id').val();
				var fixname=$('#employee_id_chzn').find('a').find('span').text();
	            //$('#employee_id').val(user.get_value('user_id'));
	            $.ajax({
					url: module.get_value('base_url') + 'employee/movement/get_current_benefits',
					data: 'employee_id='+testval+'&record_id='+module.get_value('record_id'),
					type: 'post',
					dataType: 'json',
					success: function(response)
					{
					// 	alert('hey');
						employee = response.array_data;
						var total = new Array();
						var benefit_val = new Array();
						for(var i in employee)
						{
							//$("#benefitddlb option[value=" + employee[i].benefit_id+"]").hide();
							total[i] = employee[i].benefit_id;
							benefit_val[i] = employee[i].value;
							//employee[i].benefit_id;
						}
						var total = total.join();
						var benefit_val = benefit_val.join();
						if (response.num_rows > 0){
							show_benefit(total, benefit_val);		
						}	
						//$('input[name=selected-benefits]').val(total);
						// if(response.num_rows > 0)
						// {
						// 	for(var i in response )
						// 	response.array_data
						// }
					}
				});
			}


			if ($(this).val() > 0) {

				if($('#record_id').val() == -1)
				{
					$.ajax({
						url: module.get_value('base_url') + 'employee/movement/get_employee',
						data: 'employee_id=' + $(this).val(),
						type: 'post',
						dataType: 'json',
						success: function(data) {



							if (data.msg_type == 'error') {
								$('#message-container').html(message_growl(data.msg_type, data.msg));
							} else {

								update_fields(data.data);
								//set_current_inputs_html(data.data);

							}
						}
					});
				}

				$.ajax({
					url: module.get_value('base_url') + 'employee/movement/get_leaves',
					data: 'employee_id=' + $(this).val(),
					type: 'post',
					dataType: 'json',
					success: function(data) {

						if (data.msg_type == 'error') {

							update_leaves(0);

						} else {

							update_leaves(data.data);

						}
					}
				});
			}
		});

		$('select[name="employee_id"]').trigger('change');

		//Disable current fields
		$('select[name="current_role"] ').attr('disabled', 'disabled');
		$('select[name="current_position_id"] ').attr('disabled', 'disabled');
		$('select[name="current_department_id"] ').attr('disabled', 'disabled');
		$('#current_rank_dummy').attr('readonly', 'readonly');
		$('#current_employee_type_dummy').attr('readonly', 'readonly');
		$('#current_job_level_dummy').attr('readonly', 'readonly');
		$('#current_range_of_rank_dummy').attr('readonly', 'readonly');
		$('#current_rank_code_dummy').attr('readonly', 'readonly');
		$('#current_company_dummy').attr('readonly', 'readonly');
		$('#current_division_dummy').attr('readonly', 'readonly');
		$('#current_location_dummy').attr('readonly', 'readonly');
		$('#current_segment_1_dummy').attr('readonly', 'readonly');
		$('#current_segment_2_dummy').attr('readonly', 'readonly');
		$('input[name="current_cbe"]').attr("disabled",true);

		
		function update_fields(data) 
		{
			$('#current_employment_status_id').val(data.status_id);
			$('#current_shift_calendar_id').val(data.shift_calendar_id);
			$('#current_project_name_id').html(data.project_html);
			$('#current_group_name_id').html(data.group_html);
			//Update employee position
			$('select[name="current_position_id"]').val(data.position_id);
			//$('select[name="current_position_id"]').trigger("liszt:updated");

			$('select[name="current_role"]').val(data.current_role_id);

			var salary = data.salary;
			if(salary != '') {
				$('#current_basic_salary').val(addCommas(parseFloat(salary)));
			} else {
				$('#current_basic_salary').val('0');
			}
			$('#current_basic_salary').attr('disabled', true);

			//Update employee department
			// $('#current_department_dummy').val(data.wa.department);
			$('#current_department_dummy').val(data.department);
			//$('select[name="current_department_id"]').trigger("liszt:updated");

			$('#current_rank_dummy').val(data.job_rank);
			$('#current_employee_type_dummy').val(data.curr_employee_type);
			$('#current_job_level_dummy').val(data.curr_employee_type);
			$('#current_range_of_rank_dummy').val(data.job_rank_range);
			$('#current_rank_code_dummy').val(data.job_rank_code);
			$('#current_company_dummy').val(data.company);
			$('#current_division_dummy').val(data.division);
			$('#current_location_dummy').val(data.location);
			$('#current_segment_1_dummy').val(data.segment_1);
			//$('#current_segment_2_dummy').val(data.segment_2_id);
			$('#current_employment_status').val(data.employment_status);
			$('#current_project_name_id').val(data.project_name_id);
			$('#current_group_name_id').val(data.group_name_id);
			$('#current_code_status_id').val(data.code_status_id);

			if (data.CBE == 1){
				$("#current_cbe-yes").prop("checked", true)
				$("#new_cbe-yes").prop("checked", true)
			}
			else{
				$("#current_cbe-no").prop("checked", true)
				$("#new_cbe-no").prop("checked", true)
			}

			if(module.get_value('client_no') == 2)
				$('#current_campaign').val(data.campaign);

			var hidden_current_id = 'position_id = '+data.position_id+', role_id = '+data.current_role_id+', department_id = '+data.department_id+', rank_id = '+data.rank_id+', employee_type = '+data.employee_type+', job_level = '+data.job_level+', range_of_rank = '+data.range_of_rank+', rank_code= '+data.rank_code+', company_id = '+data.company_id+', division_id = '+data.division_id+', location_id = '+data.location_id+', segment_1_id = '+data.segment_1_id+', segment_2_id = '+data.segment_2_id+', status_id = '+data.status_id+', campaign_id = '+data.campaign_id;
			$('#hidden_id_current').val(hidden_current_id);

			$('#approved_by option').remove();

			if (data.segment_2_id != ''){
				$.each(data.segment_2_id.split(','), function(index, value) {
					$('#current_segment_2_dummy option[value=' + value + ']').attr('selected', true); 
					$('#segment_2_id option[value=' + value + ']').attr('selected', true); 
				});

				$("#current_segment_2_dummy > option:not(:selected)").each(function() {				
					$(this).attr('disabled', true);
				});	

				$('#current_segment_2_dummy').trigger('liszt:updated');
				$('#segment_2_id').trigger('liszt:updated');
			}
			else{
				$("#current_segment_2_dummy > option").each(function() {
					$(this).attr('selected', false); 					
					$(this).attr('disabled', true);
				});		
				$("#segment_2_id > option").each(function() {
					$(this).attr('selected', false); 					
				});											
				$('#current_segment_2_dummy').trigger('liszt:updated');
				$('#segment_2_id').trigger('liszt:updated');
			}

			if (data.reporting_to != ''){
				$.each(data.reporting_to.split(','), function(index, value) {
					$('#current_employee_reporting_to optgroup option[value=' + value + ']').attr('selected', true); 
					$('#employee_reporting_to optgroup option[value=' + value + ']').attr('selected', true); 
				});

				$("#current_employee_reporting_to optgroup > option:not(:selected)").each(function() {				
					$(this).attr('disabled', true);
				});	

				$('#current_employee_reporting_to').trigger('liszt:updated');
				$('#employee_reporting_to').trigger('liszt:updated');
			}
			else{
				$("#current_employee_reporting_to optgroup > option").each(function() {
					$(this).attr('selected', false); 					
					$(this).attr('disabled', true);
				});		
				$("#employee_reporting_to optgroup > option").each(function() {
					$(this).attr('selected', false); 					
				});											
				$('#current_employee_reporting_to').trigger('liszt:updated');
				$('#employee_reporting_to').trigger('liszt:updated');
			}

			if (data.approvers != false) {				
				$(data.approvers).each(function (index, approver) {					
					$('#approved_by')
						.append(
							$('<option></option>')
								.val(approver.user_id)
								.text(approver.firstname + ' ' +approver.lastname)
						)
				});
				$('#approved_by').trigger('liszt:updated');
			}

			$('#current_segment_2_dummy_chzn .search-choice-close').remove()
			$('#current_employee_reporting_to_chzn .search-choice-close').remove()
		}

		function update_leaves(data)
		{

			if(data == 0){
				$('#current_sick_leave').val(0);
				$('#current_sick_leave').attr('disabled','disabled');
				$('#current_vacation_leave').val(0);
				$('#current_vacation_leave').attr('disabled','disabled');
				$('#current_emergency_leave').val(0);
				$('#current_emergency_leave').attr('disabled','disabled');
			}
			else{
				$('#current_sick_leave').val(data.sl);
				$('#current_sick_leave').attr('disabled','disabled');
				$('#current_vacation_leave').val(data.vl);
				$('#current_vacation_leave').attr('disabled','disabled');
				$('#current_emergency_leave').val(data.el);
				$('#current_emergency_leave').attr('disabled','disabled');

				$('#sick_leave').val(data.sl);
				$('#vacation_leave').val(data.vl);
				$('#emergency_leave').val(data.el);
			}
			
		}

		function update_new_total() {

			var n = new Array('#new_basic_salary', '.new_employee_benefit');
			var total = 0;

			$.each(n, function(index, selector) {

				if( selector == '.new_employee_benefit' ){

					$('.new_employee_benefit').each(function(){

						val   = $(this).val();

						if (val != '') {
							val = val.replace(/,/g, '');
							total += parseFloat(val);
						}

					});

				}
				else{

					val   = $(selector).val();

					if (val != '') {
						val = val.replace(/,/g, '');
						total += parseFloat(val);
					}

				}
			});

			$('#new_total').val(addCommas(total));
		}

		function update_current_total() {

			var n = new Array('#current_basic_salary', '.current_employee_benefit');
			var total = 0;

			$.each(n, function(index, selector) {

				if( selector == '.current_employee_benefit' ){

					$('.current_employee_benefit').each(function(){

						val   = $(this).val();

						if (val != '') {
							val = val.replace(/,/g, '');
							total += parseFloat(val);
						}

					});

				}
				else{

					val   = $(selector).val();

					if (val != '') {
						val = val.replace(/,/g, '');
						total += parseFloat(val);
					}

				}
			});

			$('#current_total').val(addCommas(total));
		}

		function set_current_inputs_html(data){


			$.ajax({
				url: module.get_value('base_url') + 'employee/movement/get_current_benefits',
				data: 'employee_id=' + data.employee_id + '&record_id=' + $('#record_id').val(),
				type: 'post',
				dataType: 'json',
				success: function(data) {

					$('label[for="employee_movement_benefit_id"]').find('#employee_benefit_table').remove();
					$('#employee_movement_benefit_id_chzn, label[for="employee_movement_benefit_id"]').css('display','none');
					$('#employee_movement_benefit_id_chzn, label[for="employee_movement_benefit_id"]').attr('disabled','disabled');

					setTimeout(function () { 
						$('#empoyee_movement_benefit_id').css('display','none');
						$('#empoyee_movement_benefit_id').attr('disabled','disabled');			
					} , 50);

					if( data.num_rows > 0 ){

						var table = $('<table width="75%" id="employee_benefit_table" style="padding: 20px 0px;"></table>');
						var tr1;  
						
						$('label[for="employee_movement_benefit_id"]').after(table);


						$.each(data.array_data, function(i,record){

							 $('#employee_benefit_table').append($('<tr></tr>'));
							 $('#employee_benefit_table tr:eq('+i+')').append($('<td style="padding:5px 0px;"></td>').html($('<label class="label-desc gray">'+record.benefit+':</label><input type="text" class="input-text text-right current_employee_benefit" name="employee_benefit[]" readonly="readonly">').val(record.value)));

							 if( $('#record_id').val() != -1 ){
							 	$('#employee_benefit_table tr:eq('+i+')').append($('<td style="padding:5px 0px;"></td>').html($('<input type="hidden" name="employee_benefit_id[]" value="'+record.benefit_id+'"><input type="text" class="input-text text-right new_employee_benefit" name="new_employee_benefit[]" value="'+record.em_benefit_value+'">')));
							 }
							 else{
								$('#employee_benefit_table tr:eq('+i+')').append($('<td style="padding:5px 0px;"></td>').html($('<input type="hidden" name="employee_benefit_id[]" value="'+record.benefit_id+'"><input type="text" class="input-text text-right new_employee_benefit" name="new_employee_benefit[]" value="0.00">')));
							 }

						});

						$('#employee_benefit_table').prepend($('<thead></thead>'));
						$('#employee_benefit_table thead').append($('<tr><td><label class="label-desc gray">Current Allowance/Premium:</label></td><td><label class="label-desc gray">New Allowance/Premium:</label></td></tr>'));

						$('label[for="employee_movement_benefit_id"]').parent().css('width', '100%');

					}
					else{

						$('#employee_benefit_table').remove();
					}

					$('#new_basic_salary, .new_employee_benefit').live().change(update_new_total);

					update_current_total();

				}
			});

			if( $('#record_id').val() != -1 ){
				$.ajax({
					url: module.get_value('base_url') + 'employee/movement/get_payroll',
					data: 'employee_id=' + data.employee_id + '&record_id=' + $('#record_id').val(),
					type: 'post',
					dataType: 'json',
					success: function(data) {

							if(data.data != 0){

								$('#current_basic_salary').val(data.data.salary);

							}
							else{

								$('#current_basic_salary').val('0.00');

							}

					}
				});
			}
			else{

				$('#current_basic_salary').val('0.00');

			}
			

			

		}


	}

	this.index = function() {
		$('.icon-16-approve').live('click', change_status);
		$('.icon-16-disapprove').live('click', change_status);

		function change_status(e) {
			var status;
			if ($(this).hasClass('icon-16-approve')) {
				status = 3;
			} else {
				status = 4;
			}

			$.ajax({
				url: module.get_value('base_url') + module.get_value('module_link') + '/change_status',
				data: 'record_id=' + $(this).parents('tr').attr('id') + '&status=' + status,
				type: 'post',
				dataType: 'json',
				success: function (data) {
					$('#message-container').html(message_growl(data.msg_type, data.msg));
					$("#jqgridcontainer").jqGrid().trigger("reloadGrid");				
				}
			})
		}		
	}

	fn = this[action];
	if (typeof(fn) === typeof(Function)) {
		fn();
	}		
};


function get_movement_type(employee_movement_type_id, to_be_change)
{
	var send = 'employee_movement_type_id='+employee_movement_type_id;
	$.ajax({
		url: module.get_value('base_url') + 'employee/movement/get_movement_type',
		data: send,
		dataType: 'json',
		type: 'post',
		success: function (response) {
			if (response.msg_type == 'error') {
				$('#message-container').html(message_growl(response.msg_type, response.msg));
			} else {
				employee=response.data;
				var changeMovement="#movement"+to_be_change;
				$(changeMovement).text(employee.movement_type);
			}
		}
	});
}

function get_position_type(position_id, to_be_change, class_name)
{
	var send = 'position_id='+position_id;
	$.ajax({
		url: module.get_value('base_url') + 'employee/movement/get_position_type',
		data: send,
		dataType: 'json',
		type: 'post',
		success: function (response) {
			if (response.msg_type == 'error') {
				$('#message-container').html(message_growl(response.msg_type, response.msg));
			} else {
				employee=response.data;
				var currPosTxt="#"+class_name+to_be_change;
				$(currPosTxt).text(employee.position);
			}
		}
	});
}

function get_department_type(position_id, to_be_change, class_name)
{
	var send = 'position_id='+position_id;
	$.ajax({
		url: module.get_value('base_url') + 'employee/movement/get_position_type',
		data: send,
		dataType: 'json',
		type: 'post',
		success: function (response) {
			if (response.msg_type == 'error') {
				$('#message-container').html(message_growl(response.msg_type, response.msg));
			} else {
				employee=response.data;
				var currPosTxt="#"+class_name+to_be_change;
				$(currPosTxt).text(employee.position);
			}
		}
	});
}



function get_division_segment(dept_id){
    var send='department_id='+dept_id;
    if(dept_id==""){
        $('#division_id').val('');
        $('#segment_1_id').val('');
        $('#segment_2_id').val('');
    }else{
        $.ajax({
        url: module.get_value('base_url') + 'employees/get_division_segment',
        data: send,
        dataType: 'json',
        type: 'post',
        success: function (response) {
            if (response.msg_type == 'error') {
                $('#message-container').html(message_growl(response.msg_type, response.msg));
            } else {
                employee = response.data;
                    $('#division_id').val(employee.division_id);
                    $('#segment_1_id').val(employee.segment1_id);
                    $('#segment_2_id').val(employee.segment2_id);
            }
        }
    });
    }
}

function get_rank_range(rank_level, record_id){

     $.ajax({
            url: module.get_value('base_url') + 'employees/get_rank_range',
            type: 'post',
            dataType: 'json',
            data: 'rank_level=' + rank_level + '&record_id=' + record_id,
            success: function (response) {
            	$('#range_of_rank').val(response.job_rank_range_id);
            	$('#range_of_rank_readonly').attr('readonly','readonly');
                $('#range_of_rank_readonly').val(response.job_rank_range);
            }
        });
}

function get_employee_type(rank_id , record_id){
     $.ajax({
            url: module.get_value('base_url') + 'employees/get_employee_type',
            type: 'post',
            dataType: 'json',
            data: 'rank_id=' + rank_id + '&record_id=' + record_id,
            success: function (response) {
        		$('#employee_type_readonly').val(response.employee_type);
        		$('#employee_type').val(response.employee_type_id);
        		// $('select[name="employee_type"]').val(response.employee_type_id);
        		var data = $('#transfer_effectivity_date, #employee_id, #current_employee_type_dummy').serialize();
        		var data = data+'&new_employee_type='+response.employee_type_id;
        		 $.ajax({
					url: module.get_value('base_url') + 'employee/movement/pro_rate_leaves',
					data: data,
					type: 'post',
					dataType: 'json',
					success: function(response)
					{
						$('#sick_leave').val('');
						$('#vacation_leave').val('');
						$('#emergency_leave').val('');
						$('#sick_leave').val(response.sl);
						$('#vacation_leave').val(response.vl);
						$('#emergency_leave').val(response.el);
					}
				});
            }
        });

}

function add_benefit(){
	if( $('#benefitddlb').val() != "" ){
		var benefit_id  = $('#benefitddlb').val();
		var selected_benefits  = $('input[name=selected-benefits]').val();
		$.ajax({
			url: module.get_value('base_url') + 'employee/movement/add_benefit_field',
			data: 'benefit_id='+benefit_id+'&selected_benefits='+selected_benefits,
			dataType: 'json',
			type: 'post',
			beforeSend: function(){
							
			},			
			success: function (data) {				
				$('.benefits-div').parent().append(data.field);
				$('input.benefit-field').each(function(){
					$(this).keyup( maskFloat);
				});
				$('input[name=selected-benefits]').val( data.selected_benefits );
				$('#benefitddlb').html(data.benefitddlb);
				$('input[name="benefit['+benefit_id+']"]').focus();

				if (quickedit_boxy != undefined) {
					boxyHeight(quickedit_boxy, '#boxyhtml');
				}				
			}
		});	
	}
	else{
		Boxy.ask("Please select a benefit?", ["Cancel"],
		function( choice ) {
			
		},
		{
			title: "Select a Benefit"
		});
	}
}

function show_benefit(selected_benefits, benefit_values){

		var pieces = selected_benefits.split(',');
		var val_pieces = benefit_values.split(',');
		for(i=0;i<pieces.length;i++)
		{			
				var benefit_id = pieces[i];
				var benefit_values = val_pieces[i];
								$.ajax({
					url: module.get_value('base_url') + 'employee/movement/add_benefit_field',
					data: 'benefit_id='+benefit_id+'&selected_benefits='+selected_benefits+'&benefit_values='+benefit_values,
					dataType: 'json',
					type: 'post',
					beforeSend: function(){
									
					},			
					success: function (data) {				
						setTimeout(function () { 
							$('.benefits-div').parent().append(data.field);
							$('input.benefit-field').each(function(){
								$(this).keyup( maskFloat);
							});
							$('input[name=selected-benefits]').val( data.selected_benefits );
							$('#benefitddlb').html(data.benefitddlb);
							
							if (quickedit_boxy != undefined) {
								boxyHeight(quickedit_boxy, '#boxyhtml');
							}	
						}, 10);			
					}
				});	
		}
}

function delete_benefit( field, benefit_id ){
	Boxy.ask("Are you sure you want to delete benefit?", ["Yes", "Cancel"],
	function( choice ) {
		if(choice == "Yes"){
			var benefitval = field.parent().parent().find('input[name=benefit_id'+benefit_id+']');
			var benefitlabel = field.parent().parent().find('input[name=benefit_label'+benefit_id+']');
			field.parent().parent().parent().remove();
			var option = '<option value="'+benefitval.val()+'">'+benefitlabel.val()+'</option>';
			$('#benefitddlb').append(option);
			var selected_benefits = new Array();
			var sb= 0;
			var temp = $('input[name=selected-benefits]').val().split(','); 
			for(var i in temp){
				if( temp[i] != benefit_id ){
					selected_benefits[sb] = temp[i];
					sb++;
				}
			}
			$('input[name=selected-benefits]').val( selected_benefits.join(',') );
		}
	},
	{
		title: "Delete Benefit"
	});
}

function clear_benefit(){
	$('.clear_benefit').remove();
	$.ajax({
		url: module.get_value('base_url') + 'employee/movement/show_option_benefit',
	//	data: 'employee_id='+$(this).val()+'&record_id='+module.get_value('record_id'),
		type: 'post',
		dataType: 'json',
		success: function(response)
		{
			$('#benefitddlb').html(response.benefitddlb);
		}
	});
}

function alert_me()
{
	alert("hey");
}

function show_fields()
{
	var show_this_field = $('#show_pick').val();
	var pieces = show_this_field.split(',');
	var adjust_css = false;

	if ($('#current_project_name_id').length > 0){
		$.ajax({
			url: module.get_value('base_url') +'employee/movement/get_project_per_employee',
			type: 'post',
			data: 'employee_id=' + $('#employee_id').val(),
			dataType: 'json',
			beforeSend: function(){
							
			},			
			success: function (data) {				
				if (data.exist == true){
					if ($('#current_project_name_id').val() == ''){
						var curr_proj = data.cur_proj_id;
					}
					else{
						var curr_proj = $('#current_project_name_id').val();
						var curr_proj_text = $('#current_project_name_id option:selected').text();						
					}

					if ($('#current_division_dummy').val() == ''){
						var curr_div = data.cur_div_name;
					}
					else{
						var curr_div = $('#current_division_dummy').val();						
					}					
					$('#current_project_name_id').html(data.html);
					$('#current_project_name_id').val(curr_proj);
					$('#current_division_dummy').val(curr_div);

					if (curr_proj != ''){
						if ( $("#current_project_name_id option[value='"+curr_proj+"']").length == 0 ){
							$('#current_project_name_id').append('<option value="'+curr_proj+'" selected="selected">'+curr_proj_text+'</option>');
						}
					}					
				}
				else{
					$('#current_project_name_id').html('');
				}
			}			
		})
	}

	if ($('#current_group_name_id').length > 0){
		$.ajax({
			url: module.get_value('base_url') +'employee/movement/get_group_per_employee',
			type: 'post',
			data: 'employee_id=' + $('#employee_id').val(),
			dataType: 'json',
			beforeSend: function(){
							
			},			
			success: function (data) {				
				if (data.exist == true){
					if ($('#current_group_name_id').val() == ''){
						var curr_group = data.current_group_id;
					}
					else{
						var curr_group = $('#current_group_name_id').val();
						var curr_group_text = $('#current_group_name_id option:selected').text();
					}

					if ($('#current_department_dummy').val() == ''){
						var curr_dept = data.current_department_name;
					}
					else{
						var curr_dept = $('#current_department_dummy').val();						
					}	

					$('#current_group_name_id').html(data.html);
					$('#current_group_name_id').val(curr_group)
					$('#current_department_dummy').val(curr_dept);

					if (curr_group != ''){
						if ( $("#current_group_name_id option[value='"+curr_group+"']").length == 0 ){
							$('#current_group_name_id').append('<option value="'+curr_group+'" selected="selected">'+curr_group_text+'</option>');
						}
					}
				}
				else{
					$('#current_group_name_id').html('');
				}				
			}			
		})
	}	

	for(var i in pieces)
	{
		$('#'+$.trim(pieces[i])).parent().parent().slideDown("slow");
		if($.trim(pieces[i]) == 'subordinates_id' || $.trim(pieces[i]) == 'employee_reporting_to' || $.trim(pieces[i]) == 'current_employee_reporting_to' || $.trim(pieces[i]) == 'segment_2_id' || $.trim(pieces[i]) == 'current_segment_2_dummy')
			adjust_css = true;
		if ($.trim(pieces[i]) == 'current_cbe' || $.trim(pieces[i]) == 'new_cbe'){
			$('label[for="current_cbe"]').parent().slideDown("slow");
			$('label[for="new_cbe"]').parent().slideDown("slow");
		}
	}

	if(adjust_css)
	{
		$('#subordinates_id_chzn').css('width','95%');
		$('.chzn-drop').css('width','100%');

		$('#employee_reporting_to_chzn').css('width','95%');
		$('.chzn-drop').css('width','100%');

		$('#current_employee_reporting_to_chzn').css('width','95%');
		$('.chzn-drop').css('width','100%');

		$('#employee_reporting_to_chzn').css('width','95%');
		$('.chzn-drop').css('width','100%');

		$('#segment_2_id_chzn').css('width','95%');
		$('.chzn-drop').css('width','100%');

		$('#current_segment_2_dummy_chzn').css('width','95%');
		$('.chzn-drop').css('width','100%');								
	}

	$('#show_pick').find('option[value="'+$('#show_pick').val()+'"]').remove();
	$('#current_shift_calendar_id').before('<input type="hidden" name="current_shift_calendar_id" value="'+$('#current_shift_calendar_id').val()+'">').attr('disabled','disabled');
	$('#current_employment_status_id').before('<input type="hidden" name="current_employment_status_id" value="'+$('#current_employment_status_id').val()+'">').attr('disabled','disabled');
	$('#current_group_name_id').before('<input type="hidden" name="current_group_name_id" value="'+$('#current_group_name_id').val()+'">').attr('disabled','disabled');
	$('#current_code_status_id').before('<input type="hidden" name="current_code_status_id" value="'+$('#current_code_status_id').val()+'">').attr('disabled','disabled');
	$('#current_segment_2_dummy option:not(:selected)').attr('disabled', true);
	//$('#current_shift_calendar_id').attr('readonly','readonly');	
}

function populate_company_relations(company_id) {
    
    // Departments dropdown populate.
    //departments = module.get_value('base_url') +'employee/movement/get_company_departments';
    var data = 'company_id=' + company_id + '&record_id=' + $('#record_id').val();
    
    // alert($('#transfer_to').val());
    $('#transfer_to').find('option').remove();
    $('#transfer_to').append($("<option></option>").attr("value",'').text("Select..."));             
        
    $.ajax({
        url: module.get_value('base_url') +'employee/movement/get_company_departments',
        type: 'post',
        data: data,
        success: function (response) {  ;
            // Append the new values to department dropdown.
            //var selected_option = false;
            var company = response.data;
            //alert(response);
            for(var i in company)
        		$('#transfer_to').append($("<option></option>").attr("value",company[i].department_id).text(company[i].department));
            
           
            $('select[id="transfer_to"]').trigger("liszt:updated");
        }                
    });        
}

function populate_reporting_to(position_id)
{
	var data = "position_id="+position_id;
	$.ajax({
        url: module.get_value('base_url') +'employee/movement/get_pos_reporting_to',
        type: 'post',
        data: data,
        success: function (response) { 
            // Append the new values to department dropdown.
            if(response.msg_type == 'error')
            {
            	$('#incumbent').remove();
            	$('#employee_reporting_to').val('');
            }
            var position = response.data;

            $('#employee_reporting_to').val(position.approver_position);
            
            $('#incumbent').remove();
            if(response.msg_type == 'success')
            	$('#employee_reporting_to').after('<br /><i id="incumbent">Incumbent: '+position.approver_name+'</i>');

        }                
    });  
}

function export_list()
{
	$('#export-form').attr('action', $('#export_link').val());
	$('#export-form').submit();

	var option = "";

	$.ajax({
		url: module.get_value('base_url') +'employee/movement/get_employee_list',
		type: 'post',
		beforeSend: function(){
			$.blockUI({ message: '<div class="now-loading align-center"><img src="'+module.get_value('base_url')+user.get_value('user_theme')+'/images/loading.gif"><br />Loading Employees, please wait...</div>'});
		},		
		success: function(response)	{
			$.unblockUI();

			Boxy.ask("<label class='label-desc gray' for='employee_id'>Employee:</label><div class='select-input-wrap' style='margin-top:5px'><select style='margin-top:20px' name='employee_export' class='chzn-select' id='employee_export' style='width:75%'>"+response.data+"</select></div>", ["Export", "Cancel"],function( choice ) {
			if(choice == "Export"){
					var data = $('#employee_export').serialize();
					$.ajax({
						url: module.get_value('base_url') +'employee/movement/export_list',
				        data: data,
				        dataType: 'json',
				        type: 'post',
				        success: function (response) {
				            var path = "/"+response.data;
			                window.location = module.get_value('base_url')+path;
				        }
					});
					
			    }
			},
			{
			    title: "Individual Movement History"
			});
			$(".chzn-select").chosen();
		}
	});

	$('#export-form').attr('action', '');
	return false;
}

function show_hidden_detail()
{
	$('label[for="new_employment_status_id"]').parent().show();
	$('label[for="current_employment_status_id"]').parent().show();

	$('label[for="project_name_id"]').parent().show();
	$('label[for="current_project_name_id"]').parent().show();
	$('label[for="current_group_name_id"]').parent().show();
	$('label[for="group_name_id"]').parent().show();
	$('label[for="extension_no_months"]').parent().show();
	$('label[for="transfer_effectivity_date_to"]').parent().show();

	$('label[for="current_position_id"]').parent().show();
	$('label[for="new_position_id"]').parent().show();
	$('label[for="current_role"]').parent().show();
	$('label[for="role"]').parent().show();
	$('label[for="current_department_dummy"]').parent().show();
	$('label[for="transfer_to"]').parent().show();
	$('label[for="current_rank_dummy"]').parent().show();
	$('label[for="rank_id"]').parent().show();
	$('label[for="current_employee_type_dummy"]').parent().show();
	$('label[for="employee_type"]').parent().show();
	$('label[for="current_job_level_dummy"]').parent().show();
	$('label[for="job_level"]').parent().show();
	$('label[for="current_range_of_rank_dummy"]').parent().show();
	$('label[for="range_of_rank"]').parent().show();
	$('label[for="current_rank_code_dummy"]').parent().show();
	$('label[for="rank_code"]').parent().show();
	$('label[for="current_company_dummy"]').parent().show();
	$('label[for="company_id"]').parent().show();
	$('label[for="current_division_dummy"]').parent().show();
	$('label[for="division_id"]').parent().show();
	$('label[for="current_code_status_id"]').parent().show();
	$('label[for="new_code_status_id"]').parent().show();
	$('label[for="current_location_dummy"]').parent().show();
	$('label[for="location_id"]').parent().show();
	$('label[for="current_segment_1_dummy"]').parent().show();
	$('label[for="segment_1_id"]').parent().show();
	$('label[for="current_segment_2_dummy"]').parent().show();
	$('label[for="segment_2_id"]').parent().show();
	$('label[for="employee_reporting_to"]').parent().show();
	$('label[for="current_employee_reporting_to"]').parent().show();	
	$('label[for="new_shift_calendar_id"]').parent().show();
	$('label[for="current_shift_calendar_id"]').parent().show();	
	$('label[for="current_cbe"]').parent().show();
	$('label[for="new_cbe"]').parent().show();		
}

function movement_leaves(data, leave_key)
{
	url_ext = 'pro_rate_leaves_'+leave_key;
	$.ajax({
        url: module.get_value('base_url') +'employee/movement/'+url_ext,
        type: 'post',
        data: data,
        success: function (response) { 
        	//temporary comment since firsbalfour has no base leave credits
/*    		$('#sick_leave').val('');
			$('#vacation_leave').val('');
			$('#emergency_leave').val('');
			$('#sick_leave').val(response.sl);
			$('#vacation_leave').val(response.vl);
			$('#emergency_leave').val(response.el);*/
        }                
    });  
}

function change_employee_dropdown(nature_id)
{
	$.ajax({
		url: module.get_value('base_url')+module.get_value('module_link')+'/change_employee_dropdown',
		data: 'nature_id='+nature_id,
		type: 'post',
		dataType: 'html',
		success: function(response) {
			$('#multiselect-employee_floating_id option').remove();
			$('#multiselect-employee_floating_id').append(response);
			$('#multiselect-employee_floating_id').multiselect("refresh");
		}
	});
}

function get_sub_repto_dropdown()
{
	$('select[name="subordinates_id"], select[name="employee_reporting_to"]').find('option').remove();   
	// $('select[name="employee_reporting_to"]').find('option').remove();   

    var data = $('#campaign_id').serialize();

    $.ajax({
        url: module.get_value('base_url')+module.get_value('module_link')+'/get_sub_repto_dropdown',
        type: 'post',
        data: data,
        success: function (response) {  

            $('select[name="subordinates_id"], select[name="employee_reporting_to"]').append(response);
            $('select[name="subordinates_id"], select[name="employee_reporting_to"]').trigger("liszt:updated");
        }                
    });   
}

function save_movement_draft( on_success, is_wizard , callback ){
	var go = 1;
	var boxes = $('input[class=employee_movement_nature_movement]:checked');
	if($(boxes).length != 0 ) {
		var nat_move = [ '1','3','8','15','10','6','7','11' ];
		var comp_adj = [ '2','4','5' ];
		var employee_movement_array = new Array();
		 $('.employee_movement_nature_movement').each(function() {
		 	if(this.checked) {
		 		employee_movement_array.push(this.value);
		 	}
	    });
	 	if(array_diff(employee_movement_array, comp_adj ) && array_diff(employee_movement_array, nat_move )) {
			if($('#compensation_effectivity_date').val() == '' && $('#transfer_effectivity_date').val() == '') {
				$('#message-container').html(message_growl("error", "Please input movement and compensation adjustment effective date."));
				go = 0;
			} else {
				if(in_array(employee_movement_array, comp_adj )) {
					if($('#compensation_effectivity_date').val() == '') {	 						
						$('#message-container').html(message_growl("error", "Please input compensation adjustment effective date."));
						go = 0;
					}
				}
				if(in_array(employee_movement_array, nat_move )) {
					if($('#transfer_effectivity_date').val() == '') {
						$('#message-container').html(message_growl("error", "Please input movement effective date."));
						go = 0;
					}
				}
			}
		} else {
			if(in_array(employee_movement_array, comp_adj )) {
				if($('#compensation_effectivity_date').val() == '') {	 						
					$('#message-container').html(message_growl("error", "Please input compensation adjustment effective date."));
					go = 0;
				}
			}
			if(in_array(employee_movement_array, nat_move )) {
				if($('#transfer_effectivity_date').val() == '') {
					$('#message-container').html(message_growl("error", "Please input movement effective date."));
					go = 0;
				}
			}
		}
	} else {
		$('#message-container').html(message_growl("info", "Select Nature of movement."));
		go = 0;
	}
 	
 	if(go) {
		$('#current_basic_salary').attr('disabled', false);
		$('form#record-form').append('<input type="hidden" name="draft" value="true" />');
		ajax_save( '', is_wizard , callback );
	}
}

function save_movement_pending( on_success, is_wizard , callback ){
	var go = 1;
	var boxes = $('input[class=employee_movement_nature_movement]:checked');
	if($(boxes).length != 0 ) {
		var nat_move = [ '1','3','8','15','10','6','7','11' ];
		var comp_adj = [ '2','4','5' ];
		var employee_movement_array = new Array();
		 $('.employee_movement_nature_movement').each(function() {
		 	if(this.checked) {
		 		employee_movement_array.push(this.value);
		 	}
	    });
	 	if(array_diff(employee_movement_array, comp_adj ) && array_diff(employee_movement_array, nat_move )) {
			if($('#compensation_effectivity_date').val() == '' && $('#transfer_effectivity_date').val() == '') {
				$('#message-container').html(message_growl("error", "Please input movement and compensation adjustment effective date."));
				go = 0;
			} else {
				if(in_array(employee_movement_array, comp_adj )) {
					if($('#compensation_effectivity_date').val() == '') {	 						
						$('#message-container').html(message_growl("error", "Please input compensation adjustment effective date."));
						go = 0;
					}
				}
				if(in_array(employee_movement_array, nat_move )) {
					if($('#transfer_effectivity_date').val() == '') {
						$('#message-container').html(message_growl("error", "Please input movement effective date."));
						go = 0;
					}
				}
			}
		} else {
			if(in_array(employee_movement_array, comp_adj )) {
				if($('#compensation_effectivity_date').val() == '') {	 						
					$('#message-container').html(message_growl("error", "Please input compensation adjustment effective date."));
					go = 0;
				}
			}
			if(in_array(employee_movement_array, nat_move )) {
				if($('#transfer_effectivity_date').val() == '') {
					$('#message-container').html(message_growl("error", "Please input movement effective date."));
					go = 0;
				}
			}
		}
	} else {
		$('#message-container').html(message_growl("info", "Select Nature of movement."));
		go = 0;
	}
 	
 	if(go) {
		$('#current_basic_salary').attr('disabled', false);
		$('form#record-form').append('<input type="hidden" name="pending" value="true" />');
		save_and_email(false, "Are You Sure?");
	}
}

//for auto approve purpose
function save_movement_back( on_success, is_wizard , callback ) {
	var go = 1;
	var boxes = $('input[class=employee_movement_nature_movement]:checked');
	if($(boxes).length != 0 ) {
		var nat_move = [ '1','3','8','15','10','6','7','11' ];
		var comp_adj = [ '2','4','5' ];
		var employee_movement_array = new Array();
		 $('.employee_movement_nature_movement').each(function() {
		 	if(this.checked) {
		 		employee_movement_array.push(this.value);
		 	}
	    });
	 	if(array_diff(employee_movement_array, comp_adj ) && array_diff(employee_movement_array, nat_move )) {
			if($('#compensation_effectivity_date').val() == '' && $('#transfer_effectivity_date').val() == '') {
				$('#message-container').html(message_growl("error", "Please input movement and compensation adjustment effective date."));
				go = 0;
			} else {
				if(in_array(employee_movement_array, comp_adj )) {
					if($('#compensation_effectivity_date').val() == '') {	 						
						$('#message-container').html(message_growl("error", "Please input compensation adjustment effective date."));
						go = 0;
					}
				}
				if(in_array(employee_movement_array, nat_move )) {
					if($('#transfer_effectivity_date').val() == '') {
						$('#message-container').html(message_growl("error", "Please input movement effective date."));
						go = 0;
					}
				}
			}
		} else {
			if(in_array(employee_movement_array, comp_adj )) {
				if($('#compensation_effectivity_date').val() == '') {	 						
					$('#message-container').html(message_growl("error", "Please input compensation adjustment effective date."));
					go = 0;
				}
			}
			if(in_array(employee_movement_array, nat_move )) {
				if($('#transfer_effectivity_date').val() == '') {
					$('#message-container').html(message_growl("error", "Please input movement effective date."));
					go = 0;
				}
			}
		}
	} else {
		$('#message-container').html(message_growl("info", "Select Nature of movement."));
		go = 0;
	}
 	
 	if(go) {
		$('#current_basic_salary').attr('disabled', false);
		$('form#record-form').append('<input type="hidden" name="pending" value="true" />');
		ajax_save('back', 0);
	}

}

function get_salary() {
	if($('#record_id').val() != -1) {
		record_id = $('#record_id').val();
		$.ajax({
			url: module.get_value('base_url') + 'employee/movement/get_employee_salary',
			data: 'record_id=' + record_id+'&view='+view,
			type: 'post',
			dataType: 'json',
			success: function(data) {
				if(view == 'edit') {
					$('#current_basic_salary').val(data.salary_current);
					$('#new_basic_salary').val(data.salary_new);
					$('#current_basic_salary').attr('disabled', true);
				} else {
					$('label[for="current_basic_salary"]').parent().remove();	
					$('label[for="compensation_effectivity_date"]').parent().before(data.html_current);
					$('label[for="new_basic_salary"]').parent().remove();	
					$('label[for="compensation_effectivity_date"]').parent().before(data.html_new);
				}
			}
		});
	}
}

function in_array(needle, haystack)
{
    for(var key in haystack)
    {
        if(needle === haystack[key])
        {
            return true;
        }
    }

    return false;
}

function array_diff(arr1) {
  var retArr = {},
    argl = arguments.length,
    k1 = '',
    i = 1,
    k = '',
    arr = {};

  arr1keys: for (k1 in arr1) {
    for (i = 1; i < argl; i++) {
      arr = arguments[i];
      for (k in arr) {
        if (arr[k] === arr1[k1]) {
          // If it reaches here, it was found in at least one array, so try next value
          continue arr1keys;
        }
      }
      retArr[k1] = arr1[k1];
    }
  }
	if(Object.keys(retArr).length  == '0') {
	  	return false;
	} else {
		return true;	
	}
}


