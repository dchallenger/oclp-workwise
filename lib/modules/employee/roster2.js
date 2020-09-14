$( document ).ready( function() {
    $(".multi-select").multiselect().multiselectfilter({
        show:['blind',250],
        hide:['blind',250],
        selectedList: 1
    });
    $("#company").multiselect({
        show:['blind',250],
        hide:['blind',250],
        selectedList: 1,
        close:function(event, ui){
            var selectedOptions = $.map($('#company :selected'),
                   function(e) { return $(e).val(); } );
            var div_id_delimited1 = selectedOptions.join(',');
            if (div_id_delimited1)
            {
                $.ajax({
                    url: module.get_value('base_url') + module.get_value('module_link') + '/get_division',
                    data: 'div_id_delimited=' + div_id_delimited1,
                    dataType: 'html',
                    type: 'post',
                    async: false,
                    beforeSend: function(){
                    
                    },                              
                    success: function ( response ) 
                    {
                        $('#company_srch').val(div_id_delimited1);
                        $('#multi-select-main-container1').show();
                        $('#multi-select-container1').html(response);
                        $('#division').multiselect().multiselectfilter({
                            show:['blind',250],
                            hide:['blind',250],
                            selectedList: 1
                        });                            
                        multiselectFilter();
                    }
                });                    
            }
        }
    })

    multiselectFilter();
});

function multiselectFilter(){
    $("#division").multiselect({
        show:['blind',250],
        hide:['blind',250],
        selectedList: 1,
        close:function(event, ui){
            var selectedOptions = $.map($('#division :selected'),
                   function(e) { return $(e).val(); } );
            var div_id_delimited2 = selectedOptions.join(',');
            if (div_id_delimited2)
            {
                $.ajax({
                    url: module.get_value('base_url') + module.get_value('module_link') + '/get_department',
                    data: 'div_id_delimited=' + div_id_delimited2,
                    dataType: 'html',
                    type: 'post',
                    async: false,
                    beforeSend: function(){
                    
                    },                              
                    success: function ( response ) 
                    {
                        $('#division_srch').val(div_id_delimited2);
                        $('#multi-select-main-container2').show();
                        $('#multi-select-container2').html(response);
                        $('#department').multiselect().multiselectfilter({
                            show:['blind',250],
                            hide:['blind',250],
                            selectedList: 1
                        }); 
                    }
                });                    
            }
        }
    });      
}

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
    if(!$('#division').val()) {
        add_error('division', 'Division', "This field is mandatory.");       
    }
    if(!$('#department').val()) {
        add_error('department', 'Department', "This field is mandatory.");       
    }
    
    ok_to_save = validate_form();

    if( ok_to_save ){    
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
}

function generate_list(){
    if(!$('#division').val()) {
        add_error('division', 'Division', "This field is mandatory.");       
    }
    if(!$('#department').val()) {
        add_error('department', 'Department', "This field is mandatory.");       
    }

    //ok_to_save = true;
    ok_to_save = validate_form();

    if( ok_to_save ){    
        $('#export-form').hasClass('export-search');
        list_search_grid( 'jqgridcontainer' );
        $('#export-form').removeClass('export-search');
        return false;
    }
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
            company : $('#company').val(),
            division : $('#division').val(),            
            department : $('#department').val()
        },  
    }).trigger("reloadGrid");

}