$(document).ready(function () {	
	init_datepick();

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

	if (module.get_value('view') == 'edit'){
		$('.icon-16-edit').live('click',function(){
			var interviewer_type = $(this).attr('interviewer-type');		
		    var candidate_interviewer_id = $(this).closest('tr').attr('id');
		    $.ajax({
		            url: module.get_value('base_url') + 'recruitment/candidate_result/interview_form',
		            type: 'post',
		            dataType: 'html',
		            data: 'candidate_interviewer_id='+candidate_interviewer_id + '&candidate_id=' + module.get_value('record_id'),
		            beforeSend: function(){
		                $.blockUI({
		                    message: '<div class="now-loading align-center"><img src="'+module.get_value('base_url')+user.get_value('user_theme')+'/images/loading.gif"><br />Loading, please wait...</div>'
		                });         
		            },  
		            success: function(response) {
		                $.unblockUI();  
		                
	                    template_form = new Boxy('<div id="boxyhtml" style="">'+ response +'</div>',
	                    {
                            title: 'INTERVIEW INFORMATION',
                            draggable: false,
                            modal: true,
                            center: true,
                            unloadOnHide: true,
                            beforeUnload: function (){

                            },
                            afterShow: function (){

                            }
	                    });
	                    // boxyHeight(template_form, '#boxyhtml');         
		            }
		    });
		});

		$('.percentile').keydown( numeric_only ); 
		$('.salary').keyup( maskFloat);
		$('.add_row').click(function() {
			var type = $(this).attr('rel');
			var id = $(this).attr('type');
			add_exam_type(type, id);
			$('.percentile').keydown( numeric_only ); 
		});


		$('.delete_row').live('click',function(){
	        var elem = $(this);
	       $(elem).parent().parent().parent().parent().remove();
	        // $(elem).parents('.exam_type')
	    });


	}

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

	// $("#fg-425 ~ div > .col-2-form,div > .col-1-form").addClass('hidden');
	$(".form-head > a").html('Show');

	$('#candidate_result_id').live('change',function(){
		if ($(this).val() == 2){
			$('#candidate_status_id_container').show();
		}
		else{
			$('#candidate_status_id_container').hide();	
		}
	});

	$('#candidate_result_id').trigger('change');

if ( module.get_value('view') != "index" && parseInt(module.get_value('record_id')) >= 1) {
    var count_file = $('.count_attachment').val();

    for(i=1; i<=count_file; i++)
    {

        $('#attachment-photo'+ i).uploadify({
                'uploader'  : module.get_value('base_url') + 'lib/uploadify214/uploadify.swf',
                'script'    : module.get_value('base_url') + 'lib/uploadify214/uploadify3.php',
                'cancelImg' : module.get_value('base_url') + 'lib/uploadify214/cancel.png',
                'folder'    : 'uploads/' + module.get_value('module_link'),
                'fileExt'   : '*.jpg;*.gif;*.png;*.doc;*.docx;*.xls;*.xlsx;*.pdf;*.txt',
                'fileDesc'  : 'Web Image Files (.JPG, .GIF, .PNG) and Text Documents and Spreadsheets (.DOC, .DOCX, .XLS, .XLSX, .PDF)',
                'auto'      : true,
                'method'    : 'POST',
                'scriptData': {module: module.get_value('module_link'), fullpath: module.get_value('fullpath'), path: "uploads/" + module.get_value('module_link'),field:"attachment-photo",text_id:""+i+""},
                'onComplete': function(event, ID, fileObj, response, data)
                {       
                    var split_res = response.split('|||||');
                    $('#dir_path'+split_res[1]).val(split_res[0]);
                    if(split_res[2] == 'image')
                    {
                        var img = '<div class="nomargin image-wrap"><img id="file-photo-'+ split_res[1] +'" src="' + module.get_value('base_url') + split_res[0] +'" width="100px"><div class="delete-image nomargin multi" field="dir_path'+ split_res[1] +'" upload_id="'+ split_res[1] +'"></div></div>';                           
                    }
                    else
                    {
                        var img = '<div class="nomargin image-wrap"><a id="file-photo-'+ split_res[1] +'" href="' + module.get_value('base_url') + split_res[0] + '" width="100px" target="_blank"><img src="' + module.get_value('base_url') + 'themes/slategray/images/file-icon-md.png"></a><div class="delete-image nomargin multi" field="dir_path'+ split_res[1] +'" upload_id="'+ split_res[1] +'"></div></div>';                                   
                    }
                    $('#photo-upload-container_2'+split_res[1]).html('');                       
                    $('#photo-upload-container_2'+split_res[1]).append(img);
                },
                'onError': function (event,ID,fileObj,errorObj) {
                    $('#error-photo').html(errorObj.type + ' Error: ' + errorObj.info);
                },
                'onCancel': function ()
                {
                    var split_res = $(event.target).attr('rel');
                    $( '#dir_path'+split_res ).val('');
                    $(this).parent('#attachment-photo'+split_res+'Queue').remove();                   
                }
            });
    }
}

	$(".endorse").click(function() {
		Boxy.ask("Endorse this candidate For Background Check?", ["Yes", "Cancel"],
		function( choice ) {
			if(choice == "Yes"){
				$('#record-form').append($('<input type="hidden" name="candidate_status_id" value="4">'));
				ajax_save('back', 0);
			}
		},
		{
			title: "Endorse"
		});
	})
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

function delete_benefit( field, benefit_id ){
	Boxy.ask("Are you sure you want to delete benefit?", ["Yes", "Cancel"],
	function( choice ) {
		if(choice == "Yes"){
			var benefitval = field.parent().parent().find('input[name=benefit_id'+benefit_id+']');
			var benefitlabel = field.parent().parent().find('input[name=benefit_label'+benefit_id+']');
			field.parent().parent().parent().remove();
			console.log(benefitlabel.val());
			if (benefitlabel.val() != undefined) {
				var option = '<option value="'+benefitval.val()+'">'+benefitlabel.val()+'</option>';
				$('#benefitddlb').append(option);
			};
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

function add_benefit(){
	if( $('#benefitddlb').val() != "" ){
		var benefit_id  = $('#benefitddlb').val();
		var selected_benefits  = $('input[name=selected-benefits]').val();
		$.ajax({
			url: module.get_value('base_url') + 'recruitment/candidate_result/add_benefit_field',
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

				// if (quickedit_boxy != undefined) {
				// 	boxyHeight(quickedit_boxy, '#boxyhtml');
				// }				
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

function add_exam_type(type, id){
	if (type == "interview" ) {
		data = 'type='+type+'&counter_line='+(parseFloat($('.count_attachment').val())+1);
    	$('.count_attachment').val(parseFloat($('.count_attachment').val())+1);
	}else{
		data = 'type='+type
	};
	 // event.preventDefault();
	$.ajax({
		url: module.get_value('base_url') +  'recruitment/candidate_result/get_exam_form',
		data: data,
		dataType: 'html',
		type: 'post',
		async: false,
		beforeSend: function(){
		
		},								
		success: function ( response ) {
			$("#"+id).before(response);

			if (type == "interview" ) {
			 $('#attachment-photo'+ $('.count_attachment').val()).uploadify({
                    'uploader'  : module.get_value('base_url') + 'lib/uploadify214/uploadify.swf',
                    'script'    : module.get_value('base_url') + 'lib/uploadify214/uploadify3.php',
                    'cancelImg' : module.get_value('base_url') + 'lib/uploadify214/cancel.png',
                    'folder'    : 'uploads/' + module.get_value('module_link'),
                    'fileExt'   : '*.jpg;*.gif;*.png;*.doc;*.docx;*.xls;*.xlsx;*.pdf;*.txt',
                    'fileDesc'  : 'Web Image Files (.JPG, .GIF, .PNG) and Text Documents and Spreadsheets (.DOC, .DOCX, .XLS, .XLSX, .PDF)',
                    'auto'      : true,
                    'method'    : 'POST',
                    'scriptData': {module: module.get_value('module_link'), fullpath: module.get_value('fullpath'), path: "uploads/" + module.get_value('module_link'),field:"attachment-photo",text_id:""+$('.count_attachment').val()+""},
                    'onComplete': function(event, ID, fileObj, response, data)
                    {       
                        var split_res = response.split('|||||');
                        $('#dir_path'+split_res[1]).val(split_res[0]);
                        if(split_res[2] == 'image')
                        {
                            var img = '<div class="nomargin image-wrap"><img id="file-photo-'+ split_res[1] +'" src="' + module.get_value('base_url') + split_res[0] +'" width="100px"><div class="delete-image nomargin multi" field="dir_path'+ split_res[1] +'" upload_id="'+ split_res[1] +'"></div></div>';                           
                        }
                        else
                        {
                            var img = '<div class="nomargin image-wrap"><a id="file-photo-'+ split_res[1] +'" href="' + module.get_value('base_url') + split_res[0] + '" width="100px" target="_blank"><img src="' + module.get_value('base_url') + 'themes/slategray/images/file-icon-md.png"></a><div class="delete-image nomargin multi" field="dir_path'+ split_res[1] +'" upload_id="'+ split_res[1] +'"></div></div>';                                   
                        }
                        $('#photo-upload-container_2'+split_res[1]).html('');                       
                        $('#photo-upload-container_2'+split_res[1]).append(img);
                    },
                    'onError': function (event,ID,fileObj,errorObj) {
                        $('#error-photo').html(errorObj.type + ' Error: ' + errorObj.info);
                    },
                    'onCancel': function ()
                    {
                        var split_res = $(event.target).attr('rel');
                        $( '#dir_path'+split_res ).val('');
                        $(this).parent('#attachment-photo'+split_res+'Queue').remove();                   
                    }
                });
			}
		}
	});		
}

