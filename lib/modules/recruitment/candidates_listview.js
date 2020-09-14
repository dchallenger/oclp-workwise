$(document).ready(function () {
	// Unbind the default method for clicking on a listview row.    
	//$('.jqgrow').die('dblclick');
	//$('.jqgrow').live('dblclick', show_candidate_quick_add);
    
	$('.icon-16-add-listview').die('click');
    $('.icon-16-calendar-add').live('click', icon_show_interview_schedule_form);

	$('.icon-16-add-listview').live('click', show_candidate_quick_add)
    $('.button-joboffer').live('click', joboffer_quick_add)

	$('.show-appraisal').live('click', function () {
		show_appraisal_form("edit", $(this).parent().parent().parent().attr("id"), 'recruitment/candidates_appraisal', $(this).attr('candidate_id'));
	});
    
	$('a.icon-16-user-add').live('click', function() {
		$.ajax({
			url: module.get_value('base_url') + module.get_value('module_link') + '/hire_candidate',
			data: 'record_id=' + $(this).parents('tr').attr('id'),
			dataType: 'json',
			type: 'post',
			success: function (data) {
				page_refresh();
				$('#message-container').html(message_growl(data.msg_type, data.msg));
			}
		});
	});
	
	$('a.icon-16-user-remove').live('click', function() {
		$.ajax({
			url: module.get_value('base_url') + module.get_value('module_link') + '/reject_candidate',
			data: 'record_id=' + $(this).parents('tr').attr('id'),
			dataType: 'json',
			type: 'post',
			success: function (data) {
				page_refresh();
				$('#message-container').html(message_growl(data.msg_type, data.msg));
			}
		});
	});	

	$('a.icon-16-calendar-month').live('click', show_reschedule_form);

	$('.reschedule_date').live('hover', function () {
		$(this).css('cursor', 'pointer');
	});

	$('.reschedule_date').live('click', function () {
		var obj = $(this);
		Boxy.ask('Accept new schedule?', 
			["Yes", "No", "Cancel"],
			function(val) { 				
				if (val == 'Yes') {
					$.ajax({
						url: module.get_value('base_url') + module.get_value('module_link') + '/reschedule',
						data: 'record_id=' + obj.parents('tr').attr('id') + '&accept=1',
						type: 'post',
						dataType: 'json',
						success: function (data) {
							$('#message-container').html(message_growl(data.msg_type, data.msg));
							page_refresh();
						}
					});
				} else if (val == 'No') {
					$.ajax({
						url: module.get_value('base_url') + module.get_value('module_link') + '/reschedule',
						data: 'record_id=' + obj.parents('tr').attr('id') + '&accept=0',
						type: 'post',
						dataType: 'json',
						success: function (data) {
							$('#message-container').html(message_growl(data.msg_type, data.msg));
							page_refresh();
						}
					});					
				}
			}, 
			{title: ''}
		);
		
		return false;
	});

	$('.icon-16-approve').live('click', function () {
		var obj = $(this);

		Boxy.ask(obj.attr('tooltip') + '?', ["Yes", "Cancel"],
			function( choice ) {
				if(choice == "Yes"){
					$.ajax({
						url: module.get_value('base_url') + 'recruitment/candidate_joboffer/change_status',
						data: 'status=accept&record_id=' + obj.attr('joboffer_id'),
						dataType: 'json',
						type: 'post',
						beforeSend: function(){
							$.blockUI({
								message: '<div class="now-loading align-center"><img src="'+module.get_value('base_url')+user.get_value('user_theme')+'/images/loading.gif"><br />Loading, please wait...</div>'
							});  		
						},			
						success: function (response) {
							page_refresh();
							$.unblockUI({
										onUnblock: function() {
											message_growl(response.msg_type, response.msg)
										}
									});								
													
						}
					});
				}
			},
			{
				title: obj.attr('tooltip')
			}
		);
	});

	$('.icon-16-disapprove').live('click', function () {
		var obj = $(this);
		var width = $(window).width()*.3;

		Boxy.confirm(
			'<div id="boxyhtml" style="width:'+width+'px">'
			+ obj.attr('tooltip') + '?'
			+ '<div>Remarks</div><textarea style="height:100px;width:340px;" id="remarks" name="remarks"></textarea></div>',
			function() {				
				$.ajax({
					url: module.get_value('base_url') + 'recruitment/candidate_joboffer/change_status',
					data: 'status=reject&record_id=' + obj.attr('joboffer_id') + '&remark=' + $('#remarks').val(),
					dataType: 'json',
					type: 'post',
					beforeSend: function(){
						$.blockUI({
							message: '<div class="now-loading align-center"><img src="'+module.get_value('base_url')+user.get_value('user_theme')+'/images/loading.gif"><br />Loading, please wait...</div>'
						});  		
					},			
					success: function (response) {				
						$.unblockUI({
							onUnblock: function() {
								message_growl(response.msg_type, response.msg)
							}
						});				
						page_refresh();
					}
				});
			},
			{
				title: obj.attr('tooltip')
			}
		);

	});

	$('.icon-16-document-stack').live('click', function(){
		var record_id = $(this).attr('joboffer_id');
		var candidate_status = $(this).attr('candidate_status');

		$.ajax({
			url: module.get_value('base_url') + 'recruitment/candidate_joboffer/get_template_form',
			data: 'record_id=' + record_id + '&candidate_status=' + candidate_status,
			dataType: 'json',
			type: 'post',
			beforeSend: function(){
				$.blockUI({
					message: '<div class="now-loading align-center"><img src="'+module.get_value('base_url')+user.get_value('user_theme')+'/images/loading.gif"><br />Loading, please wait...</div>'
				});  		
			},			
			success: function ( data ) {				
				$.unblockUI();	
				var viewport_width 	= $(window).width();
				var width 			= .30 * viewport_width;
				if(!template_form){
					template_form = new Boxy('<div id="boxyhtml" style="width:'+width+'px;">'+ data.form +'</div>',
					{
						title: 'Print Manager',
						draggable: false,
						modal: true,
						center: true,
						unloadOnHide: true,
						beforeUnload: function (){
							template_form = false;
						}
					});
					boxyHeight(template_form, '#boxyhtml');			
				}
			}
		});	
	});	
});

var template_form = false;

function print_job_offer(){
	var job_offer_id =  $('form[name="print-jocontract"] input[name="jocontract-job_offer_id"]').val();
	var jo_template_id =  $('form[name="print-jocontract"] select[name="jo_template_id"]').val();
	if( jo_template_id != "" ){
		var url = module.get_value('base_url') + 'recruitment/candidate_joboffer/print_record/' + job_offer_id + '/' + jo_template_id;
		window.open( url, '_blank');
	}
	else{
		Boxy.ask("Please select a template to use?", ["Cancel"],
		function( choice ) {
			
		},
		{
			title: "Select Template"
		});
	}
}

function print_contract(){
	var job_offer_id =  $('form[name="print-jocontract"] input[name="jocontract-job_offer_id"]').val();
	var contract_template_id =  $('form[name="print-jocontract"] select[name="contract_template_id"]').val();
	if( contract_template_id != "" ){
		var url = module.get_value('base_url') + 'recruitment/candidate_joboffer/print_contract/' + job_offer_id + '/' + contract_template_id;
		window.open( url, '_blank');
	}
	else{
		Boxy.ask("Please select a template to use?", ["Cancel"],
		function( choice ) {
			
		},
		{
			title: "Select Template"
		});
	}
}

function jqgrid_loadComplete()
{
	$('.resched_small').each(function(index, elem) {
		$(elem).parents('td')
		.attr('title', 'Reschedule to: ' + $(elem).text())	
	});
}