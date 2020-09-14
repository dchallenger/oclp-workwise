$(document).ready(function(){
	$('.icon-16-add-listview').die().live('click', function () {edit_period('-1');});
	$('.icon-16-edit').die().live('click', function () {edit_period($(this).parents('tr').attr('id'));});
});

function edit_period(record_id){
	module_url = module.get_value('base_url') + module.get_value('module_link') + '/quick_edit';
	$.ajax({
		url: module_url,
		type:"POST",
		data: 'record_id=' + record_id,
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
				quickedit_boxy = new Boxy('<div id="boxyhtml">'+ data.quickedit_form +'</div>',
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
				if (typeof(BindLoadEvents) == typeof(Function)) {
	                BindLoadEvents();
	            } 	
				$('#multiselect-apply_to').multiselect();
				$('#apply_to_id').change(update_apply_to);
				$('#period_processing_type_id').change(function(){
					if( $(this).val() == '1' ){
						$('label[for="annualized"]').parent().removeClass('hidden');
					}
					else{
						$('label[for="annualized"]').parent().addClass('hidden');
						$('#annualized-no').trigger('click');
					}
					update_apply_to();
				});
				
				if(record_id == '-1'){
					$('#apply_to_id').val(2);
				}
			}
		}
	});
}

function quickedit_boxy_callback(module) {                 
	$('#jqgridcontainer').jqGrid().trigger("reloadGrid");	
}

function update_apply_to(){
	if( $('#apply_to_id').val() != "" && $('#period_processing_type_id').val() != "" ){
		$('#multiselect-apply_to option').remove();
		$.ajax({
			url: module.get_value('base_url') + module.get_value('module_link') + '/get_apply_to',
			data: 'apply_to_id='+$('#apply_to_id').val()+'&processing_type_id='+$('#period_processing_type_id').val(),
			type: 'post',
			dataType: 'json',
			beforeSend: function(){
				
			},
			success: function (data) {
				$.unblockUI();
				$.each(data.options, function (index, value) {
					$('#multiselect-apply_to').append('<option value="'+value.value+'">'+value.text+'</option>');
				});
				$('#multiselect-apply_to').multiselect('refresh');
			}
		});	
	}
	else{
		$('#multiselect-apply_to option').remove();
		$('#multiselect-apply_to').multiselect('refresh');	
	}	
}

function process_period( period_id ){
	$.ajax({
		url: module.get_value('base_url') + module.get_value('module_link') + '/process',
		data: 'period_id='+period_id,
		type: 'post',
		dataType: 'json',
		beforeSend: function(){
			$.blockUI({
				message: '<div class="now-loading align-center"><img src="'
					+module.get_value('base_url')+user.get_value('user_theme')
					+'/images/loading.gif"><br />Processing, please wait...<div id="progressbar"></div>'
					+'<div id="progress-percent"></div>'
					+'</div>'						
			});
			// Prepare progress bar.
			var interval = setInterval(						
				function() {
					$.ajax({								
						url: module.get_value('base_url') + module.get_value('module_link') + '/getprogress',
						dataType: 'json',
						async: false,
						data: 'period_id=' + period_id,
						type: 'post',
						success: function (response) {									
							if (response.progress === false) {
								clearInterval(interval);
								$.unblockUI();
								$('#progressbar').progressbar('destroy');
							}
							else{
								$('#progressbar').progressbar({
									value: parseInt(response.progress),
									complete: function(event, ui) {	
										clearInterval(interval);
										$.unblockUI();
										$('#progressbar').progressbar('destroy');
										window.location = module.get_value('base_url') + 'payroll/current_transaction';
									}
								});
								$('#progress-percent').text(parseFloat(response.progress) + '% complete...');
							}
						}
					});
				},
				5000
			);
		},
		success: function (data) {
			if(data.msg != ""){
				if(data.msg_type == "error"){
					$.unblockUI();
					$('#message-container').html(message_growl(data.msg_type, data.msg));
					clearInterval(interval);
					$('#progressbar').progressbar('destroy');	
				}
			}
		}
	});
}

function close_period( period_id ){
	$.ajax({
		url: module.get_value('base_url') + module.get_value('module_link') + '/close',
		data: 'period_id='+period_id,
		type: 'post',
		dataType: 'html',
		beforeSend: function(){
			$.blockUI({
				message: '<div class="now-loading align-center"><img src="'
					+module.get_value('base_url')+user.get_value('user_theme')
					+'/images/loading.gif"><br />Processing, please wait...<div id="progressbar"></div>'
					+'<div id="progress-percent"></div>'
					+'</div>'						
			});
			// Prepare progress bar.
			var interval = setInterval(						
				function() {
					$.ajax({								
						url: module.get_value('base_url') + module.get_value('module_link') + '/getprogress',
						dataType: 'json',
						async: false,
						data: 'period_id=' + period_id,
						type: 'post',
						success: function (response) {									
							if (response.progress === false) {
								clearInterval(interval);
								$.unblockUI();
								$('#progressbar').progressbar('destroy');
								$("#jqgridcontainer").trigger("reloadGrid");
							}
							else{
								$('#progressbar').progressbar({
									value: parseInt(response.progress),
									complete: function(event, ui) {	
										clearInterval(interval);
										$.unblockUI();
										$('#progressbar').progressbar('destroy');
										$("#jqgridcontainer").trigger("reloadGrid");
									}
								});
								$('#progress-percent').text(parseFloat(response.progress) + '% complete...');
							}
						}
					});
				},
				5000
			);
		},
		success: function (data) {
			if(data.msg != ""){
				if(data.msg_type == 'error'){
					$.unblockUI();
					$('#message-container').html(message_growl(data.msg_type, data.msg));
					clearInterval(interval);
					$('#progressbar').progressbar('destroy');
					$("#jqgridcontainer").trigger("reloadGrid");
				}
			}
		}
	});
}
