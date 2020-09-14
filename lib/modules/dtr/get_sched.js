$( document ).ready(function(){

	get_sched_dt();
});

/**
 * Get the work schedule base on date entry
 * @return void
 */
function get_sched_dt(){

	window.onload = function(){

		var employee_id = $("#employee_id").val()
		var td = $("#date-temp").val();
		var arr_date = td.split('/');
		var month = arr_date[0];
		var day = arr_date[1];
		var year = arr_date[2];
		var ndate = year + '-' + month + '-' + day			    

		if (td){
			$.ajax({
				url: module.get_value('base_url') + module.get_value('module_link') + "/get_work_schedule",
				type:"POST",
				dataType: 'json',
				data: { employee_id: employee_id, date_in : ndate },
				beforeSend: function(){
					 		
				},
				success: function(data){
					if (data){
						$("label[for='work_shift']").next().html(data.shift_schedule);
						$("#time_in1").val(data.timein);					
						$("#time_out1").val(data.timeout);
						$("#time_in2").val(data.breakin);
						$("#time_out2").val(data.breakout);				
					}
				}
			});	
		}
		else{
			$("label[for='work_shift']").next().html("");
		} 

	}

	$("#employee_id").live("change", function(e) { 
	    e.preventDefault; 
	    var employee_id = $(this).val()
		var td = $("#date-temp").val();
		var arr_date = td.split('/');
		var month = arr_date[0];
		var day = arr_date[1];
		var year = arr_date[2];
		var ndate = year + '-' + month + '-' + day			    

		if (td){
			$.ajax({
				url: module.get_value('base_url') + module.get_value('module_link') + "/get_work_schedule",
				type:"POST",
				dataType: 'json',
				data: { employee_id: employee_id, date_in : ndate },
				beforeSend: function(){
					 		
				},
				success: function(data){
					if (data){
						$("label[for='work_shift']").next().html(data.shift_schedule);
						$("#time_in1").val(data.timein);					
						$("#time_out1").val(data.timeout);
						$("#time_in2").val(data.breakin);
						$("#time_out2").val(data.breakout);				
					}
					else{
						$("label[for='work_shift']").next().html("");
					}
				}
			});	
		}
		else{
			$("label[for='work_shift']").next().html("");
		} 
	});

	$("#date-temp").bind("change", function() {
		var td = $(this).val();
		var arr_date = td.split('/');
		var month = arr_date[0];
		var day = arr_date[1];
		var year = arr_date[2];
		var ndate = year + '-' + month + '-' + day	
	    var employee_id = $("#employee_id").val()	   
		$.ajax({
			url: module.get_value('base_url') + module.get_value('module_link') + "/get_work_schedule",
			type:"POST",
			dataType: 'json',
			data: { employee_id: employee_id, date_in : ndate },
			beforeSend: function(){
				 		
			},
			success: function(data){
				if (data){
					$("label[for='work_shift']").next().html(data.shift_schedule);
					$("#time_in1").val(data.timein);					
					$("#time_out1").val(data.timeout);
					$("#time_in2").val(data.breakin);
					$("#time_out2").val(data.breakout);
				}
			}
		});	 
	});

	if(module.get_value('client_no') != 2)
	{
		$("#time_in1").bind("change", function(){
			td = $(this).val();
			var d = td.substring(0,10);
			$("#date-temp").val(d);
			$("#date").val(d);
		})

		$("#time_out1").bind("change", function(){
			td = $(this).val();
			var d = td.substring(0,10);
			if (!($('#time_in1').val())){
				$("#date-temp").val(d);
				$("#date").val(d);
			}
		})	
	}

	$("#time_in1,#time_out1,#time_in2,#time_out2").bind("change", function(){
		var td = $("#date-temp").val();
		var arr_date = td.split('/');
		var month = arr_date[0];
		var day = arr_date[1];
		var year = arr_date[2];
		var ndate = year + '-' + month + '-' + day	
	    var employee_id = $("#employee_id").val()			
		$.ajax({
			url: module.get_value('base_url') + module.get_value('module_link') + "/get_work_schedule",
			type:"POST",
			dataType: 'json',
			data: { employee_id: employee_id, date_in : ndate },
			beforeSend: function(){
				 		
			},
			success: function(data){
				if (data.shift_schedule == ""){
					$('#message-container').html(message_growl('error', 'No work schedule assigned .'))
					$(this).val("");
				}
				else{
					$("label[for='work_shift']").next().html(data.shift_schedule);
				}
			}
		});	 		
	})	
}