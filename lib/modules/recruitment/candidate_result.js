$(document).ready(function () {	
	$('.datetimepicker').datetimepicker(
		{                            
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
			yearRange: 'c-90:c+10'
        }
    );

	$("#applicant_id").attr('disabled', true).trigger("liszt:updated");
	$('#exam_percentile_total').attr('readonly',true);
	$('<input type="hidden" name="exam_score_total" id="exam_score_total" value="">').insertAfter('#record_id');
	cal_total();

	$('input').live('change',function(){
		var arrPercentile = new Array("exam_var_percentile","exam_fvas_percentile","exam_aispuvc_percentile","exam_majca_percentile");
		var arrScore = new Array("exam_var_raw_score","exam_fvas_raw_score","exam_aispuvc_raw_score","exam_majca_raw_score");				
		var percentileCount = arrPercentile.length;
		var scoreCount = arrScore.length;

		var total_percentile = 0;
		var total_percentage = 0;		
		var total_score = 0;				

		$.each( arrPercentile, function( key, value ) {
			total_percentile += parseFloat($('#'+ value +'').val());
		});

		if (total_percentile){
			total_percentage = total_percentile / percentileCount;
			total_percentage = total_percentage.toFixed(2);
			$('#exam_percentile_total').val(parseFloat(total_percentage).toFixed(2));
		}

		$.each( arrScore, function( key, value ) {
			total_score += parseFloat($('#'+ value +'').val());
		});

		$('#exam_score_total').val(total_score.toFixed(2));
	});

	if($('input[name="is_internal"]:checked').val() == 1){
		$('label[for=applicant_id]').parent().hide();
		$('label[for=employee_id]').parent().show();
	}
	else{
		$('label[for=applicant_id]').parent().show();
		$('label[for=employee_id]').parent().hide();
	}

	$('label[for=is_internal]').parent().hide();	

	if (module.get_value('view') == 'detail'){
		if($.trim($('label[for="is_internal"]').next().html()) == "Yes"){
			$('label[for=applicant_id]').parent().hide();
			$('label[for=employee_id]').parent().show();
		}
		else{
			$('label[for=applicant_id]').parent().show();
			$('label[for=employee_id]').parent().hide();
		}		
	}

	$('.icon-send').live('click',function(){
		var employee_id = $(this).closest('div.parent_container').find('#interviewer_id').val();
		var date_time = $(this).closest('div.parent_container').find('input[name="interview_date[]"]').val();

		if(! employee_id){
			add_error_mine('interviewer', 'Interviewer', "This field is mandatory.");
		}
		if(! date_time){
			add_error_mine('date_time', 'Date', "This field is mandatory.");
		}

		ok_to_save = validate_form_mine();

		if (ok_to_save){
			$.ajax({
		        url: module.get_value('base_url') + 'recruitment/candidate_result/send_email',
		        data: 'employee_id=' + employee_id + '&candidate_id=' + $('#record_id').val(),
		        type: 'post',
		        dataType: 'json',
	            beforeSend: function(){
	                $.blockUI({
	                    message: '<div class="now-loading align-center"><img src="'+module.get_value('base_url')+user.get_value('user_theme')+'/images/loading.gif"><br />Sending, please wait...</div>'
	                });
	            },	        
		        success: function(data) {
					$.unblockUI();
		        	message_growl(data.msg_type, data.msg);
		        }
			});		
		}		
	});	
});

var error_mine = new Array;

function add_error_mine(fieldname, fieldlabel, msg)
{
	error_mine[error_ctr] = new Array(fieldname, fieldlabel, msg);
	error_ctr++;
}

function validate_form_mine()
{
	//errors
	if(error_mine.length > 0){
		var error_str = "Please correct the following errors:<br/><br/>";
		for(var i in error_mine){
			if(i == 0) $('#'+error_mine[i][0]).focus(); //set focus on the first error
			error_str = error_str + (parseFloat(i)+1) +'. '+error_mine[i][1]+" - "+error_mine[i][2]+"<br/>";
		}
		$('#message-container').html(message_growl('error', error_str));
		
		//reset errors
		error_mine = new Array();
		error_ctr = 0
		return false;
	}
	
	//no error occurred
	return true;
}

function cal_total(){
	var arrScore = new Array("exam_var_raw_score","exam_fvas_raw_score","exam_aispuvc_raw_score","exam_majca_raw_score");				
	var scoreCount = arrScore.length;

	var total_score = 0;				

	$.each( arrScore, function( key, value ) {
		total_score += parseFloat($('#'+ value +'').val());
	});	

	$('#exam_score_total').val(total_score.toFixed(2));
}

function ajax_save( on_success, is_wizard , callback ){
	if( is_wizard == 1 ){
		var current = $('.current-wizard');
		var fg_id = current.attr('fg_id');
		var ok_to_save = eval('validate_fg'+fg_id+'()')
	}
	else{
		ok_to_save = validate_form();
	}
	
	if( ok_to_save ) {	
		$("#applicant_id").attr('disabled', false);	
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

function add_interviewer(){

	$.ajax({
		url: module.get_value('base_url') +  'recruitment/candidate_result/get_interviewer_form',
		data: '',
		dataType: 'html',
		type: 'post',
		async: false,
		beforeSend: function(){
		
		},								
		success: function ( response ) {
			
			var ctrval = parseFloat($('#ctr_handler').val());
			var cls = "datetimepicker"+ctrval+"";
			$('#interviewer-container').append(response).find('input').addClass(cls);
			$('#ctr_handler').val(ctrval + 1);

			$('.'+cls+'').datetimepicker(
				{                            
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
					yearRange: 'c-90:c+10'
		        }
		    );


		}
	});		
}

function delete_benefit( field ){
	Boxy.ask("Are you sure you want to delete interviewer?", ["Yes", "Cancel"],
	function( choice ) {
		if(choice == "Yes"){
			field.parent().parent().parent().remove();
		}
	},
	{
		title: "Delete Interviewer"
	});
}