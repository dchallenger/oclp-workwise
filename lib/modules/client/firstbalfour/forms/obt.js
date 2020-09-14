$( document ).ready(function(){
	bindOBTEvents();
});

function bindOBTEvents(){
	window.onload = function(){
		if( module.get_value('view') == 'index' ){

			if( $('ul#grid-filter li').length > 0 ){

	            $('ul#grid-filter li').each(function(){ 

	                if( $(this).hasClass('active') ){

	                    if($(this).attr('filter') == 'for_approval'){
	                       $('.status-buttons').parent().show();
	                    }
	                    else{
	                       $('.status-buttons').parent().hide();
	                    }

	                }
	            });

	        }
	        else{
	            $('.status-buttons').parent().hide();
	        }

		}
	}

	if( module.get_value('module_link') == "employee/dtr" ){
		$('input[name="time_start"]').timepicker({
			showAnim: 'slideDown',
			showOn: "both",
			timeFormat: 'hh:mm tt',
			buttonImage: module.get_value('base_url') + user.get_value('user_theme') + "/icons/calendar-month.png",
			buttonImageOnly: true,
			buttonText: '',
			hourGrid: 4,
			minuteGrid: 10,
			ampm: true,
			}).keyup(function(e) {
			if(e.keyCode == 8 || e.keyCode == 46) {
			$.datepicker._clearDate(this);
			}
			});
			$('input[name="time_end"]').timepicker({
			showAnim: 'slideDown',
			showOn: "both",
			timeFormat: 'hh:mm tt',
			buttonImage: module.get_value('base_url') + user.get_value('user_theme') + "/icons/calendar-month.png",
			buttonImageOnly: true,
			buttonText: '',
			hourGrid: 4,
			minuteGrid: 10,
			ampm: true,
			}).keyup(function(e) {
			if(e.keyCode == 8 || e.keyCode == 46) {
			$.datepicker._clearDate(this);
			}
		}); 
	}

	validation_check();

	if (user.get_value('post_control') == 0){
		if ($('select[name="employee_id"]').length != 0){
			$('label[for="employee_id"]').append('<div class="text-input-wrap"><input id="employee_id" class="input-text" type="hidden" value="'+$('#employee_id option:selected').val()+'" name="employee_id"><input id="employee_name" class="input-text" type="text" value="'+$('#employee_id option:selected').text()+'" name="employee_name" disabled="disabled"></div>');
			$('label[for="employee_id"]').next().remove();
		}
	}else if(user.get_value('post_control') == 1){
		
		if ($('#filter').val() == 'personal') {
			$('label[for="employee_id"]').append('<div class="text-input-wrap"><input id="employee_id" class="input-text" type="hidden" value="'+$('#employee_id option:selected').val()+'" name="employee_id"><input id="employee_name" class="input-text" type="text" value="'+$('#employee_id option:selected').text()+'" name="employee_name" disabled="disabled"></div>');
			$('label[for="employee_id"]').next().remove();
		}else{
			get_sub_by_project();
		};
		
	}

	$('input[name="date-temp-from"]').change(function(){        
        generate_affected_dates();
    });
	$('input[name="date-temp-to"]').change(function(){        
        generate_affected_dates();
    });

    if(view == 'edit' && $('#record_id').val() != '-1'){
       generate_affected_dates();
    }

     $('a.cancel-single_obt').live('click', function () { 
        var record_id = $(this).parent().parent().parent().attr("id");      
        form_status_id = $(this).attr("form_status");
        Boxy.ask("Are you sure you want to cancel this request?", ["Yes", "No"],function( choice ) {
        if(choice == "Yes"){
                if (form_status_id == 'one'){
                    var viewport_width      = $(window).width();
                    var viewport_height     = $(window).height();
                    var width               = .35 * viewport_width;
                    var height              = viewport_height * .25;
                    var data = "record_id="+record_id;
                    $.ajax({
                        url: module.get_value('base_url') + module.get_value('module_link') + "/get_boxy_single_cancel",
                        type:"POST",
                        data: data,
                        dataType: "json",
                        beforeSend: function(){
                            $.blockUI({ message: '<div class="now-loading align-center"><img src="'+module.get_value('base_url')+user.get_value('user_theme')+'/images/loading.gif"><br />Loading, please wait...</div>' });        
                        },
                        success: function(data){
                            quickedit_boxy = new Boxy('<div id="boxyhtml" style="width:'+width+'px; height:'+height+'px;overflow:auto; background-color: #f9f9f9;">'+ data.boxy_div +'</div>',
                            {
                                title: 'Cancel Remarks',
                                draggable: false,
                                modal: true,
                                center: true,
                                unloadOnHide: true,
                                afterShow: function(){ $.unblockUI(); },
                                beforeUnload: function(){ $('.tipsy').remove(); }
                                
                            }); 
                            boxyHeight(quickedit_boxy, '#boxyhtml');
                        }
                    });
                } else {
                    var viewport_width      = $(window).width();
                    var viewport_height     = $(window).height();
                    var width               = .40 * viewport_width;
                    var height              = viewport_height * .65;
                    var data = "record_id="+record_id;
                    $.ajax({
                        url: module.get_value('base_url') + module.get_value('module_link') + "/get_boxy_multiple_cancel",
                        type:"POST",
                        data: data,
                        dataType: "json",
                        beforeSend: function(){
                            $.blockUI({ message: '<div class="now-loading align-center"><img src="'+module.get_value('base_url')+user.get_value('user_theme')+'/images/loading.gif"><br />Loading, please wait...</div>' });        
                        },
                        success: function(data){
                            quickedit_boxy = new Boxy('<div id="boxyhtml" style="width:'+width+'px; height:'+height+'px;overflow:auto; background-color: #f9f9f9;">'+ data.boxy_div +'</div>',
                            {
                                title: 'Cancel Remarks',
                                draggable: false,
                                modal: true,
                                center: true,
                                unloadOnHide: true,
                                afterShow: function(){ $.unblockUI(); },
                                beforeUnload: function(){ $('.tipsy').remove(); }
                                
                            }); 
                            boxyHeight(quickedit_boxy, '#boxyhtml');
                        }
                    });            
                }           
            }
        },
        {
            title: "Cancel OBT Request"
        });        
    });
}

/**
 * Get the work schedule base on date entry
 * @return void
 */
function validation_check(){
	$("#date_from,#date_to,#time_start,#time_end").live("change", function(e) {
		e.preventDefault; 
		var date_from = $("#date_from").val();
		var date_to = $("#date_to").val();	
	    var time_start_hh_mm = $("#time_start").val();
	    var time_end_hh_mm = $("#time_end").val();
	    var employee_id = $("#employee_id").val();
	    var record_id = $("#record_id").val();
	    if (date_from != '' && date_to != '' && time_start_hh_mm != '' && time_end_hh_mm != ''){
			$.ajax({
				url: module.get_value('base_url') + module.get_value('module_link') + "/validation_check",
				type:"POST",
				dataType: 'json',
				data: { employee_id: employee_id, record_id: record_id, date_from : date_from, date_to: date_to, time_start_hh_mm : time_start_hh_mm, time_end_hh_mm : time_end_hh_mm, form: 'obt'},
				beforeSend: function(){
					 		
				},
				success: function(response){
					if (response.err){
						$('#message-container').html(message_growl('error', response.msg_type))
					}
				}
			});	 
		}
	});
}

function get_sub_by_project() {
		    if(module.get_value('module_link') == "employee/dtr"){
		        var get_sub_url = module.get_value('base_url') + 'forms/obt' + "/get_sub_by_project";  
		    }else{
		        var get_sub_url = module.get_value('base_url') + module.get_value('module_link') + '/get_sub_by_project';
		    }
        $.ajax({
		    url: get_sub_url,
            data: 'project_hr=' + user.get_value('user_id'),
            type: 'post',
            dataType: 'json',
            success: function (data) {
            	if (data.is_projecthr == true) {
            		$('select[name="employee_id"]').html(data.subordinates);
                	$('select[name="employee_id"]').trigger("liszt:updated");		
                	
            	};
            	//commented to view all employees if user has post-control and is not a project HR
     //        	else{

     //        		$('label[for="employee_id"]').append('<div class="text-input-wrap"><input id="employee_id" class="input-text" type="hidden" value="'+data.employee_id+'" name="employee_id"><input id="employee_name" class="input-text" type="text" value="'+data.employee+'" name="employee_name" disabled="disabled"></div>');
					// $('label[for="employee_id"]').next().remove();


     //        	};
             
            }
        });  
}
var initial_load = true;
function generate_affected_dates() {
	 var ok_to_proceed = true;
    if( initial_load ){
        if (module.get_value('record_id ') == '-1') {
            ok_to_proceed = false;
        }
        initial_load = false; 
    }
    if ($('input[name="date_from"]').val() != '' && $('input[name="date_to"]').val() != '' && ok_to_proceed) {
        $.ajax({
            url: module.get_value('base_url') + module.get_value('module_link') + '/get_affected_dates',
            type: 'post',
            data: $('input[name="date_from"], input[name="record_id"], input[name="date_to"], input[name="employee_id"], select[name="employee_id"]').serialize(),
            success: function (response) {
                if (response.type == 'success') {
                    if ($('#dates-container').size() > 0) {
                        $('#dates-container').remove();
                    }
                    
                    $('label[for="label-dates-affected"]').after('<div id="dates-container"></div>');
                    var ctr = 1;
                    $.each(response.dates, function (index, data) {

                        date = '<div style="padding:2px 0" class="leave_inclusive_date_'+data.date2+'"><span style="padding-right:5px">' + data.date + '</span>';
                        
                        wd = (data.duration_id == 0) ? 'selected' : '';
                        fs = (data.duration_id == 1) ? 'selected' : '';
                        ss = (data.duration_id == 2) ? 'selected' : '';
                        
                        date += '<input type="hidden" name="employee_leave_date_id[]" value="' + data.employee_leave_date_id + '"/>';
                        date += '<input type="hidden" name="dates[]" value="' + data.date + '"/>';
                        date += '<span> - ';
                        date += data.time_range;
                        date += '</span></div>';
                        
                        $('#dates-container').append(date);

                      	$('#time_start-'+data.date2).datetimepicker({
	                        changeMonth: true,
	                        changeYear: true,
	                        showOtherMonths: true,
	                        showButtonPanel: true,
	                        showAnim: 'slideDown',
	                        selectOtherMonths: true,
	                        showOn: "both",
	                        buttonImage: module.get_value('base_url') + user.get_value('user_theme') + "/icons/calendar-month.png",
	                        buttonImageOnly: true,
	                        buttonText: '',
	                        hourGrid: 4,
	                        minuteGrid: 10,
	                        timeFormat: 'hh:mm tt',
	                        ampm: true,
	                        timeOnly: true,
	                    }); 

						$('#time_end-'+data.date2).datetimepicker({
	                        changeMonth: true,
	                        changeYear: true,
	                        showOtherMonths: true,
	                        showButtonPanel: true,
	                        showAnim: 'slideDown',
	                        selectOtherMonths: true,
	                        showOn: "both",
	                        buttonImage: module.get_value('base_url') + user.get_value('user_theme') + "/icons/calendar-month.png",
	                        buttonImageOnly: true,
	                        buttonText: '',
	                        hourGrid: 4,
	                        minuteGrid: 10,
	                        timeFormat: 'hh:mm tt',
	                        ampm: true,
						    timeOnly: true,
	                    }); 

                        ctr++;                                                
                    });
                }
            }
        });
    }

    return false;
}

function save_cancel_multiple() {
    if($('textarea[name="cancel_remarks"]').val() == '') {
        $('#message-container').html(message_growl('info', 'Remarks is mandatory.'));
    } else {
        var data = $('#multiple_cancel').serialize();
        var saveUrl = module.get_value('base_url') + module.get_value('module_link')  + "/save_cancel_multiple"
        $.ajax
        ({
            url: saveUrl,
            type:"POST",
            data: data,
            dataType: "json",
            beforeSend: function()
            {
                $.blockUI({ message: '<div class="now-loading align-center"><img src="'+module.get_value('base_url') +'css/images/loading.gif"><br />Saving, please wait...</div>' });               
            },
            success: function(data)
            {
                $.unblockUI({ onUnblock: function() { $('#message-container').html(message_growl(data.msg_type, data.msg_msg)); } });
                if(!data.tag) {
                    quickedit_boxy.hide().unload();
                    window.location = module.get_value('base_url') + module.get_value('module_link');
                }
            }
        });
    }
}

function save_cancel_single() {
    if($('textarea[name="cancel_remarks"]').val() == '') {
        $('#message-container').html(message_growl('info', 'Remarks is mandatory.'));
    } else {
        var data = $('#single_cancel').serialize();
        var saveUrl = module.get_value('base_url') + module.get_value('module_link')  + "/save_cancel_single"
        $.ajax
        ({
            url: saveUrl,
            type:"POST",
            data: data,
            dataType: "json",
            beforeSend: function()
            {
                $.blockUI({ message: '<div class="now-loading align-center"><img src="'+module.get_value('base_url') +'css/images/loading.gif"><br />Saving, please wait...</div>' });               
            },
            success: function(data)
            {
                $.unblockUI({ onUnblock: function() { $('#message-container').html(message_growl(data.msg_type, data.msg_msg)); } });
                if(!data.tag) {
                    quickedit_boxy.hide().unload();
                    window.location = module.get_value('base_url') + module.get_value('module_link');
                }
            }
        });
    }
}

function change_status_cancellation(record_id,form_status_id) {
    Boxy.ask("Are you sure you want to cancel this request?", ["Yes", "No"],function( choice ) {
    if(choice == "Yes"){
            if (form_status_id == 'single'){
                var viewport_width      = $(window).width();
                var viewport_height     = $(window).height();
                var width               = .35 * viewport_width;
                var height              = viewport_height * .25;
                var data = "record_id="+record_id;
                $.ajax({
                    url: module.get_value('base_url') + module.get_value('module_link') + "/get_boxy_single_cancel",
                    type:"POST",
                    data: data,
                    dataType: "json",
                    beforeSend: function(){
                        $.blockUI({ message: '<div class="now-loading align-center"><img src="'+module.get_value('base_url')+user.get_value('user_theme')+'/images/loading.gif"><br />Loading, please wait...</div>' });        
                    },
                    success: function(data){
                        quickedit_boxy = new Boxy('<div id="boxyhtml" style="width:'+width+'px; height:'+height+'px;overflow:auto; background-color: #f9f9f9;">'+ data.boxy_div +'</div>',
                        {
                            title: 'Cancel Remarks',
                            draggable: false,
                            modal: true,
                            center: true,
                            unloadOnHide: true,
                            afterShow: function(){ $.unblockUI(); },
                            beforeUnload: function(){ $('.tipsy').remove(); }
                            
                        }); 
                        boxyHeight(quickedit_boxy, '#boxyhtml');
                    }
                });
            } else {
                var viewport_width      = $(window).width();
                var viewport_height     = $(window).height();
                var width               = .40 * viewport_width;
                var height              = viewport_height * .65;
                var data = "record_id="+record_id;
                $.ajax({
                    url: module.get_value('base_url') + module.get_value('module_link') + "/get_boxy_multiple_cancel",
                    type:"POST",
                    data: data,
                    dataType: "json",
                    beforeSend: function(){
                        $.blockUI({ message: '<div class="now-loading align-center"><img src="'+module.get_value('base_url')+user.get_value('user_theme')+'/images/loading.gif"><br />Loading, please wait...</div>' });        
                    },
                    success: function(data){
                        quickedit_boxy = new Boxy('<div id="boxyhtml" style="width:'+width+'px; height:'+height+'px;overflow:auto; background-color: #f9f9f9;">'+ data.boxy_div +'</div>',
                        {
                            title: 'Cancel Remarks',
                            draggable: false,
                            modal: true,
                            center: true,
                            unloadOnHide: true,
                            afterShow: function(){ $.unblockUI(); },
                            beforeUnload: function(){ $('.tipsy').remove(); }
                            
                        }); 
                        boxyHeight(quickedit_boxy, '#boxyhtml');
                    }
                });            
            }           
        }
    },
    {
        title: "Cancel OBT Request"
    });
}

function change_status_boxy(record_id, form_status_id, callback){

    switch(form_status_id){
        case 3:
            var question = "Are you sure you want to approve this request?";
            var title = "Approve Request";
            break;
        case 4:
            var question = "Are you sure you want to disapprove this request?";
            var title = "Decline Request";
            break;
        case 5:
            var question = "Are you sure you want to cancel this request";
            var title = "Cancel Request";
            break;
    }
    Boxy.ask(question, ["Yes", "No"],function( choice ) {
    if(choice == "Yes"){
             if( form_status_id == 4 ){

                 Boxy.ask("Add Remarks: <br /> <textarea name='decline_remarks' id='decline_remarks' style='width:100%;'></textarea>", ["Send", "Cancel"],function( add ) {
                    if(add == "Send"){
                        change_status(record_id, form_status_id, callback, $('#decline_remarks').val());
                    }
                },
                {
                    title: "Decline Remarks"
                });

            }else{

                change_status(record_id, form_status_id, callback);
            }
        }
    },
    {
        title: title
    });   
}


function goto_detail( data ){
    if (data.record_id > 0 && data.record_id != '') {
        module.set_value('record_id', data.record_id);    
        window.location.href = module.get_value('base_url') + module.get_value('module_link') + '/detail/' + module.get_value('record_id');         
    }else{
        window.location = module.get_value('base_url') + module.get_value('module_link');
    }
}

function change_status(record_id, form_status_id, callback, decline_remarks) {
    var data = 'record_id=' + record_id + '&form_status_id=' + form_status_id;

    if(decline_remarks){
        data += '&decline_remarks='+decline_remarks;
    }else{
        decline_remarks = '';
        data += '&decline_remarks='+decline_remarks;
    }

    $.ajax({
        url: module.get_value('base_url') + module.get_value('module_link') + '/change_status',
        data: data,
        type: 'post',
        dataType: 'json',
        success: function(response) {
            message_growl(response.type, response.message);
                    
            if (typeof(callback) == typeof(Function))
                callback(response);
        }
    }); 
}
