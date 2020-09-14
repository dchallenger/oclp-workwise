$(document).ready(function(){
	/** ajax_save on Enter **/
	$('#record-form').keypress(function (e) {		
		if (e.which == 13 && !$('.chzn-search input').is(':focus')) {
			//$('a[rel="record-save"]').first().trigger('click');
		}
	});

	var dummy_upload = module.get_value('base_url') + user.get_value('user_theme') +"/images/no-photo.jpg";
	$( ".image-wrap .image-delete" ).hide();
	$( ".image-wrap" ).live('mouseenter', function (){
		var src = $( this ).find('img').attr('src');
		if( src != dummy_upload ) $( this ).find( ".image-delete" ).show();
	});
	
	$( ".image-wrap" ).live('mouseleave', function (){
		$( this ).find( ".image-delete" ).hide();
	});
	
	$('.image-delete').live('click', function(){
		var delete_button = $( this );
		var field = $( this ).attr('field');
		var has_class = $( this ).hasClass('multi');
		var del_upload_id = $( this ).attr('upload_id');
		Boxy.ask("Are you sure you want to delete uploaded file?", ["Yes", "Cancel"],
		function( choice ) {
			if(choice == "Yes"){
				if( has_class ){
					var upload_ids = $('input[name="'+field+'"]').val().split(",");
					var temp = new Array();
					var temp_ctr = 0;
					for (var i in upload_ids){
						if( upload_ids[i] != del_upload_id ){
							temp[temp_ctr] = upload_ids[i];
							temp_ctr++;
						}
					}
					$('input[name="'+field+'"]').val(temp);
					delete_button.parent().remove();
					
					if(temp.length == 0){
						var img = '<div class="nomargin image-wrap"><img src="'+ dummy_upload +'" width="100px"></div>';
						$('#'+field+'-upload-container').append(img);
					}
				}
				else{
					$( '#' + field ).val('');
					$( '#file-' + field ).attr('src', dummy_upload);
				}
			}
		},
		{
			title: "Delete Record"
		});
	});
});

//global js var in edit view
var error = new Array();
var error_ctr = 0;
var related_module_boxy = new Array();
var related_module_boxy_count = 0;
var quickedit_boxy = "";
var module_id = 0;


//added variable msg to change the text on boxy. by default it is "Send request to approver?"
function save_and_email(is_wizard, msg) {
	if(msg==null)
		msg="Send request to approver?";

    Boxy.ask(msg, ["Yes", "Cancel"],
        function( choice ) 
        {
            if(choice == "Yes") {
                ajax_save('email', is_wizard, function () { window.location = module.get_value('base_url') + module.get_value('module_link'); });
            }
        },
        {
                title: "Send Request"
        }
    );
}

function ajax_save( on_success, is_wizard , callback ){
	if( is_wizard == 1 ){
		var current = $('.current-wizard');
		var fg_id = current.attr('fg_id');
		var ok_to_save = eval('validate_fg'+fg_id+'()')
	}
	else{
		ok_to_save = validate_form();
	}
	
	if( ok_to_save ) {		
		$('#record-form').find('.chzn-done').each(function (index, elem) {
			if (elem.multiple) {
				if ($(elem).attr('name') != $(elem).attr('id') + '[]') {
					$(elem).attr('name', $(elem).attr('name') + '[]');
				}
				
				var values = new Array();
				for(var i=0; i< elem.options.length; i++) {
					if(elem.options[i].selected == true) {
						values[values.length] = elem.options[i].value;
					}
				}
				$(elem).val(values);
			}
		});

		var data = $('#record-form').serialize();
		var saveUrl = module.get_value('base_url')+module.get_value('module_link')+"/ajax_save"		

		$.ajax({
			url: saveUrl,
			type:"POST",
			data: data,
			dataType: "json",
			/**async: false, // Removed because loading box is not displayed when set to false **/
			beforeSend: function(){
					show_saving_blockui();
			},
			success: function(data){
				if(  data.record_id != null ){
					//check if new record, update record_id
					if($('#record_id').val() == -1 && data.record_id != ""){
						$('#record_id').val(data.record_id);
						$('#record_id').trigger('change');
						if( is_wizard == 1 ) window.location = module.get_value('base_url') + module.get_value('module_link') + '/edit/' + data.record_id;
					}
					else{
						$('#record_id').val( data.record_id );
					}
				}

				if( data.msg_type != "error"){					
					switch( on_success ){
						case 'back':
							go_to_previous_page( data.msg );
							break;
						case 'email':							
							if (data.record_id > 0 && data.record_id != '') {
								// Ajax request to send email.                    
								$.ajax({
									url: module.get_value('base_url') + module.get_value('module_link') + '/send_email',
									data: 'record_id=' + data.record_id,
									dataType: 'json',
									type: 'post',
									async: false,
									beforeSend: function(){
											show_saving_blockui();
										},								
									success: function () {
									}
								});
							}							
							//custom ajax save callback
							if (typeof(callback) == typeof(Function)) callback( data );
						default:
							if (typeof data.page_refresh != 'undefined' && data.page_refresh == "true"){
									window.location = module.get_value('base_url') + module.get_value('module_link') + '/edit/' + data.record_id;
							}
							else{
								//generic ajax save callback
								if(typeof window.ajax_save_callback == 'function') ajax_save_callback();
								//custom ajax save callback
								if (typeof(callback) == typeof(Function)) callback( data );
								$.unblockUI({ onUnblock: function() { message_growl(data.msg_type, data.msg) } });
							}
							break;
					}	
				}
				else{
					$.unblockUI({ onUnblock: function() { message_growl(data.msg_type, data.msg) } });
				}
			}
		});
	}
	else{
		return false;
	}
	return true;
}

function getRelatedModule(field_id, fieldname){
	$.ajax({
		url: module.get_value('base_url') + module.get_value('module_link') + "/get_related_module",
		type:"POST",
		data: "field_id="+field_id+"&fieldname="+fieldname,
		dataType: "json",
		beforeSend: function(){
			$.blockUI({ message: '<div class="now-loading align-center"><img src="'+module.get_value('base_url')+user.get_value('user_theme')+'/images/loading.gif"><br />Loading, please wait...</div>' });  		
		},
		success: function(data){
			if(data.msg != ""){
				$.unblockUI();
				$('#message-container').html(message_growl(data.msg_type, data.msg));
			}
			if(data.link != "") showRelatedModule(data.link, data.short_name, fieldname, data.column);
		}
	}); 
}

function showRelatedModule(related_module_link, related_module, fieldname, column)
{
	$.ajax({
		url: module.get_value('base_url') + related_module_link + "/show_related_module",
		type:"POST",
		data: "fieldname="+fieldname+"&column="+column+'&module_link='+related_module_link+"&fmlinkctr="+related_module_boxy_count,
		dataType: "html",
		beforeSend: function(){
		},
		success: function(data){
			related_module_boxy[related_module_boxy_count] = new Boxy('<div id="related_module_boxy-'+related_module_boxy_count+'-container">'+ data +'</div>',
			{
				title: related_module,
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

function showQuickEditForm( module_url, data)
{
	$.ajax({
		url: module_url,
		type:"POST",
		data: data,
		dataType: "json",
		beforeSend: function(){
			$.blockUI({ message: '<div class="now-loading align-center"><img src="'+module.get_value('base_url')+user.get_value('user_theme')+'/images/loading.gif"><br />Loading, please wait...</div>' });  		
		},
		success: function(data){
			$.unblockUI();
			if(data.msg != "") $('#message-container').html(message_growl(data.msg_type, data.msg));
			if(data.quickedit_form != ""){
				var width = $(window).width()*.7;
				quickedit_boxy = new Boxy('<div id="boxyhtml" style="width:'+width+'px">'+ data.quickedit_form +'</div>',
				{
					title: 'Quick Add/Edit',
					draggable: false,
					modal: true,
					center: true,
					unloadOnHide: true,
					beforeUnload: function (){ 
						$('.tipsy').remove();
						if (typeof(UnloadBindEvents) == typeof(Function)) {
			               UnloadBindEvents();
			            }
					},
					afterShow: function () {
						if (typeof(init_quickedit_datepick) == typeof(Function)) {
							init_quickedit_datepick();                                
						}
						if (typeof(BindLoadEvents) == typeof(Function)) {
			               BindLoadEvents();
			            }
			            if (typeof(CustomBindLoadEvents) == typeof(Function)) {
			               CustomBindLoadEvents();
			            }
					}
				});
				boxyHeight(quickedit_boxy, '#boxyhtml');
			}
		}
	});
}

function quickedit_callback( record_id )
{
	
}

function clearField( name )
{
	$('input[name="'+name+'"]').val('');
	$('input[name="'+name+'-name"]').val('');
	$('input[name="'+name+'"]').trigger('change');
}

/******************************************************/
/* Toggle Field Group Visibility
/******************************************************/
function toggleFieldGroupVisibility( obj )
{
    if ( obj.parent().parent().find('.col-1-form,.col-2-form').is( ":hidden" ) ){
        obj.parent().parent().find('.col-1-form,.col-2-form').removeClass( "hidden" );
        obj.text("Hide");
    }
    else{
        obj.parent().parent().find('.col-1-form,.col-2-form').addClass( "hidden" );
        obj.text("Show");
    }
}

function disable_field( field )
{
	field.attr('disabled','disabled');
}

function enable_field( field )
{
	field.attr('disabled','');
}