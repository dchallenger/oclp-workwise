$(document).ready(function(){
    
    if (module.get_value('view') == 'index') {
        $('.icon-16-add-listview').die().live('click', function () {edit_transaction('-1');});
        $('.icon-16-edit').die().live('click', function () {edit_transaction($(this).parents('tr').attr('id'));});
        
        $('input[name="filter-payroll_date"]').datepicker({
            changeMonth: true,
            changeYear: true,
            showOtherMonths: true,
            showButtonPanel: true,
            showAnim: 'slideDown',
            selectOtherMonths: true,
            showOn: "both",
            yearRange: 'c-90:c+10',
            buttonImage: module.get_value('base_url') + user.get_value('user_theme') + "/icons/calendar-month.png",
            buttonImageOnly: true,
            buttonText: ''
        });
        $('input[name="filter-payroll_date"]').change(function(){
            $('select[name="filter-employee_id"]').val(''); 
            filter_grid(true);
        });
       
        $('select[name="filter-employee_id"]').chosen();
        $('select[name="filter-employee_id"]').change(function(){ filter_grid(false)});
    }
});



function edit_transaction(record_id){
    module_url = module.get_value('base_url') + module.get_value('module_link') + '/quick_edit';
    $.ajax({
        url: module_url,
        type:"POST",
        data: 'record_id=' + record_id,
        dataType: "json",
        beforeSend: function(){
            $.blockUI({
                message: '<div class="now-loading align-center"><img src="'+module.get_value('base_url')+user.get_value('user_theme')+'/images/loading.gif"><br />Loading, please wait...</div>'
            });
        },
        success: function(data){
            $.unblockUI();
            if(data.msg != "") $('#message-container').html(message_growl(data.msg_type, data.msg));
            if(data.quickedit_form != ""){              
                quickedit_boxy = new Boxy('<div id="boxyhtml">'+ data.quickedit_form +'</div>',
                {
                    title: 'Quick Add/Edit',
                    draggable: false,
                    modal: true,
                    center: true,
                    unloadOnHide: true,
                    beforeUnload: function (){
                        $('.tipsy').remove();
                    }               
                });
                boxyHeight(quickedit_boxy, '#boxyhtml');
                if (typeof(BindLoadEvents) == typeof(Function)) {
                    BindLoadEvents();
                } 
            }
        }
    });
}

function quickedit_boxy_callback(module) {                 
    $('#jqgridcontainer').jqGrid().trigger("reloadGrid");   
}

function filter_grid( update_employee_list )
{
    var jqgridcontainer = 'jqgridcontainer';
    var searchfield;
    var searchop;
    var searchstring = $('.search-'+ jqgridcontainer ).val() != "Search..." ? $('.search-'+ jqgridcontainer ).val() : "";
    
    var payroll_date = $('input[name="filter-payroll_date"]').val();
    var employee_id = $('select[name="filter-employee_id"]').val();
    
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
            payroll_date: payroll_date,
            employee_id: employee_id,
            update_employee_list: update_employee_list
        },  
    }).trigger("reloadGrid");   
}