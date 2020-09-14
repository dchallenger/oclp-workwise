$( document ).ready( function() {
    var lastsel;
    $("#jqgridcontainer").jqGrid({
        url: module.get_value('base_url') + module.get_value('module_link') + '/listview',
        loadtext: '<img src="'+ module.get_value('base_url') + user.get_value('user_theme') + '/images/loading.gif"><br />Loading...',
        datatype: "json",
        mtype: "POST",
        rowNum: 25,
        rowList: [10,15,25, 40, 60, 85, 100],
        toolbar: [true,"top"],
        height: 'auto',
        autowidth: true,
        pager: "#jqgridpager",
        pagerpos: 'right',
        toppager: true,
        viewrecords: true,
        altRows: true,
        forceFit: true,
        shrinkToFit: true,
        colNames:["Agent Name","Date","Time In","Time Out","Biometrics","Dialed Hours","Difference","Remarks","OE for Discrepancy"],
        colModel:[{name : 'agent_name',width : '180',align : 'center'},{name : 'date'},{name : 'time_in'},{name : 'time_out'},{name : 'biometrics'},{name : 'dialed_hours'},{name : 'difference'},{name : 'remarks'},{name : 'oe_for_discrepancy'}],
        loadComplete: function(data){
            post_gridcomplete_function(data, '#jqgridcontainer');
        },
        gridComplete:function(){
        },
        onSelectRow: function(id){ 
            if(id && id!==lastsel){ 
                $('#jqgridcontainer').jqGrid('restoreRow',lastsel); 
                $('#jqgridcontainer').jqGrid('editRow',id,true,null,null,null,{date:$('#date').val()}); 
                lastsel=id; 
            } 
        },
        ondblClickRow: function(rowid) {
            return;
        },          
        editurl: module.get_value('base_url') + module.get_value('module_link') + '/save_row',        
        caption: " List",
    });
    $("#jqgridcontainer").jqGrid('navGrid','#jqgridpager',{refresh:false, edit:false, add:false, del:false, search:false});
    $("#jqgridcontainer_toppager_center").hide();
    $("#jqgridpager_center").hide();
    //$("#t_jqgridcontainer").append($('.search-wrap').html());
    $(".search-trigger[tooltip]").tipsy({
        title: 'tooltip',
        gravity: 'se',
        opacity: 0.85,
        live: true,
        delayIn: 500
    });

    init_datepick();

    $("#date").change(function(){
        $('#date').val($('#date').val());
    });

    // $('#date').live('change',function(){
    //     generate_list();
    // });

    $('.dialed-import').live('click', function () {
        $.ajax({
            url: module.get_value('base_url') + 'employee/manual_dialed_hours_entry/module_import_options',
            data: 'module_id=' + module.get_value('module_id'),
            type: 'post',
            dataType: 'json',
            beforeSend: function(){
                $.blockUI({
                    message: '<div class="now-loading align-center"><img src="'+module.get_value('base_url')+user.get_value('user_theme')+'/images/loading.gif"><br />Loading, please wait...</div>'
                });         
            },
            success: function(data){
                $.unblockUI();
                
                if(data.html != ""){
                    var width = $(window).width()*.2;
                    quickedit_boxy = new Boxy('<div id="boxyhtml" style="width:'+width+'px; height:100px;">'+ data.html +'</div>',
                    {
                        title: 'Import',
                        draggable: false,
                        modal: true,
                        center: true,
                        unloadOnHide: true,
                        beforeUnload: function (){
                            $('.tipsy').remove();
                        }
                    });
                    boxyHeight(quickedit_boxy, '#boxyhtml');
                }
            }
        });
    });

    $('#import-form').die('submit');

    $('#campaign_id').chosen();
});

grid_resize('jqgridcontainer');
function gridResize_jqgridcontainer() {
    $("#jqgridcontainer").jqGrid("setGridWidth", $("#body-content-wrap").width() );
}
$(window).resize(gridResize_jqgridcontainer); 

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

// function export_list(){
//     var sortColumnName = $("#jqgridcontainer").jqGrid('getGridParam','sortname');
//     var sortOrder = $("#jqgridcontainer").jqGrid('getGridParam','sortorder');
//     if (sortColumnName != ''){
//         $('#previous_page').append('<input id="sidx" type="hidden" value="'+ sortColumnName +'" name="sidx"><input id="sord" type="hidden" value="'+ sortOrder +'" name="sord">');
//     }
//     $('#export-form').attr('action', $('#export_link').val());
//     $('#export-form').submit();
//     $('#export-form').attr('action', '');
//     return false;
// }

function export_list(){
    if( ( $('#date').val() == "" || $('#date_to').val() == "" ) ) {
        add_error('date', 'Date Period', "This field is mandatory.");      
    } else {
        var data = $('#date, #date_to, #campaign_id').serialize();
        $.ajax({
            url: module.get_value('base_url') +'employee/manual_dialed_hours_entry/export',
            data: data,
            dataType: 'json',
            type: 'post',
            success: function (response) {
                var path = "/"+response.data;
                window.location = module.get_value('base_url')+path;
            }
        });
    }
}

function generate_list(){
    if( ( $('#date').val() == "" ) ){
        add_error('date', 'Date From Period', "This field is mandatory.");
    }
    
    if($('#date_to').val() == "") {
        add_error('date_to', 'Date To Period', "This field is mandatory.");      
    }

    ok_to_save = validate_form();

    if( ok_to_save ){
        list_search_grid( 'jqgridcontainer' );
    }
}

function list_search_grid( jqgridcontainer ){

    $("#"+jqgridcontainer).jqGrid('setGridParam', 
    {
        postData: {
            date : $('#date').val(),
            date_to : $('#date_to').val(),
            campaign_id : $('#campaign_id').val()
        },  
    }).trigger("reloadGrid");

}