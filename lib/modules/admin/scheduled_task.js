$( document ).ready( function() {
   
	window.onload = function(){

		if( module.get_value('view') == 'edit' ){
			if( $('#crontask_function_id').val() > 0 ){
				get_function_variables();
			}
		}

		if( module.get_value('view') == 'detail' ){
			get_function_variables();
			get_record_info();
		}
	}

	$('#crontask_function_id').live('change',function(){
		get_function_variables();
	});

});

function execute_task( schedule_task_id ){
	Boxy.ask("Do you really want to execute the selected task now?", ["Yes", "No"],function( choice ) {
		if(choice == 'Yes'){
			$.ajax({
			    url: module.get_value('base_url') + module.get_value('module_link') + '/execute_task',
			    data: 'scheduled_task_id=' + schedule_task_id,
			    type: 'post',
			    dataType: 'json',
			    beforeSend: function(){
					$.blockUI({ message: '<div class="now-loading align-center"><img src="'+module.get_value('base_url')+user.get_value('user_theme')+'/images/loading.gif"><br />Loading, please wait...</div>' });
				},
			    success: function(data) {
			    	$.unblockUI();
					if (data.msg instanceof Array) {
						var msg_string = "<b>Message Log</b><br/>";
						for(var i in data.msg){
							msg_string = msg_string + (parseFloat(i)+1) +'. '+	data.msg[i] + "<br/>";
						}
						message_growl('attention', msg_string);
					} else {
						message_growl(data.msg_type, data.msg);
					}
			    	
			    	$("#jqgridcontainer").trigger("reloadGrid");
			    }
			});
		}
	},
	{
	title: "Execute Task"
	});
}

function suspend_task( schedule_task_id ){
	Boxy.ask("Do you really want to suspend the selected task?", ["Yes", "No"],function( choice ) {
		if(choice == 'Yes'){
			$.ajax({
			    url: module.get_value('base_url') + module.get_value('module_link') + '/change_status',
			    data: 'scheduled_task_id=' + schedule_task_id+'&task_status=3',
			    type: 'post',
			    dataType: 'json',
			    beforeSend: function(){
					$.blockUI({ message: '<div class="now-loading align-center"><img src="'+module.get_value('base_url')+user.get_value('user_theme')+'/images/loading.gif"><br />Loading, please wait...</div>' });
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
	title: "Suspend Task"
	});
}

function unsuspend_task( schedule_task_id ){
	Boxy.ask("Do you really want to unsuspend the selected task?", ["Yes", "No"],function( choice ) {
		if(choice == 'Yes'){
			$.ajax({
			    url: module.get_value('base_url') + module.get_value('module_link') + '/change_status',
			    data: 'scheduled_task_id=' + schedule_task_id+'&task_status=1',
			    type: 'post',
			    dataType: 'json',
			    beforeSend: function(){
					$.blockUI({ message: '<div class="now-loading align-center"><img src="'+module.get_value('base_url')+user.get_value('user_theme')+'/images/loading.gif"><br />Loading, please wait...</div>' });
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
	title: "Unsuspend Task"
	});
}

function get_record_info(){

	$.ajax({
	        url: module.get_value('base_url') + module.get_value('module_link') + '/get_record_info',
	        data: 'record_id=' + $('#record_id').val(),
	        type: 'post',
	        dataType: 'json',
	        success: function(data) {

	        	if( data.implement_type == 1 ){

	        		$('label[for="time"]').parent().show();
					$('label[for="hour"]').parent().hide();
					$('label[for="minute"]').parent().hide();

	        	}
	        	else if( data.implement_type == 2 ){

	        		$('label[for="time"]').parent().hide();
					$('label[for="hour"]').parent().show();
					$('label[for="minute"]').parent().show();

	        	}
	        	else{

	        		$('label[for="time"]').parent().hide();
					$('label[for="hour"]').parent().hide();
					$('label[for="minute"]').parent().hide();

	        	}

	        }
	});


}

function get_function_variables(){

	$.ajax({
        url: module.get_value('base_url') + module.get_value('module_link') + '/get_function_variables',
        dataType: 'html',
        type:"POST",
        data: 'function=' + $('#crontask_function_id').val() + '&record_id=' + $('#record_id').val() + '&view=' + module.get_value('view'),
        success: function (response) {

        	$('#alert_frequency_variables fieldset').html(response);

        }
    });

}