$(document).ready(function () {
    // Unbind the default method for clicking on a listview row.
    $('.jqgrow').die('dblclick');
    $('.icon-16-info').die('click');
    
    // Define module specific event.
    $('.jqgrow').live('dblclick', function () {
        return false;
    });
    
    $('a.search_participants').live('click', function () {
        window.location = module.get_value('base_url') + 'training/training_revalida_participants/index/' + $(this).parents('tr').attr('id');
    });

    $('a.view_revalida').live('click', function () {

        $('#record-form').append('<input id="participant_direct" type="hidden" value="1" name="participant_direct" />');

        var url = module.get_value('base_url') + module.get_value('module_link') + '/get_revalida_participant_id';
        var module_link = $(this).attr('module_link');

        $.ajax({
            url: url,
            dataType: 'json',
            type:"POST",
            data: 'calendar_id=' + $(this).parent().parent().parent().attr("id"),
            success: function (response) {

                if( response.revalida_id != 0 ){
                    record_action("detail", response.revalida_id, module_link);
                }
            }

        });

    });

    $('a.edit_revalida').live('click', function () {

        $('#record-form').append('<input id="participant_direct" type="hidden" value="1" name="participant_direct" />');

        window.location = module.get_value('base_url') + 'training/training_revalida_participants/edit/' + $(this).parents('tr').attr('id');
    
        var url = module.get_value('base_url') + module.get_value('module_link') + '/get_revalida_participant_id';
        var module_link = $(this).attr('module_link');

        $.ajax({
            url: url,
            dataType: 'json',
            type:"POST",
            data: 'calendar_id=' + $(this).parent().parent().parent().attr("id"),
            success: function (response) {

                if( response.revalida_id != 0 ){
                    record_action("edit", response.revalida_id, module_link);
                }
            }

        });

    });
    
});


function record_action(action, record_id, action_link, related_field, related_field_val)
{

    //update_gridsearch_parameters();
    $.blockUI({
        message: '<div class="now-loading align-center"><img src="'+module.get_value('base_url')+user.get_value('user_theme')+'/images/loading.gif"><br />Loading, please wait...</div>'
    });
    if(related_field != "")
    {
        $('#record-form').append('<input type="hidden" name="'+ related_field +'" value="'+ related_field_val +'" >');
    }

    $('#record_id').val(record_id);
    if(action_link == "") action_link = module.get_value('module_link');
    $('#record-form').attr("action", module.get_value('base_url') + action_link + "/" + action);

    switch( action ){
        case "detail":
            $('#record-form').submit(); 
            break;
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



