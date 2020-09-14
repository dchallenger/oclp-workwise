$(document).ready(function () {   

    window.onload = function(){
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
});

var initial_load = true;

function init_filter_tabs(){
    $('ul#grid-filter li').click(function(){
        $('ul#grid-filter li').each(function(){ 
            $(this).removeClass('active') 
        });

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

function ajax_save( on_success, is_wizard ){

    if( is_wizard == 1 ){
        var current = $('.current-wizard');
        var fg_id = current.attr('fg_id');
        var ok_to_save = eval('validate_fg'+fg_id+'()')
    }
    else{
        ok_to_save = validate_form();
    }
    
    if( ok_to_save ){
        var data = $('#record-form').serialize();
        data += "&on_success=" + on_success;
        var saveUrl = module.get_value('base_url')+module.get_value('module_link')+"/ajax_save"
                
        $.ajax({
            url: saveUrl,
            type:"POST",
            data: data,
            dataType: "json",
            async: false,
            beforeSend: function(){
                if( $('.now-loading').length == 0) $.blockUI({ message: '<div class="now-loading align-center"><img src="'+module.get_value('base_url')+user.get_value('user_theme')+'/images/loading.gif"><br />Saving, please wait...</div>' });
            },
            success: function(data){                   
                if(on_success == "back") {
                    go_to_previous_page( data.msg );
                } else if (on_success == "email" && data.msg_type == "success") {
                                    // Ajax request to send email.
                                    $.ajax({
                                        url: module.get_value('base_url') + module.get_value('module_link') + '/send_email',
                                        data: 'record_id=' + data.record_id,
                                        type: 'post',
                                        success: function () {
                                          window.location = module.get_value('base_url') + module.get_value('module_link') + '/detail/' + data.record_id;
                                          $.unblockUI({ onUnblock: function() { message_growl(data.msg_type, data.msg) } });
                                        }
                                    });                                    
                                } else{
                    //check if new record, update record_id
                    if($('#record_id').val() == -1 && data.record_id != ""){
                        $('#record_id').val(data.record_id);
                        $('#record_id').trigger('change');
                        if( is_wizard == 1 ) window.location = module.get_value('base_url') + module.get_value('module_link') + '/edit/' + data.record_id;
                    }                                        
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

function save_and_email() {
    ajax_save('email', 0);
}