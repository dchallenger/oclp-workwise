var b_obj, af_obj;

$(document).ready(function () {	
	//('chzn-disabled')

	$('.icon-16-info-applicant').live('click', function(){
		var candidate_id = $(this).parent().parent().parent().attr("id");
		action_link = $(this).attr('module_link');
		$.ajax({
			url: module.get_value('base_url') + 'recruitment/candidates/get_applicant_id',
			type: 'post',
			dataType: 'json',
			data: 'candidate_id=' + candidate_id,
			success: function (data) {
				if (data.candidate_id > 0){
					$('#record_id').val(data.candidate_id);
					$('#record-form').append('<input id="rec_from" type="hidden" name="rec_from" value="1">');
					$('#record-form').attr("action", module.get_value('base_url') + action_link + "/detail");
					$('#record-form').submit();	
				}
				else{
					return false;
				}
			}
		});		
	});

	window.onload = function(){

		$.ajax({
				url: module.get_value('base_url') + 'recruitment/candidates/check_management_trainee',
				type: 'post',
				dataType: 'json',
				data: 'mrf_id=' + $('#mrf_id').val(),
				success: function (response) {

					if( response.management_trainee == 0 ){
						$('#mt_priority_id').attr('disabled','disabled');
						$('label[for=mt_priority_id]').parent().hide();
					}

				}
			});

	}

	$('#mrf_id').live('change',function(){

			$.ajax({
				url: module.get_value('base_url') + 'recruitment/candidates/check_management_trainee',
				type: 'post',
				dataType: 'json',
				data: 'mrf_id=' + $('#mrf_id').val(),
				success: function (response) {

					if( response.management_trainee == 0 ){
						$('#mt_priority_id').attr('disabled','disabled');
						$('label[for=mt_priority_id]').parent().hide();
					}
					else{
						$('#mt_priority_id').removeAttr('disabled');
						$('label[for=mt_priority_id]').parent().show();
					}

				}
			});

		});


	if(module.get_value('view') == "edit"){

		//modified ajax save for manpower
		window.onload = function(){
			$('.icon-16-disk-back').attr("onclick","ajax_save_candidate('back',0,'',1)");
		
		}

		$('.icon-16-listback').live('click', function(){
			window.location = module.get_value('base_url') + module.get_value('module_link') + "/index/" + $('#mrf_id').val();
		});
		$('a[rel="action-back"]').live('click', function(){
			window.location = module.get_value('base_url') + module.get_value('module_link') + "/index/" + $('#mrf_id').val();
		});

		//$('#previous_page').val($('#previous_page').val() + "/index/" + $('input[name="mrf_id"]').val());
	}


	if(module.get_value('view') == "detail") {
		$('.cancel').live('click', function(){
			window.location = module.get_value('base_url') + module.get_value('module_link') + "/index/" + $.trim($('label[for="mrf_real_id"]').siblings('div').text());
		});
	}

	$('.icon-16-users').live('click', function(){

		window.location = module.get_value('base_url') + "recruitment/applicants/detail/" + $(this).attr('candidate_id') ;
	});

	$('input[name=is_internal]').live('change', function(){
		if($(this).val() == 1){
			$('label[for=applicant_id]').parent().hide();
			$('label[for=employee_id]').parent().show();
			$('#employee_id_chzn').css('width','93%');
			$('.chzn-drop').css('width','100%');
			$('#employee_id_chzn').find('.chzn-choices').find('.search-field').css('width','100%');
			$('#employee_id_chzn').find('.chzn-choices').find('.search-field').find('.default').css('width','100%');

			$('#applicant_id_chzn').find('.chzn-drop').find('.chzn-results').find('.result-selected').addClass('active-result').removeClass('result-selected');
			$('#applicant_id_chzn').find('.chzn-choices').find('li:not(.search-field)').remove();
			$('#applicant_id').val('').trigger('liszt:updated');
		}
		else{
			
			$('#employee_id_chzn').find('.chzn-drop').find('.chzn-results').find('.result-selected').addClass('active-result').removeClass('result-selected');
			$('#employee_id_chzn').find('.chzn-choices').find('li:not(.search-field)').remove();
			$('#employee_id').val('').trigger('liszt:updated');
			$('label[for=applicant_id]').parent().show();
			$('label[for=employee_id]').parent().hide();
		}
	});	

	if($('input[name="is_internal"]:checked').val() == 1){
		$('label[for=applicant_id]').parent().hide();
		$('label[for=employee_id]').parent().show();
	}
	else{
		$('label[for=applicant_id]').parent().show();
		$('label[for=employee_id]').parent().hide();

		$('#applicant_id').parents('form')
			.append(
				$('<input type="hidden" name="applicant_id" />')
					.val($('#applicant_id option:selected').val())
			);
		$('#applicant_id').attr('disabled', 'disabled');
	}

	$('#screener_total, #final_total').attr('disabled', 'disabled');
	setTimeout(function () {
		$('#position_id').attr('disabled','disabled').trigger("liszt:updated");
		$('#candidates-quick-edit-form h3').not(':first').remove();
		$('#interview_date').datetimepicker(
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
                        timeFormat: 'hh:mm tt',
                        minuteGrid: 10,
                        ampm: true,
                        yearRange: 'c-90:c+10' 
                    }
            );
	},
	100
	);

	$('#candidate_status_id').change(function () {
		if ($(this).val() == 3 || $(this).val() == 5 || $(this).val() == 12) {
			$('#screener_id').siblings('span.icon-group').addClass('hidden');
			$('#interview_date').attr('disabled','disabled');
		} else {
			$('#screener_id').siblings('span.icon-group').removeClass('hidden');
			$('#interview_date').removeAttr('disabled');
		}

		if ($(this).val() == 5 || $(this).val() == 12) {
			$('#final_interviewer_id').attr('disabled', 'disabled').trigger("liszt:updated");
			$('#final_interview_date').attr('disabled','disabled');			
		} else {
			$('#final_interviewer_id').removeAttr('disabled').trigger("liszt:updated");
			$('#final_interview_date').removeAttr('disabled');
		}
	});

	$('#candidate_status_id').trigger('change');	

	if ($('#record_id').val() > 0) {
		$('#applicant_id').siblings('span.icon-group').remove();
		$('#employee_id').siblings('span.icon-group').remove();
		/*$('#mrf_id').siblings('span.icon-group').addClass('hidden');*/
		$('input[name="is_internal"]').attr('disabled', 'disabled');
	}

	if(module.get_value('view') == "edit") {
		var allowed;
		status_id = $('select#candidate_status_id').val();
		if (status_id == 1){
			allowed = new Array('', '1', '2', '7', '11');
		} else if (status_id == 2 || status_id == 3) {
			allowed = new Array('', '1', '2', '7', '9', '3', '5');
		} else if (status_id == 5) {
			allowed = new Array('', '5', '3', '2', '1');
		} else if (status_id == 12) {
			allowed = new Array('', '12', '5', '3', '2', '1');
		} else if (status_id == 13) {
			allowed = new Array('', '13', '12', '5', '1', '11');
		} else if (status_id == 8) {
			allowed = new Array('', '8', '12', '5', '2', '3', '1', '11');
		} else if (status_id == 9) {
			allowed = new Array('', '9', '2', '3', '1', '7');
		} else if (status_id == 15) {
			allowed = new Array('', '15', '5', '2', '3', '1');
		} else if (status_id == 16) {
			allowed = new Array('', '16', '12', '5', '2', '3', '1');
		} else {
			allowed = new Array('', '1', '2', status_id);
		}
 
		$('select#candidate_status_id option').each(function (index, elem) {

			if ($.inArray($(elem).val(), allowed) == -1) {
				$(elem).remove();
			}
		});

		setTimeout(function () {
			$.ajax({
				url: module.get_value('base_url') + module.get_value('module_link') + '/get_final_interviewer',
				data: 'record_id=' + $('#record_id').val(),
				type: 'post',	
				dataType: 'json',			
				success: function (response) {
					if (response.msg_type == 'success') {					
						$('#final_interviewer_id, #final_interviewer_id_chzn').remove();
						$('label[for="final_interviewer_id"]').next().append(response.html);
						$('select[name="final_interviewer_id"]').chosen();

						if($('select#candidate_status_id').val() <= 3){
							$('input#final_interview_date').parent().parent().parent().parent().find('h3 a').trigger('click');
							$('select#candidate_status_id').parent().parent().parent().parent().find('h3 a').trigger('click');
							$('select#job_offer_status_id').parent().parent().parent().parent().find('h3 a').trigger('click');
							$('input#basic').parent().parent().parent().parent().find('h3 a').trigger('click');
						}
						
						if($('select#candidate_status_id').val() == 3){
							$('input#final_interview_date').parent().parent().parent().parent().find('h3 a').trigger('click');
						}
					} else {
						$('#message-container').html(message_growl(response.msg_type, response.msg));
					}
				}
			});
		}, 100);
		
		b_obj = new afb_object($('input[name="blacklisted"]'));
		af_obj = new afb_object($('input[name="active_file"]'));		

		$('select[name="mrf_position_id"]').change(function (e) {				
			$('#position_id').val($('#mrf_position_id').val());
			$('#position_id').trigger("liszt:updated");
		});

		$('select[name="mrf_position_id"]').trigger('change');
	}

	/*--------------------------------------------------*/
	/** Detail View **/

	if (module.get_value('view') == 'detail') {
		$('label[for="initial-interview"]').next().html('<div class="icon-label"><a class="icon-16-document-view" id="initial-results" href="javascript:void(0)"><span>Results</span></a></div>');
		$('label[for="final-interview"]').next().html('<div class="icon-label"><a class="icon-16-document-view" id="final-results" href="javascript:void(0)"><span>Results</span></a></div>');

		$('#initial-results').live('click', function() {
			get_assessment_detailview_boxy('initial');
		});

		$('#final-results').live('click', function() {
			get_assessment_detailview_boxy('final');
		});		
	}	
});

function get_assessment_detailview_boxy(type)
{
	var title;
	if (type == 'initial') {
		title = 'Initial Inverview Results';
	} else {
		title = 'Final Inverview Results';
	}

	$.ajax({
		url: module.get_value('base_url') + 'recruitment/candidates_appraisal/get_appraisal_detail',
		type: 'post',
		dataType: 'html',
		data: 'record_id=' + module.get_value('record_id') + '&type=' + type,
		success: function (data) {
			var width = $(window).width()*.7;
			quickedit_boxy = new Boxy('<div id="boxyhtml" style="width:'+width+'px">'+ data +'</div>',
			{
				title: title,
				draggable: false,
				modal: true,
				center: true,
				unloadOnHide: true,
				beforeUnload: function (){
					$('.tipsy').remove();
				}
			});
			boxyHeight(quickedit_boxy, '#boxyhtml');
		}
	});
}

function afb_object(obj) {
	this.obj = obj;
	if (obj.attr('name') == 'blacklisted') {
		this.child = new b_object(this.obj);
	} else {
		this.child = new af_object(this.obj);
	}		

	this.toggleOn = function () {this.child.toggleOn()};
	this.toggleOff = function () {this.child.toggleOff()};	
	this.toggle = function() {
		if (this.obj.attr('checked') == 'checked') {			
			this.child.toggleOn();
		} else {
			this.child.toggleOff();
		}				
	};

	this.register_observers();
	this.toggle();
}

afb_object.prototype.register_observers = function() {
	var _this = this;
	this.obj.click(function (evt) {
		return _this.toggle();
	});	
};

function b_object(obj) {
	return {
		select: $('#recruitment_candidate_blacklist_status'),
		toggleOn: function () {
			this.select.removeAttr('disabled');
			af_obj.obj.removeAttr('checked');
			af_obj.toggle();			
		},
		toggleOff: function () {
			this.select.attr('disabled', 'disabled');	
		}
	};	
}

function af_object(obj) {
	return {
		select : $('#af_position_id'),
		toggleOn: function () {
			this.select.removeAttr('disabled').trigger("liszt:updated");
			b_obj.obj.removeAttr('checked');
			b_obj.toggle();
		},
		toggleOff: function () {
			this.select.attr('disabled', 'disabled').trigger("liszt:updated");
		}
	};
}

function date_from_close(dateText) {
 	var date1 = new Date(dateText);
 	var month;
 	// "+7" = + 6 months
 	if( (date1.getMonth() + 7) > 12 ){
		date1.setMonth( (date1.getMonth()+7)-12);
		date1.setFullYear( date1.getFullYear() + 1);
		month = date1.getMonth();
	} else if (date1.getMonth() == 5) {
		month = 12;
 	} else {
 		date1.setMonth(date1.getMonth() + 7);
 		month = date1.getMonth();
 	}

 	// Timeout is needed calendar does not update when two instances are open.
 	setTimeout(function () {
 		$('#date-temp-to').datepicker('setDate', month + '/' + date1.getDate() + '/' + date1.getFullYear()); 	
 	}, 100);
}

function set_candidate_status(status_id, candidate_id){
	Boxy.ask('Are you sure you want to change the status of the selected candidate?', ["Proceed", "Cancel"],
	function( choice ) {
		if(choice == "Proceed"){
			$.ajax({
				url: module.get_value('base_url') + module.get_value('module_link') + '/set_candidate_status',
				data: 'candidate_status_id=' + status_id + '&candidate_id='+candidate_id,
				type: 'post',
				dataType: 'json',
				success: function (data) {
					if(data.msg != "")
						$('#message-container').html(message_growl(data.msg_type, data.msg));
					else
						page_refresh();
				}
			});
		}
	},
	{
		title: "Attention"
	});
}

function show_applicant_detail() {
	$.ajax({
		url: module.get_value('base_url') + module.get_value('module_link') + '/applicant_detail_redirect',
		data: 'record_id=' + $(this).parents('tr').attr('id'),
		type: 'post',
		dataType: 'json',
		success: function (data) {
			$('#record-form').attr('action', module.get_value('base_url') + 'recruitment/applicants/detail');
			$('#record_id').val(data.id);
			$('#record-form').submit();
		}
	});
}

var interview_boxy, previous;

function joboffer_quick_add()
{
	module_url = module.get_value('base_url') + $(this).attr('module_link') + '/quick_edit';

	$.ajax({
		url: module_url,
		type:"POST",
		data: 'record_id=' + $(this).attr('joboffer_id') + '&candidate_id=' + $(this).parents('tr').attr('id') + '&position_id=' + $(this).attr('position_id'),
		dataType: "json",
		beforeSend: function(){
			$.blockUI({
				message: '<div class="now-loading align-center"><img src="'+module.get_value('base_url')+user.get_value('user_theme')+'/images/loading.gif"><br />Loading, please wait...</div>'
			});
		},
		success: function(data){
			$.unblockUI();
			if(data.msg != "") $('#message-container').html(message_growl(data.msg_type, data.msg));
			if(data.quickedit_form != ""){
				var width = $(window).width()*.7;
				quickedit_boxy = new Boxy('<div id="boxyhtml" style="width:'+width+'px">'+ data.quickedit_form +'</div>',
				{
					title: 'Quick Add/Edit',
					draggable: false,
					modal: true,
					center: true,
					unloadOnHide: true,
					beforeUnload: function (){
						$('.tipsy').remove();
					}
				});
				boxyHeight(quickedit_boxy, '#boxyhtml');

	            if (typeof(init_quickedit_datepick) == typeof(Function)) {
	                init_quickedit_datepick();
	            }
	            if (typeof(BindLoadEvents) == typeof(Function)) {
	                BindLoadEvents();
	            }
			}
		}
	});
}

function show_candidate_quick_add(event)
{
	module_url = module.get_value('base_url') + module.get_value('module_link') + '/quick_edit';

	if (event.type == 'click') {
		record_id = '-1';
	} else {
		record_id = $(this).attr('id');
	}

	$.ajax({
		url: module_url,
		type:"POST",
		data: 'record_id=' + record_id + '&mrf_id=' + $('input[name="mrf_id"]').val(),
		dataType: "json",
		beforeSend: function(){
			$.blockUI({
				message: '<div class="now-loading align-center"><img src="'+module.get_value('base_url')+user.get_value('user_theme')+'/images/loading.gif"><br />Loading, please wait...</div>'
			});  		
		},
		success: function(data){
			$.unblockUI();
			if(data.msg != "") $('#message-container').html(message_growl(data.msg_type, data.msg));
			if(data.quickedit_form != ""){
				var width = $(window).width()*.7;
				quickedit_boxy = new Boxy('<div id="boxyhtml" style="width:'+width+'px">'+ data.quickedit_form +'</div>',
				{
					title: 'Quick Add/Edit',
					draggable: false,
					modal: true,
					center: true,
					unloadOnHide: true,
					beforeUnload: function (){
						$('.tipsy').remove();
					},
					afterShow: function () {
						$('#candidates-quick-edit-form #applicant_id')
							.siblings('.icon-group').children().first('a')
							.attr('onclick', '').click(function () {showCandidateAdd('304','applicant_id')});
					}
				});
				boxyHeight(quickedit_boxy, '#boxyhtml');	
				if (typeof(BindLoadEvents) == typeof(Function)) {
	                BindLoadEvents();
	            } 	
			}


				$.ajax({
						url: module.get_value('base_url') + 'recruitment/candidates/check_management_trainee',
						type: 'post',
						dataType: 'json',
						data: 'mrf_id=' + $('#mrf_id').val(),
						success: function (response) {

							if( response.management_trainee == 0 ){
								$('#mt_priority_id').attr('disabled','disabled');
								$('label[for=mt_priority_id]').parent().hide();
							}

						}
					});


		}
	});
	setTimeout(function () {
		$('#mrf_id_chzn').addClass('chzn-disabled');
			// $('a[rel="record-save-candidates"]').live('click', function(){
			// 	window.location
			// });
	},
	900
	);
}

function showCandidateAdd(field_id, fieldname){
	$.ajax({
		url: module.get_value('base_url') + module.get_value('module_link') + "/get_related_module",
		type:"POST",
		data: "field_id="+field_id+"&fieldname="+fieldname,
		dataType: "json",
		beforeSend: function(){
			$.blockUI({ message: '<div class="now-loading align-center"><img src="'+module.get_value('base_url')+user.get_value('user_theme')+'/images/loading.gif"><br />Loading, please wait...</div>' });  		
		},
		success: function(data){
			if(data.msg != ""){
				$.unblockUI();
				$('#message-container').html(message_growl(data.msg_type, data.msg));
			}
			if(data.link != "") showApplicantFilterAdd(data.link, data.short_name, fieldname, data.column);
		}
	}); 
}

function showApplicantFilterAdd(related_module_link, related_module, fieldname, column)
{
	$.ajax({
		url: module.get_value('base_url') + related_module_link + "/show_related_module",
		type:"POST",
		data: "fieldname="+fieldname+"&column="+column+'&module_link='+related_module_link+"&fmlinkctr="+related_module_boxy_count+'&candidate=true&mrf_id='+$('input[name="mrf_id"]').val(),
		dataType: "html",
		beforeSend: function(){
		},
		success: function(data){
			related_module_boxy[related_module_boxy_count] = new Boxy('<div id="related_module_boxy-'+related_module_boxy_count+'-container">'+ data +'</div>',
			{
				title: related_module,
				draggable: false,
				modal: true,
				center: true,
				unloadOnHide: true,
				show: false,
				afterShow: function(){ $.unblockUI(); },
				beforeUnload: function(){ $('.tipsy').remove(); }
			});
			boxyHeight(related_module_boxy[related_module_boxy_count], '#related_module_boxy-'+related_module_boxy_count+'-container');
			related_module_boxy_count++;
		}
	});
}



function icon_show_interview_schedule_form() {
	obj = new Object();
	obj.id = $(this).parents('tr').attr('id');
	obj.text = $(this).parents('tr').children('td[aria-describedby="jqgridcontainer_t0firstnamelastname"]').text();    
    
	show_interview_schedule_form(obj, 0);    
}

function tr_show_interview_schedule_form() {
	obj = new Object();
	obj.id = $(this).attr('id');
	obj.text = $(this).children('td[aria-describedby="jqgridcontainer_t0firstnamelastname"]').text();    
    
	show_interview_schedule_form(obj, 0);
}

function show_reschedule_form() {
	obj = new Object();
	obj.id = $(this).parents('tr').attr('id');
	obj.text = $(this).parents('tr').children('td[aria-describedby="jqgridcontainer_t0firstnamelastname"]').text();
    
	show_interview_schedule_form(obj, 1);
}

function show_interview_schedule_form(obj, for_reschedule) {
	$.ajax({
		url: module.get_value('base_url') + 'recruitment/candidates_schedule/quick_edit',
		type:"POST",
		data: 'record_id=-1&candidate_id=' + obj.id + '&for_reschedule=' + for_reschedule,
		dataType: "json",
		beforeSend: function(){
			$.blockUI({
				message: '<div class="now-loading align-center"><img src="'+module.get_value('base_url')+user.get_value('user_theme')+'/images/loading.gif"><br />Loading, please wait...</div>'
			});  		
		},
		success: function(data){
			$.unblockUI();
			if(data.msg != "") $('#message-container').html(message_growl(data.msg_type, data.msg));
			if(data.msg_type != 'attention' && data.quickedit_form != ""){
				var width = $(window).width()*.4;
				quickedit_boxy = new Boxy('<div id="boxyhtml" style="width:'+width+'px">'+ data.quickedit_form +'</div>',
				{
					title: 'Set Interview Schedule for ' + obj.text,
					draggable: false,
					modal: true,
					center: true,
					unloadOnHide: true,
					afterShow: function(){
						$('input[name="interview_datetime"]').datetimepicker(
						{
							changeMonth: true,
							changeYear: true,
							showOtherMonths: true,
							showButtonPanel: true,
							showAnim: 'slideDown',
							selectOtherMonths: true,
							showOn: "button",
							buttonImage: module.get_value('base_url') + user.get_value('user_theme') + "/icons/calendar-month.png",
							buttonImageOnly: true,  
							buttonText: '',                    
							hourGrid: 4,
							minuteGrid: 10,
							ampm: true
						}
						);

						$('input[name="candidate_id"]').val(obj.id);
                                            
						$.unblockUI();
					},                                        
					beforeUnload: function (){
						$('.tipsy').remove();
					}
				});
				boxyHeight(quickedit_boxy, '#boxyhtml');
				if (typeof(BindLoadEvents) == typeof(Function)) {
	                BindLoadEvents();
	            }
			}
		}
	});    
}

function shortlist(applicant_id, fmlinkctr) {

	$.ajax({
		url: module.get_value('base_url') + 'recruitment/candidates/ajax_save',
		type: 'post',
		data: 'applicant_id=' + applicant_id + '&record_id=-1&mrf_id=' + $('input[name="mrf_id"]').val(),
		beforeSend: function(){
			$.blockUI({
				message: '<div class="now-loading align-center"><img src="'+module.get_value('base_url') + user.get_value('user_theme') + '/images/loading.gif"><br />Saving, please wait...</div>'
			});
		},        
		success: function () {
			related_module_boxy[fmlinkctr].hide().unload();
			page_refresh();
			$.unblockUI();
		}
	});
}

function show_appraisal_form(action, record_id, action_link, candidate_id)
{	
	$.blockUI({
		message: '<div class="now-loading align-center"><img src="'+module.get_value('base_url')+user.get_value('user_theme')+'/images/loading.gif"><br />Loading, please wait...</div>'
	});

	//$('#record_id').val(record_id);
	if(action_link == "") action_link = module.get_value('module_link');
	$('#record-form').attr("action", module.get_value('base_url') + action_link + "/" + action);
    
	$('#record-form').append('<input type="hidden" name="candidate_id" value="' + candidate_id + '"/>');
	if(action == "detail")
	{
		$('#record-form').submit();	
	}
	else if(action == "edit"){		
		$('#form-div').html('');
		$('#record-form').submit();			
	}	
}

if (typeof(add_error) != typeof(Function)) {
	var error, error_ctr;

	function add_error(fieldname, fieldlabel, msg)
	{
		error[error_ctr] = new Array(fieldname, fieldlabel, msg);
		error_ctr++;
	}

	function validate_mandatory(fieldname, fieldlabel)
	{		
		if($('input[name="'+fieldname+'"]').attr('type') == "checkbox"){
			var checked = 0;
			$('input[name="'+fieldname+'"]').each(function(){
				if($(this).attr('checked')) checked++;
			});
		
			if(checked == 0){
				add_error(fieldname, fieldlabel, "This field is mandatory, select at least 1.");
				return false;
			}
		}
		else{
			if($('input[name="'+fieldname+'"]').val() == "" || 
				$('select[name="'+fieldname+'"]').val() == "" || 
				$('textarea[name="'+fieldname+'"]').val() == "" ||
				$('input[name="'+fieldname+'"]:checked').val() == ""){
				if( fieldname == "password" && $('.password-field-div').length > 0 ){
					if( $('.password-field-div').css('display') != "none"){
						add_error(fieldname, fieldlabel, "This field is mandatory.");
						return false;
					}
				}else{
					add_error(fieldname, fieldlabel, "This field is mandatory.");
					return false;
				}
			}
		}
		return true;
	}
    
}

function quickedit_boxy_callback(module) {                 
	page_refresh();
}

function ajax_save_appraisal( on_success){
	ok_to_save = true;
    
	if( ok_to_save ){
		var data = $('form[name="appraisal-form"]').serialize();
		var saveUrl = $('form[name="appraisal-form"]').attr('action');
				
		$.ajax({
			url: saveUrl,
			type:"POST",
			data: data,
			dataType: "json",
			async: false,
			beforeSend: function(){
				if( $('.now-loading').length == 0) $.blockUI({
					message: '<div class="now-loading align-center"><img src="'+module.get_value('base_url')+user.get_value('user_theme')+'/images/loading.gif"><br />Saving, please wait...</div>'
				});
			},
			success: function(data){
				if(on_success == "back") {
					go_to_previous_page( data.msg );
				} else if (on_success == "email") {
					// Ajax request to send email.
					$.ajax({
						url: module.get_value('base_url') + module.get_value('module_link') + '/send_email',
						data: 'record_id=' + data.record_id,
						type: 'post',
						success: function () {
							if( is_wizard == 1 ) window.location = module.get_value('base_url') + module.get_value('module_link') + '/edit/' + data.record_id;
							$.unblockUI({
								onUnblock: function() {
									message_growl(data.msg_type, data.msg)
								}
							});
						}
					});                                    
				} else{
					//check if new record, update record_id
					if($('#record_id').val() == -1 && data.record_id != ""){
						$('#record_id').val(data.record_id);
						$('#record_id').trigger('change');
						if( is_wizard == 1 ) window.location = module.get_value('base_url') + module.get_value('module_link') + '/edit/' + data.record_id;
					}
					$.unblockUI({
						onUnblock: function() {
							message_growl(data.msg_type, data.msg)
						}
					});
				}
			}
		}); 
	}
	else{
		return false;
	}
	return true;
}

function clearField( name )
{
	$('input[name="'+name+'"]').val('');
	$('input[name="'+name+'-name"]').val('');
	$('input[name="'+name+'"]').trigger('change');
}

function ajax_save( on_success, is_wizard , callback ){
	validate_custom();
	if( is_wizard == 1 ){
		var current = $('.current-wizard');
		var fg_id = current.attr('fg_id');
		var ok_to_save = eval('validate_fg'+fg_id+'()')
	}
	else{
		ok_to_save = validate_form();
	}

	if( ok_to_save ) {		
/*		strength.updateElement(); 					
		areas_improvement.updateElement(); 					
		job_fit.updateElement(); */			
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

function validate_custom(){
	var arr = ["exam_var_raw_score", "exam_var_percentile"];
	var obj = {exam_var_raw_score:"Vocabulary & Arithmetic Reasoning Exam",
			   exam_var_percentile:"Vocabulary & Arithmetic Reasoning Percentile",
			   exam_fvas_raw_score:"Flexibility, Vocabulary and Arithmetic skills Exam",
			   exam_fvas_percentile:"Flexibility, Vocabulary and Arithmetic skills Percentile",
			   exam_aispuvc_raw_score:"Analysis of Information Exam",
			   exam_aispuvc_percentile:"Analysis of Information Percentile",
			   exam_majca_raw_score:"Mental alertness Exam",
			   exam_majca_percentile:"Mental alertness Percentile",
			   recommendation:"Recommendation"
			  }; 
	$.each(obj, function(key, value) {
	    if( $('#'+key+'').val() == "" ){
	        add_error(key, value, "This field is mandatory.");
	    }
	});	
}


function ajax_save_candidate( on_success, is_wizard , callback , custom ){

	if( is_wizard == 1 ){
		var current = $('.current-wizard');
		var fg_id = current.attr('fg_id');
		var ok_to_save = eval('validate_fg'+fg_id+'()')
	}
	else{
		ok_to_save = validate_form();
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
							
							if( custom == 1 ){
								window.location = module.get_value('base_url') + module.get_value('module_link') + "/index/" + $('#mrf_id').val();
							}
							else{
								go_to_previous_page( data.msg );
							}
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