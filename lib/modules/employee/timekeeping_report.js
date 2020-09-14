$( document ).ready( function() {
    init_datepick();

    $("#date_start").change(function(){
        $('#date_from').val($('#date_start').val());
    });

    $("#date_end").change(function(){
        $('#date_to').val($('#date_end').val());
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
    if( ( $('#date_start').val() == "" ) || ( $('#date_end').val() == "" ) ){
        add_error('date', 'Date Period', "This field is mandatory.");     
    }
    ok_to_save = validate_form();

    if( ok_to_save ){    
        $('#export-form').attr('action', $('#export_link').val());
        blockUIForDownload();          
        $('#export-form').submit();      
        $('#export-form').attr('action', '');
        return false;
    }
}

var fileDownloadCheckTimer;
function blockUIForDownload() {
    var token = new Date().getTime(); //use the current timestamp as the token value
    $('#download_token_value_id').val(token);
    $.blockUI({ message: '<div class="now-loading align-center"><img src="'+module.get_value('base_url')+user.get_value('user_theme')+'/images/loading.gif"><br />Exporting, please wait...</div>' });
    fileDownloadCheckTimer = window.setInterval(function () {
        var cookieValue = $.cookie('fileDownloadToken');
        if (cookieValue == token)
            finishDownload();
    }, 1000);
}

function finishDownload() {
    window.clearInterval(fileDownloadCheckTimer);
    $.cookie('fileDownloadToken', null); //clears this cookie value
    $.unblockUI();
}  

/*function export_list(){
    var sortColumnName = $("#jqgridcontainer").jqGrid('getGridParam','sortname');
    var sortOrder = $("#jqgridcontainer").jqGrid('getGridParam','sortorder');
    if (sortColumnName != ''){
        $('#previous_page').append('<input id="sidx" type="hidden" value="'+ sortColumnName +'" name="sidx"><input id="sord" type="hidden" value="'+ sortOrder +'" name="sord">');
    }
    $('#export-form').attr('action', $('#export_link').val());
    $('#export-form').submit();
    $('#export-form').attr('action', '');
    return false;
}*/

function generate_list(){

    if( ( $('#date_start').val() == "" ) || ( $('#date_end').val() == "" ) ){
        add_error('date', 'Date Period', "This field is mandatory.");     
    }
    ok_to_save = validate_form();

    if( ok_to_save ){
        $('#export-form').hasClass('export-search');
        list_search_grid( 'jqgridcontainer' );
        $('#export-form').removeClass('export-search');
        return false;
    }
}

function list_search_grid( jqgridcontainer ){

    $("#"+jqgridcontainer).jqGrid('setGridParam', 
    {
        search: true,
        postData: {
            dateStart : $('#date_start').val(),
            dateEnd : $('#date_end').val(),
        },  
    }).trigger("reloadGrid");

}