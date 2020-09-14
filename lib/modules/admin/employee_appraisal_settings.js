$( document ).ready( function() {
	

    window.onload = function(){

        var status_length = $('#status_list').find('.list_row').length;
        var scale_length = $('#scale_list').find('.list_row').length;
        var group_length = $('#group_list').find('.list_row').length;

        if( status_length == 0 ){
            $('#status_list').hide();
        }
        
        if( scale_length == 0 ){
            $('#scale_list').hide();
        }
        
        if( group_length == 0 ){
            $('#group_list').hide();
        }

    }

	$('.appraisal_delete_status').live('click',function(){

		$(this).parent().parent().parent().remove();

        var status_length = $('#status_list').find('.list_row').length;

        if( status_length == 0 ){
            $('#status_list').hide();
        }
	});

    $('.appraisal_edit_status').live('click',function(){

        var status_name = $(this).parent().parent().parent().find('.status_name_html');
        var status_name_val = $.trim(status_name.text());

        $(this).parent().find('.appraisal_save_status').show();
        $(this).parent().find('.appraisal_cancel_status').show();
        $(this).parent().find('.appraisal_delete_status').hide();
        $(this).hide();

        status_name.empty();
        status_name.html('<input type="text" name="status_text" class="status_text"/>');
        status_name.find('.status_text').val(status_name_val);

    });

    $('.appraisal_save_status').live('click',function(){

        var status_parent = $(this).parent().parent().parent();
        var status_name = $(this).parent().parent().parent().find('.status_name_html');
        var status_name_val = $.trim(status_name.find('.status_text').val());

        if( status_name_val != "" ){

            $(this).hide();
            $(this).parent().find('.appraisal_cancel_status').hide();
            $(this).parent().find('.appraisal_delete_status').show();
            $(this).parent().find('.appraisal_edit_status').show();

            status_name.empty();
            status_name.text(status_name_val);
            status_parent.find('.status_name').val(status_name_val);

        }
        else{

            message_growl('error', 'Status name is required');

        }

    });

    $('.appraisal_cancel_status').live('click',function(){

        var status_parent = $(this).parent().parent().parent();
        var status_name = $(this).parent().parent().parent().find('.status_name_html');
        var status_name_val = status_parent.find('.status_name').val();

        $(this).hide();
        $(this).parent().find('.appraisal_save_status').hide();
        $(this).parent().find('.appraisal_edit_status').show();
        $(this).parent().find('.appraisal_delete_status').show();

        status_name.empty();
        status_name.text(status_name_val);
        status_parent.find('.status_name_html').val(status_name_val);

    });


    $('.appraisal_delete_scale').live('click',function(){

        $(this).parent().parent().parent().remove();

        var scale_length = $('#scale_list').find('.list_row').length;
        
        if( scale_length == 0 ){
            $('#scale_list').hide();
        }

    });

    $('.appraisal_edit_scale').live('click',function(){

        var scale_name = $(this).parent().parent().parent().find('.scale_name_html');
        var scale_name_val = $.trim(scale_name.text());

        var scale_times = $(this).parent().parent().parent().find('.scale_times_html');
        var scale_times_val = $.trim(scale_times.text());

        $(this).parent().find('.appraisal_save_scale').show();
        $(this).parent().find('.appraisal_cancel_scale').show();
        $(this).parent().find('.appraisal_delete_scale').hide();
        $(this).hide();

        scale_name.empty();
        scale_name.html('<input type="text" name="scale_text" class="scale_text"/>');
        scale_name.find('.scale_text').val(scale_name_val);

        scale_times.empty();
        scale_times.html('<input type="text" style="width:50px;" name="scale_times_text" class="scale_times_text"/>');
        scale_times.find('.scale_times_text').val(scale_times_val);

    });

    $('.appraisal_save_scale').live('click',function(){

        var scale_parent = $(this).parent().parent().parent();
        var scale_name = $(this).parent().parent().parent().find('.scale_name_html');
        var scale_name_val = $.trim(scale_name.find('.scale_text').val());

        var scale_times = $(this).parent().parent().parent().find('.scale_times_html');
        var scale_times_val = $.trim(scale_times.find('.scale_times_text').val());

        if( scale_name_val != "" && scale_times_val != "" ){

            var valid = /^-?\d+$/.test( scale_times_val );

            if( valid ){

                $(this).hide();
                $(this).parent().find('.appraisal_cancel_scale').hide();
                $(this).parent().find('.appraisal_delete_scale').show();
                $(this).parent().find('.appraisal_edit_scale').show();

                scale_name.empty();
                scale_name.text(scale_name_val);
                scale_parent.find('.scale_name').val(scale_name_val);

                scale_times.empty();
                scale_times.text(scale_times_val);
                scale_parent.find('.scale_times').val(scale_times_val);

            }
            else{
                message_growl('error', 'Scale must be a decimal number');
            }
        }
        else{
            message_growl('error', 'Scale name is required');
        }

    });

    $('.appraisal_cancel_scale').live('click',function(){

        var scale_parent = $(this).parent().parent().parent();
        var scale_name = $(this).parent().parent().parent().find('.scale_name_html');
        var scale_name_val = scale_parent.find('.scale_name').val();

        var scale_times = $(this).parent().parent().parent().find('.scale_times_html');
        var scale_times_val = scale_parent.find('.scale_times').val();

        $(this).hide();
        $(this).parent().find('.appraisal_save_scale').hide();
        $(this).parent().find('.appraisal_edit_scale').show();
        $(this).parent().find('.appraisal_delete_scale').show();

        scale_name.empty();
        scale_name.text(scale_name_val);
        scale_parent.find('.scale_name_html').val(scale_name_val);

        scale_times.empty();
        scale_times.text(scale_times_val);
        scale_parent.find('.scale_times_html').val(scale_times_val);

    });




    $('.appraisal_delete_group').live('click',function(){

        $(this).parent().parent().parent().remove();

        var group_length = $('#group_list').find('.list_row').length;
        
        if( group_length == 0 ){
            $('#group_list').hide();
        }

    });

    $('.appraisal_edit_group').live('click',function(){

        var group_name = $(this).parent().parent().parent().find('.group_name_html');
        var group_name_val = $.trim(group_name.text());

        $(this).parent().find('.appraisal_save_group').show();
        $(this).parent().find('.appraisal_cancel_group').show();
        $(this).parent().find('.appraisal_delete_group').hide();
        $(this).hide();

        group_name.empty();
        group_name.html('<input type="text" name="group_text" class="group_text"/>');
        group_name.find('.group_text').val(group_name_val);

    });

    $('.appraisal_save_group').live('click',function(){

        var group_parent = $(this).parent().parent().parent();
        var group_name = $(this).parent().parent().parent().find('.group_name_html');
        var group_name_val = $.trim(group_name.find('.group_text').val());

        if( group_name_val != "" ){

            $(this).hide();
            $(this).parent().find('.appraisal_cancel_group').hide();
            $(this).parent().find('.appraisal_delete_group').show();
            $(this).parent().find('.appraisal_edit_group').show();

            group_name.empty();
            group_name.text(group_name_val);
            group_parent.find('.group_name').val(group_name_val);

        }
        else{

            message_growl('error', 'Group name is required');

        }

    });

    $('.appraisal_cancel_group').live('click',function(){

        var group_parent = $(this).parent().parent().parent();
        var group_name = $(this).parent().parent().parent().find('.group_name_html');
        var group_name_val = group_parent.find('.group_name').val();

        $(this).hide();
        $(this).parent().find('.appraisal_save_group').hide();
        $(this).parent().find('.appraisal_edit_group').show();
        $(this).parent().find('.appraisal_delete_group').show();

        group_name.empty();
        group_name.text(group_name_val);
        group_parent.find('.group_name_html').val(group_name_val);

    });




});

function validate_form()
{	

    var period = $('#appraisal_periods').val();
    var multiplier = $('#appraisal_multiplier').val();
	var valid = /^-?\d+$/.test( multiplier );

    var error_cnt = 0;
    var error_msg = '';


    if( !valid ){
        error_cnt++;
        error_msg = error_msg + 'Multiplier must be an integer<br />'; 
    }

    if( period == 0 ){
        error_cnt++;
        error_msg = error_msg + 'Period is required<br />'; 
    }

    if( error_cnt > 0 ){
        message_growl('error', error_msg);
    }
    else{
        return true;
    }
}

function add_appraisal_status(){

	var status_name = $.trim($('#appraisal_add_status_name').val());

    if( status_name != ""){
    	$.ajax({
            url: module.get_value('base_url') + module.get_value('module_link') + '/add_status_form',
            data: 'status_name=' + status_name,
            type: 'post',
            dataType: 'json',
            success: function(response) {
                
                $('#appraisal_add_status_name').val('');

                $('#status_list').show();
            	$('#status_list').append(response.status_form);

            }
        }); 
    }
    else{
        message_growl('error', 'Status name is required');
    }    

}

function add_appraisal_scale(){

    var scale_name = $.trim($('#appraisal_add_scale_name').val());
    var scale_times = $.trim($('#appraisal_add_scale_times').val());

    if( scale_name != "" && scale_times != "" ){

        var valid = /^-?\d+$/.test( scale_times );

        if( valid ){

            $.ajax({
                url: module.get_value('base_url') + module.get_value('module_link') + '/add_scale_form',
                data: 'scale_name=' + scale_name + '&scale_times=' + scale_times,
                type: 'post',
                dataType: 'json',
                success: function(response) {
                    
                    $('#appraisal_add_scale_name').val('');
                    $('#appraisal_add_scale_times').val('');

                    $('#scale_list').show();
                    $('#scale_list').append(response.scale_form);

                }
            }); 

        }
        else{
            message_growl('error', 'Scale must be a decimal number');
        }
    }
    else{
        message_growl('error', 'Name and Scale are required');
    }    
}

function add_appraisal_group(){

    var group_name = $.trim($('#appraisal_add_group_name').val());

    if( group_name != ""){
        $.ajax({
            url: module.get_value('base_url') + module.get_value('module_link') + '/add_group_form',
            data: 'group_name=' + group_name,
            type: 'post',
            dataType: 'json',
            success: function(response) {
                
                $('#appraisal_add_group_name').val('');

                $('#group_list').show();
                $('#group_list').append(response.group_form);

            }
        }); 
    }
    else{
        message_growl('error', 'Group name is required');
    }    

}