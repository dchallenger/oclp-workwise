$( document ).ready(function(){
	bindOBTEvents();
});

function bindOBTEvents(){
	window.onload = function(){
		if( module.get_value('view') == 'index' ){

			if( $('ul#grid-filter li').length > 0 ){

	            $('ul#grid-filter li').each(function(){ 

	                if( $(this).hasClass('active') ){

	                    if($(this).attr('filter') == 'for_approval'){
	                       $('.status-buttons').parent().show();
	                    }
	                    else{
	                       $('.status-buttons').parent().hide();
	                    }

	                }
	            });

	        }
	        else{
	            $('.status-buttons').parent().hide();
	        }

		}
	}

	if( module.get_value('module_link') == "employee/dtr" ){
		$('input[name="time_start"]').timepicker({
			showAnim: 'slideDown',
			showOn: "both",
			timeFormat: 'hh:mm tt',
			buttonImage: module.get_value('base_url') + user.get_value('user_theme') + "/icons/calendar-month.png",
			buttonImageOnly: true,
			buttonText: '',
			hourGrid: 4,
			minuteGrid: 10,
			ampm: true,
			}).keyup(function(e) {
			if(e.keyCode == 8 || e.keyCode == 46) {
			$.datepicker._clearDate(this);
			}
			});
			$('input[name="time_end"]').timepicker({
			showAnim: 'slideDown',
			showOn: "both",
			timeFormat: 'hh:mm tt',
			buttonImage: module.get_value('base_url') + user.get_value('user_theme') + "/icons/calendar-month.png",
			buttonImageOnly: true,
			buttonText: '',
			hourGrid: 4,
			minuteGrid: 10,
			ampm: true,
			}).keyup(function(e) {
			if(e.keyCode == 8 || e.keyCode == 46) {
			$.datepicker._clearDate(this);
			}
		}); 
	}

	validation_check();

	if (user.get_value('post_control') == 0){
		if (module.get_value('module_link') == "employee/dtr") {
			if($('#employee_id option:selected').val()){
				get_obt_detail($('#employee_id option:selected').val(), $('#employee_id option:selected').text());
			}
		}else if ($('select[name="employee_id"]').length != 0){
			$('label[for="employee_id"]').append('<div class="text-input-wrap"><input id="employee_id" class="input-text" type="hidden" value="'+$('#employee_id option:selected').val()+'" name="employee_id"><input id="employee_name" class="input-text" type="text" value="'+$('#employee_id option:selected').text()+'" name="employee_name" disabled="disabled"></div>');
			$('label[for="employee_id"]').next().remove();
		}
	}else if(user.get_value('post_control') == 1){
		
		if ($('#filter').val() == 'personal') {
			$('label[for="employee_id"]').append('<div class="text-input-wrap"><input id="employee_id" class="input-text" type="hidden" value="'+$('#employee_id option:selected').val()+'" name="employee_id"><input id="employee_name" class="input-text" type="text" value="'+$('#employee_id option:selected').text()+'" name="employee_name" disabled="disabled"></div>');
			$('label[for="employee_id"]').next().remove();
		}else if (module.get_value('module_link') == "employee/dtr") {
			if($('#employee_id option:selected').val()){
				get_obt_detail($('#employee_id option:selected').val(), $('#employee_id option:selected').text());
			}
		}else{
			get_sub_by_project();
		};
	
	}

	
}

/**
 * Get the work schedule base on date entry
 * @return void
 */
function validation_check(){
	$("#date_from,#date_to,#time_start,#time_end").live("change", function(e) {
		e.preventDefault; 
		var date_from = $("#date_from").val();
		var date_to = $("#date_to").val();	
	    var time_start_hh_mm = $("#time_start").val();
	    var time_end_hh_mm = $("#time_end").val();
	    var employee_id = $("#employee_id").val();
	    var record_id = $("#record_id").val();
	    if (date_from != '' && date_to != '' && time_start_hh_mm != '' && time_end_hh_mm != ''){
			$.ajax({
				url: module.get_value('base_url') + module.get_value('module_link') + "/validation_check",
				type:"POST",
				dataType: 'json',
				data: { employee_id: employee_id, record_id: record_id, date_from : date_from, date_to: date_to, time_start_hh_mm : time_start_hh_mm, time_end_hh_mm : time_end_hh_mm, form: 'obt'},
				beforeSend: function(){
					 		
				},
				success: function(response){
					if (response.err){
						$('#message-container').html(message_growl('error', response.msg_type))
					}
				}
			});	 
		}
	});
}

function get_sub_by_project() {
		    if(module.get_value('module_link') == "employee/dtr"){
		        var get_sub_url = module.get_value('base_url') + 'forms/obt' + "/get_sub_by_project";  
		    }else{
		        var get_sub_url = module.get_value('base_url') + module.get_value('module_link') + '/get_sub_by_project';
		    }
        $.ajax({
            url: get_sub_url,
            data: 'project_hr=' + user.get_value('user_id'),
            type: 'post',
            dataType: 'json',
            success: function (data) {
            	if (data.is_projecthr == true) {
            		$('select[name="employee_id"]').html(data.subordinates);
                	$('select[name="employee_id"]').trigger("liszt:updated");		
                	
            	};
            	//commented to view all employees if user has post-control and is not a project HR
     //        	else{

     //        		$('label[for="employee_id"]').append('<div class="text-input-wrap"><input id="employee_id" class="input-text" type="hidden" value="'+data.employee_id+'" name="employee_id"><input id="employee_name" class="input-text" type="text" value="'+data.employee+'" name="employee_name" disabled="disabled"></div>');
					// $('label[for="employee_id"]').next().remove();


     //        	};
             
            }
        });  
}

function get_obt_detail(user_id, user_name) {
        $.ajax({
            url: module.get_value('base_url') + module.get_value('module_link') + '/get_obt_detail',
            data: 'user_id=' + user_id,
            type: 'post',
            dataType: 'json',
            success: function (data) {
            		$('select[name="employee_id"]').html(data.subordinates);
                	$('select[name="employee_id"]').trigger("liszt:updated");	
					$('select[name="employee_id"]').next().remove();	
					if(user_id > 0){
						$('label[for="employee_id"]').append(data.employee);
					// }else{
					// 	$('label[for="employee_id"]').append('<div class="text-input-wrap"> <input id="employee_id" class="input-text" type="hidden" value="'+$('#employee_id option:selected').val()+'" name="employee_id"><input id="employee_name" class="input-text" type="text" value="'+$('#employee_id option:selected').text()+'" name="employee_name" disabled="disabled"> </div>');
					}
            }
        });  
}
