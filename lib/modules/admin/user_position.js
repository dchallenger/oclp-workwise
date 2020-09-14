$(document).ready(function () {
	$('#record-form').append(
			$('<input type="hidden" name="reporting_to-temp" />').val($('#reporting_to').val())
		);

	get_reporting_to();		

	$('#company_id').change(get_reporting_to);
	
	if( module.get_value('view') == "edit" ){
		$('#record_id').change( get_jd_form );
		if( $('#record_id').val() != "-1" ) get_jd_items();
	}
	
	if( module.get_value('view') == "index" ){
		$('.print-jd').live('click', function(){
			url = module.get_value('base_url') + $(this).attr('module_link') + '/print_jd/' + $(this).parent().parent().parent().attr("id");
			window.location = url;
		});
	}

    if( module.get_value('view') == "my_jd"){
        $('.or-cancel').hide();
    }	
});

function delete_approver(obj, module_id){
	obj.parent().parent().parent().remove();
	var no_approvers = $('.approverno-'+module_id).length;
	$('.approverno-'+module_id).each(function(){
		$(this).val(no_approvers);
		$(this).next().html(no_approvers);
		no_approvers--;
	});
}

function get_reporting_to() {
	var reporting_to_val = $('input[name="reporting_to-temp"]').val();

	$.ajax({
		url: module.get_value('base_url') + module.get_value('module_link') + '/get_reporting_to',
		type: 'post',
		dataType: 'json',
		data: $('#record-form').serialize(),
		success: function (response) {
			$('#reporting_to option').remove();
			$('#reporting_to').append('<option value="">Select&hellip;</option>');

			$.each(response.positions, function(index, position) {
				if(position.position_code != "") position.position = position.position + ' - ' + position.position_code;
				$('#reporting_to')
					.append(
						$('<option></option>')
							.val(position.position_id)
							.text(position.position)
					);
			});
			$('#reporting_to').val(reporting_to_val);
		}
	});	
}

/**
 * Get the JD form
 * 
 * @return void
 */
function get_jd_form(  ) {
	$.ajax({
		url: module.get_value('base_url') + module.get_value('module_link') + "/get_jd_form",
		type:"POST",	
		data: "record_id="+$('#record_id').val(),
		dataType: "json",
		beforeSend: function(){
			$.blockUI({ message: '<div class="now-loading align-center"><img src="'+module.get_value('base_url')+user.get_value('user_theme')+'/images/loading.gif"><br />Loading, please wait...</div>' });  		
		},
		success: function( data ){
			$('.jd-div').html( data.jd_form );
			$.unblockUI();			
		}
	});
}

/**
 * Quick Add/Edit JD Item
 * 
 * @return void
 */
function edit_jd_item( jditem_id, position_id ){
	var data = 'record_id='+jditem_id+'&position_id='+position_id;
	var module_url = module.get_value('base_url') + 'admin/jd_item/quick_edit';
	showQuickEditForm( module_url, data);
}

/**
 * Quick Add/Edit JD Item Detail
 * 
 * @return void
 */
function edit_jditem_detail( jditem_detail_id, jditem_id ){
	var data = 'record_id='+jditem_detail_id+'&jditem_id='+jditem_id;
	var module_url = module.get_value('base_url') + 'admin/jditem_detail/quick_edit';
	showQuickEditForm( module_url, data);
}

/**
 * Get the JD Items
 * 
 * @return void
 */
function get_jd_items(){
	$.ajax({
		url: module.get_value('base_url') + module.get_value('module_link') + "/get_jd_items",
		type:"POST",	
		data: "record_id="+$('#record_id').val(),
		dataType: "json",
		beforeSend: function(){
			$('.jditem-div').block({ message: '<div class="now-loading align-center"><img src="'+module.get_value('base_url')+user.get_value('user_theme')+'/images/loading.gif"><br />Loading, please wait...</div>' });  		
		},
		success: function( data ){
			$('.jditem-div').html( data.jd_items );
			$('.jditem-div').unblock();			
		}
	});
}

/**
 * Delete JD Item
 * 
 * @return void
 */
function delete_jditem( jditem_id )
{
	Boxy.ask("Delete selected JD Item?", ["Yes", "Cancel"],
	function( choice ) {
		if(choice == "Yes"){
			$.ajax({
				url: module.get_value('base_url')+"admin/jd_item/delete",
				type:"POST",
				dataType: "json",
				data: 'record_id='+jditem_id,
				success: function(data){
					$('#message-container').html(message_growl(data.msg_type, data.msg));
					get_jd_items();	
				}
			});
		}
	},
	{
		title: "Delete JD Item Group"
	});
}

/**
 * Delete JD Item
 * 
 * @return void
 */
function delete_jditem_detail( jditem_detail_id )
{
	Boxy.ask("Delete selected JD Item Detail?", ["Yes", "Cancel"],
	function( choice ) {
		if(choice == "Yes"){
			$.ajax({
				url: module.get_value('base_url')+"admin/jditem_detail/delete",
				type:"POST",
				dataType: "json",
				data: 'record_id='+jditem_detail_id,
				success: function(data){
					$('#message-container').html(message_growl(data.msg_type, data.msg));
					get_jd_items();	
				}
			});
		}
	},
	{
		title: "Delete JD Item Group"
	});
}

/**
 * Return listview UI for position/approver.
 * 
 * @param  string approver_module_id Module ID to assign the approver to.
 * @return void
 */
function get_positions_boxy(approver_module_id) {
	$.ajax({
		url: module.get_value('base_url') + module.get_value('module_link') + "/positions_notification_boxy",
		type:"POST",	
		data: "fmlinkctr="+related_module_boxy_count+"&module_id=" + approver_module_id,
		dataType: "html",
		beforeSend: function(){
			$.blockUI({ message: '<div class="now-loading align-center"><img src="'+module.get_value('base_url')+user.get_value('user_theme')+'/images/loading.gif"><br />Loading, please wait...</div>' });  		
		},
		success: function(data){
			related_module_boxy[related_module_boxy_count] = new Boxy('<div id="related_module_boxy-'+related_module_boxy_count+'-container">'+ data +'</div>',
			{
				title: 'Select Position',
				draggable: false,
				modal: true,
				center: true,
				unloadOnHide: true,
				show: false,
				afterShow: function(){ $.unblockUI(); },
				beforeUnload: function(){ $('.tipsy').remove(); }
			});
			boxyHeight(related_module_boxy[related_module_boxy_count], '#related_module_boxy-'+related_module_boxy_count+'-container');
			related_module_boxy_count++;					
		}
	});	
}

function quickedit_boxy_callback( e ){
	get_jd_items();	
}

function add_approver(position_id, module_id, position, fmlinkctr)
{
	if ($('input[name="approver[' + module_id + '][' + position_id +']"]').size() > 0) {
		$.unblockUI({ onUnblock: function() { message_growl('attention', 'Position already selected.') }});
	} else {
		var row_ctr = 1;
		$('.approver-'+module_id).each(function(){
			row_ctr++;
		});
		new_row = '<tr class="approver-'+module_id+'"><td class="odd">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' + position + '</td>';
		new_row += '<td class="even" align="center"><input type="hidden" value="'+row_ctr+'" name="notifications[' + module_id + '][' + position_id +'][approver_no]">'+row_ctr+'</td>';
		new_row += '<td class="odd" align="center"><input type="checkbox" name="notifications[' + module_id + '][' + position_id +'][approver]" value="1"/></td>';
		new_row += '<td class="even" align="center"><input type="checkbox" name="notifications[' + module_id + '][' + position_id +'][email]" value="1"/></td></tr>';

		$('#module-notification-' + module_id).after(new_row);

		related_module_boxy[fmlinkctr].hide().unload();	
		$.unblockUI();	
	}
}
