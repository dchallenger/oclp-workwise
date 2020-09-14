$(document).ready(function () {
    if( module.get_value('view') == "edit" )
    {
       
        $('.add_new_job_container').hide();

        if($('#record_id').val() != '-1'){
            $.ajax({
                url: module.get_value('base_url') + module.get_value('module_link') + '/get_position_per_project',
                data: 'project_name_id=' + $('#project_name_id').val() + '&record_id=' + $('#record_id').val(),
                dataType: 'html',
                type: 'post',
                async: false,
                beforeSend: function(){
                
                },                              
                success: function ( response ) {
                    $('#module-access-container').html(response);
                    $( "#module-access" ).delegate( 'td, th','mouseover mouseleave', function(e) {
                        if ( e.type == 'mouseover' ) {
                          $( this ).parent().addClass( "hover" );
                        }
                        else {
                          $( this ).parent().removeClass( "hover" );
                        }
                    });    

                    $.ajax({
                        url: module.get_value('base_url') + module.get_value('module_link') + '/get_positions',
                        data: 'project_name_id=' + $('#project_name_id').val() + '&record_id=' + $('#record_id').val(),
                        dataType: 'html',
                        type: 'post',
                        async: false,
                        beforeSend: function(){
                        
                        },                              
                        success: function ( response ) {
                            $('#add_existing_position_chzn').css('text-align','left');
                            $('.add_new_job_container').show().css('text-align','left');;
                            $('#add_existing_position').html(response);
                            $('#add_existing_position').chosen().trigger("liszt:updated");
                        }
                    });                    
                }
            });

        }else{

            $.ajax({
                url: module.get_value('base_url') + module.get_value('module_link') + '/get_amp_user_type',
                data: '',
                dataType: 'json',
                type: 'post',
                async: false,
                beforeSend: function(){
                
                },                              
                success: function ( response ) {
                    var amp_user_type = response.amp_user_type;
                    var employee_id = response.employee_id;
                    
                    if (amp_user_type == 'employee'){
                        var category_id = response.category_id;
                        var category_value = response.category_value_id;

                        $('#created_by').val(employee_id);
                        $("#created_by").attr('disabled', true).trigger("liszt:updated");
                        setTimeout(function (){
                            $('#created_by_chzn').children('a').children('abbr').remove();  
                        }, 100);                    
                        
                        if (category_id != "" && category_id == 2) {

                            $('#project_name_id').val(category_value);
                            

                            setTimeout(function (){
                                get_position_per_project();
                                // get_position_per_category(category_id, category_value);

                            }, 100);
                            
                        };
                        
                    }
                }
            });

        }


    }
    $('a.approve-single').live('click', function () {
        if (module.get_value('view') == "detail") {
            var record_id = $("#record_id").val();
            var callback = window.location.href = module.get_value('base_url') + module.get_value('module_link');    

        }else{
             var record_id = $(this).parent().parent().parent().attr("id");    
             var callback =  $('#jqgridcontainer').trigger('reloadGrid');      
        };
        
        change_status(record_id,3,
                function () {
                    callback
                }
            ); 
        
    });

    $('a.decline-single').live('click', function () {    
        var record_id = $(this).parent().parent().parent().attr("id");        
        Boxy.ask("Are you sure you want to disapprove this request?", ["Yes", "No"],function( choice ) {
            if(choice == "Yes"){
                Boxy.ask("Add Remarks: <br /> <textarea name='decline_remarks' id='decline_remarks'></textarea>", ["Send", "Cancel"],function( add ) {
                    if(add == "Send"){
                        change_status(record_id, 5, function () { $('#jqgridcontainer').trigger('reloadGrid'); }, $('#decline_remarks').val());
                    }
                },
                {
                    title: "Decline Remarks"
                });
            }
        },
        {
            title: "Decline Leave Request"
        });        
    });

    $('a.decline-single-detail').live('click', function () {    
        var record_id = $("#record_id").val();        
        Boxy.ask("Are you sure you want to disapprove this request?", ["Yes", "No"],function( choice ) {
            if(choice == "Yes"){
                Boxy.ask("Add Remarks: <br /> <textarea name='decline_remarks' id='decline_remarks'></textarea>", ["Send", "Cancel"],function( add ) {
                    if(add == "Send"){
                        change_status(record_id, 5, function () { window.location.href = module.get_value('base_url') + module.get_value('module_link'); }, $('#decline_remarks').val());
                    }
                },
                {
                    title: "Decline Remarks"
                });
            }
        },
        {
            title: "Decline Leave Request"
        });        
    });
    // $.ajax({
    //     url:module.get_value('base_url') + module.get_value('module_link') + '/get_division_head',
    //     type: 'post',
    //     dataType: 'html',
    //      async: false,
    //     success: function(response){
    //         // if (response != ''){
    //         //     $('#division_head_id option').remove();
    //         //     $('#division_head_id').append(response);
    //         // }
    //         console.log(response);
    //     }
    // });  


    $('#project_name_id').bind('change',function(){
        get_position_per_project();
    });

        $('.add_existing_job').live('click',function(){

            // $('#module-headcount').show();
            // $('.existing_job_headcount').show();         

            var position_id = $('#add_existing_position').val();
            var position = $('#add_existing_position option:selected').text();
            $.ajax({
                url: module.get_value('base_url') + module.get_value('module_link') + '/get_form_position',
                data: 'position_id=' + position_id + '&position=' + position,
                dataType: 'html',
                type: 'post',
                async: false,
                beforeSend: function(){
                
                },                              
                success: function ( response ) {
                    // $('.existing_job_headcount').append(response);
                    $('#module-access-container').append(response);
                    $('#add_existing_position option:selected').remove()
                    $('#add_existing_position').chosen().trigger('liszt:updated');

                }
            }); 

            return false;

        });

})

function get_position_per_project() {

         $.ajax({
                url: module.get_value('base_url') + module.get_value('module_link') + '/get_position_per_project',
                data: 'project_name_id=' + $('#project_name_id').val() + '&record_id=' + $('#record_id').val(),
                dataType: 'html',
                type: 'post',
                async: false,
                beforeSend: function(){
                
                },                              
                success: function ( response ) {
                        $.ajax({
                            url: module.get_value('base_url') + module.get_value('module_link') + '/get_division_head',
                            data: 'project_name_id=' + $('#project_name_id').val(),
                            dataType: 'html',
                            type: 'post',
                            async: false,
                            beforeSend: function(){
                            
                            },                              
                            success: function ( response ) {
                             
                                if (response != ''){
                                    $('#division_head_id option').remove();
                                    $('#division_head_id').append(response);
                                }
                            }
                        });  

                    $('#module-access-container').html(response);
                    $( "#module-access" ).delegate( 'td, th','mouseover mouseleave', function(e) {
                        if ( e.type == 'mouseover' ) {
                          $( this ).parent().addClass( "hover" );
                        }
                        else {
                          $( this ).parent().removeClass( "hover" );
                        }
                    });    

                    $.ajax({
                        url: module.get_value('base_url') + module.get_value('module_link') + '/get_positions',
                        data: 'project_name_id=' + $('#project_name_id').val(),
                        dataType: 'html',
                        type: 'post',
                        async: false,
                        beforeSend: function(){
                        
                        },                              
                        success: function ( response ) {
                            $('#add_existing_position_chzn').css('text-align','left');
                            $('.add_new_job_container').show().css('text-align','left');;
                            $('#add_existing_position').html(response);
                            $('#add_existing_position').chosen().trigger("liszt:updated");
                        }
                    });                    
                }
            });

            $.ajax({
                url: module.get_value('base_url') + module.get_value('module_link') + '/get_project_cost_code',
                data: 'project_name_id=' + $('#project_name_id').val(),
                dataType: 'json',
                type: 'post',
                async: false,
                beforeSend: function(){
                
                },                              
                success: function ( response ) {
                    $('#cost_code').val(response);
                }
            });
}

function change_status(record_id, form_status_id, callback, decline_remarks) {
    var data = 'record_id=' + record_id + '&form_status_id=' + form_status_id;

    if(decline_remarks){
        data += '&decline_remarks='+decline_remarks;
    }else{
        decline_remarks = false;
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

function goto_detail( data )
{
    if (data.record_id > 0 && data.record_id != '') 
    {
        module.set_value('record_id', data.record_id);    
        window.location.href = module.get_value('base_url') + module.get_value('module_link') + '/detail/' + module.get_value('record_id');         
    }
}

function backtolistview()
{
	window.location.href = module.get_value('base_url') + module.get_value('module_link');	
}

function validate_ajax_save( on_success, is_wizard , callback ){
	if(error.length > 0){
		var error_str = "Please correct the following errors:<br/><br/>";
		for(var i in error){
			if(i == 0) $('#'+error[i][0]).focus(); //set focus on the first error
			error_str = error_str + (parseFloat(i)+1) +'. '+error[i][1]+" - "+error[i][2]+"<br/>";
		}
		$('#message-container').html(message_growl('error', error_str));
		
		//reset errors
		error = new Array();
		error_ctr = 0
		return false;
	}
	ajax_save( on_success, is_wizard , callback );
}