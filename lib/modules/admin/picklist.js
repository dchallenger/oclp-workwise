function edit_picklist_value( value_id, picklist_name, picklist_table )
{
	var data = "value_id="+value_id+"&picklist_name="+picklist_name+"&picklist_table="+picklist_table;
	$.ajax({
		url: module.get_value('base_url') + module.get_value('module_link') + "/picklist_value_editor",
		type:"POST",
		data: data,
		dataType: "json",
		beforeSend: function(){
			$.blockUI({ message: '<div class="now-loading align-center"><img src="'+module.get_value('base_url')+user.get_value('user_theme')+'/images/loading.gif"><br />Loading, please wait...</div>' });
		},
		success: function(data){
			if(data.msg != ''){
				$.unblockUI();
				message_growl(data.msg_type, data.msg);
			}
			else{
				value_editor = new Boxy('<div id="boxyhtml">'+ data.picklist_value_editor +'</div>',
				{
					title: "Picklist Value Editor",
					draggable: false,
					modal: false,
					center: true,
					unloadOnHide: true,
					modal: true,
					afterShow: function(){ $.unblockUI(); },
					beforeUnload: function(){ get_picklist_values(); }
				});
			}
		}
	});
}

function picklist_value_save( action )
{
	if( $('input[name="picklist_value"]').val() == ""){
		message_growl('error', "Picklist value cannot be empty.")
	}
	else{
		$.ajax({
			url: module.get_value('base_url') + module.get_value('module_link') + "/save_picklist_value",
			type:"POST",
			data: $('#picklist-value-form').serialize(),
			dataType: "json",
			beforeSend: function(){
				$.blockUI({ message: '<div class="now-loading align-center"><img src="'+module.get_value('base_url')+user.get_value('user_theme')+'/images/loading.gif"><br />Saving, please wait...</div>' });
			},
			success: function(data){
				$.unblockUI();
				if(data.msg != '') message_growl(data.msg_type, data.msg);
				if(data.value_id != -1) $('input[name="value_id"]').val(data.value_id);
				if(action == "back") value_editor.hide();				
			}
		});
	}
}

function del_picklist_value( value_id, picklist_name, picklist_table )
{
	Boxy.ask("Are you sure you want to delete selected value?", ["Yes", "Cancel"],
	function( choice ) {
		if(choice == "Yes"){
			var data = "value_id="+value_id+"&picklist_name="+picklist_name+"&picklist_table="+picklist_table;
			var url = module.get_value('base_url') + module.get_value('module_link') + "/del_picklist_value";
			simpleAjax(url, data);
			get_picklist_values();
		}
	},
	{
		title: "Delete Selected"
	});
}

function showPicklistManager()
{
	if($('#record_id') == -1 || $('input[name="picklist_type"]:checked').val() == 1){
		$('#picklist-manager').css('display', 'none');
	}
	else{
		$('#picklist-manager').css('display', '');
		get_picklist_values();
	}
}

function get_picklist_values()
{
	$.ajax({
		url: module.get_value('base_url') + module.get_value('module_link') + "/get_picklist_values",
		type: "POST",
		data: "picklist_id="+$('input[name="record_id"]').val(),
		dataType: "json",
		beforeSend: function(){
			$('#picklist-values').block({ 
				message: '<tr><td colspan="3"><div class="now-loading align-center"><img src="'+module.get_value('base_url')+user.get_value('user_theme')+'/images/loading.gif"><br />Loading, please wait...</div></td></tr>',
				showOverlay: false
			}); 
		},
		success: function(data){
			if(data.msg != '') message_growl(data.msg_type, data.msg);			
			$('#picklist-values').html(data.picklist_values);
			$('#picklist-values').unblock();
		}
	});
}