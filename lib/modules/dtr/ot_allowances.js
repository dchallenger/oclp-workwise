var error = new Array();
var error_ctr = 0;
var colNames = ["ID", "Date Applied", "OT Date", "Start", "End", "Actual OT"];
var colModel = [
    {name: 'row_id', hidden: true, key:true},
    {name: 'date_applied', sortable: false, width: 150, align: 'left'},
    {name: 'ot_date', sortable: false, width: 100},
    {name: 'start', sortable: false, width: 100},
    {name: 'end', sortable: false, width: 100},
    {name: 'actual_ot', sortable: false, width: 100}
];


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
    $("#"+jqgridcontainer).jqGrid('clearGridData', { clearfooter: true});
    $("#"+jqgridcontainer).GridUnload();
    $("#"+jqgridcontainer).jqGrid({
        url: module.get_value('base_url') + module.get_value('module_link') + '/listview',
        loadtext: '<img src="'+ module.get_value('base_url') + user.get_value('user_theme') + '/images/loading.gif"><br />Loading...',
        datatype: "json",
        mtype: "POST",
        height: 'auto',
        autowidth: true,
        altRows: true,
        loadonce: true,
        forceFit: true,
        shrinkToFit: true,
        treeGrid: true,
        gridview: true,
        treeGridModel: 'adjacency',
        colNames:colNames,
        colModel:colModel,
        ExpandColumn : 'date_applied',
        pager: "#jqgridpager",
        pagerpos: 'right',
        viewrecords: true,
        toppager: true,
        postData: {
            category : $('#category').val(),
            department : $('#department').val(),
            company : $('#company').val(),
            division : $('#division').val(),
            employee : $('#employee').val(),            
            dateStart : $('#date_start').val(),
            dateEnd : $('#date_end').val(),
        }, 
        rowNum: 9999,
        loadComplete: expand_all,
        gridComplete: function(){ },
    });
    $("#jqgridcontainer").jqGrid('setGroupHeaders', {
          useColSpanStyle: false, 
          groupHeaders:[
            {startColumnName: 'start', numberOfColumns: 2, titleText: '<span style="font-weight:bold;">OT Date</span>'}
          ]
    }).trigger("reloadGrid");
}

function expand_all(){
    $(".treeclick").each(function() {
        if( $(this).hasClass("tree-minus") ) $(this).trigger("click");
    });
}