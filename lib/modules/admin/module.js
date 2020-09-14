$(document).ready(function(){
	if ($('input[name="code"]').val() != '') {
		$('input[name="code"]').attr('readonly', 'readonly');
	}
	
	if( view == 'edit' ){
		module_id = $('#record_id').val();
		$('#record_id').live('change', function(){
			toggleManagersVisibility();
		});
		toggleManagersVisibility();
	}
	
	if(view == "index"){
		$('.icon-16-export').live('click', function(){
			var record_id = $(this).attr('record_id');
			window.open(module.get_value('base_url')+module.get_value('module_link')+'/export_sqlscript/'+record_id, "Export SQL Script");
		});

		$('.icon-16-clone').live('click', function(){
			var record_id = $(this).attr('record_id');
			clone_module( record_id );
		});
	}
});

// Return a helper with preserved width of cells
var fixHelper = function(e, ui) {
	ui.children().each(function() {
		$(this).width($(this).width());
	});
	return ui;
};

function toggleManagersVisibility(){
	if($('#record_id').val() != -1){
		$('.managers-div').css('display', '');
		getFieldGroups();
		getListviews();
	}
}

function getListviews( )
{
	var module_id = $('#record_id').val();
	var data = "module_id="+module_id;
	$.ajax({
		url: module.get_value('base_url') + module.get_value('module_link') + "/get_listview",
		type:"POST",
		data: data,
		dataType: "json",
		beforeSend: function(){
			$('#temp-td-fg').block({ 
				message: '<div class="now-loading align-center"><img src="'+module.get_value('base_url')+user.get_value('user_theme')+'/images/loading.gif"><br />Loading, please wait...</div>',
				showOverlay: false
			});  		
		},
		success: function(data){
			$('#temp-td-fg').unblock();
			if(data.msg != "") $('#message-container').html(message_growl(data.msg_type, data.msg));
			if( data.listview != "" ) $('#listview-tbody').html( data.listview );
			if(data.sortable){
				$( '.sort-listview').sortable({
					update: function () {
						listviewSequence();	
					},
					forceHelperSize: false,
					opacity: 0.7
				});	
			}
		}
	});
}

function getFieldGroups()
{
	var module_id = $('#record_id').val();
	var data = "module_id="+module_id;
	$.ajax({
		url: module.get_value('base_url') + module.get_value('module_link') + "/get_fieldgroup",
		type:"POST",
		data: data,
		dataType: "json",
		beforeSend: function(){
			$('#temp-td-fg').block({ 
				message: '<div class="now-loading align-center"><img src="'+module.get_value('base_url')+user.get_value('user_theme')+'/images/loading.gif"><br />Loading, please wait...</div>',
				showOverlay: false
			});  		
		},
		success: function(data){
			$('#temp-td-fg').unblock();
			if(data.msg != "") $('#message-container').html(message_growl(data.msg_type, data.msg));
			if( data.fieldgroup != "" ) $('#fieldgroup-tbody').html( data.fieldgroup );
			if(data.sortable){
				$( '#fieldgroup-list tbody' ).sortable({
					update: function () {
						fieldGroupSequence();
					},
					opacity: 0.7,
					forceHelperSize: true,
					helper: fixHelper
				});
				
				$( '.column-left').sortable({
					connectWith: '.connect',
					update: function (event, ui) {
						fieldSequence('left', ui);
					},
					forceHelperSize: false,
					opacity: 0.7
				});	
				
				$( '.column-right').sortable({
					connectWith: '.connect',
					update: function (event, ui) {
						fieldSequence('right', ui);
					},
					forceHelperSize: false,
					opacity: 0.7
				});
			}
		}
	});
}

function editFieldGroup( fieldgroup_id, sequence )
{
	var module_id = $('#record_id').val();
	
	if( sequence == 0 ){
		sequence = 1;
		if( $('#fieldgroup-tbody').children(':first-child').hasClass('no-fg') == false ){	
			$('#fieldgroup-tbody tr').each(function(){
				sequence++;
			});
		}
	}
	
	var data = "record_id="+fieldgroup_id+"&module_id="+module_id+"&sequence="+sequence;
	showQuickEditForm( module.get_value('base_url') + "admin/fieldgroup/quick_edit", data );
}

function addColumn( listview_id )
{
	var data = "listview_id="+listview_id;
	$.ajax({
		url: module.get_value('base_url') + module.get_value('module_link') + "/get_lv_avail_field",
		type:"POST",
		data: data,
		dataType: "json",
		beforeSend: function(){
			$.blockUI({ message: '<div class="now-loading align-center"><img src="'+module.get_value('base_url')+user.get_value('user_theme')+'/images/loading.gif"><br />Loading, please wait...</div>' });  		
		},
		success: function(data){
			if(data.msg != "") $('#message-container').html(message_growl(data.msg_type, data.msg));
			if(data.avail_fields != ""){
				var width = $(window).width()*.5;
				quickedit_boxy = new Boxy('<div id="boxyhtml" style="width:'+width+'px; background-color: #f9f9f9;">'+ data.avail_fields +'</div>',
				{
					title: 'Add Field as Column to Listview',
					draggable: false,
					modal: true,
					center: true,
					unloadOnHide: true,
					afterShow: function(){ $.unblockUI(); },
					beforeUnload: function(){ $('.tipsy').remove(); }
					
				});	
				boxyHeight(quickedit_boxy, '#boxyhtml');
			}
		}
	});
}

function addToListview( listview_id, field_id )
{
	var data = "listview_id="+listview_id+'&field_id='+field_id;
	$.ajax({
		url: module.get_value('base_url') + module.get_value('module_link') + "/add_field_to_listview",
		type:"POST",
		data: data,
		dataType: "json",
		beforeSend: function(){
			$.blockUI({ message: '<div class="now-loading align-center"><img src="'+module.get_value('base_url')+user.get_value('user_theme')+'/images/loading.gif"><br />Loading, please wait...</div>' });  		
		},
		success: function(data){
			$('#message-container').html(message_growl(data.msg_type, data.msg));
			$('#alvf-'+field_id).remove();
			getListviews();
			$.unblockUI();
		}
	});
}

function deleteFieldGroup( fieldgroup_id )
{
	Boxy.ask("Delete selected Field Group?", ["Yes", "Cancel"],
	function( choice ) {
		if(choice == "Yes"){
			$.ajax({
				url: module.get_value('base_url')+"admin/fieldgroup/delete",
				type:"POST",
				dataType: "json",
				data: 'record_id='+fieldgroup_id,
				success: function(data){
					$('#message-container').html(message_growl(data.msg_type, data.msg));
					$('#fg-'+fieldgroup_id).remove();
				}
			});
		}
	},
	{
		title: "Delete Field Group"
	});
}

function deleteField( field_id )
{
	Boxy.ask("Delete selected Field?", ["Yes", "Cancel"],
	function( choice ) {
		if(choice == "Yes"){
			$.ajax({
				url: module.get_value('base_url')+"admin/field/delete",
				type:"POST",
				dataType: "json",
				data: 'record_id='+field_id,
				success: function(data){
					$('#message-container').html(message_growl(data.msg_type, data.msg));
					$('#f-'+field_id).remove();
				}
			}); 
		}
	},
	{
		title: "Delete Field"
	});
}

function deleteListview( listview_id )
{
	Boxy.ask("Delete selected Listview?", ["Yes", "Cancel"],
	function( choice ) {
		if(choice == "Yes"){
			$.ajax({
				url: module.get_value('base_url')+"admin/listviews/delete",
				type:"POST",
				dataType: "json",
				data: 'record_id='+listview_id,
				success: function(data){
					$('#message-container').html(message_growl(data.msg_type, data.msg));
					$('#lv-'+listview_id).remove();
				}
			}); 
		}
	},
	{
		title: "Delete Listview"
	});
}

function deleteFieldFromListview( listview_id, field_id ) 
{
	Boxy.ask("Delete selected Field from Listview?", ["Yes", "Cancel"],
	function( choice ) {
		if(choice == "Yes"){
			$.ajax({
				url: module.get_value('base_url')+ module.get_value('module_link') + "/delete_field_from_listview",
				type:"POST",
				dataType: "json",
				data: 'listview_id='+listview_id+'&field_id='+field_id,
				success: function(data){
					$('#message-container').html(message_growl(data.msg_type, data.msg));
					$('#lvf-'+field_id+listview_id).remove();
				}
			}); 
		}
	},
	{
		title: "Delete Listview Field"
	});
}

function fieldGroupSequence()
{
	var sequence = $('#fieldgroup-list tbody').sortable('serialize');
	var data = sequence+'&module_id='+module_id;
	simpleAjax( module.get_value('base_url')+ module.get_value('module_link') +"/fieldgroup_sequence", data )	
}

function listviewSequence()
{
	$('.sort-listview').each(function () {
		var listview_id = $(this).attr('lv_id');
		var sequence = $(this).sortable('serialize');
		var data = sequence+'&listview_id='+listview_id;
		if(sequence != "") simpleAjax( module.get_value('base_url')+ module.get_value('module_link') +"/listview_sequence", data );
	});
}

function fieldSequence(side, obj){
	$(obj.item).parents('.column-'+side).each(function () {
		var fg_id = $(this).attr('fg_id');
		var sequence = $(this).sortable('serialize');
		var data = sequence+'&fieldgroup_id='+fg_id+"&column="+side;
		if(sequence != "") simpleAjax( module.get_value('base_url')+ module.get_value('module_link') +"/field_sequence", data );
	});
}

function editField( fg_id, field_id)
{
	var sequence = "";
	if(field_id == -1){
		var ctr = 1;
		$('#fg-'+fg_id+' .field_label').each(function(){
			ctr++;
		});	
		sequence = "&sequence="+ctr + "&tabindex=" + ctr;
	}
	var module_id = $('#record_id').val();
	var data = "module_id="+module_id+"&record_id="+field_id+"&fieldgroup_id="+fg_id+sequence;
	showQuickEditForm( module.get_value('base_url') + "admin/field/quick_edit", data );
}

function editListviewColumn( listview_id, field_id)
{
	var data = "listview_id="+listview_id+"&field_id="+field_id;
	$.ajax({
		url: module.get_value('base_url') + module.get_value('module_link') + '/edit_listview_column',
		type:"POST",
		data: data,
		dataType: "json",
		beforeSend: function(){
			$.blockUI({ message: '<div class="now-loading align-center"><img src="'+module.get_value("base_url")+user.get_value('user_theme')+'/images/loading.gif"><br />Loading, please wait...</div>' });  		
		},
		success: function(data){
			if(data.msg != "") $('#message-container').html(message_growl(data.msg_type, data.msg));
			if(data.editform != ""){
				var width = $(window).width()*.3;
				quickedit_boxy = new Boxy('<div id="boxyhtml" style="width:'+width+'px">'+ data.editform +'</div>',
				{
					title: 'Edit Listview Column  Add/Edit',
					draggable: false,
					modal: true,
					center: true,
					unloadOnHide: true,
					afterShow: function(){ $.unblockUI(); },
					beforeUnload: function(){ $('.tipsy').remove(); }
				});	
			}
		}
	});
}

function lvec_save()
{
	var data = $('#editlistviewcolumn-form').serialize();
	simpleAjax( module.get_value('base_url')+ module.get_value('module_link') +"/save_listview_column", data );
	quickedit_boxy.hide().unload();
}

function toggleModuleInactive(button, module_id) {
	var visible;
	if(button.hasClass('icon-16-active')){
		button.removeClass('icon-16-active');
		button.addClass('icon-16-xgreen-orb')
		visible = "visible=1";
	}else{
		button.removeClass('icon-16-xgreen-orb');
		button.addClass('icon-16-active')
		visible = "visible=0";
	}
	
	data = visible+"&module_id="+module_id;
	simpleAjax( module.get_value('base_url')+ module.get_value('module_link') +"/toggle_module_state", data );
	$("#jqgridcontainer").jqGrid().trigger("reloadGrid");
	button.tipsy("hide");
}

function toggleVisibility(button, fieldgroup_id)
{
	var visible;
	
	if(button.hasClass('icon-16-active')){
		button.removeClass('icon-16-active');
		button.addClass('icon-16-xgreen-orb')
		visible = "visible=0";
	}else{
		button.removeClass('icon-16-xgreen-orb');
		button.addClass('icon-16-active')
		visible = "visible=1";
	}
	var data = visible+"&fieldgroup_id="+fieldgroup_id;
	simpleAjax( module.get_value('base_url')+ module.get_value('module_link') +"/fg_toggle_visibility", data );
}

function toggleDefaultListview( module_id, listview_id)
{
	$.ajax({
		url: module.get_value('base_url') + module.get_value('module_link') + "/default_listview",
		type:"POST",
		dataType: "json",
		data: 'listview_id='+listview_id+'&module_id='+module_id,
		success: function(data){
			$('#message-container').html(message_growl(data.msg_type, data.msg));
			getListviews();
		}
	}); 
}

function editListview( listview_id )
{
	var module_id = $('#record_id').val();
	var data = "record_id="+listview_id+"&module_id="+module_id;
	showQuickEditForm( module.get_value('base_url') + "admin/listviews/quick_edit", data );
} 

function editPicklist(picklist_id, field_id)
{
	var data = "record_id="+picklist_id+"&field_id="+field_id;
	showQuickEditForm( module.get_value('base_url') + "admin/picklist/quick_edit", data ); 
}

function editFMLink(fm_link_id, field_id)
{
	var data = "record_id="+fm_link_id+"&field_id="+field_id;
	showQuickEditForm( module.get_value('base_url') + "admin/field_module_link/quick_edit", data ); 
}

function editMultiselect(multiselect_id, field_id)
{
	var data = "record_id="+multiselect_id+"&field_id="+field_id;
	showQuickEditForm( module.get_value('base_url') + "admin/field_multiselect/quick_edit", data ); 
}

function editOptionSet(field_option_id, field_id)
{
	var data = "record_id="+field_option_id+"&field_id="+field_id;
	showQuickEditForm( module.get_value('base_url') + "admin/field_radio_options/quick_edit", data ); 
}

function editAutocomplete(field_option_id, field_id)
{
	var data = "record_id="+field_option_id+"&field_id="+field_id;
	showQuickEditForm( module.get_value('base_url') + "admin/field_autocomplete/quick_edit", data ); 
}


function quickedit_boxy_callback( module ){
	if(module == "field" || module == "fieldgroup") getFieldGroups();
	if(module == "field" || module == "fieldgroup") getFieldGroups();
	if(module == "listviews") getListviews();
}

function clone_module( record_id )
{
	if( user.get_value('add_control') == 1 ){
		$.ajax({
			url: module.get_value('base_url') + module.get_value('module_link') + "/clone_module",
			type:"POST",
			dataType: "json",
			beforeSend: function(){
				$.blockUI({ message: '<div class="now-loading align-center"><img src="'+module.get_value("base_url")+user.get_value('user_theme')+'/images/loading.gif"><br />Cloaning Module, please wait...</div>'});  		
			},
			data: 'record_id='+record_id,
			success: function(data){
				$.unblockUI();
				if( data.msg != "" ) message_growl(data.msg_type, data.msg);
				if (typeof data.page_refresh != 'undefined' && data.page_refresh == "true"){
					page_refresh();
				}
			}
		}); 
	}
	else{
		message_growl('You do not have sufficient privilege to execute this operation', 'error');
	}
} 