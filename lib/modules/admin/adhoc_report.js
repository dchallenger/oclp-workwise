$( document ).ready( function() {

    window.onload = function(){
        $(".multi-select").multiselect({
            show:['blind',250],
            hide:['blind',250],
            selectedList: 1
        });     
    }
    
    init_datepick();

    $('#module_id').live('change',function(){
        var module_id = $(this).val();
        if (module_id > 0){
            $.ajax({
                url: module.get_value('base_url') + module.get_value('module_link') + '/get_fields',
                data: 'module_id=' + module_id,
                dataType: 'html',
                type: 'post',
                async: false,
                beforeSend: function(){
                    $('#sortable-container').hide();
                    $('#filter-container').hide(); 
                    $('#orderby-container').hide();
                    $('#multi-select-main-container').hide();
                    $('#multi-select-loader').html('<div><img src="'+module.get_value('base_url')+user.get_value('user_theme')+'/images/loading.gif" style="vertical-align:middle"><span style="padding-left:10px">Loading, please wait...</span></div>');                
                },                              
                success: function ( response ) {
                    $('#multi-select-loader').html('');                    
                    $('#multi-select-main-container').show();
                    $('#multi-select-container').html(response);                   
                    $('#fields').multiselect().multiselectfilter({
                        show:['blind',250],
                        hide:['blind',250],
                        selectedList: 1
                    });
                    $("#fields").multiselect({
                        close: function(event, ui){
                            var count = $("#fields :selected").length;                            
                            if (count < 1) {
                                $('#sortable-container').hide();
                                $('#filter-container').hide(); 
                                $('#orderby-container').hide();                                
                                return;
                            }
                            var data = $('#export-form').serialize();
                            $.ajax({
                                url: module.get_value('base_url') + module.get_value('module_link') + '/get_fields_selected',
                                data: data,
                                dataType: 'json',
                                type: 'post',
                                beforeSend: function(){
                                    $.blockUI({
                                        message: '<div class="now-loading align-center"><img src="'+module.get_value('base_url')+user.get_value('user_theme')+'/images/loading.gif"><br />Loading, please wait...</div>'
                                    });                                    
                                },                                                        
                                success: function ( response ) {
                                    $.unblockUI();
                                    $('#sortable-container').show();
                                    $('#sortable').html(response.html1); 
                                    $('#sortable').sortable();     
                                    $('#filter-container').show(); 
                                    $('#filter').html(response.html2);
                                    $('#orderby-container').show();
                                    $('#orderby').html(response.html3);
                                    $('.datepicker').datepicker({
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
                                        yearRange: 'c-90:c+10',
                                        beforeShow: function(input, inst) {                     
                                            
                                        },
                                        onClose: function(dateText) {

                                        }
                                    }); 
                                    $('.datetimepicker').datetimepicker({
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
                                        yearRange: 'c-90:c+10',
                                        onClose: function(dateText, inst){
                                            if ($('#datetime_from').val() == "" || $('#datetime_to').val() == ""){
                                                return;
                                            }
                                        }
                                    });  
                                    $('.timepicker').timepicker({
                                        showAnim: 'slideDown',
                                        selectOtherMonths: true,
                                        showOn: "both",
                                        buttonImage: module.get_value('base_url') + user.get_value('user_theme') + "/icons/calendar-month.png",
                                        buttonImageOnly: true,
                                        buttonText: '',
                                        hourGrid: 4,
                                        minuteGrid: 10,
                                        ampm: true
                                    }); 
                                    $('.multiselect').multiselect().multiselectfilter({
                                        show:['blind',250],
                                        hide:['blind',250],
                                        selectedList: 1
                                    });                                                                                                                                                                                                                
                                }
                            });
                        }
                    }); 
                }
            });
        }
        else{
            $('#multi-select-main-container').hide();
            $('#category_selected').html('');
            $('#multi-select-container').html('');          
        }   
    }); 
});


function validate_form()
{   
    //errors
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
    
    //no error occurred
    return true;
}

function export_list(){
    if( $("#fields :selected").length == 0 ){
        add_error('field', 'Field', "This field is mandatory.");
    }
    if( ( $('#module_id').val() == "" ) ){
        add_error('module', 'Module', "This field is mandatory.");
    }
    ok_to_save = validate_form();

    if(ok_to_save){
        var sortColumnName = $("#jqgridcontainer").jqGrid('getGridParam','sortname');
        var sortOrder = $("#jqgridcontainer").jqGrid('getGridParam','sortorder');
        if (sortColumnName != ''){
            $('#previous_page').append('<input id="sidx" type="hidden" value="'+ sortColumnName +'" name="sidx"><input id="sord" type="hidden" value="'+ sortOrder +'" name="sord">');
        }
        $('#export-form').attr('action', $('#export_link').val());

        var data = $('#export-form').serialize();
        $.ajax({
            url: module.get_value('base_url') + module.get_value('module_link') + '/export',
            data: data,
            type: 'post',
            beforeSend: function(){
                $.blockUI({
                    message: '<div class="now-loading align-center"><img src="'+module.get_value('base_url')+user.get_value('user_theme')+'/images/loading.gif"><br />Loading, please wait...</div>'
                });
            },
            success: function(response)
            {
                if (response.msg_type == 'success'){
                    var path = "/"+response.data;
                    window.location = module.get_value('base_url')+path;
                    $.unblockUI();
                }
                else{
                    $.unblockUI();                    
                    $('#message-container').html(message_growl("attention","No records has been found."))
                    return;
                }
            }  
        });
        $('#export-form').attr('action', '');
        return false;
    }
}

function clearField( name )
{
    $('input[name="'+name+'"]').val('');
    $('input[name="'+name+'-name"]').val('');
    $('input[name="'+name+'"]').trigger('change');
}

function list_search_grid( jqgridcontainer ){

    $("#"+jqgridcontainer).jqGrid('setGridParam', 
    {
        search: true,
        postData: {
            module_id : $('#module_id').val(),
        },  
    }).trigger("reloadGrid");

}