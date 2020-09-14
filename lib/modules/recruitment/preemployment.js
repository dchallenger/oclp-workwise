$(document).ready(function () {
	$('label[for="label_hr"],label[for="label"]').each(function (index, obj) {
		label = $.trim(new String($(obj).text()));
		$(obj).text(label.substr(0, label.length - 1));             
		$(obj).removeClass('gray');
		$(obj).removeClass('label-desc');
	}
	);

	$('.icon-16-edit').die('click');
	$('.icon-16-listback').die('click');
	$('.icon-16-edit').live('click', function () {
		record_action("detail", $(this).parent().parent().parent().attr("id"), $(this).attr('module_link'));	
	});

	$('.button-joboffer').live('click', joboffer_quick_add);

	$('.icon-16-document-stack').die('click').live('click', function(){
		var record_id = $(this).attr('joboffer_id');
		var candidate_status = $(this).attr('candidate_status');
		$.ajax({
			url: module.get_value('base_url') + 'recruitment/candidate_joboffer/get_template_form',
			data: 'record_id=' + record_id + '&candidate_status='+candidate_status,
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

function new_201( applicant_id ){
	$('#record-form #applicant_id').val( applicant_id );
	var data = 'page_refresh=true&applicant_id='+applicant_id + '&module_id='+ module.get_value('module_id');
	showQuickEditForm( module.get_value('base_url') + "employees/quick_edit", data );
}


function ajax_save( on_success, is_wizard ){
	if( is_wizard == 1 ){
		var current = $('.current-wizard');
		var fg_id = current.attr('fg_id');
		var ok_to_save = eval('validate_fg'+fg_id+'()')
	}
	else{
		ok_to_save = validate_form();
	}
	
	if( ok_to_save ){
		$('form[name="record-form"]').append($('<input type="hidden" name="completed" />').val('1'));    
		var data = $('#record-form').serialize();
		var saveUrl = module.get_value('base_url')+module.get_value('module_link')+"/ajax_save"
				
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


function preemployment_back() {
	$.unblockUI({
		onUnblock: function() {       
			window.location = $('a.cancel').attr('href');
		}
	});
}

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
