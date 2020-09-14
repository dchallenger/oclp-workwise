$(document).ready(function () {
	var action = module.get_value('view');

	var template = new Template(action);
	
	fn = template[action];
	
	if (typeof(fn) === typeof(Function)) {
		fn();
	}

	$('.icon-16-document-view').live('click', function(){
		record_action('submit',$(this).parent().parent().parent().attr("id"), $(this).attr('module_link'));
	});

});

function Template(action) 
{
	this.edit = function () {
		if ($('#record_id').val() == '-1') {

		} else {
			get_template_criterias();
		}		
	}	
}


/**
 * Show quick edit appraisal criteria form.
 * 
 * @param  int template_criteria_id
 * @param  int employee_appraisal_template_id
 * @return void
 */
function edit_appraisal_criteria( template_criteria_id, employee_appraisal_template_id ){
	var data = 'record_id='+template_criteria_id+'&employee_appraisal_template_id='+employee_appraisal_template_id;
	var module_url = module.get_value('base_url') + 'admin/appraisal_criteria/quick_edit';
	showQuickEditForm( module_url, data);
}

/**
 * Show quick edit appraisal criteria question form.
 * 
 * @param  int template_criteria_question_id
 * @param  int employee_appraisal_criteria_id
 * @return void
 */
function edit_appraisal_criteria_question( template_criteria_question_id, employee_appraisal_criteria_id ){
	var data = 'record_id='+template_criteria_question_id+'&employee_appraisal_criteria_id='+employee_appraisal_criteria_id;
	var module_url = module.get_value('base_url') + 'admin/appraisal_criteria_question/quick_edit';
	showQuickEditForm( module_url, data);
}

/**
 * Refreshes the criteria list when saving criteria
 * 
 * @param  object e 
 * @return void
 */
function quickedit_boxy_callback( e ) {
	get_template_criterias();
}

/**
 * [get_template_criterias description]
 * @return {[type]} [description]
 */
function get_template_criterias() {
	$.ajax({
		url: module.get_value('base_url') + module.get_value('module_link') + '/get_template_criterias',
		data: 'record_id=' + $('#record_id').val(),
		type: 'post',
		dataType: 'json',
		beforeSend: function(){
			$('#appraisal-template-criteria-container').block({ message: '<div class="now-loading align-center"><img src="'+module.get_value('base_url')+user.get_value('user_theme')+'/images/loading.gif"><br />Loading, please wait...</div>' });  		
		},		
		success: function (response) {
			$('#appraisal-template-criteria-container').unblock();
			if (response.msg_type == 'error') {
				$('#message-container').html(message_growl(response.msg_type, response.msg));
			} else {
				$('#appraisal-template-criteria-container').html(response.html);
			}			
		}
	});
}

function template_delete(record_id, action_url) {
	Boxy.ask("Delete?", ["Yes", "Cancel"],
	function( choice ) {
		if(choice == "Yes"){
			$.ajax({
				url: module.get_value('base_url') + action_url +"/delete",
				type:"POST",
				dataType: "json",
				data: 'record_id='+record_id,
				beforeSend: function(){					
					$.blockUI({
						message: '<div class="now-loading align-center"><img src="'+module.get_value('base_url')+user.get_value('user_theme')+'/images/loading.gif"><br />Deleting, please wait...</div>'
					});
				},
				success: function(data){					
					$('#message-container').html(message_growl(data.msg_type, data.msg));
					get_template_criterias();
				}
			});
		}
	},
	{
		title: "Delete Record"
	});
}

function record_action(action,record_id, action_link, related_field, related_field_val)
{
	update_gridsearch_parameters();
	$.blockUI({
		message: '<div class="now-loading align-center"><img src="'+module.get_value('base_url')+user.get_value('user_theme')+'/images/loading.gif"><br />Loading, please wait...</div>'
	});
	if(related_field != "")
	{
		$('#record-form').append('<input type="hidden" name="'+ related_field +'" value="'+ related_field_val +'" >');
	}
	$('#record_id').val(record_id);
	if(action_link == "") action_link = module.get_value('module_link');

	if (action == 'submit'){
		$('#record-form').attr("action", module.get_value('base_url') + action_link);
	}
	else{
		$('#record-form').attr("action", module.get_value('base_url') + action_link + "/" + action);		
	}
	
	//$('#record-form').submit();	
	
	switch( action ){
		case "detail":
			$('#record-form').submit();	
			break;
		case "edit/rehire":
		case "edit":
			if(user.get_value('edit_control') == 1){
				$('#form-div').html('');
				$('#record-form').submit();	
			}
			else{
				$.unblockUI();
				$('#message-container').html(message_growl('attention', 'Insufficient Access grant!\nPlease contact the system administrator.'))
			}
			break;
		case "duplicate_record":
			if(user.get_value('edit_control') == 1){
				$('#record-form').attr("action", module.get_value('base_url') + action_link + "/edit");
				$('#form-div').html('');
				$('#record-form').append('<input name="duplicate" value="1">');
				$('#record-form').submit();	
			}
			else{
				$.unblockUI();
				$('#message-container').html(message_growl('attention', 'Insufficient Access grant!\nPlease contact the system administrator.'))
			}
			break;
		default:
			$('#record-form').submit();
			break;
	}	
}