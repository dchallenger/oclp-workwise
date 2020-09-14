$( document ).ready( function() {

    window.onload = function(){
        $(".multi-select").multiselect({
            show:['blind',250],
            hide:['blind',250],
            selectedList: 1
        });
    }
    
    init_datepick();

    $("#date_start").change(function(){
        $('#date_from').val($('#date_start').val());
    });

    $("#date_end").change(function(){
        $('#date_to').val($('#date_end').val());
    });

    $('#category').live('change',function(){
        var items = {1 : "Company",2 : "Division",3 : "Department",4 : "Employee"};
        var category_id = $(this).val();
        var category = items[category_id];

        var eleid = category.toLowerCase()

        if (category_id > 0){
            $.ajax({
                url: module.get_value('base_url') + module.get_value('module_link') + '/get_employee_time_record',
                data: 'category_id=' + category_id,
                dataType: 'html',
                type: 'post',
                async: false,
                beforeSend: function(){
                    $('#multi-select-main-container').hide();
                    $('#multi-select-loader').html('<div><img src="'+module.get_value('base_url')+user.get_value('user_theme')+'/images/loading.gif" style="vertical-align:middle"><span style="padding-left:10px">Loading, please wait...</span></div>');                
                },                              
                success: function ( response ) {
                    $('#multi-select-loader').html('');                    
                    $('#multi-select-main-container').show();
                    $('#category_selected').html(category + ':');
                    $('#multi-select-container').html(response);
                    $('#'+eleid).multiselect().multiselectfilter({
                        show:['blind',250],
                        hide:['blind',250],
                        selectedList: 1
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

    $('#category1').live('change', function(){
        generate_list();         
        if ($(this).val() == 1) {
            dynamic_switch();
            $('#dynamic').attr('checked', false);
            $('#dynamic_conatiner').hide();
            return true;
        }
        else{
            //$('#dynamic').attr('checked', false);
            $('#dynamic_conatiner').show();   
        }
    });  

    $('input[name="dynamic"]').click(function(){
        if( ( $('#date_start').val() != "" ) && ( $('#date_end').val() != "" ) ){
            generate_list();
        }
        else{
            dynamic_switch(); 
        }               
    }) 
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

    if(ok_to_save){
        var sortColumnName = $("#jqgridcontainer").jqGrid('getGridParam','sortname');
        var sortOrder = $("#jqgridcontainer").jqGrid('getGridParam','sortorder');
        if (sortColumnName != ''){
            $('#previous_page').append('<input id="sidx" type="hidden" value="'+ sortColumnName +'" name="sidx"><input id="sord" type="hidden" value="'+ sortOrder +'" name="sord">');
        }
        $('#export-form').attr('action', $('#export_link').val());

        var data = $('#category, #category1, #company, #division, #department, #employee, #date_start, #date_end').serialize();
        $.ajax({
            url: module.get_value('base_url') + module.get_value('module_link') + '/excel_ajax_export',
            data: data,
            type: 'post',
            beforeSend: function(){
                $.blockUI({
                    message: '<div class="now-loading align-center"><img src="'+module.get_value('base_url')+user.get_value('user_theme')+'/images/loading.gif"><br />Loading, please wait...</div>'
                });
            },
            success: function(response)
            {
                var path = "/"+response.data;
                window.open(module.get_value('base_url')+path, 'excelexport');
                $.unblockUI();
            }  
        });
        $('#export-form').attr('action', '');
        return false;
    }
}

function generate_list(){

    if( ( $('#date_start').val() == "" ) || ( $('#date_end').val() == "" ) ){
        add_error('date', 'Date Period', "This field is mandatory.");
        dynamic_switch();       
    }
    ok_to_save = validate_form();

    if( ok_to_save ){
        $('#export-form').hasClass('export-search');
        list_search_grid( 'jqgridcontainer' );
        $('#export-form').removeClass('export-search');
        
        dynamic_switch();

        return false;
    }
}

function dynamic_switch(){
    var category1 = $('#category1').val();
    if(category1 == 7) category1 = 1;
    var col_pos = parseFloat(category1) + 2;
    var column_name = $('#jqgridcontainer').getGridParam("colModel")[col_pos].name
    if (category1 != 1){
        if ($('input[name="dynamic"]:checked').val()){
            hide_display_column(column_name);
        }
        else{
            $('#jqgridcontainer').jqGrid('showCol', 'hours_worked');
            $('#jqgridcontainer').jqGrid('showCol', 'absent');
            $('#jqgridcontainer').jqGrid('showCol', 'lates');
            $('#jqgridcontainer').jqGrid('showCol', 'overtime');
            $('#jqgridcontainer').jqGrid('showCol', 'undertime');                
        }
    }
    else{
        $('#jqgridcontainer').jqGrid('showCol', 'hours_worked');
        $('#jqgridcontainer').jqGrid('showCol', 'absent');
        $('#jqgridcontainer').jqGrid('showCol', 'lates');
        $('#jqgridcontainer').jqGrid('showCol', 'overtime');
        $('#jqgridcontainer').jqGrid('showCol', 'undertime');
    }
    $('#jqgridcontainer').setGridWidth(1094, true);
}

function hide_display_column(column_name){
    $('#jqgridcontainer').jqGrid('hideCol', 'hours_worked');
    $('#jqgridcontainer').jqGrid('hideCol', 'absent');
    $('#jqgridcontainer').jqGrid('hideCol', 'lates');
    $('#jqgridcontainer').jqGrid('hideCol', 'overtime');
    $('#jqgridcontainer').jqGrid('hideCol', 'undertime');

    if (column_name == "Hours Worked"){
        column_name = "hours_worked";
    }

    $('#jqgridcontainer').jqGrid('showCol', column_name);      
}

function list_search_grid( jqgridcontainer ){

    $("#"+jqgridcontainer).jqGrid('setGridParam', 
    {
        url: module.get_value('base_url') + module.get_value('module_link') + '/listview',
        datatype: 'json',
        search: true,
        postData: {
            category : $('#category').val(),
            category1 : $('#category1').val(),
            department : $('#department').val(),
            company : $('#company').val(),
            division : $('#division').val(),
            employee : $('#employee').val(),            
            dynamic : $('#dynamic').val(),
            dateStart : $('#date_start').val(),
            dateEnd : $('#date_end').val(),
        },  
    }).trigger("reloadGrid");

}