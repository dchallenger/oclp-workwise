$(document).ready(function () {
	$('.trigger-edit').click(edit_checklist);
	$('.trigger-delete').click(delete_checklist);
	$('.trigger-print').click(print_checklist);
	$('.trigger-quickclaim').click(print_quickclaim);
	$('.trigger-coe').click(print_selected_coe);

	if(module.get_value('client_no') == 2 && module.get_value('view') == 'edit')
		get_default_signatories();

	 if(module.get_value('record_id') != -1 && module.get_value('view') == 'edit'){
	 	var fix_name=$('#employee_id option[value="' + $('#employee_id').val() + '"]').text();
	 	$('#employee_id').replaceWith('<input type="hidden" id="employee_id" name="employee_id" value="'+$('#employee_id').val()+'" /><input type="textbox" readonly=true  style="width: 513px;" value="'+fix_name+'"/>');
	 }

	 $('.cancel_clearance').live('click',function(){

	 	var id = $(this).parent().parent().parent().attr('id');

	 	Boxy.ask("Are you sure you want to cancel the clearance? All changes to the employee will be reverted.", ["Yes", "No"],function( choice ) {
        if(choice == "Yes"){
              
			 	$.ajax({
			        url: module.get_value('base_url')+module.get_value('module_link')+'/cancel_clearance',
			        type: 'post',
			        dataType: 'json',
			        data: 'record_id=' + id,
			        success: function (response) {

			        	if( response.msg_type == "success" ){

			        		$('#jqgridcontainer').trigger('reloadGrid');

			        	}

			        	message_growl(response.msg_type,response.msg);

			        }
			    });

              
            }
        },
        {
            title: "Cancel Clearance"
        });

	 });
	 
});

function go_back(){
        window.location = module.get_value('base_url') + 'employee/clearance';
}

function edit_checklist() {
	submit_to_submodule('edit', this);
}

function delete_checklist() {
	submit_to_submodule('delete', this);
}

function print_checklist() {
	submit_to_submodule('print_record', this);
}

function print_quickclaim(){
	//submit_to_submodule('print_record', this);
	submit_to_submodule('print_quickclaim', this);
}

function print_selected_coe(){
	var clearance_id = module.get_value('record_id');
	// var clearance_id =  $('form[name="print-coe"] input[name="coe_clearance_id"]').val();
	// var coe_template_id =  $('form[name="print-coe"] select[name="coe_template_id"]').val();
	var coe_template_id =  1;
	if( coe_template_id != "" ){
		// var url = module.get_value('base_url') + 'employee/quickclaim_received/print_coe_template/' + clearance_id + '/' + coe_template_id;
		var url = module.get_value('base_url') + 'employee/quickclaim_received/print_coe_template/' + clearance_id;
		window.open( url, '_blank');
	}
	else{
		Boxy.ask("Please select a template to use?", ["Cancel"],
		function( choice ) {
			
		},
		{
			title: "Select Template"
		});
	}
}

function print_coe(){
	
		var record_id = module.get_value('record_id');

		$.ajax({
			url: module.get_value('base_url') + 'employee/quickclaim_received/get_template_form',
			data: 'record_id=' + record_id,
			dataType: 'json',
			type: 'post',
			beforeSend: function(){
				$.blockUI({
					message: '<div class="now-loading align-center"><img src="'+module.get_value('base_url')+user.get_value('user_theme')+'/images/loading.gif"><br />Loading, please wait...</div>'
				});  		
			},			
			success: function ( data ) {				
				$.unblockUI();	
					template_form = new Boxy('<div id="boxyhtml" style="">'+ data.form +'</div>',
					{
						title: 'Print Manager',
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
		});	

}

function submit_to_submodule(action, obj) {
	var rel = $(obj).parents('li').attr('rel');
    
	if (rel == '#') {
		return false;
	} else {    
		$('body').append($('<form></form>').attr('name', 'dummy-form').attr('method', 'post'));

		url = module.get_value('base_url') + rel + '/' + action;

		$('form[name="dummy-form"]')
			.append($('<input type="hidden"></input>').attr('name', 'employee_id').val($('#employee_id').val()))
			.append($('<input type="hidden"></input>').attr('name', 'employee_clearance_id').val($('#record_id').val()))
			.attr('action', url).submit();                				
	}
}

function get_default_signatories()
{
	$.ajax({
		url: module.get_value('base_url')+module.get_value('module_link')+'/get_default_signatories',
		dataType: 'json',
		success: function(response) {
			if(response.status == 'success') {
				var default_signatories = response.signatories;

				for(var i in default_signatories)
					 // $('option[value="'+default_signatories[i].approver_id+'"]').attr('selected', 'selected');
				
				$('#signatories').trigger('liszt:updated');
			}
		}
	});
}

function ajax_save( on_success, is_wizard , callback ){
    if( is_wizard == 1 ){
        var current = $('.current-wizard');
        var fg_id = current.attr('fg_id');
        var ok_to_save = eval('validate_fg'+fg_id+'()')
    }
    else{
    	var signatories = $('#signatories').val();
	    if (signatories == null) {
	    	add_error('signatories', 'Signatories', "This field is mandatory.");
	    };
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
                             window.location = module.get_value('base_url') + module.get_value('module_link') + '/detail/' + data.record_id;
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
                                 // go_to_previous_page( data.msg );
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
