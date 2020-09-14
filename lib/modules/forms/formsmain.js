$(document).ready(function () {   
    bindFormsmainEvents();
});

function bindFormsmainEvents(){
    if ( user.get_value('post_control') != 1 || $('#filter').val() == "personal" ) {
        $('input[name="employee_id"]').siblings('span.icon-group').remove();
    }

    if(module.get_value('view') == "detail")
    {
        if($.trim($('label[for="form_status_id"]').siblings('div').text()) == "Disapproved")
            $('label[for="decline_remarks"]').parent().show();
    }
    
    $('a.approve-single').live('click', function () {        
        change_status($(this).parent().parent().parent().attr("id"),
            3,
            function () {
                $('#jqgridcontainer').trigger('reloadGrid');
            }
            );        
    });
    
    $('a.decline-single').live('click', function () {        
        var record_id = $(this).parent().parent().parent().attr("id");
        Boxy.ask("Are you sure you want to disapprove this request?", ["Yes", "No"],function( choice ) {
            if(choice == "Yes"){
                 Boxy.ask("Add Remarks: <br /> <textarea name='decline_remarks' style='width:100%;' id='decline_remarks'></textarea>", ["Send", "Cancel"],function( add ) {
                    if(add == "Send"){
                        change_status(record_id, 4, function () { $('#jqgridcontainer').trigger('reloadGrid'); }, $('#decline_remarks').val());
                    }
                },
                {
                    title: "Decline Remarks"
                });
            }
        },
        {
            title: "Decline Request"
        });        
    });

    $('a.cancel-single').live('click', function () {   
        var record_id = $(this).parent().parent().parent().attr("id");     
        Boxy.ask("Are you sure you want to cancel this request?", ["Yes", "No"],function( choice ) {
        if(choice == "Yes"){
              change_status(record_id, 5, function () { $('#jqgridcontainer').trigger('reloadGrid'); }); 
            }
        },
        {
            title: "Cancel Request"
        });        
    }); 

    if( module.get_value('view') == "edit" ){
        if( module.get_value('record_id') == '-1' ){
            $('#form_status_id').val(1);
        }
    } 
    
    $('#employee_id').change(function () {
        get_approvers();
    });

    $('#employee_id').trigger('change');

     $('.approve-array').live('click', function(){
        if(user.get_value('approve_control') == 1)
        {
            var selected = $("#"+$(this).attr('container')).jqGrid("getGridParam", "selarrrow");
            
            if(selected.length > 0)
            {
                if(selected[0] == '')
                {
                    //remove the value for "check all"
                    selected.shift();
                }
                approve_record(selected, $(this).attr('module_link'), $(this).attr('container'));
            }
            else{
                $('#message-container').html(message_growl('attention', 'No record was selected!'))
            }
        }
        else{
            $('#message-container').html(message_growl('error', 'You dont have delete privileges! Please contact the System Administrator.'));
        }
    });

  $('.disapprove-array').live('click', function(){
        if(user.get_value('decline_control') == 1)
        {
            var selected = $("#"+$(this).attr('container')).jqGrid("getGridParam", "selarrrow");
            
            if(selected.length > 0)
            {
                if(selected[0] == '')
                {
                    //remove the value for "check all"
                    selected.shift();
                }
                disapprove_record(selected, $(this).attr('module_link'), $(this).attr('container'));
            }
            else{
                $('#message-container').html(message_growl('attention', 'No record was selected!'))
            }
        }
        else{
            $('#message-container').html(message_growl('error', 'You dont have delete privileges! Please contact the System Administrator.'));
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
        data += "&on_success=" + on_success;
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
                if( data.msg_type != "error" && data.record_id != null ){                   
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
                                        success: function () {
                                          window.location = module.get_value('base_url') + module.get_value('module_link') + '/detail/' + data.record_id;
                                          $.unblockUI({ onUnblock: function() { message_growl(data.msg_type, data.msg) } });
                                        }
                                    });  
                            }                           
                            //custom ajax save callback
                            if (typeof(callback) == typeof(Function)) callback( data );
                            break;
                        default:
                            if (typeof data.page_refresh != 'undefined' && data.page_refresh == "true"){
                                    window.location = module.get_value('base_url') + module.get_value('module_link') + '/edit/' + data.record_id;
                            }
                            else{
                                //check if new record, update record_id
                                if($('#record_id').val() == -1 && data.record_id != ""){
                                    $('#record_id').val(data.record_id);
                                    $('#record_id').trigger('change');
                                    if( is_wizard == 1 ) window.location = module.get_value('base_url') + module.get_value('module_link') + '/edit/' + data.record_id;
                                }
                                else{
                                    $('#record_id').val( data.record_id );
                                }
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

function get_approvers(){
    if( $('#employee_id').val() != "" ){
        $.ajax({
            url: module.get_value('base_url') + module.get_value('module_link') + '/get_approvers',
            data: 'employee_id=' + $('#employee_id').val(),
            type: 'post',
            dataType: 'json',
            success: function (data) {
                $('#approvers-container').html(data.approvers);
            }
        });  
    }
}


function approve() {
    record_id = $('#record_id').val();
    change_status(record_id, 3);
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

function change_status_multiple(record_id, form_status_id, callback) {
    $.ajax({
        url: module.get_value('base_url') + module.get_value('module_link') + '/change_status_multiple',
        data: 'record_id=' + record_id + '&form_status_id=' + form_status_id,
        type: 'post',
        dataType: 'json',
        success: function(response) {
            message_growl(response.type, response.message);
                    
            if (typeof(callback) == typeof(Function))
                callback(response);
        }
    }); 
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
            response.record_id = record_id;
            if (typeof(callback) == typeof(Function))
                callback(response);
        }
    });	
}

function goto_detail( data ){
    module.set_value('record_id', data.record_id); 
    window.location.href = module.get_value('base_url') + module.get_value('module_link') + '/detail/' + module.get_value('record_id'); 
}

function init_filter_tabs(){
    $('ul#grid-filter li').click(function(){
        $('ul#grid-filter li').each(function(){ $(this).removeClass('active') });
        $(this).addClass('active');
        $('#filter').val( $(this).attr('filter') );

        if( $(this).attr('filter') == 'for_approval' ){
            $('.status-buttons').parent().show();
        }
        else{
            $('.status-buttons').parent().hide();
        }

        filter_grid( 'jqgridcontainer', $(this).attr('filter') );
    });
}

function filter_grid( jqgridcontainer, filter )
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
            searchString: searchstring,
            filter: filter
        },  
    }).trigger("reloadGrid");   
}

function approve_record(selected, action_url, container){
    Boxy.ask("Approve "+ selected.length +" selected record(s)?", ["Yes", "Cancel"],
        function( choice ) {
            if(choice == "Yes"){

                change_status_multiple(selected,
                    3,
                    function () {
                        $('#jqgridcontainer').trigger('reloadGrid');
                        $.ajax({
                            url: module.get_value('base_url') + module.get_value('module_link') + '/get_for_approval_count',
                            type: 'get',
                            dataType: 'json',
                            success: function (response) {

                                if(response){
                                    $('#approval-counter').html(response.count);
                                }
                            }
                        });
                    }
                );


            }
        },
        {
            title: "Approve Record"
        });
}

function disapprove_record(selected, action_url, container){
    if( selected.length == 1 ){
        Boxy.ask("Disapprove "+ selected.length +" selected record(s)?", ["Yes", "Cancel"],
            function( choice ) {
                if(choice == "Yes"){

                    change_status_multiple(selected,
                        4,
                        function () {
                            $('#jqgridcontainer').trigger('reloadGrid');
                            $.ajax({
                                url: module.get_value('base_url') + module.get_value('module_link') + '/get_for_approval_count',
                                type: 'get',
                                dataType: 'json',
                                success: function (response) {
                                    if(response){
                                        $('#approval-counter').html(response.count);
                                    }
                                }
                            });
                        }
                    );


                }
            },
            {
                title: "Disapprove Record"
            });
        return;
    }
    Boxy.alert('Bulk Disapprove is no longer allowed.');
   
}