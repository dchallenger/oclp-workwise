$(document).ready(function () {	
	$('.jqgrow').die().live('dblclick', function(){
		$('#record-form')
			.append($('<input type="hidden" name="period_id" />').val($(this).parents('tr').attr('id')))
			.attr('action', module.get_value('base_url') + 'dtr/summary')
			.submit();
	});	

	window.onload = function(){
		$(".multi-select").multiselect({
			show:['blind',250],
			hide:['blind',250],
			selectedList: 1
		});
		
	}
	

	$('.process-period').die().live('click', function () {

		var period_id = $(this).parents('tr').attr('id');
		var tstamp = new Date().getTime();
		var filename = tstamp+'-'+user.get_value('user_id') + '-' + period_id
		$.ajax({
			url: module.get_value('base_url') + module.get_value('module_link') + '/get_period_option_ui',
			data: 'period_id='+period_id+'&filename='+filename,
			type: 'post',
			dataType: 'html',
			success: function (data) {
				var width = $(window).width()*.3;
				var quickedit_boxy = new Boxy('<div id="boxyhtml" style="width:'+width+'px">'+ data +'</div>',
				{
					title: 'Process Period',
					draggable: false,
					modal: true,
					center: true,
					unloadOnHide: true,
					beforeUnload: function (){
						$('.tipsy').remove();
					},
					afterShow: function () {
						$('#period-process-type').trigger('change');
						$('#process-period').click(function () {

							if( $('#values').val() == null ){
								$('#message-container').html(message_growl('error', 'Values field is required'));

								return false;
							}

							$.ajax({
								url: module.get_value('base_url') + module.get_value('module_link') + '/process',
								data: $('#form-period-options').serialize() + '&period_id=' + period_id+'&filename='+filename,
								dataType: 'json',
								type: 'post',
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
												data: $('#form-period-options').serialize() + '&period_id=' + period_id+'&filename='+filename,
												type: 'post',
												success: function (response) {									
													$('#progressbar').progressbar({
														value: parseInt(response.progress),
														complete: function(event, ui) {	
															if(response.otfile != undefined) window.open(module.get_value('base_url')+response.otfile,'OTFILE','',true);
															clearInterval(interval);
															$.unblockUI();
															$('#progressbar').progressbar('destroy');
														}
													});

													$('#progress-percent').text(parseFloat(response.progress) + '% complete...');
													
													if (response.progress === false) {
														if(response.otfile != undefined) window.open(module.get_value('base_url')+response.otfile,'OTFILE','',true);
														clearInterval(interval);
														$.unblockUI();
														$('#progressbar').progressbar('destroy');
													}
												}
											});
										},
										3000
									);
								},
								success: function(response){
									if(response.msg != "") $('#message-container').html(message_growl(response.msg_type, response.msg));
								}
							});							
						});

						$('#values').multiselect().multiselectfilter({show:['blind',250],hide:['blind',250],selectedList: 1});
						$('.multi-select').multiselect().multiselectfilter({show:['blind',250],hide:['blind',250],selectedList: 1});
					}
				});

				boxyHeight(quickedit_boxy, '#boxyhtml');				
			}
		});
	});
	
	$('.icon-16-add-listview').die().live('click', function () {edit_period('-1');});			
	$('.icon-16-edit').die().live('click', function () {edit_period($(this).parents('tr').attr('id'));});
	$('.icon-16-info').die().live('click', function () {			
			$('#record-form')
				.append($('<input type="hidden" name="period_id" />').val($(this).parents('tr').attr('id')))
				.attr('action', module.get_value('base_url') + 'dtr/summary')
				.submit();
		});

	$('#populate').click(function () {
		
	});
});

function edit_period(record_id)
{
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

	            if( $('#apply_to_id').length > 0 ){
		            $('#apply_to_id').change(update_apply_to);

		            if(record_id == '-1'){
						$('#apply_to_id').val(2);
					}
					else{
						if($('#apply_to_id').val() != '2'){
							update_apply_to();
						}
					}	
				}
			}
		}
	});
}

function update_apply_to(){
	if( $('#apply_to_id').val() != "" ){
		$('#multiselect-apply_to option').remove();
		$.ajax({
			url: module.get_value('base_url') + module.get_value('module_link') + '/get_apply_to',
			data: 'apply_to_id='+$('#apply_to_id').val()+'&processing_type_id='+$('#period_processing_type_id').val(),
			type: 'post',
			dataType: 'json',
			beforeSend: function(){
				$.blockUI({
					message: '<div class="now-loading align-center"><img src="'+module.get_value('base_url')+user.get_value('user_theme')+'/images/loading.gif"><br />Loading, please wait...</div>'
				});	
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

function quickedit_boxy_callback(module) {                 
	$('#jqgridcontainer').jqGrid().trigger("reloadGrid");	
}

function closePeriod(obj, period_id) {
	Boxy.ask("Do you want to close this period?",["Yes","No"],
		function(choice){
			if (choice == "Yes"){
				$.ajax({
					url: module.get_value('base_url') + module.get_value('module_link') + '/closePeriod',
					data: 'period_id=' + period_id,
					type: 'post',
					dataType: 'json',
					beforeSend: function() {
						$.blockUI({
							message: '<div class="now-loading align-center"><img src="'+module.get_value('base_url')+user.get_value('user_theme')+'/images/loading.gif"><br />Loading, please wait...</div>'
						});
					},
					success: function (response) {
						$.unblockUI();
						message_growl(response.msg_type, response.msg);

						$('#jqgridcontainer').jqGrid().trigger("reloadGrid");
					}		
				});
			}
		}
	)	
}