$(document).ready(function(){
	if( module.get_value('view') == 'index' ){
		 toggleOn();
	}
});

function send( email_id ){
	Boxy.ask("Do you really want to send the selected message now?", ["Yes", "No"],function( choice ) {
		if(choice == 'Yes'){
			$.ajax({
			    url: module.get_value('base_url') + module.get_value('module_link') + '/send',
			    data: 'email_id=' + email_id,
			    type: 'post',
			    dataType: 'json',
			    beforeSend: function(){
					$.blockUI({ message: '<div class="now-loading align-center"><img src="'+module.get_value('base_url')+user.get_value('user_theme')+'/images/loading.gif"><br />Sending, please wait...</div>' });
				},
			    success: function(data) {
			    	$.unblockUI();
			    	message_growl(data.msg_type, data.msg);
			    	$("#jqgridcontainer").trigger("reloadGrid");
			    }
			});
		}
	},
	{
	title: "Send Email"
	});
}

function send_yahoo( email_id ){
	Boxy.ask("Do you really want to send the selected message now?", ["Yes", "No"],function( choice ) {
		if(choice == 'Yes'){
			$.ajax({
			    url: module.get_value('base_url') + module.get_value('module_link') + '/send_mail',
			    data: 'email_id=' + email_id,
			    type: 'post',
			    dataType: 'json',
			    beforeSend: function(){
					$.blockUI({ message: '<div class="now-loading align-center"><img src="'+module.get_value('base_url')+user.get_value('user_theme')+'/images/loading.gif"><br />Sending, please wait...</div>' });
				},
			    success: function(data) {
			    	$.unblockUI();
			    	message_growl(data.msg_type, data.msg);
			    	$("#jqgridcontainer").trigger("reloadGrid");
			    }
			});
		}
	},
	{
	title: "Send Email"
	});
}

var body_boxy = false;
function view_body( email_id ){
	$.ajax({
		url: module.get_value('base_url') + module.get_value('module_link') + '/get_body',
		data: 'email_id=' + email_id,
		type: 'post',
		dataType: 'json',
		beforeSend: function(){
			$.blockUI({ message: '<div class="now-loading align-center"><img src="'+module.get_value('base_url')+user.get_value('user_theme')+'/images/loading.gif"><br />Loading, please wait...</div>' });
		},
		success: function(data) {
		message_growl(data.msg_type, data.msg);
			if(!body_boxy){
				body_boxy = new Boxy('<div id="remarks_boxy" style="width: 677px;">'+ data.email.body +'</div>',{
					title: 'Remarks',
					draggable: false,
					modal: true,
					center: true,
					unloadOnHide: true,
					show: true,
					afterShow: function(){ $.unblockUI(); },
					beforeUnload: function(){ $.unblockUI(); body_boxy = false; }
				});
				boxyHeight(body_boxy, '#remarks_boxy');	
				$.unblockUI();
			}
		}
	});
		
}