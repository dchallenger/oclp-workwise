$(document).ready( function(){
	//default hide left navigation
	$("aside").animate({"margin-left": "-265"});	
	$("#body-content-wrap").css({"width": "98%", "padding-left": "20px"});
	$("#btn-panel").removeClass("close-panel");
	$("#btn-panel").addClass("open-panel").remove();
	$('#body-content-wrap').css('border-left', 'none');

	//todo tab effect
	$('.panel-master li').live('click', function(){
		var detail = $(this).attr('id');
		$('.panel-master li').each( function(){ $(this).removeClass('active'); });
		$(this).addClass('active');
		$('.panel-detail').each( function(){ $(this).addClass('hidden'); });
		$('.'+detail).removeClass('hidden');
	});
	$('.panel-master li:first').trigger('click');
	
	$('.icon-16-refresh').live('click', function(){
		$.ajax({
			url: module.get_value('base_url') + module.get_value('module_link') + '/reset_portlet_state',
			type:"POST",
			data: '',
			dataType: "json",
			success: function(data){
				if(data.msg != "")
					$('#message-container').html(message_growl(data.msg_type, data.msg));
				else
					window.location = module.get_value('base_url') + module.get_value('module_link');	
			}
		});
	});
	
	$(".portlet-top").sortable({
		handle: 'h4.portlet-handle',		
		update : function () {
			portlet_order('top')
		},
		forceHelperSize: false,
		opacity: 0.8
	});

	$(".portlet-left").sortable({
		handle: 'h4.portlet-handle',
		connectWith: '.portlet-right',
		update : function () {
			portlet_order('left')
		},
		forceHelperSize: false,
		opacity: 0.8
	});
	
	$(".portlet-right").sortable({
		handle: 'h4.portlet-handle',
		connectWith: '.portlet-left',
		update : function () {
			portlet_order('right')
		},
		forceHelperSize: false,
		opacity: 0.8
	});
	
	
	$( document ).oneTime( 1, function() {
		refresh_portlet('','');
		$(this).stopTime();
	}, 1);

	$.ajax({
	        url: module.get_value('base_url') + module.get_value('module_link') + '/get_sub_portlet',
	        data: '',
	        dataType: 'json',
	        type: 'post',
	        async: false,
			success: function( response ){
				if (response.reminder == true) {
					var sub_reminder = '<div id="sub_portlet" style="width: 99%; margin-left: 25%;">';
					sub_reminder = sub_reminder + '<div style="width: 50%; padding: 15px 40px 10px 10px; margin: 20px; border: 1px solid #C0C0C0;  position: relative;line-height:18px;font-size: 12px">';
					sub_reminder = sub_reminder + '<div>';
					sub_reminder = sub_reminder + response.planning_html;
					sub_reminder = sub_reminder + '</div>';
					sub_reminder = sub_reminder + '<div>';
					sub_reminder = sub_reminder + response.reminder_html;
					sub_reminder = sub_reminder + '</div>';
					sub_reminder = sub_reminder + '<a onclick="close_sub_portlet();" container="jqgridcontainer" tooltip="Close" style="position: absolute; top: 10px; right: 10px; height: 25px; width: 25px; background-image: url(&quot;themes/slategray/icons/icon-close-24.png&quot;);"></a>';
					sub_reminder = sub_reminder + '</div>';
			        sub_reminder = sub_reminder + '</div>';
					$(".portlet-top").before(sub_reminder);		
				};	
			}
	});

	//Training and development javascript
	$('.more_info_button').live('click', function (){

		var id = $(this).attr('calendarid');
		var subordinate = $(this).attr('subordinate');

		$.ajax({
		        url: module.get_value('base_url') + 'dashboard/get_training_template_form',
		        data: 'calendar_id=' + id + '&subordinate='+subordinate,
		        type: 'post',
		        dataType: 'json',
		        beforeSend: function(){
					$.blockUI({
						message: '<div class="now-loading align-center"><img src="'+module.get_value('base_url')+user.get_value('user_theme')+'/images/loading.gif"><br />Loading, please wait...</div>'
					});  		
				},	
		        success: function(response) {

		        	if(response.msg_type == 'error'){
		        	
		        		$.unblockUI();	
		        		message_growl(response.msg_type, response.msg);

		      		}
					else{

		        	$.unblockUI();	

						template_form = new Boxy('<div id="boxyhtml" style="">'+ response.form +'</div>',
						{
								title: 'Training and Development',
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

	$('.calendar_status').live('click',function(){

		var element = $(this);
		var status_id = $(this).attr('statusid');
		var calendar_id = $(this).attr('calendarid');
		var message = "";

		if( status_id == 2 ){
			message = "Are you sure to confirm this training?";

			Boxy.confirm(
			'<div id="boxyhtml" height="50px">'+message+'</div>',
				function () {
					
					$.ajax({
					        url: module.get_value('base_url') + 'dashboard/join_quit_training',
					        data: 'calendar_id=' + calendar_id + '&status_id=' + status_id,
					        type: 'post',
					        dataType: 'json',
					        success: function(data) {
					        	message_growl(data.msg_type, data.msg);
					        
					        	element.parent().empty();

					        }
					});

				}			
			);

		}
		else{

			$('.training_confirm_reason_container').find('.confirm_reason_training_calendar_id').val(calendar_id);
			$('.training_confirm_reason_container').find('.confirm_reason_training_status_id').val(status_id);

			var html = $('.training_confirm_reason_container').html();

			template_form = new Boxy('<div id="boxyhtmlreason" style="">'+html+'</div>',
			{
				title: 'Reason',
				draggable: false,
				modal: true,
				center: true,
				unloadOnHide: true,
				beforeUnload: function (){
					template_form = false;
				}
			});
			boxyHeight(template_form, '#boxyhtmlreason');	


		}

	});

	$('.training_submit_confirm_reason').live('click',function(){

		var calendar_id = $(this).parent().find('.confirm_reason_training_calendar_id').val();
		var status_id = $(this).parent().find('.confirm_reason_training_status_id').val();
		var remarks = $(this).parents('#boxyhtmlreason').find('.confirm_reason').val();
		var element = $('.more_info_button').parents('tr').find('td:last');


		$.ajax({
	        url: module.get_value('base_url') + 'dashboard/join_quit_training',
	        data: 'calendar_id=' + calendar_id + '&status_id=' + status_id + '&remarks=' + remarks,
	        type: 'post',
	        dataType: 'json',
	        success: function(data) {
	        	message_growl(data.msg_type, data.msg);
	        
	        	element.empty();

	        	$.unblockUI();	
	        	Boxy.get($('#boxyhtmlreason')).hide();
	        	

	        }
		});

	});


	
	// JS for out of office	
});

function refresh_portlet( portlet, portletfile)
{
	if( portlet == '' ){
		$(".portlet-inside").each(function(){
			get_portlet_content($(this).attr('id'), $(this).attr('reference'));
		});
	}
	else{
		var portlet = 'portlet-inside-'+portlet;
		get_portlet_content(portlet, portletfile);
	}
}

function get_portlet_content(portlet, portletfile){
	$.ajax({
			url: module.get_value('base_url') +   module.get_value('module_link') + '/get_portlet_content',
			type:'POST',			
			dataType: "html",
			data: 'portlet_file='+portletfile+'&portlet_id='+portlet,
			beforeSend: function(){
				$("#" + portlet).html( '<img src="'+user.get_value('user_theme')+'/images/loading3.gif" height="25px" alt="Loading..."/>' );
			},
			success: function( data ){
				$("#" + portlet).html( data );				
			}
		});
}

function portlet_order( column )
{	
	switch( column ){
		case "left":
			var sequence = $('.portlet-left').sortable('serialize');
			break;
		case "right":
			var sequence = $('.portlet-right').sortable('serialize');
			break;
		case "top":
			var sequence = $('.portlet-top').sortable('serialize');
			break;			
	}
	$.ajax({
		url: module.get_value('base_url') + module.get_value('module_link') + "/portlet_order",
		type:"POST",
		data: sequence+'&column='+column,
		dataType: "json",
		success: function(data){
			if( data.msg != "" ) $('#message-container').html(message_growl(data.msg_type, data.msg));
		}
	});	
}

function resize_portlet(portlet_id) {		
	$.ajax({		
		url: module.get_value('base_url') + module.get_value('module_link') + "/portlet_resize",		
		type:"POST",		
		data: 'portlet_id=' + portlet_id,		
		dataType: "json",
		success: function(data) {
			if( data.msg != "" ) 
				$('#message-container').html(message_growl(data.msg_type, data.msg));

				$('#portlet-' + portlet_id).appendTo('.portlet-' + data.class);
		}	
	});
}

function fold_portlet( portlet, portlet_id )
{    
	var is_folded = 1;
	if( portlet.parent().next().css('display') == "none") is_folded = 0;
	portlet.parent().next().slideToggle();
	
	$.ajax({
		url: module.get_value('base_url') + module.get_value('module_link') + "/portlet_fold_state",
		type:"POST",
		data: 'is_folded='+is_folded+'&portlet_id='+ portlet_id,
		dataType: "json",
		success: function(data){
			if( data.msg != "" ) $('#message-container').html(message_growl(data.msg_type, data.msg));
		}
	});
	if( portlet.hasClass( 'icon-16-portlet-fold' ) ){
		portlet.removeClass( 'icon-16-portlet-fold' );
		portlet.addClass( 'icon-16-portlet-unfold' );
	}
	else{
		portlet.addClass( 'icon-16-portlet-fold' );
		portlet.removeClass( 'icon-16-portlet-unfold' );
	}
}

function join_quit_training(calendar_id, status_id){

	Boxy.confirm(
		'<div id="boxyhtml" height="50px">Are you sure?</div>',
		function () {
			
			$.ajax({
			        url: module.get_value('base_url') + 'dashboard/join_quit_training',
			        data: 'calendar_id=' + $('#calendarid').val() + '&status_id=' + status_id,
			        type: 'post',
			        dataType: 'json',
			        success: function(data) {
			        	message_growl(data.msg_type, data.msg);
			        }
			});

		}			
	);	
}

function close_sub_portlet(){
	$('#sub_portlet').hide();
}