$(document).ready(function(){
	//moduele specific js goes here
	$('.icon-16-active').live('click', function(){
		toggle_active("active", $(this).parent().parent().parent().attr("id"),$(this))											   
	});
	
	$('.icon-16-xgreen-orb').live('click', function(){
		toggle_active("xactive", $(this).parent().parent().parent().attr("id"),$(this))											   
	});

	// disable for profile only
	if($.trim(module.get_value('view')) == "profile") {
		// Move the label confirm on change password
		$('.profile_move_down').remove();
		$('#password').after('<br /><label class="label-desc gray" for="password"><span class="password-field-div profile_move_down" style="">Confirm:</span></label>');

		// Remove browse and delete button on picture
		$('.image-delete').remove();
		setTimeout(function () {
			$('#uploadify-photoUploader').hide();
		}, 100);
		

		// Disable all textbox and textarea and salutation
		$('input[type=text], textarea').prop('readonly', true);
		$('#salutation').prop('disabled', true);
	}
	$('.icon-16-key').live('click', function(){		
		var changepasswordBoxy = false;
		var record_id = $(this).parent().parent().parent().attr("id");
		Boxy.ask("Change Password selected record(s)?", ["Yes", "No"],
			function( choice ) 
			{
					if(choice == "Yes")
				{
					if (!changepasswordBoxy) 
				    {
				        $.ajax({
				            url: module.get_value('base_url') + module.get_value('module_link') + "/get_boxy_change_password",
				            type:"POST",
				            data: "user_id="+record_id,
				            dataType: "json",
				            async: false,
				            success: function(data)
				            {
				            	if(data.access)
				            	{
					                changepasswordBoxy = new Boxy('<div id="password-detail-entry-form" style="width:350px; height:150px;">'+ data.change_form +'</div>',
					                {
					                    title: "Change Password Form",
					                    draggable: false,
					                    modal: true,
					                    center: true,
					                    unloadOnHide: true,
					                    show: true,
					                    afterHide: function() { changepasswordBoxy = false; },
					                    afterShow:function(){  }
					                });
					                boxyHeight(changepasswordBoxy, '#password-detail-entry-form');
				            	}
							    else
							    {
							    	$('#message-container').html(message_growl(data.msg_type, data.msg));
							    }
				            }
				        });     
				        return false;
				    }					
				}
				else
				{
					
				}       
			
			},
			{
				title: "Change Password"
			}
		);		
	});
});

//global js var in edit view
var form_boxy = "";

function toggle_active(type,user_id,obj){
	if (type == "active"){
		val = 1;
		$(obj).removeClass('icon-16-active');
		$(obj).addClass('icon-16-xgreen-orb');
	}
	else{
		val = 0;
		$(obj).removeClass('icon-16-xgreen-orb');
		$(obj).addClass('icon-16-active');		
	}

	$.ajax({
		url: module.get_value('base_url') + module.get_value('module_link') + "/toggle_active",
		type:"POST",
		data: 'user_id=' + user_id + '&val=' + val,
		beforeSend: function(){
			  		
		},
		success: function(data){
			return true;
		}
	});		
}

function addCustomAccess( details )
{
	var data = "details="+details;
	$.ajax({
		url: module.get_value('base_url') + module.get_value('module_link') + "/add_custom_access",
		type:"POST",
		dataType: "json",
		data: data,
		beforeSend: function(){
			  		
		},
		success: function(data){
			if(data.msg != "") $('#message-container').html(message_growl(data.msg_type, data.msg));
			if(data.custom_access != ""){
				var width = 400;
				form_boxy = new Boxy('<div id="boxyhtml" style="width:'+width+'px; background-color: #f9f9f9;">'+ data.custom_access +'</div>',
				{
					title: 'Add Custom Access',
					draggable: false,
					modal: false,
					center: true,
					unloadOnHide: true,
					beforeUnload: function(){ $('.tipsy').remove(); }
				});	
			}
		}
	});	
}

function add_to_access_list( remove_tr )
{
	if( remove_tr != "" ) $('#'+remove_tr).remove();
	
	var module_id = $('select[name="module_id"]').val();
	var module_action = $('select[name="module_action"]').val();
	var module_access = $('select[name="module_access"]').val();
	
	if( module_id == "" || module_action == "" || module_access == ""){
		alert('Please make sure you have completely filledup the form.')
	}
	else{
		var access = (module_access == 1 ? 'Yes' : 'No');
		var short_name = $('select[name="module_id"] :selected').text();
		var tr_id = module_action +'-'+ module_id;
		var tr_count = ($('#custom-access > tr').size());
		var objclass = (tr_count % 2 == 0 ? 'odd' : 'even');
		//check if tr_id exist
		if($('#'+tr_id).size() > 0) $('#'+tr_id).remove();
		var to_append = '<tr class="'+objclass+'" id="'+ tr_id +'">';
		to_append = to_append + '<td><input name="'+ module_action +'['+ module_id +']" value="'+ module_access +'" type="hidden">'+ short_name +'</td>';
		to_append = to_append + '<td>'+ module_action +'</td>';
		to_append = to_append + '<td>'+ access +'</td>';
		to_append = to_append + '<td><span class="icon-group">';
		to_append = to_append + '<a href="javascript:void(0)" tooltip="Edit" class="icon-button icon-16-edit" onclick="addCustomAccess(\''+ tr_id +'-'+module_access+'\')"></a>';
		to_append = to_append + '<a href="javascript:void(0)" tooltip="Delete" class="icon-button icon-16-delete" onclick="deleteCustomAccess(\''+ tr_id +'\')"></a>';
		to_append = to_append + '</span></td>';
		to_append = to_append + '</tr>';
		$('#custom-access').append(to_append);
		form_boxy.unload();
	}
	
}

function deleteCustomAccess(tr_id)
{
	$('#'+tr_id).remove();
}

function save_password(user_id)
{
	var p = $('#password').val();
	var pc = $('#password-confirm').val();
	if(p == '')
	{
		message_growl("error", "Password - This field is mandatory.");
	}
	else
	{
		if(p != pc)
		{
			message_growl("error", "Password - Did not match.");
		}
		else
		{
			var data = $('#password-entry-form_2').serialize();	
			var saveUrl = module.get_value('base_url') + module.get_value('module_link') + "/change_password"
			$.ajax
			({
				url: saveUrl,
				type:"POST",
				data: data,
				dataType: "json",
				beforeSend: function()
				{
				    $.blockUI({ message: '<div class="now-loading align-center"><img src="'+module.get_value('base_url')+'css/images/loading.gif"><br />Saving, please wait...</div>' });        		
				},
				success: function(data)
				{
					$.unblockUI({ onUnblock: function() { message_growl(data.msg_type, data.msg); } });
					$('#cancel_me').trigger('click');
				}				
			});
		}
	}
}