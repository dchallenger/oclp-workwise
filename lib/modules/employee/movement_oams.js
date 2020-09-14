var benefit_id_global="";
var benefit_val_global="";
$(document).ready(function () {
	var action = module.get_value('view');		

	var x = new Movement(action);


});



function Movement(action) {	

	if( user.get_value('post_control') != 1 ){

		$('label[for="additional_remarks"]').parent().remove();

	}

	this.edit = function () {

		if ($('#record_id').val() == '-1') {
			setTimeout(function () {
				$('#created_date').datepicker('setDate', new Date());
			}, 100);
		}		

		$('#current_basic_salary, #current_allowances, #current_commission, #current_total').attr('readonly', 'readonly');

		//$('#new_basic_salary, #new_allowances, #new_commission').change(update_total);

		
		//Initialize Nature of Movement Type Checkbox
		
		options = $('#employee_movement_nature_movement option');	
		var rows = new Array();	

		$(options).each(function (index, element) {
			option = $(element);
			field  = $('<input type="checkbox" class="employee_movement_nature_movement" name="employee_movement_nature_movement[]"/>').val(option.val());

			if (element.selected) {			
				field.attr('checked', 'checked');
			}		

			rows[option.text().replace(' ', '').replace(' ', '')] = $(field)[0].outerHTML + option.text();	

		});

		
		var table = $('<table width="50%"></table>');
		var tr1   = $('<tr></tr>');
		var tr2   = $('<tr></tr>');

		tr1.append($('<td></td>').html(rows['Regularization']));
		tr1.append($('<td></td>').html(rows['Promotion']));
		tr1.append($('<td></td>').html(rows['Re-assignment']));	
		
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
		tr1.append($('<td></td>').html(rows['EndofContract']));
		
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
		$('#employee_movement_compensation_adjustment, label[for="employee_movement_compensation_adjustment"]').remove();

		setTimeout(function () { 
			$('#employee_movement_compensation_adjustment_chzn').remove();			
		} , 50);

		

		$('.employee_movement_nature_movement').click(function(){

			var nature_movement_val = $(this).val();

			if( ( nature_movement_val != 6 ) && ( nature_movement_val != 7 ) && ( nature_movement_val != 9 ) ){

				if($(this).attr('checked')){

					$('.employee_movement_nature_movement').each(function(){

						if( $(this).val() == 6 || $(this).val() == 7 || $(this).val() == 9  ){

							$(this).removeAttr('checked');

						}

					});

				}
				

			}

			//If Resignation is checked
			else if( ( nature_movement_val == 6 ) || ( nature_movement_val == 7 ) || ( nature_movement_val == 9 ) ){


				$('.employee_movement_nature_movement').each(function(){

					if( $(this).val() != nature_movement_val ){

						$(this).removeAttr('checked');

					}

				});
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
						show_benefit(total, benefit_val);		
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
	            $('#employee_id_chzn').replaceWith('<input type="textbox" style="border:0px" readonly=true value="'+ fixname+'"/>');
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
						show_benefit(total, benefit_val);		
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
		$('select[name="current_position_id"] ').attr('disabled', 'disabled');
		$('select[name="current_department_id"] ').attr('disabled', 'disabled');


		
		function update_fields(data) 
		{

			//Update employee position
			$('select[name="current_position_id"]').val(data.position_id);
			//$('select[name="current_position_id"]').trigger("liszt:updated");


			//Update employee department
			$('select[name="current_department_id"]').val(data.department_id);
			//$('select[name="current_department_id"]').trigger("liszt:updated");

		}

		function update_leaves(data)
		{

			if(data == 0){
				$('#current_sick_leave').val(0);
				$('#current_sick_leave').attr('disabled','disabled');
				$('#current_vacation_leave').val(0);
				$('#current_vacation_leave').attr('disabled','disabled');
			}
			else{
				$('#current_sick_leave').val(data.sl);
				$('#current_sick_leave').attr('disabled','disabled');
				$('#current_vacation_leave').val(data.vl);
				$('#current_vacation_leave').attr('disabled','disabled');
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
							total += parseInt(val);
						}

					});

				}
				else{

					val   = $(selector).val();

					if (val != '') {
						val = val.replace(/,/g, '');
						total += parseInt(val);
					}
					alert(total);
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
							total += parseInt(val);
						}

					});

				}
				else{

					val   = $(selector).val();

					if (val != '') {
						val = val.replace(/,/g, '');
						total += parseInt(val);
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

function show_full_movement(record_id)
{
	var send='record_id='+record_id;
	$.ajax({
		url: module.get_value('base_url') + 'employee/movement/show_full_movement',
		data: send,
		dataType: 'json',
		type: 'post',
		success: function (response) {
			if (response.msg_type == 'error') {
				$('#message-container').html(message_growl(response.msg_type, response.msg));
			} else {
				employee = response.data;
				var width = $(window).width()*.6;
				width=width+50;
				var movementCtr=0;
				var html ='<table class="boxyTable" style="text-align:center;width:'+width+'px;"><thead style="background: #909090  ;font-weight:bolder;color:#fff;"><tr><td style="width:20%;padding:5px;border: solid 1px">Movement Type</td><td style="width:30%;padding:5px;border: solid 1px">From</td><td style="width:30%;padding:5px;border: solid 1px">To</td><td style="width:20%;padding:5px;border: solid 1px">Effectivity</td></tr></thead>';
				for(var i in employee){
					if(employee[i].new_department_name!=null)
						html +='<tr><td id="movement" style="padding:5px;border-bottom: solid 1px #E0E0E0 ">'+employee[i].movement_type+'</td><td id="currPos'+movementCtr+'" style="padding:5px;border-bottom: solid 1px #E0E0E0 ">'+employee[i].old_department_name+'</td><td id="newPos'+movementCtr+'" style="padding:5px;border-bottom: solid 1px #E0E0E0 ">'+employee[i].new_department_name+'</td><td style="padding:5px;border-bottom: solid 1px #E0E0E0 ">'+$.datepicker.formatDate('M dd, yy',new Date(employee[i].transfer_effectivity_date))+'</td></tr>';
					if(employee[i].current_position_name!=null)
						html +='<tr><td id="movement" style="padding:5px;border-bottom: solid 1px #E0E0E0 ">'+employee[i].movement_type+'</td><td id="currPos'+movementCtr+'" style="padding:5px;border-bottom: solid 1px #E0E0E0 ">'+employee[i].old_position_name+'</td><td id="newPos'+movementCtr+'" style="padding:5px;border-bottom: solid 1px #E0E0E0 ">'+employee[i].current_position_name+'</td><td style="padding:5px;border-bottom: solid 1px #E0E0E0 ">'+$.datepicker.formatDate('M dd, yy',new Date(employee[i].transfer_effectivity_date))+'</td></tr>';
					if(employee[i].new_basic_salary==null)
						html +='<tr><td id="movement" style="padding:5px;border-bottom: solid 1px #E0E0E0 ">'+employee[i].movement_type+'</td><td id="currPos'+movementCtr+'" style="padding:5px;border-bottom: solid 1px #E0E0E0 ">'+employee[i].current_basic_salary+'</td><td id="newPos'+movementCtr+'" style="padding:5px;border-bottom: solid 1px #E0E0E0 ">'+employee[i].new_basic_salary+'</td><td style="padding:5px;border-bottom: solid 1px #E0E0E0 ">'+$.datepicker.formatDate('M dd, yy',new Date(employee[i].transfer_effectivity_date))+'</td></tr>';
					if(employee[i].new_total==null)
						html +='<tr><td id="movement" style="padding:5px;border-bottom: solid 1px #E0E0E0 ">'+employee[i].movement_type+'</td><td id="currPos'+movementCtr+'" style="padding:5px;border-bottom: solid 1px #E0E0E0 ">'+employee[i].current_total+'</td><td id="newPos'+movementCtr+'" style="padding:5px;border-bottom: solid 1px #E0E0E0 ">'+employee[i].new_total+'</td><td style="padding:5px;border-bottom: solid 1px #E0E0E0 ">'+$.datepicker.formatDate('M dd, yy',new Date(employee[i].transfer_effectivity_date))+'</td></tr>';
				}
				html +='</table>';
				// var dialogue=new Boxy(html, {title: "Dialog", modal: true});
				// //var boxySize=dialogue.getContentSize();
				// dialogue.resize();
				// dialogue.center();

				quickedit_boxy = new Boxy(html,
				{
					title: 'Employee Movement',
					draggable: false,
					modal: true,
					center: true,
					unloadOnHide: true,
					afterShow: function(){ $.unblockUI(); },
					beforeUnload: function(){ $('.tipsy').remove(); }
					
				});	
				boxyHeight(quickedit_boxy, '#boxyhtml');

				//Boxy.alert(employee.employee_movement_type_id+" // "+employee.old_position_name+" "+employee.current_position_name+"||"+employee.old_department_name+" "+employee.new_department_name);
			}
		}
	});
}

function show_full_movement11(record_id)
{
	var send='record_id='+record_id;
	$.ajax({
		url: module.get_value('base_url') + 'employee/movement/show_full_movement',
		data: send,
		dataType: 'json',
		type: 'post',
		success: function (response) {
			if (response.msg_type == 'error') {
				$('#message-container').html(message_growl(response.msg_type, response.msg));
			} else {
				employee = response.data;
				var movementCtr=0;
				var html ='<table class="boxyTable" style="text-align:center;width:100%;border: solid 1px"><thead><tr><td style="width:20%;padding:10px;border: solid 1px">Movement Type</td><td style="width:30%;padding:10px;border: solid 1px">From</td><td style="width:30%;padding:10px;border: solid 1px">To</td><td style="width:20%;padding:10px;border: solid 1px">Effectivity</td></tr></thead>';
				for(var i in employee){
					if(employee[i].employee_movement_type_id.length>1)
					{
						var movement_type = employee[i].employee_movement_type_id.split(',');
						// for(var q in movement_type)
						for(var x=0;x<movement_type.length;x++)
						{
							html +='<tr><td id="movement'+movementCtr+'" style="padding:10px;border: solid 1px">'+movement_type[x]+'</td><td id="currPos'+movementCtr+'" style="padding:10px;border: solid 1px">'+employee[i].current_position_id+'</td><td id="newPos'+movementCtr+'" style="padding:10px;border: solid 1px">'+employee[i].new_position_id+'</td><td style="padding:10px;border: solid 1px">'+employee[i].transfer_effectivity_date+'</td></tr>';
							//alert(employee[i].current_position_id);
							//var current_pos_id = employee[i].current_position_id;
							// var movement_type_id = movement_type[i];
							// var addme=get_movement_type(movement_type_id);
							//alert(addme);
							//html += '<tr><td>'+addme+'</td></tr>';
							movementCtr++;
						}
							//if(movement_type[q]==3)alert(send);
					}
					else
					{
						html +='<tr><td id="movement'+movementCtr+'" style="padding:10px;border: solid 1px">'+employee[i].employee_movement_type_id+'</td><td id="currPos'+movementCtr+'" style="padding:10px;border: solid 1px">'+employee[i].current_position_id+'</td><td id="newPos'+movementCtr+'" style="padding:10px;border: solid 1px">'+employee[i].new_position_id+'</td><td style="padding:10px;border: solid 1px">'+employee[i].transfer_effectivity_date+'</td></tr>';						
						movementCtr++;
					}
						// if(employee[i].employee_movement_type_id==3)
						// {
						//  	html +='<tr><td>'+employee[i].movement_type+'</td><td>'+employee[i].current_position_id+'</td><td>'+employee[i].new_position_id+'</td><td>'+employee[i].transfer_effectivity_date+'</td></tr>';
						// }
						// if(employee[i].employee_movement_type_id==8)
						// {
						//  	html +='<tr><td>'+employee[i].movement_type+'</td><td>'+employee[i].current_department_id+'</td><td>'+employee[i].current_department_id+'</td><td>'+employee[i].transfer_effectivity_date+'</td></tr>';
						// }
				}

					html +='</table>';
					//$('.boxy-wrapper').css('width','1000px');
					//Boxy.resize(400,400);
					var dialogue=new Boxy(html, {title: "Dialog", modal: true});
					var boxySize=dialogue.getContentSize();
					dialogue.resize(boxySize);
					dialogue.center();
					//Boxy.dialogue(html);
					for(var i=0;i<movementCtr;i++)
					{
						var movementtxt="#movement"+i;
						get_movement_type($(movementtxt).text(), i);
						var currPosTxt="#currPos"+i;
						get_position_type($(currPosTxt).text(), i, 'currPos');
						var currPosTxt="#newPos"+i;
						get_position_type($(currPosTxt).text(), i, 'newPos');
					}
					setTimeout(function(){
						dialogue.moveTo(boxySize);
					},800);
					//dialogue.unload();
					// if(iCtr==0)
					// {
					// 	var currPosTxt="#currPos"+iCtr;
					// 	get_position_type($(currPosTxt).text(), iCtr);
					// }
					// for(var y=0;y<=iCtr;y++)
					// {
					// 	var currPosTxt="#currPos"+y;
					// 	//get_position_type($(currPosTxt).text(), i);
					// 	alert($(currPosTxt).text());
					// }
			}
		}
	});
}

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
		//var benefit_id  = $('#benefitddlb').val();

		var pieces = selected_benefits.split(',');
		var val_pieces = benefit_values.split(',');
		for(i=0;i<pieces.length;i++)
		{			
				var benefit_id = pieces[i];
				var benefit_values = val_pieces[i];
				//var selected_benefits  = $('input[name=selected-benefits]').val();
				//var selected_benefits = selected_benefits;
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
							//$('input[name="benefit['+benefit_id+']"]').focus();
							//$('input[name="benefit['+benefit_id+']"]').val(data.benefit_values);

							if (quickedit_boxy != undefined) {
								boxyHeight(quickedit_boxy, '#boxyhtml');
							}	
						}, 10);			
					}
				});	
		}
	// if( $('#benefitddlb').val() != "" ){
	// 	var benefit_id  = $('#benefitddlb').val();
	// 	var selected_benefits  = $('input[name=selected-benefits]').val();
	// 	$.ajax({
	// 		url: module.get_value('base_url') + 'employee/movement/add_benefit_field',
	// 		data: 'benefit_id='+benefit_id+'&selected_benefits='+selected_benefits,
	// 		dataType: 'json',
	// 		type: 'post',
	// 		beforeSend: function(){
							
	// 		},			
	// 		success: function (data) {				
	// 			$('.benefits-div').parent().append(data.field);
	// 			$('input.benefit-field').each(function(){
	// 				$(this).keyup( maskFloat);
	// 			});
	// 			$('input[name=selected-benefits]').val( data.selected_benefits );
	// 			$('#benefitddlb').html(data.benefitddlb);
	// 			$('input[name="benefit['+benefit_id+']"]').focus();

	// 			if (quickedit_boxy != undefined) {
	// 				boxyHeight(quickedit_boxy, '#boxyhtml');
	// 			}				
	// 		}
	// 	});	
	// }
	// else{
	// 	Boxy.ask("Please select a benefit?", ["Cancel"],
	// 	function( choice ) {
			
	// 	},
	// 	{
	// 		title: "Select a Benefit"
	// 	});
	// }
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

	// var benefitval = field.parent().parent().find('input[name=benefit_id'+benefit_id+']');
	// var benefitlabel = field.parent().parent().find('input[name=benefit_label'+benefit_id+']');
	// field.parent().parent().parent().remove();
	// var option = '<option value="'+benefitval.val()+'">'+benefitlabel.val()+'</option>';
	// $('#benefitddlb').append(option);
	// var selected_benefits = new Array();
	// var sb= 0;
	// var temp = $('input[name=selected-benefits]').val().split(','); 
	// for(var i in temp){
	// 	if( temp[i] != benefit_id ){
	// 		selected_benefits[sb] = temp[i];
	// 		sb++;
	// 	}
	// }
	// $('input[name=selected-benefits]').val( selected_benefits.join(',') );



/*

function Movement(action) {	
	this.edit = function () {
		if ($('#record_id').val() == '-1') {
			setTimeout(function () {
				$('#created_date').datepicker('setDate', new Date());
			}, 100);
		}		

		$('#current_basic_salary, #current_allowances, #current_commission, #current_total')
			.attr('readonly', 'readonly');

		$('#new_basic_salary, #new_allowances, #new_commission').change(update_total);		
		
		options = $('#employee_movement_type_id option');	
		var rows = new Array();	

		$(options).each(function (index, element) {
			option = $(element);
			field  = $('<input type="checkbox" name="employee_movement_type_id[]"/>').val(option.val());

			if (element.selected) {			
				field.attr('checked', 'checked');
			}		

			rows[option.text().replace(' ', '')] = $(field)[0].outerHTML + option.text();		
		});

		var table = $('<table width="100%"></table>');
		var tr1   = $('<tr></tr>');
		var tr2   = $('<tr></tr>');

		tr1.append($('<td></td>').html(rows['Regularization']));
		tr1.append($('<td></td>').html(rows['Promotion']));
		tr1.append($('<td></td>').html(rows['Resignation']));
		tr1.append($('<td></td>').html(rows['Termination']));

		tr2.append($('<td></td>').html(rows['SalaryIncrease']));
		tr2.append($('<td></td>').html(rows['WageOrder']));
		tr2.append($('<td></td>').html(rows['Merit/Performance']));	
		
		table.append(tr1);
		table.append(tr2);

		$('label[for="employee_movement_type_id"]').after(table);

		$('label[for="employee_movement_type_id"]').parent().css('width', '100%');

		$('#employee_movement_type_id, label[for="employee_movement_type_id"]').remove();

		setTimeout(function () { 
			$('#employee_movement_type_id_chzn').remove();			
		} , 50);

		$('select[name="employee_id"]').chosen().change(function () {
			if ($(this).val() > 0) {
				$.ajax({
					url: module.get_value('base_url') + 'employees/get_employee',
					data: 'employee_id=' + $(this).val(),
					type: 'post',
					dataType: 'json',
					success: function(data) {
						if (data.msg_type == 'error') {
							$('#message-container').html(message_growl(data.msg_type, data.msg));
						} else {
							update_fields(data.data);
						}
					}
				});
			}
		});

		$('select[name="employee_id"]').trigger('change');

		$('select[name="current_position_id"]').attr('disabled', 'disabled');

		function update_fields(data) 
		{
			$('select[name="current_position_id"]').val(data.position_id);
		}

		function update_total() {
			var n = new Array('#new_basic_salary', '#new_allowances', '#new_commission');
			var total = 0;

			$.each(n, function(index, selector) {
				val   = $(selector).val();

				if (val != '') {
					val = val.replace(/,/g, '');
					total += parseInt(val);
				}
			});

			$('#new_total').val(addCommas(total));
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
*/