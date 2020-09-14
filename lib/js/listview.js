/*
 * Author: Harold D. Ramirez
 * Desc: Default functions and actions for grids/listview
*/
$(document).ready( function () {					 
	$('.jqgrow').live('dblclick', function(){
		record_action("detail", $(this).attr('id'), '');	
	});
	
	$('.icon-16-info').live('click', function(){
		record_action("detail", $(this).parent().parent().parent().attr("id"), $(this).attr('module_link'));
	});
	
	$('.icon-16-edit').live('click', function(){
		record_action("edit", $(this).parent().parent().parent().attr("id"), $(this).attr('module_link'));
	});
	
	$('.icon-16-add-listview').live('click', function(){
		record_action("edit", -1, $(this).attr('module_link'), $(this).attr('related_field'), $(this).attr('related_field_value'));
	});
	
	$('.icon-16-clock-extend"').live('click', function(){
		record_action("edit", -1, $(this).attr('module_link'), $(this).attr('related_field'), $(this).attr('related_field_value'), $(this).parent().parent().parent().attr("id"));
	});
	
	$('.print-record').live('click', function(){
		url = module.get_value('base_url') + $(this).attr('module_link') + '/print_record/' + $(this).parent().parent().parent().attr("id");
		window.location = url;
	});
	
	$('.delete-single').live('click', function(){
		if(user.get_value('delete_control') == 1)
		{
			var record_id = new Array();
			record_id[0] = $(this).parents('tr').attr("id");
			delete_record(record_id, $(this).attr('module_link'), $(this).attr('container'));
		}
		else{
			$('#message-container').html(message_growl('error', 'You dont have delete privileges! Please contact the System Administrator.'));
		}
	});
	
	$('.delete-array').live('click', function(){
		if(user.get_value('delete_control') == 1)
		{
			var selected = $("#"+$(this).attr('container')).jqGrid("getGridParam", "selarrrow");
			
			if(selected.length > 0)
			{
				if(selected[0] == '')
				{
					//remove the value for "check all"
					selected.shift();
				}
				delete_record(selected, $(this).attr('module_link'), $(this).attr('container'));
			}
			else{
				$('#message-container').html(message_growl('attention', 'No record was selected!'))
			}
		}
		else{
			$('#message-container').html(message_growl('error', 'You dont have delete privileges! Please contact the System Administrator.'));
		}
	});
	
	$(".search-trigger").live("click", function(e) {
		$("form.search-options").toggleClass("hidden");
		$(this).parent(".search-form").toggleClass("options-open");
		e.preventDefault();                            
	});
	
	$('#search-btn').live('click', function (){
		$(this).parent().trigger('submit');
		return false; 									 
	});
	
	$('.search').live('submit', function(){
		search_grid( $(this).attr('jqgridcontainer') );
		return false;									
	});
	
	$('.icon-16-send-email').live('click', function () {
		var id = $(this).parent().parent().parent().attr("id");
		Boxy.ask("Send email?", ["Yes", "Cancel"],
			function( choice ) {
				if(choice == "Yes"){
					$.ajax({
						url: module.get_value('base_url') + module.get_value('module_link') + '/send_email',
						data: 'record_id=' + id,
						type: 'post',
						beforeSend: function(){
							$('.jqgfirstrow').removeClass('ui-state-highlight');
							$.blockUI({
								message: '<div class="now-loading align-center"><img src="'+module.get_value('base_url')+user.get_value('user_theme')+'/images/loading.gif"><br />Sending email, please wait...</div>'
							});
						},
						success: function(data){
							$.unblockUI();
							$('#message-container').html(message_growl(data.msg_type, data.msg));
							$("#jqgridcontainer").jqGrid().trigger("reloadGrid");
						}			
					});
				}
			},
			{
				title: "Send Email"
			}
		);
	});

	
	$('.module-export').live('click', function () {
		$.ajax({
			url: module.get_value('base_url') + 'admin/export_query/module_export_options',
			data: 'module_id=' + module.get_value('module_id'),
			type: 'post',
			dataType: 'json',
			beforeSend: function(){
				$.blockUI({
					message: '<div class="now-loading align-center"><img src="'+module.get_value('base_url')+user.get_value('user_theme')+'/images/loading.gif"><br />Loading, please wait...</div>'
				});  		
			},
			success: function(data){
				$.unblockUI();
				
				if(data.html != ""){
					var width = $(window).width()*.7;
					quickedit_boxy = new Boxy('<div id="boxyhtml" style="width:'+width+'px; height:400px;">'+ data.html +'</div>',
					{
						title: 'Select Export Type',
						draggable: false,
						modal: true,
						center: true,
						unloadOnHide: true,
						beforeUnload: function (){
							$('.tipsy').remove();
						}
					});
					boxyHeight(quickedit_boxy, '#boxyhtml');
				}
			}
		});
	});

	$('#quick_export_query_id').live('change', function() {
		if ($(this).val() != '') {
			$.ajax({
				url: module.get_value('base_url') + 'admin/export_query/get_query_fields',
				type: 'post',
				dataType: 'json',
				data: 'export_query_id=' + $(this).val(),
				success: function(response) {
					$('#field-container').empty();
					$('#field-container').html(response.html);

					$('#export-buttons').removeClass('hidden');
				}
			});
		}
	});

	$('.module-import').live('click', function () {
		$.ajax({
			url: module.get_value('base_url') + 'admin/import/module_import_options',
			data: 'module_id=' + module.get_value('module_id'),
			type: 'post',
			dataType: 'json',
			beforeSend: function(){
				$.blockUI({
					message: '<div class="now-loading align-center"><img src="'+module.get_value('base_url')+user.get_value('user_theme')+'/images/loading.gif"><br />Loading, please wait...</div>'
				});  		
			},
			success: function(data){
				$.unblockUI();
				
				if(data.html != ""){
					var width = $(window).width()*.2;
					quickedit_boxy = new Boxy('<div id="boxyhtml" style="width:'+width+'px; height:100px;">'+ data.html +'</div>',
					{
						title: 'Import',
						draggable: false,
						modal: true,
						center: true,
						unloadOnHide: true,
						beforeUnload: function (){
							$('.tipsy').remove();
						}
					});
					boxyHeight(quickedit_boxy, '#boxyhtml');
				}
			}
		});
	});

	$('#import-form').die('submit');
	
});

function record_action(action, record_id, action_link, related_field, related_field_val, related_id)
{
	update_gridsearch_parameters();
	$.blockUI({
		message: '<div class="now-loading align-center"><img src="'+module.get_value('base_url')+user.get_value('user_theme')+'/images/loading.gif"><br />Loading, please wait...</div>'
	});
	if(related_field != "")
	{
		$('#record-form').append('<input type="hidden" name="'+ related_field +'" value="'+ related_field_val +'" >');
	}
	if(related_id != "")
	{
		$('#record-form').append('<input type="hidden" name="related_id" value="'+ related_id +'" >');
	}
	$('#record_id').val(record_id);
	if(action_link == "") action_link = module.get_value('module_link');
	$('#record-form').attr("action", module.get_value('base_url') + action_link + "/" + action);
	
	switch( action ){
		case "detail":
			$('#record-form').submit();	
			break;
		case "edit/rehire":
		case "edit":
			if(user.get_value('edit_control') == 1){
				$('#form-div').html('');
				$('#record-form').submit();	
			}
			else{
				$.unblockUI();
				$('#message-container').html(message_growl('attention', 'Insufficient Access grant!\nPlease contact the system administrator.'))
			}
			break;
		case "duplicate_record":
			if(user.get_value('edit_control') == 1){
				$('#record-form').attr("action", module.get_value('base_url') + action_link + "/edit");
				$('#form-div').html('');
				$('#record-form').append('<input name="duplicate" value="1">');
				$('#record-form').submit();	
			}
			else{
				$.unblockUI();
				$('#message-container').html(message_growl('attention', 'Insufficient Access grant!\nPlease contact the system administrator.'))
			}
			break;
			break;
	}	
}

function delete_record(selected, action_url, container){
	Boxy.ask("Delete "+ selected.length +" selected record(s)?", ["Yes", "Cancel"],
		function( choice ) {
			if(choice == "Yes"){
				$.ajax({
					url: module.get_value('base_url') + action_url +"/delete",
					type:"POST",
					dataType: "json",
					data: 'record_id='+selected,
					beforeSend: function(){
						$('.jqgfirstrow').removeClass('ui-state-highlight');
						$.blockUI({
							message: '<div class="now-loading align-center"><img src="'+module.get_value('base_url')+user.get_value('user_theme')+'/images/loading.gif"><br />Deleting, please wait...</div>'
						});
					},
					success: function(data){
						$.unblockUI();
						$('#message-container').html(message_growl(data.msg_type, data.msg));
						$("#"+container).jqGrid().trigger("reloadGrid");
					}
				});
			}
		},
		{
			title: "Delete Record"
		});
}

function search_grid( jqgridcontainer )
{
	var searchfield;
	var searchop;
	var searchstring = $('.search-'+ jqgridcontainer ).val() != "Search..." ? $('.search-'+ jqgridcontainer ).val() : "";
	
	if( $("form.search-options-"+ jqgridcontainer).hasClass("hidden") ){
		searchfield = "all";
		searchop = "";
	}else{
		searchfield = $('#searchfield-'+jqgridcontainer).val();
		searchop = $('#searchop-'+jqgridcontainer).val()	
	}

	//search history
	$('#prev_search_str').val(searchstring);
	$('#prev_search_field').val(searchfield);
	$('#prev_search_option').val(searchop);
	$('#prev_search_page').val( $("#"+jqgridcontainer).jqGrid("getGridParam", "page") );

	$("#"+jqgridcontainer).jqGrid('setGridParam', 
	{
		search: true,
		postData: {
			searchField: searchfield, 
			searchOper: searchop, 
			searchString: searchstring
		}, 	
	}).trigger("reloadGrid");	
}

function post_gridcomplete_function(data, container)
{
	if(data.msg != "")
	{
		$('#message-container').html(message_growl(data.msg_type, data.msg));
	}
	
	$(container).jqGrid("setGridWidth", $(container).parent("div").width());
}

function update_gridsearch_parameters( )
{
	var jqgridcontainer = 'jqgridcontainer';
	var searchfield;
	var searchop;
	var searchstring = $('.search-'+ jqgridcontainer ).val() != "Search..." ? $('.search-'+ jqgridcontainer ).val() : "";
	
	if( $("form.search-options-"+ jqgridcontainer).hasClass("hidden") ){
		searchfield = "all";
		searchop = "";
	}else{
		searchfield = $('#searchfield-'+jqgridcontainer).val();
		searchop = $('#searchop-'+jqgridcontainer).val()	
	}

	//search history
	$('#prev_search_str').val(searchstring);
	$('#prev_search_field').val(searchfield);
	$('#prev_search_option').val(searchop);
	$('#prev_search_page').val( $("#"+jqgridcontainer).jqGrid("getGridParam", "page") );
}