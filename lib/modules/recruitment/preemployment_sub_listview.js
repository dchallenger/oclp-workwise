$(document).ready(function () {
    // Unbind the default method for clicking on a listview row.    
    $('.jqgrow').die('dblclick');
    $('.icon-16-info').die('click');  
    $('.icon-16-edit').die('click');  
    
    $('.jqgrow').live('dblclick', tr_show_preemployment_checklist);
    $('.icon-16-edit').live('click', icon_show_preemployment_checklist);
});


function icon_show_preemployment_checklist() {
    obj = new Object();
    obj.id = $(this).parents('tr').attr('id');    
    
    show_preemployment_checklist(obj);
}

function tr_show_preemployment_checklist() {
    obj = new Object();
    obj.id = $(this).attr('id');    
    
    show_preemployment_checklist(obj);    
}

function show_preemployment_checklist(obj) {
    var rel = module.get_value('module_link');
    // Get id of proper table using record_id.
    $.ajax({
        url: module.get_value('base_url') + 'recruitment/preemployment/fetch_form_id',
        type: 'post',
        data: 'record_id=' + obj.id + '&rel=' + rel,
        dataType: 'json',
        beforeSend: function(){
            $.blockUI({
                message: '<div class="now-loading align-center"><img src="'+module.get_value('base_url')+user.get_value('user_theme')+'/images/loading.gif"><br />Loading, please wait...</div>'
            });  		
        },
        success: function(data){            
            $.unblockUI();
            
            if (data.type == 'error') {
                message_growl(data.type, data.message);
            } else {
                $('input[name="record_id"]').val(data.record_id);
                url = module.get_value('base_url') + rel + '/edit';

                $('form[name="record-form"]').attr('action', url).submit();                
            }
        }
    });    
}