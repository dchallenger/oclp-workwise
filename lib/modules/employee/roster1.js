$( document ).ready( function() {
    init_datepick();
    //setTimeout( 'init_cst();', 100);
    window.onload = function(){
        $('#department').multiselect('refresh');
        $('#department').multiselect().multiselectfilter({
            show:['blind',250],
            hide:['blind',250],
            selectedList: 1
        });               
    }
});

/*function init_cst(){
    $('#department').multiselect({show:['blind',250],hide:['blind',250],selectedList: 1});
}*/

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
    var sortColumnName = $("#jqgridcontainer").jqGrid('getGridParam','sortname');
    var sortOrder = $("#jqgridcontainer").jqGrid('getGridParam','sortorder');
    if (sortColumnName != ''){
        $('#previous_page').append('<input id="sidx" type="hidden" value="'+ sortColumnName +'" name="sidx"><input id="sord" type="hidden" value="'+ sortOrder +'" name="sord">');
    }
    $('#export-form').attr('action', $('#export_link').val());
    $('#export-form').submit();
    $('#export-form').attr('action', '');
    return false;
}

function generate_list(){
    $('#export-form').hasClass('export-search');
    list_search_grid( 'jqgridcontainer' );
    $('#export-form').removeClass('export-search');
    return false;
}

function list_search_grid( jqgridcontainer ){
    $("#jqgridcontainer").jqGrid('clearGridData', { clearfooter: true});
    $("#"+jqgridcontainer).jqGrid('setGridParam', 
    {
        postData: null
    });
        
    $("#"+jqgridcontainer).jqGrid('setGridParam', 
    {
        search: true,
        postData: {
            department : $('#department').val(),
            date_start:$("#date_start").val(),
            date_end:$("#date_end").val()
        },  
    }).trigger("reloadGrid");

}