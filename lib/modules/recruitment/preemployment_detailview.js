$(document).ready(function () {
	$('.trigger-edit').click(edit_checklist);
	$('.trigger-print').click(print_checklist);
	$('.trigger-delete').click(delete_checklist);
});

function print_checklist() {
	submit_to_submodule('print_record', this);
}

function edit_checklist() {
	submit_to_submodule('edit', this);
}

function delete_checklist() {
	var obj = $(this);
	var rel = obj.parents('li').attr('rel');
    
	Boxy.confirm("Delete record?", 
		function() { 
			if (rel == 'recruitment/preemployment/filter/for_201') {
				$.ajax({
					url : module.get_value('base_url') + rel + '/reset',
					data: 'applicant_id=' + $('input[name="applicant_id"]').val(),
					type: 'post',
					dataType: 'json',
					success: function(response) {
						if (response.msg_type == 'success') {
							obj.parent('span').siblings('div').children('span.completed').remove();
						}                                

						if( response.msg != "" ) $( '#message-container' ).html( message_growl( response.msg_type, response.msg ) );
					}
				});                  
			} else {
				$.ajax({
					url: module.get_value('base_url') + module.get_value('module_link') + '/fetch_form_id',
					type: 'post',
					data: $('form[name="record-form"]').serialize() + '&rel=' + rel,
					dataType: 'json',
					beforeSend: function(){
						$.blockUI({
							message: '<div class="now-loading align-center"><img src="'+module.get_value('base_url')+user.get_value('user_theme')+'/images/loading.gif"><br />Loading, please wait...</div>'
						});  		
					},
					success: function(data){
						$.ajax({
							url : module.get_value('base_url') + rel + '/delete',
							data: 'record_id=' + data.record_id,
							type: 'post',
							dataType: 'json',
							success: function(response) {
								if (response.msg_type == 'success') {
									obj.parent('span').siblings('div').children('span.completed').remove();
								}                                
                                
								if( response.msg != "" ) $( '#message-container' ).html( message_growl( response.msg_type, response.msg ) );
							}
						});                                                
					}
				});
			}                         
		}, 
		{
			title: 'Confirm Delete'
		}
		);     
}

function submit_to_submodule(action, obj) {
	var rel = $(obj).parents('li').attr('rel');
    var extra = $(obj).parents('li').attr('extra');
    var schedule_type = $(obj).parents('li').attr('scheduletype');
    var applicant_id = $(obj).parents('li').attr('applicantid');
    var employee_id = $(obj).parents('li').attr('employeeid');
    var candidate_id = $(obj).parents('li').attr('candidateid');

	if (rel == '#') {
		return false;
	} else if (rel == 'recruitment/preemployment/filter/for_201') {	
		if (action == 'print_record') {            
			window.location = module.get_value('base_url') + 'employees/print_record_applicant/' + $('input[name="applicant_id"]').val();            
		} else {        
			// var data = $('#record-form input[name="applicant_id"],input[name="employee_id"]').serialize();
			var data = $('#record-form input[name="applicant_id"],input[name="employee_id"]').serialize() + '&module_id='+ module.get_value('module_id');
			showQuickEditForm( module.get_value('base_url') + "employees/quick_edit", data );                    
		}

		return false;
	} else { 
		if (extra != 1){   
			// Get id of proper table using record_id.
			$.ajax({
				url: module.get_value('base_url') + module.get_value('module_link') + '/fetch_form_id',
				type: 'post',
				data: $('form[name="record-form"]').serialize() + '&rel=' + rel,
				dataType: 'json',
				beforeSend: function(){
					$.blockUI({
						message: '<div class="now-loading align-center"><img src="'+module.get_value('base_url')+user.get_value('user_theme')+'/images/loading.gif"><br />Loading, please wait...</div>'
					});  		
				},
				success: function(data){            
					$.unblockUI();
					if (data.type == 'error') {
						message_growl(data.type, data.message);
					} else {					
						$('body').append($('<form></form>').attr('name', 'dummy-form').attr('method', 'post'));

						url = module.get_value('base_url') + rel + '/' + action;

						$('form[name="dummy-form"]')
							.append($('<input type="hidden"></input>').attr('name', 'record_id').val(data.record_id))
							.attr('action', url).submit();                
					}				
				}
			});   
		} 
		else{
			$('body').append($('<form></form>').attr('name', 'dummy-form').attr('method', 'post'));

			url = module.get_value('base_url') + rel + '/' + action;

			$('form[name="dummy-form"]')
				.append($('<input type="hidden"></input>').attr('name', 'applicant_id').val(applicant_id))
				.append($('<input type="hidden"></input>').attr('name', 'employee_id').val(employee_id))
				.append($('<input type="hidden"></input>').attr('name', 'candidate_id').val(candidate_id))
				.append('<input type="hidden" name="from_preemployment" value="1"></input>')
				.append('<input type="hidden" name="schedule_type" value="'+schedule_type+'"></input>')
				.attr('action', url).submit();  			
		}    
	}
}