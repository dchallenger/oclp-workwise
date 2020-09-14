$(document).ready(function(){
	bindOvertimeEvents();
});

function bindOvertimeEvents(){
	window.onload = function(){
		if( module.get_value('view') == 'edit' || module.get_value('module_link') == "employee/dtr" ){
			get_inclusive_undertime_blanket(1);
			get_inclusive_emergency_blanket(1);
			get_inclusive_worksched(1);
		}

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

	if( module.get_value('view') == 'edit'){
		$('#date_created-temp').datepicker().datepicker('disable');

		var curr_date = $.datepicker.formatDate('mm/dd/yy', new Date());
		$("#date_created").val(curr_date);
		$("#date_created-temp").val(curr_date);	

		//if extended OT
		if($('#related_id').val() > 0 ){

			var date_to = new Date($("#datetime_to").val());
			var date_to_hours = (date_to.getHours() < 10 ? '0' : '') + (date_to.getHours());
			var date_to_minutes = (date_to.getMinutes() < 10 ? '0' : '') + (date_to.getMinutes());
			if(date_to_hours > 11){
				date_to_sign = 'pm';
				if(date_to_hours > 12){
					date_to_hours = date_to_hours - 12;
					date_to_hours = (date_to_hours < 10 ? '0' : '') + (date_to_hours)
				}
			}else{
				date_to_sign = 'am';
			}

			related_date_to = $.datepicker.formatDate('mm/dd/yy', date_to) + ' ' + date_to_hours + ':' + date_to_minutes + ' ' + date_to_sign;
			// console.log($.datepicker.formatDate('mm/dd/yy', date_to) + ' ' + date_to_hours + ':' + date_to_minutes + ' ' + date_to_sign);

			$("#reason").attr('readonly','readonly');
			$("#datetime_from").val(related_date_to);
			$('#date-temp').datepicker().datepicker('disable');
			$('#datetime_from').datepicker().datepicker('disable');

			$('#record-form').append('<div id="with_related_id"><input readonly class="related_datefrom" type="hidden" name="datetime_from" id="datetime_from" value="'+ related_date_to +'" ></div>');
			
			$('.related_datefrom').datepicker().datepicker('disable');
			$(".related_datefrom").prop('disabled', false);

			$("#datetime_to").val('');
			$("#extended_ot").val(1);
		}else{		
			$("#extended_ot").val(0);
		}
	}


	if( module.get_value('view') == 'edit' || module.get_value('module_link') == "employee/dtr" ){
		$('input[name="datetime_from"],input[name="datetime_to"]').datetimepicker({
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
				if ($('#datetime_from').val() == "" || $('#datetime_to').val() == ""){
					return;
				}

				validate_date_from('datetime', 'Inclusive Date and Time', $('input[name="datetime_from"]'), $('input[name="datetime_to"]'));
			}
		}); 

		$('#employee_id').change(function(){
			get_inclusive_undertime_blanket(0);
			get_inclusive_emergency_blanket(0);
			get_inclusive_worksched(0);
		});

		$('#date-temp').change(function(){
			get_inclusive_undertime_blanket(0);
			get_inclusive_emergency_blanket(0);
			get_inclusive_worksched(0);
		});
	}
}


/**
 * [get_inclusive_worksched description]
 * @return {[type]} [description]
 */
function get_inclusive_worksched(init){
	if( $('#employee_id').val() != "" && $('#date').val() != ""  ){
		$.ajax({
            url: module.get_value('base_url') + module.get_value('module_link') + '/get_inclusive_worksched',
            type: 'post',
            dataType: 'json',
            data: 'employee_id=' + $('#employee_id').val() + '&date=' + $('#date').val(),
            success: function (response) {
           		shift_start = new Date(response.shifttime_start);
           		shift_end = new Date(response.shifttime_end);
           		preot_start = new Date(response.preot_start);
           		postot_end = new Date(response.postot_end);
            	if(response.shift_id == 0 || response.shift_id == 1 || response.considered_halfday > 0)
            		rest_day = true;
            	else
            		rest_day = false;

            	if (init == 0 && module.get_value('module_link') != "employee/dtr"){
	            	$('#datetime_from').val('');
	            	$('#datetime_to').val('');
				}

            	if(response.holiday == true){
            		holiday = true;
            	}
            	else{
            		holiday = false;
            	}
            }
        });
	}
	else{
		$('label[for="employee_oot_id"]').next().html('<span>No Work Schedule To Be Displayed</span>');
	}
}

function get_inclusive_undertime_blanket(init){
	if( $('#employee_id').val() != "" && $('#date').val() != ""  ){
		$.ajax({
            url: module.get_value('base_url') + module.get_value('module_link') + '/get_inclusive_undertime_blanket',
            type: 'post',
            dataType: 'json',
            data: 'employee_id=' + $('#employee_id').val() + '&date=' + $('#date').val(),
            success: function (response) {
           		employee_out_blanket = response.employee_out_blanket;
            }
        });
	}
}

function get_inclusive_emergency_blanket(init){
	if( $('#employee_id').val() != "" && $('#date').val() != ""  ){
		$.ajax({
            url: module.get_value('base_url') + module.get_value('module_link') + '/get_inclusive_emergency_blanket',
            type: 'post',
            dataType: 'json',
            data: 'employee_id=' + $('#employee_id').val() + '&date=' + $('#date').val(),
            success: function (response) {
           		employee_emergency_blanket = response.employee_emergency_blanket;
            }
        });
	}
}

var shift_start, shift_end;
var preot_start, postot_end;
var rest_day = false;
var holiday = false;
var employee_out_blanket = 0;
var employee_emergency_blanket = 0;

function check_ot_time(){
	var milot_start = Date.parse($('#datetime_from').val());
	var milot_end = Date.parse($('#datetime_to').val());
	var milshift_start = shift_start.getTime();//Date.parse(shift_sta = shift_end.getTime();//Date.parse(shift_end);
	var milshift_end = shift_end.getTime();
	var milpreot_start = preot_start.getTime(); //Date.parse(preot_start);
	var milpostot_end = postot_end.getTime();//Date.parse(postot_end);
	
	if ( (milot_end.getTime()-milot_start.getTime()) < 1800000 ) {
		add_error('', '', "You can not file overtime less than 30 minutes.");
		return false;
	}

	if(holiday == false && rest_day == false){
		if(employee_out_blanket == 0 && employee_emergency_blanket == 0){
			if( milot_start.getTime() >= milshift_start && milot_start.getTime() < milshift_end ){
				add_error('datetime_from', 'Date time from', "Date time from should be before or after your shift.");
				return false;
			}

			if( milot_end.getTime() > milshift_start && milot_end.getTime() <= milshift_end ){
				add_error('datetime_to', 'Date time to', "Date time to should be before or after your shift.");
				return false;
			}
		}



		if( milot_start.getTime() < milshift_start && milot_end.getTime() > milshift_end ){
			add_error('datetime_to', 'Date time from and to', "Your OT application should not engulf your shift.");
			return false;
		}

		if( milot_start.getTime() <= milshift_start || milot_end.getTime() <= milshift_start ){
			//preshift ot
			if(milot_start.getTime() < milpreot_start){
				add_error('datetime_from','Date time from', 'Filed overtime is beyond the allowable pre-shift OT.');
				return false;	
			}
		}

		if( milot_start.getTime() >= milshift_end || milot_end.getTime() >= milshift_end ){
			//postshift ot
			if(milot_end.getTime() > milpostot_end){
				add_error('datetime_to', 'Date time to','Filed overtime is beyond the allowable post-shift OT.');
				return false;	
			}
		}

	}

	return true;
}


function ajax_save( on_success, is_wizard , callback ){
	if( is_wizard == 1 ){
		var current = $('.current-wizard');
		var fg_id = current.attr('fg_id');
		var ok_to_save = eval('validate_fg'+fg_id+'()');
	}
	else{
		ok_to_save = validate_form();
	}
	
	if(ok_to_save){
		ok_to_save = check_ot_time();
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
				if( data.msg_type != "error" && data.record_id != null ){					
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
						default:
							if (typeof data.page_refresh != 'undefined' && data.page_refresh == "true"){
									window.location = module.get_value('base_url') + module.get_value('module_link') + '/edit/' + data.record_id;
							}
							else{
								//check if new record, update record_id
								if($('#record_id').val() == -1 && data.record_id != ""){
									$('#record_id').val(data.record_id);
									$('#record_id').trigger('change');
									if( is_wizard == 1 ) window.location = module.get_value('base_url') + module.get_value('module_link') + '/edit/' + data.record_id;
								}
								else{
									$('#record_id').val( data.record_id );
								}
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